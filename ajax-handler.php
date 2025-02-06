<?php
add_action('wp_ajax_filter_gallery', 'handle_gallery_filter');
add_action('wp_ajax_nopriv_filter_gallery', 'handle_gallery_filter');

function handle_gallery_filter() {
    check_ajax_referer('gallery-filter-nonce', 'security');

    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $response = array(
        'images' => array(),
        'valid_combinations' => array()
    );

    try {
        $images = get_filtered_images($filters);
        $response['images'] = $images;
        $response['valid_combinations'] = get_valid_combinations($images);
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }

    wp_send_json_success($response);
}

function get_filtered_images($filters) {
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'meta_query' => array()
    );

    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            $args['meta_query'][] = array(
                'key' => $key,
                'value' => $value,
                'compare' => in_array($key, array('upgrades', 'yard_features')) ? 'LIKE' : '='
            );
        }
    }

    $query = new WP_Query($args);
    $images = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $images[] = array(
                'id' => get_the_ID(),
                'url' => wp_get_attachment_url(get_the_ID()),
                'title' => get_the_title(),
                'metadata' => get_post_meta(get_the_ID())
            );
        }
    }

    wp_reset_postdata();
    return $images;
}

function get_valid_combinations($images) {
    $combinations = array();
    
    foreach ($images as $image) {
        foreach ($image['metadata'] as $key => $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $combinations[$key][] = $value;
                }
            } else {
                $combinations[$key][] = $values;
            }
        }
    }
    
    foreach ($combinations as $key => $values) {
        $combinations[$key] = array_unique($values);
    }
    
    return $combinations;
}