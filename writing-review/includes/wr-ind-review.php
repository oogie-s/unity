<div class="wrap">           
    <h1>Writing Review</h1> 

    <!-- Different permissions. 
            0- Permission not asked
            1- Permission asked
            2- Allowed to publish
            3- Not allowed to publish
            4- Published
    -->

    <?php
global $wpdb;
global $review_id;
global $current_user;
global $review_id;
//global $doc_userloc;
global $cust_info;
global $cust_id;

if (isset($_POST['id']))
{
    //Retrieve query ID
    $review_id = $_POST['id'];
}
//Check if the review ID is set. If not give a box to type in review ID
else if (isset($_GET['id']))
{
    //Retrieve query ID
    $review_id = $_GET['id'];
}

if (isset($_POST['submit']))
{
    wp_upload_bits($_FILES['fileToUpload']['name'], null, file_get_contents($_FILES['fileToUpload']['tmp_name']));

    //Get the user directory and url for the user whose work you are reviewing
    $upload_dir = wp_upload_dir();
    $cust_info = get_userdata($_POST['cust_id']);
    $user_dirname = $upload_dir['basedir'] . '/writingreview/' . $cust_info->user_login;
    $user_urlname = $upload_dir['baseurl'] . '/writingreview/' . $cust_info->user_login;

    $review_id = $_POST['review_id'];

    if (isset($_POST['submit']) && isset($_FILES['fileToUpload']))
    {

        //File has not been moved to the users folder
        //If a file with that name already exists, need to append next number
        $next_doc_id = $wpdb->get_var($wpdb->prepare("SELECT COUNT(wd.ID)+1 FROM " . $wpdb->prefix . "wr_doc wd
                JOIN " . $wpdb->prefix . "wr_review wr ON wd.review_id = wr.id
                WHERE review_id=%d  AND closed = 0;", $review_id));

        //retrieve extension of file
        $extension = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);
        //Set the file name to be the document ID which number document it is
        $name_file = $review_id . "-" . $next_doc_id . "." . $extension;

        //$name_file = $_FILES['fileToUpload']['name'];
        $tmp_name = $_FILES['fileToUpload']['tmp_name'];

        if (move_uploaded_file($tmp_name, $user_dirname . '/' . $name_file))
        {

            //Write to the document table so we can retrieve the documents
            $query_doc = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_doc (
                        url, dir, doc_name, review_id, date, reviewed) 
                    VALUES (%s, %s, %s, %d, now(), 0);", $user_urlname, $user_dirname, $name_file, $review_id);
            $wpdb->query($query_doc);

            //Retreive the ID of the row just created
            $review_doc_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wr_doc 
                    WHERE url=%s AND doc_name=%s AND review_id=%d;", $user_urlname, $name_file, $review_id));

            //Write any comments to the comments table
            $query_add_comments = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_comments (review_id, date, comment, comment_number, user_id, upload_id, comment_read) 
                    VALUES (%d, now(), %s, 1, %d, %d, 0);", $review_id, $_POST['comments'], $current_user->ID, $review_doc_id);
            $wpdb->query($query_add_comments);

            //Send email to reviewer to let them know new files/comments have been added
            $email = $cust_info->user_email;

            $subject = "New comments/files added to review ID " . $review_id;

            $body = "Dear " . $cust_info->display_name . "\n\n 
							New file/comments have been added to the review ID " . $review_id . "\n\n
							You can see the comments by going to your account or clicking the link " . site_url('/writing-review/', 'https');

            //Send email to owner
            //$emailTo = get_option('tz_email');
            $emailTo = 'no-reply@pjmorris.org';
            if (!isset($emailTo) || ($emailTo == ''))
            {
                $emailTo = get_option('admin_email');
            }

            $headers = 'From: P.J.Morris website <' . $emailTo . '>' . "\r\n" . 'Reply-To: ' . $emailTo;

            wp_mail($email, $subject, $body, $headers);

            echo "File was successfully uploaded. It will shortly be reviewed.";
        }
        else
        {
            echo "The file was not uploaded";
        }
    }
}

//If the button is pressed to suggest we are ready to request permission to publish the work
if (isset($_POST['req_permission']))
{
    //Update the status for the review
    update_review_status(1, $_POST['review_id']);
}

//Button to publish the work
if (isset($_POST['publish']))
{
    if (isset($_POST['review_id']))
    {
        $review_id = $_POST['review_id'];
    }

    $cust_info = get_userdata($_POST['cust_id']);
    $essay_to_publish = "";

    //Check if the file is a pdf or a docx
    $doc_query = $wpdb->prepare("SELECT CONCAT(CONCAT(dir, '/'), doc_name) FROM " . $wpdb->prefix . "wr_doc WHERE review_id=%d AND
            ID = (SELECT MAX(ID) FROM " . $wpdb->prefix . "wr_doc WHERE review_id=%d);", $review_id, $review_id);
    $file_loc = $wpdb->get_var($doc_query);

    if (pathinfo($file_loc, PATHINFO_EXTENSION) == "pdf")
    {
        $a = new PDF2Text();
        $a->setFilename($file_loc);
        $a->decodePDF();
        $essay_to_publish = $a->output();
    }
    else if (pathinfo($file_loc, PATHINFO_EXTENSION) == "docx")
    {
        $content = '';

        $zip = new ZipArchive;

        if (true === $zip->open($file_loc))
        {
            for ($i = 0;$i < $zip->numFiles;$i++)
            {
                $entry = $zip->getNameIndex($i);
                if (preg_match("/document(\d).xml/", $entry) or (preg_match("/document.xml/", $entry)))
                {
                    $data .= $zip->getFromName($entry);
                }
            }
            $zip->close();

            $essay_to_publish = str_replace("wt:", "span", $data);
            $essay_to_publish = strip_tags($essay_to_publish, ['<span>', '</span>']);
        }
    }

    //call the function to publish
    if (isset($_POST['title']))
    {
        wr_publish_post($_POST['title'], $essay_to_publish, $_POST['cust_id']);

        $cust_info = get_userdata($_POST['cust_id']);

        //Send email to the writer to let them know it has been published
        $email = $cust_info->user_email;

        $subject = "Review Closed";

        $body = "Dear " . $cust_info->display_name . "\n\n 
                        Your review is now closed.";

        //Send email to owner
        //$emailTo = get_option('tz_email');
        $emailTo = 'no-reply@pjmorris.org';
        if (!isset($emailTo) || ($emailTo == ''))
        {
            $emailTo = get_option('admin_email');
        }

        $headers = 'From: P.J.Morris website <' . $emailTo . '>' . "\r\n" . 'Reply-To: ' . $emailTo;

        wp_mail($email, $subject, $body, $headers);

        //Update review status
        update_review_status(4, $review_id);
        //Close review
        wr_close_review($review_id);
    }
}

if (isset($_POST['close']))
{
    //If the reviewer decides to close the review
    wr_close_review($_POST['review_id']);
}

if (!is_null($review_id) and ($review_id != ''))
{
    //Update comments table to show comments have been viewed
    $update_comments = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_comments SET comment_read = 0 
            WHERE review_id = %d AND user_id != %d AND %d = (SELECT reviewer_id FROM " . $wpdb->prefix . "wr_review
            WHERE ID = %d);", $review_id, $current_user->ID, $current_user->ID, $review_id);

    $wpdb->query($update_comments);
    $review_date = $wpdb->get_var($wpdb->prepare("SELECT date FROM " . $wpdb->prefix . "wr_review
									WHERE id=%d;", $review_id));

?>
            <p><b>Review Details for review ID <?php echo $review_id . " - " . $review_date ?> </b></p>

            <table class="wr-table">
                <tr class="wr-tr">
                    <th class="wr-th">Comment Date</th>
                    <th class="wr-th">Author</th>
                    <th class="wr-th">Comments</th>
                    <th class="wr-th">Document</th>
                </tr>

                    <!-- Retrieve all the details for review with specific ID -->
                    <?php
    $review_details_list = $wpdb->get_results($wpdb->prepare("SELECT CONCAT(wd.url, '/' ,wd.doc_name) AS doc_location, 
                    wr.ID AS ID, wr.date AS review_date, wc.date AS comment_date, wc.comment, wc.user_id, wr.reviewer_id, wr.status,
                    wr.cust_id, wc.comment_read, wr.closed 
                    FROM " . $wpdb->prefix . "wr_review wr
                    JOIN " . $wpdb->prefix . "wr_comments wc ON wc.review_id = wr.ID
                    JOIN " . $wpdb->prefix . "wr_doc wd ON wd.review_ID = wr.ID AND wd.ID = wc.upload_id
                    WHERE wr.ID = %d ORDER BY wc.comment_number ASC;", $review_id));

    foreach ($review_details_list as $review_details)
    {
?>
                        <tr class="wr-tr">
                        <td class="wr-td"><?php echo $review_details->comment_date ?></td>
                        <?php $comment_info = get_userdata($review_details->user_id); ?>
                        <td class="wr-td"><?php echo $comment_info->first_name . " " . $comment_info->last_name; ?></td>
                        <td class="wr-td"><?php echo nl2br($review_details->comment) ?></td>
                        <td class="wr-td"><a href=<?php echo $review_details->doc_location ?> download>Download</a></td>
                        <?php
        if ($review_details->comment_read == 1 and $review_details->comment_read != $current_user->ID)
        {
            echo "<td class='wr-td new_comment'>!</td>";
        }
        $reviewer_id = $review_details->reviewer_id;
        $status = $review_details->status;
        $cust_id = $review_details->cust_id;
        $closed = $review_details->closed; ?>
                        </tr>
                    <?php
    }
?>
            </table> <?php
    //If the current user is the reviewer allow them to add comments and upload files
    if (($reviewer_id = $current_user->ID) and ($closed == 0))
    { ?>
                <br>
                <form action="" enctype="multipart/form-data" method="post">
                    <label for="commentsText">Add comments:</label> 
                    <textarea name="comments" id="commentsText" rows="5" cols="170"></textarea>
                    <br>
                    <input id="fileToUpload" name="fileToUpload" type="file" accept=".pdf, .docx"> 
                    <br>
                    <input type="hidden" name="review_id" value=<?php echo $review_id ?> />
                    <input type="hidden" name="cust_id" value=<?php echo $cust_id ?> />
                    <input name="submit" type="submit" value="Add comments/Upload File">
                </form>
                <br><br>
                <?php
        //permission not asked
        if ($status == 0)
        { ?>
                    <form action='' method='POST'>
                    <label for="req_permission">Are we ready to ask for permission to publish the work</label>
                    <input type="hidden" name="review_id" value=<?php echo $review_id ?> />
                    <button type='submit' name='req_permission'>Request Permission</button>
                <?php
        }
        else if ($status == 2)
        { //permission granted to publish
            
?>
                    <form action='' method='POST'>
                    <label for="publish">If there are no more changes to be made, you can publish the work. The review
                    will be automatically closed.</label>
                    <br>
                    <lable for="title">Please enter the title of the essay*</lable>
                    <input type="textbox" name="title" required/>
                    <input type="hidden" name="review_id" value=<?php echo $review_id ?> />
                    <input type="hidden" name="cust_id" value=<?php echo $cust_id ?> />
                    <button type='submit' name='publish'>Publish</button>
                <?php
        } ?>
                <br>
                <form action='' method='POST'>
                    <label for="close">Would you like to close the review?</label>
                    <input type="hidden" name="review_id" value=<?php echo $review_id ?> />
                    <input type="hidden" name="cust_id" value=<?php echo $cust_id ?> />
                    <button type='submit' name='close'>Close Review</button>
            <?php
    }
    else if ($closed == 1)
    {
?>
                <p>The review is now closed</p> 
                <?php
    }
}
else
{
?>
            <form action='' method='POST'>
            <label for="id">Review ID:</label>
            <input type="text" id="id" name="id">
            <button type='submit' name='find'>Find Details</button> <?php
}
?>
</div>
