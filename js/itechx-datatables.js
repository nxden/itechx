jQuery(document).ready(function($) {
    $('#itechx-stats-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": itechx_stats_ajax.ajax_url,
            "method": "POST",
            "data": function(d) {
                d.action = 'itechx_stats_load_data'; // AJAX действие
                // Добавьте сюда любые дополнительные параметры, если необходимо
            }
        },
        "columns": [
            { "data": "date" },
            { "data": "publisher_id" }, // Новый столбец "Publisher ID"
            { "data": "search_channel" },
            { "data": "country" },
            { "data": "total_searches" },
            { "data": "monetized_searches" },
            { "data": "clicks" },
            { "data": "amount" }
            // Определите другие колонки, если требуется
        ]
    });
});
