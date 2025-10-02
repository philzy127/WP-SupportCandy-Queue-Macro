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
});