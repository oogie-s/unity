<div class='wrap'>
    <h1>Writing Review Settings</h1> 

    <!--Code to update how many reviews can be open at a time -->
    <?php
global $wpdb;
if (isset($_POST['update_review_no']))
{
    //If the button is pressed then assign that particular review
    $query_update_max_reviews = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_settings SET contents = %d 
            WHERE name='MAX_REVIEWS';", $_POST['review_no']);
    $wpdb->query($query_update_max_reviews);
}

if (isset($_POST['update_tandc']))
{
    //If the button is pressed then assign that particular review
    $query_update_tandc = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_settings SET contents = %s
            WHERE name='TERMS_AND_CONDITIONS';", $_POST['tandc']);
    $wpdb->query($query_update_tandc);
}
?>

    <form action='' method='POST'>
        <label for="review_no">How many reviews can a user have open:</label>
        <?php
$max_review = $wpdb->get_var($wpdb->prepare("SELECT CONTENTS FROM " . $wpdb->prefix . "wr_settings WHERE name='MAX_REVIEWS'"));
?>
        <input type="text" id="review_no" name="review_no" value=<?php echo $max_review ?>></input>
        <button type='submit' name='update_review_no'>Update</button>
    </form>
    <br>
    <form action='' method='POST'>
        <label for="tandc">Terms and Conditions as displayed when asking if work can be published:</label>
        <?php
$tandc = $wpdb->get_var($wpdb->prepare("SELECT CONTENTS FROM " . $wpdb->prefix . "wr_settings WHERE name='TERMS_AND_CONDITIONS'"));
?>
        <br>
        <textarea id="tandc" name="tandc" rows="80" cols="190"><?php echo $tandc ?></textarea>
        <br>
        <button type='submit' name='update_tandc'>Update</button>
    </form>
</div>
