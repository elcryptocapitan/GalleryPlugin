<div class="et_pb_gallery_items">
    <?php
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'orderby' => 'rand'
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo wp_get_attachment_image(get_the_ID(), 'large');
        }
    }
    wp_reset_postdata();
    ?>
</div>