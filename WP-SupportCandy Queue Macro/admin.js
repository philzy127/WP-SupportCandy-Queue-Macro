jQuery(document).ready(function($){
    $('#add_status').click(function(){
        $('#available_statuses option:selected').each(function(){
            $(this).remove().appendTo('#selected_statuses');
        });
    });
    $('#remove_status').click(function(){
        $('#selected_statuses option:selected').each(function(){
            $(this).remove().appendTo('#available_statuses');
        });
    });

    $('#scq_test_button').click(function(){
        var resultsContent = $('#scq_test_results_content');
        var resultsContainer = $('#scq_test_results');
        resultsContent.html('<p>Loading...</p>');
        resultsContainer.show();

        $.post(scq_ajax.ajax_url, {
            action: 'scq_test_queues',
            nonce: scq_ajax.nonce
        }, function(response) {
            if (response.success) {
                var html = '<ul>';
                $.each(response.data, function(key, value) {
                    html += '<li><strong>' + key + ':</strong> ' + value + '</li>';
                });
                html += '</ul>';
                resultsContent.html(html);
            } else {
                resultsContent.html('<p>Error: ' + response.data + '</p>');
            }
        });
    });
});
