<?php
// wp-content/plugins/itechx-stats/includes/functions.php

/**
 * Функция для обработки и анализа данных из файла CSV
 * @param string $file_path Путь к файлу CSV
 * @return array Обработанные данные
 */
function itechx_stats_process_csv($file_path) {
    $data = [];
    $headers = [];
    $row_count = 0;

    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
            if ($row_count === 0) {
                $headers = $row;
            } else {
                $data[] = array_combine($headers, $row);
            }
            $row_count++;
        }
        fclose($handle);
    }

    return $data;
}

/**
 * Функция для сохранения обработанных данных в базу данных WordPress
 * @param array $data Данные для сохранения
 * @param string $unique_key Уникальный ключ для обновления или вставки данных
 */
function itechx_stats_save_data($data, $unique_key) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'itechx_stats';

    foreach ($data as $data_row) {
        if (isset($data_row[$unique_key])) {
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE $unique_key = %s",
                $data_row[$unique_key]
            ));

            if ($existing_id) {
                $wpdb->update($table_name, $data_row, [$unique_key => $data_row[$unique_key]]);
            } else {
                $wpdb->insert($table_name, $data_row);
            }
        }
    }
}

/**
 * Генерация и отправка образца файла CSV пользователю.
 */
function itechx_stats_generate_sample_csv() {
    $csv_headers = ['Date', 'Publisher ID', 'Search Channel', 'Country', 'Total Searches', 'Monetized Searches', 'Clicks', 'Amount']; // Publisher ID добавлен вторым столбцом
    $sample_data = [
        ['2023-09-26', '777', '1000', 'US', '24863', '7621', '921', '114.80'], // Publisher ID добавлен вторым столбцом
        ['2023-09-26', '777', '1001', 'US', '8202', '908', '125', '10.33'] // Publisher ID добавлен вторым столбцом
    ];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sample.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $csv_headers, ';');
    foreach ($sample_data as $row) {
        fputcsv($output, $row, ';');
    }
    fclose($output);
    exit;
}


/**
 * Регистрация обработчика для скачивания CSV-образца в WordPress.
 */
function itechx_stats_register_download_sample_handler() {
    add_action('admin_post_itechx_stats_download_sample', 'itechx_stats_generate_sample_csv');
}
add_action('admin_init', 'itechx_stats_register_download_sample_handler');

function itechx_stats_load_data_callback() {
    global $wpdb;
    
    // Проверка nonce для безопасности
    if (!check_ajax_referer('itechx_stats_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce verification failed');
        return;
    }

    // Имя вашей таблицы
    $table_name = $wpdb->prefix . 'itechx_stats';

    // Запрос к базе данных для получения данных
    $results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);

    // Логирование данных для отладки
    error_log(print_r($results, true));

    // Подготовка данных для DataTables
    $data = array();
    foreach ($results as $row) {
        $data[] = array(
            'date' => $row['date'],
            'publisher_id' => $row['publisher_id'], // Добавленный столбец 'Publisher ID'
            'search_channel' => $row['search_channel'],
            'country' => $row['country'],
            'total_searches' => $row['total_searches'],
            'monetized_searches' => $row['monetized_searches'],
            'clicks' => $row['clicks'],
            'amount' => $row['amount']
            // Добавьте остальные столбцы по аналогии
        );
    }
// Debug information
echo '<pre>';
var_dump($data);  // выводим содержание обработанного массива данных чтобы проверить его структуру
echo '</pre>';

// Отправка данных
wp_send_json_success(array('data' => $data));

    // Отправка данных
    wp_send_json_success(array('data' => $data));
}
add_action('wp_ajax_itechx_stats_load_data', 'itechx_stats_load_data_callback');
