function edit_row_class(no) 
{
    document.getElementById("edit_button_class"+no).style.display="none";
    document.getElementById("save_button_class"+no).style.display="block";
    document.getElementById("delete_button_class"+no).style.display="none";

    fetch(ajax_object.ajax_url + '?action=load_all_classes')
    .then(response => response.json())
    .then(out => buildClassOptions(out));

    function buildClassOptions(data)
    {
        var className = document.getElementById("class_name" + no);
        var currentClassName = className.innerHTML;
        className.innerHTML = null;
        var classSelect = document.createElement("select");
        classSelect.id = "class"+no;
        classSelect.name = "class"+no;
        classSelect.required = true;
        data.forEach(function (indclass)
        {
            var classOption = document.createElement("option");
            classOption.value = indclass.id;
            classOption.text = indclass.class_name;
            if (indclass.class_name == currentClassName)
            {
                classOption.selected = true;
            }
            classSelect.appendChild(classOption);
        });
        className.appendChild(classSelect);
    }

    fetch(ajax_object.ajax_url + '?action=load_all_days')
    .then(response => response.json())
    .then(out => buildDayOptions(out));

    function buildDayOptions(data)
    {
        var day = document.getElementById("day"+no);
        var currentDay = day.innerHTML;
        day.innerHTML = null;
        var daySelect = document.createElement("select");
        daySelect.id = "new_day"+no;
        daySelect.name = "new_day"+no;
        daySelect.required = true;
        data.forEach(function (indDay)
        {
            var dayOption = document.createElement("option");
            dayOption.value = indDay.id;
            dayOption.text = indDay.day;
            if (indDay.day == currentDay)
            {
                dayOption.selected = true;
            }
            daySelect.appendChild(dayOption);
        });
        day.appendChild(daySelect);
    }

    fetch(ajax_object.ajax_url + '?action=load_all_locations')
    .then(response => response.json())
    .then(out => buildLocationOptions(out));

    function buildLocationOptions(data)
    {
        var location = document.getElementById("location"+no);
        var currentLocation = location.innerHTML;
        location.innerHTML = null;
        var locationSelect = document.createElement("select");
        locationSelect.id = "new_location"+no;
        locationSelect.name = "new_location"+no;
        locationSelect.required = true;
        data.forEach(function (indLocation)
        {
            var locationOption = document.createElement("option");
            locationOption.value = indLocation.id;
            locationOption.text = indLocation.location.replace("\\", "");            
            if (indLocation.location.replace("\\", "") == currentLocation)
            {
                locationOption.selected = true;
            }
            locationSelect.appendChild(locationOption);
        });
        location.appendChild(locationSelect);
    }

    var max_students = document.getElementById("max_students"+no);
    var class_time = document.getElementById("class_time"+no);

    var max_students_data=max_students.innerHTML;
    var class_time_data=class_time.innerHTML;

    max_students.innerHTML="<input type='text' class='max_students_text' id='max_students_text"+no+"' value='"+max_students_data+"'>";
    class_time.innerHTML="<input type='text' id='class_time_text"+no+"' value='"+class_time_data+"'>";
}

function save_row_class(no){
    var class_name_val=document.getElementById("class"+no).value;
    var max_students_val=document.getElementById("max_students_text"+no).value;
    var day_val=document.getElementById("new_day"+no).value;
    var class_time_val=document.getElementById("class_time_text"+no).value;
    var location_val=document.getElementById("new_location"+no).value;

    fetch(ajax_object.ajax_url + '?action=save_row_class', {
        method: "POST",
        body: JSON.stringify({ID:no,
                              class:class_name_val,
                              max_students:max_students_val,
                              day:day_val,
                              time:class_time_val,
                              location:location_val}),

    }).then(res => {
        console.log("Current class updated:", res);
    })
    window.location.reload();

}

