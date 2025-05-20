<?php
/**
 * GPD Business Maps Shortcodes
 *
 * @package GPD_Business_Maps
 * @since 1.0.0
 * @date 2025-05-20
 */

defined('ABSPATH') || exit;

/**
 * GPDBM_Shortcodes Class
 */
class GPDBM_Shortcodes {
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        
        // Register shortcodes
        add_shortcode('gpdbm-map', array($this, 'map_shortcode'));
        add_shortcode('gpdbm-business-map', array($this, 'business_map_shortcode'));
    }

    /**
     * Register scripts and styles
     */
    public function register_assets() {
        // Register Leaflet CSS and JS
        wp_register_style(
            'leaflet', 
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );
        
        wp_register_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );
        
        // Register Leaflet MarkerCluster (for clustering markers)
        wp_register_style(
            'leaflet-markercluster', 
            'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css',
            array('leaflet'),
            '1.4.1'
        );
        
        wp_register_style(
            'leaflet-markercluster-default', 
            'https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css',
            array('leaflet-markercluster'),
            '1.4.1'
        );
        
        wp_register_script(
            'leaflet-markercluster',
            'https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js',
            array('leaflet'),
            '1.4.1',
            true
        );
        
        // Plugin CSS and JS
        wp_register_style(
            'gpdbm-map', 
            GPDBM_PLUGIN_URL . 'assets/css/gpdbm-map.css',
            array('leaflet'),
            GPDBM_VERSION
        );
        
        wp_register_script(
            'gpdbm-map',
            GPDBM_PLUGIN_URL . 'assets/js/gpdbm-map.js',
            array('jquery', 'leaflet', 'leaflet-markercluster'),
            GPDBM_VERSION,
            true
        );
        
        // Localize script with our variables
        wp_localize_script('gpdbm-map', 'gpdbmVars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gpdbm_nonce'),
            'map_tile_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'map_attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        ));
    }

    /**
     * Map shortcode to display one or more businesses on a map
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function map_shortcode($atts) {
        // Enqueue required assets
        wp_enqueue_style('leaflet');
        wp_enqueue_style('leaflet-markercluster');
        wp_enqueue_style('leaflet-markercluster-default');
        wp_enqueue_style('gpdbm-map');
        wp_enqueue_script('gpdbm-map');
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'category' => '',            // Filter by business category slug
            'limit' => 100,              // Maximum number of businesses to show
            'height' => '400px',         // Map height
            'zoom' => 13,                // Default zoom level
            'clustering' => 'true',      // Use marker clustering
            'class' => '',               // Additional CSS class
        ), $atts, 'gpdbm-map');
        
        // Convert string attributes to proper types
        $atts['clustering'] = filter_var($atts['clustering'], FILTER_VALIDATE_BOOLEAN);
        
        // Generate unique ID for this map
        $map_id = 'gpdbm-map-' . uniqid();
        
        // Build CSS classes
        $css_classes = array('gpdbm-map');
        if (!empty($atts['class'])) {
            $css_classes[] = sanitize_html_class($atts['class']);
        }
        
        // Get businesses with location data
        $businesses = $this->get_businesses_with_location_data(
            $atts['limit'], 
            $atts['category']
        );
        
        // Calculate map center if we have businesses
        $center = $this->calculate_map_center($businesses);
        
        // Start output buffering
        ob_start();
        
        if (empty($businesses)) {
            echo '<p class="gpdbm-error">' . esc_html__('No businesses with location data found.', 'gpd-business-maps') . '</p>';
        } else {
            ?>
            <div class="<?php echo esc_attr(implode(' ', $css_classes)); ?>">
                <div id="<?php echo esc_attr($map_id); ?>" style="height: <?php echo esc_attr($atts['height']); ?>"></div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    if (typeof gpdbmInitMap === 'function') {
                        gpdbmInitMap('<?php echo esc_js($map_id); ?>', {
                            center: <?php echo json_encode($center); ?>,
                            zoom: <?php echo intval($atts['zoom']); ?>,
                            clustering: <?php echo $atts['clustering'] ? 'true' : 'false'; ?>,
                            businesses: <?php echo json_encode($businesses); ?>
                        });
                    }
                });
            </script>
            <?php
        }
        
        return ob_get_clean();
    }
    
    /**
     * Business map shortcode to display a specific business on a map
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function business_map_shortcode($atts) {
        // Enqueue required assets
        wp_enqueue_style('leaflet');
        wp_enqueue_style('gpdbm-map');
        wp_enqueue_script('gpdbm-map');
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'id' => 0,                   // Business post ID (defaults to current post)
            'height' => '400px',         // Map height
            'zoom' => 15,                // Default zoom level
            'class' => '',               // Additional CSS class
        ), $atts, 'gpdbm-business-map');
        
        // Get business ID
        $business_id = intval($atts['id']);
        if ($business_id === 0) {
            $business_id = get_the_ID();
        }
        
        // Generate unique ID for this map
        $map_id = 'gpdbm-map-' . uniqid();
        
        // Build CSS classes
        $css_classes = array('gpdbm-map', 'gpdbm-single-business-map');
        if (!empty($atts['class'])) {
            $css_classes[] = sanitize_html_class($atts['class']);
        }
        
        // Get business location data
        $lat = get_post_meta($business_id, '_gpd_latitude', true);
        $lng = get_post_meta($business_id, '_gpd_longitude', true);
        $maps_uri = get_post_meta($business_id, '_gpd_maps_uri', true);
        
        // Start output buffering
        ob_start();
        
        if (empty($lat) || empty($lng)) {
            echo '<p class="gpdbm-error">' . esc_html__('No location data found for this business.', 'gpd-business-maps') . '</p>';
        } else {
            $business = array(
                'id' => $business_id,
                'title' => get_the_title($business_id),
                'lat' => $lat,
                'lng' => $lng,
                'maps_uri' => $maps_uri,
                'permalink' => get_permalink($business_id),
                'address' => get_post_meta($business_id, '_gpd_address', true),
                'thumbnail' => get_the_post_thumbnail_url($business_id, 'thumbnail'),
            );
            
            ?>
            <div class="<?php echo esc_attr(implode(' ', $css_classes)); ?>">
                <div id="<?php echo esc_attr($map_id); ?>" style="height: <?php echo esc_attr($atts['height']); ?>"></div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    if (typeof gpdbmInitMap === 'function') {
                        gpdbmInitMap('<?php echo esc_js($map_id); ?>', {
                            center: {
                                lat: <?php echo floatval($lat); ?>,
                                lng: <?php echo floatval($lng); ?>
                            },
                            zoom: <?php echo intval($atts['zoom']); ?>,
                            clustering: false,
                            businesses: [<?php echo json_encode($business); ?>]
                        });
                    }
                });
            </script>
            <?php
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get businesses with location data
     *
     * @param int $limit Maximum number of businesses to return
     * @param string $category Optional category slug to filter by
     * @return array Businesses with location data
     */
    private function get_businesses_with_location_data($limit = 100, $category = '') {
        $args = array(
            'post_type' => 'business',
            'posts_per_page' => intval($limit),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_gpd_latitude',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => '_gpd_longitude',
                    'compare' => 'EXISTS',
                ),
            ),
        );
        
        // Add category filter if provided
        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'business_category',
                    'field' => 'slug',
                    'terms' => sanitize_title($category),
                ),
            );
        }
        
        $businesses = array();
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $business_id = get_the_ID();
                $lat = get_post_meta($business_id, '_gpd_latitude', true);
                $lng = get_post_meta($business_id, '_gpd_longitude', true);
                $maps_uri = get_post_meta($business_id, '_gpd_maps_uri', true);
                
                if ($lat && $lng) {
                    $businesses[] = array(
                        'id' => $business_id,
                        'title' => get_the_title(),
                        'lat' => $lat,
                        'lng' => $lng,
                        'maps_uri' => $maps_uri,
                        'permalink' => get_permalink(),
                        'address' => get_post_meta($business_id, '_gpd_address', true),
                        'thumbnail' => get_the_post_thumbnail_url($business_id, 'thumbnail'),
                    );
                }
            }
            
            wp_reset_postdata();
        }
        
        return $businesses;
    }
    
    /**
     * Calculate the center point for a group of businesses
     *
     * @param array $businesses Businesses with location data
     * @return array Center coordinates
     */
    private function calculate_map_center($businesses) {
        $default_center = array(
            'lat' => 40.7128, // Default to New York City coordinates
            'lng' => -74.0060,
        );
        
        if (empty($businesses)) {
            return $default_center;
        }
        
        if (count($businesses) === 1) {
            return array(
                'lat' => floatval($businesses[0]['lat']),
                'lng' => floatval($businesses[0]['lng']),
            );
        }
        
        $lat_sum = 0;
        $lng_sum = 0;
        $count = 0;
        
        foreach ($businesses as $business) {
            $lat_sum += floatval($business['lat']);
            $lng_sum += floatval($business['lng']);
            $count++;
        }
        
        return array(
            'lat' => $lat_sum / $count,
            'lng' => $lng_sum / $count,
        );
    }
}

// Initialize shortcodes
new GPDBM_Shortcodes();
