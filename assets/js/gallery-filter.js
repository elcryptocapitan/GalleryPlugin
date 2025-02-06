jQuery(document).ready(function($) {
    // Track the original gallery HTML
    let originalGalleryHTML = $('.et_pb_gallery_items').html();
    let ajaxRequest = null;

    // Initialize collapsible sections
    $('.filter-group').each(function() {
        $(this).addClass('collapsed');
        if ($(this).find('.filter-group-content').length === 0) {
            $(this).find('.filter-row').wrap('<div class="filter-group-content"></div>');
        }
    });

    // Collapse/expand logic
    $('.filter-group-title').click(function() {
        const $group = $(this).closest('.filter-group');
        const $content = $group.find('.filter-group-content');
        
        if (!$group.hasClass('collapsed')) {
            $group.addClass('collapsed');
            $content.css('max-height', '0');
            return;
        }

        $('.filter-group').not($group).addClass('collapsed')
            .find('.filter-group-content').css('max-height', '0');
        
        $group.removeClass('collapsed');
        $content.css('max-height', $content[0].scrollHeight + 'px');
    });

    // Get selected filters
    function getSelectedFilters() {
        const filters = {};
        $('.filter-option input[type="radio"]:checked').each(function() {
            filters[$(this).attr('name')] = $(this).val().trim();
        });
        return filters;
    }

    // Update available options
    function updateAvailableOptions(validCombinations) {
        const selectedFilters = getSelectedFilters();
        
        $('.filter-option').each(function() {
            const $input = $(this).find('input[type="radio"]');
            const filterKey = $input.attr('name');
            const value = $input.val().trim();

            if ($input.prop('checked')) {
                $(this).show();
                return;
            }

            const isValid = !validCombinations[filterKey] || validCombinations[filterKey].includes(value);
            $(this).toggle(isValid);

            if (!isValid && $input.prop('checked')) {
                $input.prop('checked', false);
            }
        });

        $('.filter-group').each(function() {
            const $visible = $(this).find('.filter-option:visible');
            $(this).toggle($visible.length > 0);
        });
    }

    // Update gallery via AJAX
    function updateGallery(isReset = false) {
        if (ajaxRequest) ajaxRequest.abort();
        
        const $galleryGrid = $('.et_pb_gallery_items');
        $galleryGrid.css('opacity', '0.5');
        
        ajaxRequest = $.ajax({
            url: galleryFilterVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_gallery',
                filters: getSelectedFilters(),
                nonce: galleryFilterVars.nonce
            },
            success: (response) => {
                if (response.success) {
                    if (isReset) {
                        restoreOriginalGallery();
                        originalGalleryHTML = $galleryGrid.html();
                    } else {
                        updateGalleryGrid(response.data.images);
                    }
                    updateAvailableOptions(response.data.valid_combinations);
                }
                $galleryGrid.css('opacity', '1');
            },
            error: (xhr, status, error) => {
                console.error('Error:', error);
                $galleryGrid.css('opacity', '1');
            }
        });
    }

    // Restore original gallery
    function restoreOriginalGallery() {
        const $galleryGrid = $('.et_pb_gallery_items');
        $galleryGrid.html(originalGalleryHTML);
        // Reinitialize any third-party scripts here
    }

    // Update gallery grid
    function updateGalleryGrid(images) {
        const $galleryGrid = $('.et_pb_gallery_items');
        $galleryGrid.fadeOut(300, () => {
            $galleryGrid.empty();
            
            if (images.length === 0) {
                $galleryGrid.append('<p class="no-results">No matching images found.</p>');
            } else {
                const fragment = document.createDocumentFragment();
                images.forEach(image => {
                    const $item = $(/* sanitized HTML template */);
                    fragment.appendChild($item[0]);
                });
                $galleryGrid.append(fragment);
            }
            
            $galleryGrid.fadeIn(300);
        });
    }

    // Event handlers
    let timeout;
    $('.filter-option input[type="radio"]').change(() => {
        clearTimeout(timeout);
        timeout = setTimeout(() => updateGallery(false), 300);
    });

    $('#clear-filters').click(() => {
        $('.filter-option input[type="radio"]').prop('checked', false);
        $('.filter-option, .filter-group').show();
        updateGallery(true);
    });

    // Initial setup
    $('.filter-group').addClass('collapsed')
        .find('.filter-group-content').css('max-height', '0');
    
    const $firstGroup = $('.filter-group:first');
    $firstGroup.removeClass('collapsed')
        .find('.filter-group-content').css('max-height', 'auto');

    updateGallery(false);
});
