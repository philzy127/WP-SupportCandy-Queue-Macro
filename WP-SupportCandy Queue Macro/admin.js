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

    // Before submitting the form, select all items in the 'selected' list
    $('#scq_settings_form').submit(function() {
        $('#selected_statuses option').prop('selected', true);
    });
});