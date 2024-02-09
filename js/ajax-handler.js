jQuery(document).ready(function($) {
    $('#itechx-filter-apply').on('click', function() {
        // Сбор данных фильтра
        var data = {
            'action': 'load_stats_data', // Это значение должно совпадать с хуком wp_ajax_{action}
            'nonce': itechx_stats_ajax.security, // Nonce значение, переданное из wp_localize_script
            'date_filter': $('#itechx-date-filter').val(),
            'status_filter': $('#itechx-status-filter').val(),
            'publisher_id_filter': $('#itechx-publisher-id-filter').val(),
            'feed_id_filter': $('#itechx-feed-id-filter').val()
        };

        // AJAX запрос на сервер
        $.post(itechx_stats_ajax.ajax_url, data, function(response) {
            if (response.success) {
                // Обработка полученных данных и обновление таблицы
                console.log(response.data); // Вместо console.log вы будете обновлять данные в таблице
            } else {
                // Обработка ошибок
                console.error('Произошла ошибка при получении данных');
            }
        });
    });
});
