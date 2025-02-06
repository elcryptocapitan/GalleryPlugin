<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up plugin data
delete_option('pfgf_settings');