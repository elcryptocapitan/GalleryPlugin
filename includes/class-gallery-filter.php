<?php
class Gallery_Filter {
    public static function init() {
        $self = new self();
        add_action('wp_enqueue_scripts', array($self, 'enqueue_assets'));
        add_shortcode('pool_fence_gallery', array($self, 'render_gallery'));
    }

    public static function activate() {
        // Activation logic (e.g., create database tables)
    }

    public static function deactivate() {
        // Deactivation logic
    }

    public function enqueue_assets() {
        if ($this->is_gallery_page()) {
            wp_enqueue_style(
                'gallery-filter-css',
                PFGF_PLUGIN_URL . 'assets/css/gallery-filter.css',
                array(),
                PFGF_VERSION
            );

            wp_enqueue_script(
                'gallery-filter-js',
                PFGF_PLUGIN_URL . 'assets/js/gallery-filter.js',
                array('jquery'),
                PFGF_VERSION,
                true
            );

            wp_localize_script('gallery-filter-js', 'galleryFilterVars', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gallery-filter-nonce')
            ));
        }
    }

    private function is_gallery_page() {
        global $post;
        return (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'pool_fence_gallery'));
    }

    public function render_gallery() {
        ob_start();
        include PFGF_PLUGIN_DIR . 'templates/gallery-filters.php';
        include PFGF_PLUGIN_DIR . 'templates/gallery-grid.php';
        return ob_get_clean();
    }
}
