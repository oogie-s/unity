jQuery(document).ready(function($){
    $("#class").change(function(){
        const $classid = $("#class").val();
        $.ajax({
            url: ajax_object.ajax_url + '?action=load_dates',
            method: 'post',
            data: 'classid=' + $classid
        }).done(function(dates){
            $('#date').empty();
            dates.forEach(function(date){
                $('#date').append('<option>' + date.date + '</option>');
            });
            $("#date").prop('selectedIndex', 0);
        });
    });
});