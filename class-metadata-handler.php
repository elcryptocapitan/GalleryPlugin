<?php
class Metadata_Handler {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_import_page'));
        add_action('admin_init', array(__CLASS__, 'handle_metadata_import'));
    }

    public static function add_import_page() {
        add_submenu_page(
            'tools.php',
            'Import Metadata',
            'Import Metadata',
            'manage_options',
            'import-metadata',
            array(__CLASS__, 'render_import_page')
        );
    }

    public static function render_import_page() {
        ?>
        <div class="wrap">
            <h1>Import Image Metadata</h1>
            <?php 
            if (isset($_GET['import_status'])) {
                $status = sanitize_text_field($_GET['import_status']);
                $message = sanitize_text_field(urldecode($_GET['message']));
                $class = ($status === 'success') ? 'notice-success' : 'notice-error';
                ?>
                <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
                    <p><?php echo esc_html($message); ?></p>
                </div>
                <?php
            }
            ?>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('metadata_import_nonce', 'metadata_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="metadata_file">Select JSON File</label></th>
                        <td>
                            <input type="file" name="metadata_file" id="metadata_file" accept=".json">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Import Metadata'); ?>
            </form>
        </div>
        <?php
    }

    public static function handle_metadata_import() {
        if (!isset($_POST['metadata_nonce']) || 
            !wp_verify_nonce($_POST['metadata_nonce'], 'metadata_import_nonce')) {
            return;
        }

        try {
            if (empty($_FILES['metadata_file']['tmp_name'])) {
                throw new Exception('No file uploaded');
            }

            // Read and decode JSON file
            $json = file_get_contents($_FILES['metadata_file']['tmp_name']);
            if ($json === false) {
                throw new Exception('Failed to read uploaded file');
            }

            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format: ' . json_last_error_msg());
            }

            if (!isset($data['metadata']['images'])) {
                throw new Exception('Invalid metadata structure: missing images data');
            }

            $success_count = 0;
            $error_count = 0;
            $processed = array();

            foreach ($data['metadata']['images'] as $filename => $metadata) {
                try {
                    // Find attachment by filename
                    $attachment = self::get_attachment_by_filename($filename);
                    if (!$attachment) {
                        $error_count++;
                        error_log("Image not found: {$filename}");
                        continue;
                    }

                    // Update metadata
                    foreach ($metadata as $key => $value) {
                        if ($value !== 'Unselected') {
                            update_field($key, $value, $attachment->ID);
                        }
                    }

                    $success_count++;
                    $processed[] = $filename;

                } catch (Exception $e) {
                    $error_count++;
                    error_log("Error processing {$filename}: " . $e->getMessage());
                }
            }

            $message = sprintf(
                'Import completed. Successfully processed %d images, %d errors.',
                $success_count,
                $error_count
            );

            wp_redirect(add_query_arg(array(
                'page' => 'import-metadata',
                'import_status' => ($error_count === 0) ? 'success' : 'warning',
                'message' => urlencode($message)
            ), admin_url('tools.php')));
            exit;

        } catch (Exception $e) {
            wp_redirect(add_query_arg(array(
                'page' => 'import-metadata',
                'import_status' => 'error',
                'message' => urlencode($e->getMessage())
            ), admin_url('tools.php')));
            exit;
        }
    }

    private static function get_attachment_by_filename($filename) {
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'title' => pathinfo($filename, PATHINFO_FILENAME)
        );

        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            return $query->posts[0];
        }

        return null;
    }
}