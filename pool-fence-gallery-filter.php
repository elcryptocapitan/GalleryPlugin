<?php
/*
Plugin Name: Pool Fence Gallery Filter
Description: Advanced gallery filter with metadata support for franchise websites.
Version: 1.0
Author: Your Name
Text Domain: pool-fence-gallery-filter
Domain Path: /languages
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PFGF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PFGF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PFGF_VERSION', '1.0');

// Load translation files
add_action('plugins_loaded', 'pfgf_load_textdomain');
function pfgf_load_textdomain() {
    load_plugin_textdomain(
        'pool-fence-gallery-filter',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}

// Load dependencies
require_once PFGF_PLUGIN_DIR . 'includes/class-gallery-filter.php';
require_once PFGF_PLUGIN_DIR . 'includes/class-metadata-handler.php';
require_once PFGF_PLUGIN_DIR . 'includes/ajax-handler.php';
require_once PFGF_PLUGIN_DIR . 'includes/acf-fields.php';

// Initialize the plugin
add_action('plugins_loaded', array('Gallery_Filter', 'init'));
add_action('plugins_loaded', array('Metadata_Handler', 'init'));

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('Gallery_Filter', 'activate'));
register_deactivation_hook(__FILE__, array('Gallery_Filter', 'deactivate'));
