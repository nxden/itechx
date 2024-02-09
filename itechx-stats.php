<?php
/**
 * Plugin Name: iTechX Stats
 * Description: Custom statistics management for iTechX.
 * Version: 1.0
 * Author: Denis Antonov
 */

// Register activation hook
register_activation_hook( __FILE__, 'itechx_stats_create_table' );

// Enqueue styles and scripts
function itechx_stats_enqueue_scripts($hook) {
    if ('toplevel_page_itechx-stats' !== $hook && 'itechx-stats_page_itechx-stats-front' !== $hook) {
        return;
    }

    wp_enqueue_style('itechx-stats-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('itechx-stats-script', plugins_url('js/script.js', __FILE__));
    wp_enqueue_style('datatables-style', 'https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css');
    wp_enqueue_script('datatables-script', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', array('jquery'));
    wp_enqueue_script('itechx-datatables-script', plugins_url('js/itechx-datatables.js', __FILE__), array('jquery', 'datatables-script'));
    wp_enqueue_script('itechx-ajax-handler', plugins_url('js/ajax-handler.js', __FILE__), array('jquery'), null, true);

    $nonce = wp_create_nonce('itechx_stats_nonce');
    wp_localize_script('itechx-ajax-handler', 'itechx_stats_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => $nonce
    ));
}

add_action('admin_enqueue_scripts', 'itechx_stats_enqueue_scripts');


// Include additional PHP files
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// Create plugin settings page in the admin panel
function itechx_stats_menu() {
    // Add main menu item
    add_menu_page(
        'iTechX Stats', // Menu title
        'iTechX Stats', // Menu title
        'manage_options', // Capability required to access the menu
        'itechx-stats', // Menu slug
        'itechx_stats_upload_page_callback', // Function to display page content
        'dashicons-chart-area', // Menu icon
        6 // Menu position
    );

    // Add submenu item "Upload data"
    add_submenu_page(
        'itechx-stats', // Parent menu slug
        'Upload data', // Page title
        'Upload data', // Menu title
        'manage_options', // Capability required to access the menu
        'itechx-stats', // Submenu slug
        'itechx_stats_upload_page_callback' // Function to display page content
    );

    // Add submenu item "Front"
    add_submenu_page(
        'itechx-stats', // Parent menu slug
        'Front', // Page title
        'Front', // Menu title
        'manage_options', // Capability required to access the menu
        'itechx-stats-front', // Submenu slug
        'itechx_stats_front_page_callback' // Function to display page content
    );
}
add_action('admin_menu', 'itechx_stats_menu');

// HTML for upload page
function itechx_stats_upload_page_callback() {
    ?>
    <div class="wrap">
        <h2>Upload data</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" />
            <input type="submit" value="Upload" name="submit_csv">
        </form>

        <!-- Button to download CSV sample -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="itechx_stats_download_sample">
            <input type="submit" value="Download sample CSV">
        </form>
    </div>
    <?php
}

// Check and handle POST request
// Проверка и обработка POST запроса
function itechx_stats_handle_post() {
    if (isset($_POST['submit_csv']) && !empty($_FILES['csv_file'])) {
        $file = $_FILES['csv_file'];

        // Проверка на ошибки при загрузке файла
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="error"><p>Error uploading CSV file. Please try again.</p></div>';
            return;
        }

        // Проверка типа файла (можно добавить другие проверки, например, размер файла)
        $allowed_types = array('text/csv', 'application/vnd.ms-excel');
        if (!in_array($file['type'], $allowed_types)) {
            echo '<div class="error"><p>Invalid file type. Please upload a CSV file.</p></div>';
            return;
        }

        // Открываем файл с правильным разделителем
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            echo '<div class="error"><p>Error opening CSV file. Please try again.</p></div>';
            return;
        }

        // Проверка соответствия столбцов
        $expected_columns = array('Date', 'Publisher ID', 'Search Channel', 'Country', 'Total Searches', 'Monetized Searches', 'Clicks', 'Amount');
        $csv_columns = fgetcsv($handle, 0, ";"); // Здесь указываем разделитель
        fclose($handle);
        if ($csv_columns !== $expected_columns) {
            echo '<div class="error"><p>Invalid CSV file format. Please make sure the file contains the correct columns: Date, Publisher ID, Search Channel, Country, Total Searches, Monetized Searches, Clicks, Amount.</p></div>';
            return;
        }

        // Вызываем функцию для обработки CSV только если загружен файл
        $csv_data = itechx_stats_process_csv($file['tmp_name']);
        if ($csv_data) {
            echo '<div class="updated"><p>CSV file uploaded successfully.</p></div>';
        } else {
            echo '<div class="error"><p>Error processing CSV file.</p></div>';
        }
    }
}
add_action('admin_init', 'itechx_stats_handle_post');



// Register download handler for sample CSV, function itechx_stats_generate_sample_csv should be defined in 'includes/functions.php'
function itechx_stats_register_download_handler() {
    add_action('admin_post_itechx_stats_download_sample', 'itechx_stats_generate_sample_csv');
}
add_action('admin_init', 'itechx_stats_register_download_handler');

// Activation hook function
function itechx_stats_activate() {
    itechx_stats_create_table();
}
register_activation_hook(__FILE__, 'itechx_stats_activate');

// Function to create database table
function itechx_stats_create_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'itechx_stats';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        public_id mediumint(9) NOT NULL,
        search_channel mediumint(9) NOT NULL,
        country varchar(2) NOT NULL,
        total_searches bigint(20) NOT NULL,
        monetized_searches bigint(20) NOT NULL,
        clicks bigint(20) NOT NULL,
        amount decimal(10,2) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