function delete_row_class(no)
{
    fetch(ajax_object.ajax_url + '?action=delete_curr_class', {
        method: "POST",
        body: JSON.stringify({ID:no}),

    }).then(res => {
        console.log("Current class deleted:", res);
    })
    window.location.reload();
}

function edit_row_location(no) 
{
    document.getElementById("edit_button_location"+no).style.display="none";
    document.getElementById("save_button_location"+no).style.display="block";
    document.getElementById("delete_button_location"+no).style.display="none";

    var location_name = document.getElementById("location"+no);
    var address = document.getElementById("address"+no);
    var postcode = document.getElementById("postcode"+no);
        
    var location_data=location_name.innerHTML;
    var address_data=address.innerHTML;
    var postcode_data=postcode.innerHTML;
    location_name.innerHTML="<input type='text' class='location_text' id='location_text"+no+"' value='"+location_data+"'>";
    address.innerHTML="<input type='text' class='location_address_text' id='address_text"+no+"' value='"+address_data+"'>";
    postcode.innerHTML="<input type='text' class='postcode_text' id='postcode_text"+no+"' value='"+postcode_data+"'>";
}

function save_row_location(no){
    var location_val=document.getElementById("location_text"+no).value;
    var address_val=document.getElementById("address_text"+no).value;
    var postcode_val=document.getElementById("postcode_text"+no).value;

    fetch(ajax_object.ajax_url + '?action=save_row_location', {
        method: "POST",
        body: JSON.stringify({ID:no,
                              location:location_val,
                              address: address_val,
                              postcode: postcode_val}),

    }).then(res => {
        console.log("Location updated:", res);
    })
    window.location.reload();

}

function delete_row_location(no)
{
    fetch(ajax_object.ajax_url + '?action=delete_curr_location', {
        method: "POST",
        body: JSON.stringify({ID:no}),

    }).then(res => {
        console.log("Current location deleted:", res);
    })
    window.location.reload();
}

function edit_row_class_type(no) 
{
    document.getElementById("edit_button_class_type"+no).style.display="none";
    document.getElementById("save_button_class_type"+no).style.display="block";
    document.getElementById("delete_button_class_type"+no).style.display="none";

    var class_name = document.getElementById("class_name"+no);
    var min_age = document.getElementById("min_age"+no);
    var max_age = document.getElementById("max_age"+no);
        
    var class_name_data=class_name.innerHTML;
    var min_age_data=min_age.innerHTML;
    var max_age_data=max_age.innerHTML;
    class_name.innerHTML="<input type='text' id='class_name_text"+no+"' value='"+class_name_data+"'>";
    min_age.innerHTML="<input type='text' id='min_age_text"+no+"' value='"+min_age_data+"'>";
    max_age.innerHTML="<input type='text' id='max_age_text"+no+"' value='"+max_age_data+"'>";
}

function save_row_class_type(no){
    var class_name_val=document.getElementById("class_name_text"+no).value;
    var min_age_val=document.getElementById("min_age_text"+no).value;
    var max_age_val=document.getElementById("max_age_text"+no).value;

    fetch(ajax_object.ajax_url + '?action=save_row_class_type', {
        method: "POST",
        body: JSON.stringify({ID:no,
                              class_name:class_name_val,
                              min_age: min_age_val,
                              max_age: max_age_val}),

    }).then(res => {
        console.log("Class type updated:", res);
    })
    window.location.reload();

}

function delete_row_class_type(no)
{
    fetch(ajax_object.ajax_url + '?action=delete_class_type', {
        method: "POST",
        body: JSON.stringify({ID:no}),

    }).then(res => {
        console.log("Class type deleted:", res);
    })
    window.location.reload();
}

function delete_row_date(no)
{
    fetch(ajax_object.ajax_url + '?action=delete_date', {
        method: "POST",
        body: JSON.stringify({ID:no}),

    }).then(res => {
        console.log("Date deleted:", res);
    })
    window.location.reload();
}