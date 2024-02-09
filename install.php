<?php
// wp-content/plugins/itechx-stats/install.php

// Функция для создания таблицы при активации плагина
function itechx_stats_create_table() {
    global $wpdb;

    $table_name = 'itechx_stats';

    // SQL запрос для создания таблицы
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        publisher_id varchar(255) NOT NULL,
        search_channel varchar(255) NOT NULL,
        country varchar(255) NOT NULL,
        total_searches int(11) NOT NULL,
        monetized_searches int(11) NOT NULL,
        clicks int(11) NOT NULL,
        amount float NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    // Запуск SQL запроса
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Добавление нового столбца publisher_id, если его еще нет
    $wpdb->query("ALTER TABLE $table_name ADD COLUMN publisher_id varchar(255) NOT NULL;");
}

// Регистрация функции создания таблицы при активации плагина
register_activation_hook(__FILE__, 'itechx_stats_create_table');
