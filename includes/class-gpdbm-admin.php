<?php
/**
 * GPD Business Maps Admin
 *
 * @package GPD_Business_Maps
 * @since 1.0.0
 * @date 2025-05-20
 */

defined('ABSPATH') || exit;

/**
 * GPDBM_Admin Class
 */
class GPDBM_Admin {
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
        // Add shortcode docs to GPD documentation page if it exists
        add_action('gdp_docs_after_shortcodes', array($this, 'add_map_shortcode_docs'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=business',
            __('Business Maps', 'gpd-business-maps'),
            __('Business Maps', 'gpd-business-maps'),
            'manage_options',
            'gpdbm-maps',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('GPD Business Maps', 'gpd-business-maps'); ?></h1>
            
            <div class="card">
                <h2><?php echo esc_html__('Map Shortcodes', 'gpd-business-maps'); ?></h2>
                <p><?php echo esc_html__('Use these shortcodes to display business maps on your site.', 'gpd-business-maps'); ?></p>
                
                <h3><?php echo esc_html__('All Businesses Map', 'gpd-business-maps'); ?></h3>
                <code>[gpdbm-map]</code>
                
                <h3><?php echo esc_html__('Single Business Map', 'gpd-business-maps'); ?></h3>
                <code>[gpdbm-business-map id="123"]</code>
                
                <p><?php echo esc_html__('See the documentation for more shortcode options.', 'gpd-business-maps'); ?></p>
            </div>
            
            <div class="card">
                <h2><?php echo esc_html__('Shortcode Parameters', 'gpd-business-maps'); ?></h2>
                
                <h3><?php echo esc_html__('[gpdbm-map] Parameters', 'gpd-business-maps'); ?></h3>
                <table class="widefat" style="width: 95%">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Parameter', 'gpd-business-maps'); ?></th>
                            <th><?php echo esc_html__('Description', 'gpd-business-maps'); ?></th>
                            <th><?php echo esc_html__('Default', 'gpd-business-maps'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>category</code></td>
                            <td><?php echo esc_html__('Filter by business category slug', 'gpd-business-maps'); ?></td>
                            <td><em><?php echo esc_html__('empty (all categories)', 'gpd-business-maps'); ?></em></td>
                        </tr>
                        <tr>
                            <td><code>limit</code></td>
                            <td><?php echo esc_html__('Maximum number of businesses to display', 'gpd-business-maps'); ?></td>
                            <td>100</td>
                        </tr>
                        <tr>
                            <td><code>height</code></td>
                            <td><?php echo esc_html__('Height of the map', 'gpd-business-maps'); ?></td>
                            <td>400px</td>
                        </tr>
                        <tr>
                            <td><code>zoom</code></td>
                            <td><?php echo esc_html__('Initial zoom level (1-20)', 'gpd-business-maps'); ?></td>
                            <td>13</td>
                        </tr>
                        <tr>
                            <td><code>clustering</code></td>
                            <td><?php echo esc_html__('Group nearby markers together', 'gpd-business-maps'); ?></td>
                            <td>true</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3><?php echo esc_html__('[gpdbm-business-map] Parameters', 'gpd-business-maps'); ?></h3>
                <table class="widefat" style="width: 95%">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Parameter', 'gpd-business-maps'); ?></th>
                            <th><?php echo esc_html__('Description', 'gpd-business-maps'); ?></th>
                            <th><?php echo esc_html__('Default', 'gpd-business-maps'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>id</code></td>
                            <td><?php echo esc_html__('The business post ID', 'gpd-business-maps'); ?></td>
                            <td>0 (current post)</td>
                        </tr>
                        <tr>
                            <td><code>height</code></td>
                            <td><?php echo esc_html__('Height of the map', 'gpd-business-maps'); ?></td>
                            <td>400px</td>
                        </tr>
                        <tr>
                            <td><code>zoom</code></td>
                            <td><?php echo esc_html__('Initial zoom level (1-20)', 'gpd-business-maps'); ?></td>
                            <td>15</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2><?php echo esc_html__('Example Usage', 'gpd-business-maps'); ?></h2>
                
                <h3><?php echo esc_html__('Display all restaurant businesses on a 500px tall map', 'gpd-business-maps'); ?></h3>
                <code>[gpdbm-map category="restaurants" height="500px" zoom="14" clustering="true"]</code>
                
                <h3><?php echo esc_html__('Display a single business on a map', 'gpd-business-maps'); ?></h3>
                <code>[gpdbm-business-map id="123" height="350px" zoom="16"]</code>
                
                <h3><?php echo esc_html__('Display the current business on a map (for single business templates)', 'gpd-business-maps'); ?></h3>
                <code>[gpdbm-business-map]</code>
            </div>
        </div>
        <?php
    }

    /**
     * Add map shortcode documentation to the GPD Docs page
     */
    public function add_map_shortcode_docs() {
        ?>
        <div class="gpd-docs-section">
            <h2><?php echo esc_html__('Business Maps (GPD Business Maps Plugin)', 'gpd-business-maps'); ?></h2>
            <p><?php echo esc_html__('Use these shortcodes to display businesses on interactive maps.', 'gpd-business-maps'); ?></p>
            
            <h3><?php echo esc_html__('All Businesses Map', 'gpd-business-maps'); ?></h3>
            <p><?php echo esc_html__('The [gpdbm-map] shortcode displays multiple businesses on a map.', 'gpd-business-maps'); ?></p>
            
            <table class="widefat" style="width: 95%">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Parameter', 'gpd-business-maps'); ?></th>
                        <th><?php echo esc_html__('Description', 'gpd-business-maps'); ?></th>
                        <th><?php echo esc_html__('Default', 'gpd-business-maps'); ?></th>
                        <th><?php echo esc_html__('Options', 'gpd-business-maps'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>category</code></td>
                        <td><?php echo esc_html__('Filter by business category slug', 'gpd-business-maps'); ?></td>
                        <td><em><?php echo esc_html__('empty', 'gpd-business-maps'); ?></em></td>
                        <td><?php echo esc_html__('Any valid category slug', 'gpd-business-maps'); ?></td>
                    </tr>
                    <tr>
                        <td><code>limit</code></td>
                        <td><?php echo esc_html__('Maximum number of businesses to display', 'gpd-business-maps'); ?></td>
                        <td>100</td>
                        <td><?php echo esc_html__('Any positive number', 'gpd-business-maps'); ?></td>
                    </tr>
                    <tr>
                        <td><code>height</code></td>
                        <td><?php echo esc_html__('Height of the map', 'gpd-business-maps'); ?></td>
                        <td>400px</td>
                        <td><?php echo esc_html__('Any valid CSS height value', 'gpd-business-maps'); ?></td>
                    </tr>
                    <tr>
                        <td><code>zoom</code></td>
                        <td><?php echo esc_html__('Initial zoom level', 'gpd-business-maps'); ?></td>
                        <td>13</td>
                        <td>1-20</td>
                    </tr>
                    <tr>
                        <td><code>clustering</code></td>
                        <td><?php echo esc_html__('Group nearby markers together', 'gpd-business-maps'); ?></td>
                        <td>true</td>
                        <td>true, false</td>
                    </tr>
                    <tr>
                        <td><code>class</code></td>
                        <td><?php echo esc_html__('Additional CSS class', 'gpd-business-maps'); ?></td>
                        <td><em><?php echo esc_html__('empty', 'gpd-business-maps'); ?></em></td>
                        <td><?php echo esc_html__('Any valid CSS class name', 'gpd-business-maps'); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="gpd-shortcode-example">[gpdbm-map category="restaurants" height="500px" zoom="14"]</div>
            
            <h3><?php echo esc_html__('Single Business Map', 'gpd-business-maps'); ?></h3>
            <p><?php echo esc_html__('The [gpdbm-business-map] shortcode displays a single business on a map.', 'gpd-business-maps'); ?></p>
            
            <table class="widefat" style="width: 95%">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Parameter', 'gpd-business-maps'); ?></th>
                        <th><?php echo esc_html__('Description', 'gpd-business-maps'); ?></th>
                        <th><?php echo esc_html__('Default', 'gpd-business-maps'); ?></th>
                        <th><?php echo esc_html__('Options', 'gpd-business-maps'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>id</code></td>
                        <td><?php echo esc_html__('The business post ID', 'gpd-business-maps'); ?></td>
                        <td>0 (current post)</td>
                        <td><?php echo esc_html__('Any valid business post ID', 'gpd-business-maps'); ?></td>
                    </tr>
                    <tr>
                        <td><code>height</code></td>
                        <td><?php echo esc_html__('Height of the map', 'gpd-business-maps'); ?></td>
                        <td>400px</td>
                        <td><?php echo esc_html__('Any valid CSS height value', 'gpd-business-maps'); ?></td>
                    </tr>
                    <tr>
                        <td><code>zoom</code></td>
                        <td><?php echo esc_html__('Initial zoom level', 'gpd-business-maps'); ?></td>
                        <td>15</td>
                        <td>1-20</td>
                    </tr>
                    <tr>
                        <td><code>class</code></td>
                        <td><?php echo esc_html__('Additional CSS class', 'gpd-business-maps'); ?></td>
                        <td><em><?php echo esc_html__('empty', 'gpd-business-maps'); ?></em></td>
                        <td><?php echo esc_html__('Any valid CSS class name', 'gpd-business-maps'); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="gpd-shortcode-example">[gpdbm-business-map]</div>
            <div class="gpd-shortcode-example">[gpdbm-business-map id="123" height="350px" zoom="16"]</div>
        </div>
        <?php
    }
}

// Initialize admin
new GPDBM_Admin();
