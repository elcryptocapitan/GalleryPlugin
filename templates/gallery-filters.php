<div class="gallery-filters">
    <?php
    $filter_groups = array(
        'Colors' => array(
            'border_color' => array('Black', 'Brown', 'Tan', 'White'),
            'mesh_color' => array('Black', 'Brown', 'White'),
            'pole_color' => array('Black', 'Brown', 'Copper Vein', 'Tan', 'White')
        ),
        // Add other filter groups...
    );

    foreach ($filter_groups as $group_name => $filters) {
        echo '<div class="filter-group">';
        echo '<h3 class="filter-group-title">' . esc_html($group_name) . '</h3>';
        echo '<div class="filter-row">';
        
        foreach ($filters as $filter_key => $options) {
            echo '<div class="filter-section">';
            echo '<h4>' . esc_html(ucfirst(str_replace('_', ' ', $filter_key))) . '</h4>';
            echo '<div class="filter-options">';
            
            foreach ($options as $option) {
                $input_id = sanitize_title("{$filter_key}-{$option}");
                echo '<div class="filter-option">';
                echo '<input type="radio" name="' . esc_attr($filter_key) . '" id="' . esc_attr($input_id) . '" value="' . esc_attr($option) . '">';
                echo '<label for="' . esc_attr($input_id) . '">' . esc_html($option) . '</label>';
                echo '</div>';
            }
            
            echo '</div></div>';
        }
        
        echo '</div></div>';
    }
    ?>
    <button id="clear-filters" class="clear-filters-btn">Clear All Filters</button>
</div>
