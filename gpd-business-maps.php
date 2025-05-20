<?php
/**
 * Plugin Name: GPD Business Maps
 * Plugin URI: https://wordpress.org/plugins/gpd-business-maps/
 * Description: Adds Leaflet-based maps for Google Places Directory. Shows businesses on interactive maps with location pins.
 * Version: 1.0.0
 * Author: TheRev
 * Author URI: https://yoursite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: gpd-business-maps
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Date: 2025-05-20
 * 
 * GPD Business Maps is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main GPD Business Maps Class
 */
class GPD_Business_Maps {
    /**
     * Instance of this class
     *
     * @var object
     */
    private static $instance = null;

    /**
     * The plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Get the singleton instance of this class
     *
     * @return object
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Define constants
     */
    private function define_constants() {
        define('GPDBM_VERSION', $this->version);
        define('GPDBM_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('GPDBM_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('GPDBM_PLUGIN_BASENAME', plugin_basename(__FILE__));
    }

    /**
     * Include required files
     */
    private function includes() {
        // Only include files when GPD is active
        if ($this->is_gpd_active()) {
            require_once GPDBM_PLUGIN_DIR . 'includes/class-gpdbm-shortcodes.php';
            require_once GPDBM_PLUGIN_DIR . 'includes/class-gpdbm-admin.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activation_check'));
        
        add_action('admin_init', array($this, 'check_gpd_dependency'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'gpd-business-maps', 
            false, 
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Check if Google Places Directory is active
     *
     * @return boolean
     */
    public function is_gpd_active() {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        return is_plugin_active('google-places-directory/google-places-directory.php');
    }

    /**
     * Check GPD dependency on activation
     */
    public function activation_check() {
        if (!$this->is_gpd_active()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('GPD Business Maps requires Google Places Directory plugin to be installed and activated.', 'gpd-business-maps'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }
    }

    /**
     * Check if Google Places Directory is active, deactivate if not
     */
    public function check_gpd_dependency() {
        if (!$this->is_gpd_active() && is_plugin_active(plugin_basename(__FILE__))) {
            deactivate_plugins(plugin_basename(__FILE__));
            add_action('admin_notices', array($this, 'disabled_notice'));
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }

    /**
     * Display notice when plugin is deactivated
     */
    public function disabled_notice() {
        echo '<div class="error"><p>' . esc_html__('GPD Business Maps was deactivated because it requires Google Places Directory plugin to be installed and activated.', 'gpd-business-maps') . '</p></div>';
    }
}

/**
 * Start the plugin
 */
function GPD_Business_Maps() {
    return GPD_Business_Maps::instance();
}

// Initialize the plugin
GPD_Business_Maps();
