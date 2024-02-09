<?php
// wp-content/plugins/itechx-stats/includes/admin.php

function itechx_stats_settings_page() {
    ?>
    <div class="wrap">
        <h1>iTechX Stats</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('itechx_stats_options_group');
            do_settings_sections('itechx-stats');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function itechx_stats_register_settings() {
    register_setting('itechx_stats_options_group', 'itechx_stats_option_name'); // Группа настроек
    // Добавление других настроек и полей можно сделать здесь
}
add_action('admin_init', 'itechx_stats_register_settings');

function itechx_stats_view_page_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'itechx_stats'; // используйте $wpdb->prefix для получения правильного имени таблицы

    $results = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY date DESC", ARRAY_A);

    echo '<div class="wrap"><h2>View Statistics</h2><table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Date</th><th>Search Channel</th><th>Country</th><th>Total Searches</th><th>Monetized Searches</th><th>Clicks</th><th>Amount</th></tr></thead><tbody>';

    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . $row['date'] . '</td>';
        echo '<td>' . $row['search_channel'] . '</td>';
        echo '<td>' . $row['country'] . '</td>';
        echo '<td>' . $row['total_searches'] . '</td>';
        echo '<td>' . $row['monetized_searches'] . '</td>';
        echo '<td>' . $row['clicks'] . '</td>';
        echo '<td>' . $row['amount'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

function itechx_stats_front_page_callback() {
    ?>
    <div class="wrap">
        <h1>Front Page Settings</h1>
        <p>Statistics for Administrators.</p>
        
        <table id="itechx_stats" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Publisher ID</th> <!-- Added column -->
                    <th>Search Channel</th>
                    <th>Country</th>
                    <th>Total Searches</th>
                    <th>Monetized Searches</th>
                    <th>Clicks</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Table content will be added via AJAX -->
            </tbody>
        </table>
    </div>
    <?php
}
