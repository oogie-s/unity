jQuery(document).ready(function($){
    $("#birthday").change(function(){
        const dob = $("#birthday").val();
        $.ajax({
            url: ajax_object.ajax_url + '?action=load_classes',
            method: 'post',
            data: 'birthday=' + dob
        }).done(function(classes){
            $('#class').empty();
            classes.forEach(function(indclass){
                console.log( indclass );
                $('#class').append('<option value='+indclass.id+'>' + indclass.class_name +' - '+ indclass.day + ' ' + indclass.class_time + '-' + indclass.location.replace("\\", "")+ '</option>');
            }
            );
            $("#class").prop('selectedIndex', 0);
            $("#class").trigger('change');
        });
    });
});