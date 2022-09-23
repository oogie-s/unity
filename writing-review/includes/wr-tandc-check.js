function tandccheckbox(tandCcheckbox) {
    if (tandCcheckbox.checked) {
        document.getElementById("publishme").disabled = false;
        document.getElementById("publishme").style.color = "black";
    }
    else {
        document.getElementById("publishme").disabled = true;
        document.getElementById("publishme").style.color = "grey";
    }
}