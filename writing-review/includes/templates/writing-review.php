<?php
/**
 * Template Name: Writing Review
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _s
 */

?>

<?php
global $current_user;

global $review_id;

global $reviewer_id;

global $reviewer;

global $review_doc_id;

global $comment;

$new_review = false;

if (isset($_POST['submit']))
{
    if ($_FILES['fileToUpload']['size'] != 0 && $_FILES['fileToUpload']['error'] == 0)
    {

        wp_upload_bits($_FILES['fileToUpload']['name'], null, file_get_contents($_FILES['fileToUpload']['tmp_name']));

        //Create folder for user if it doesn't already exist in the writingreview director
        get_currentuserinfo();
        $upload_dir = wp_upload_dir();
        $user_dirname = $upload_dir['basedir'] . '/writingreview/' . $current_user->user_login;
        $user_urlname = $upload_dir['baseurl'] . '/writingreview/' . $current_user->user_login;

        if (!file_exists($user_dirname)) wp_mkdir_p($user_dirname);

        //File has not been moved to the users folder
        //If new reveiw- Write to review. And get the review ID
        if (isset($_POST['new_review']))
        {
            $query_review = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_review (date, cust_id, shared, closed, unread_comments) 
				VALUES (now(), %d, 0, 0, 1);", $current_user->ID);
            $wpdb->query($query_review);
            //Retreive the ID of the row just created
            $review_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wr_review 
				WHERE cust_id=%d AND closed = 0;", $current_user->ID));
        }

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
            $query_doc = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_doc (url, dir, doc_name, review_id, date, reviewed) 
				VALUES (%s, %s, %s, %d, now(), 0);", $user_urlname, $user_dirname, $name_file, $review_id);
            $wpdb->query($query_doc);

            //Retreive the ID of the row just created
            $review_doc_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wr_doc 
				WHERE url=%s AND doc_name=%s AND review_id=%d;", $user_urlname, $name_file, $review_id));
        }
    }

    //If there are comments add them
    if (isset($_POST['comments']))
    {
        //Write any comments to the comments table
        $query_add_comments = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_comments 
			(review_id, date, comment, comment_number, user_id, upload_id) 
			VALUES (%d, now(), %s, 1, %d, %d);", $_POST['review_id'], $_POST['comments'], $current_user->ID, $_POST['review_doc_id']);
        $wpdb->query($query_add_comments);
    }

    if (isset($_POST['new_review']))
    {
        $comment = "File/comments were successfully uploaded. It will shortly be reviewed.";
    }
    else if (isset($_POST['reviewer_id']))
    {
        $reviewer = get_userdata($_POST['reviewer_id']);

        if (isset($_POST['review_id']))
        {
            $review_id = $_POST['review_id'];
        }

        //Send email to reviewer to let them know new files/comments have been added
        $email = $reviewer->user_email;

        $subject = "New comments/files added to review ID " . $review_id;

        $body = "Dear " . $reviewer->first_name . "\n\n 
					New file/comments have been added to the review ID " . $review_id . "\n\n
					You can see the comments by going to your account or clicking the link " . admin_url('admin.php?page=wr-ind-review&id=' . $review_id, 'https');

        $emailTo = 'no-reply@pjmorris.org';
        if (!isset($emailTo) || ($emailTo == ''))
        {
            $emailTo = get_option('admin_email');
        }

        $headers = 'From: P.J.Morris website <' . $emailTo . '>' . "\r\n" . 'Reply-To: ' . $emailTo;

        wp_mail($email, $subject, $body, $headers);

        $comment = "Thank you for adding file/comments. We have emailed your reviewer to let them know.";
    }
}

if (isset($_POST['publishme']))
{
    //If customer would like to be published. Change the status to say approved
    //Update the status for the review
    if (isset($_POST['reviewer_id']))
    {
        $reviewer = get_userdata($_POST['reviewer_id']);
    }

    if (isset($_POST['review_id']))
    {
        $review_id = $_POST['review_id'];
    }

    update_review_status(2, $review_id);

    $email = $reviewer->user_email;

    $subject = "Writer is allowing us to publish review with review ID " . $review_id;

    $body = "Dear " . $reviewer->first_name . "\n\n 
				The writer has confirmed that we can publish review ID " . $review_id . "\n\n
				You can publish the work by going here " . admin_url('admin.php?page=wr-ind-review&id=' . $review_id, 'https');

    $emailTo = 'no-reply@pjmorris.org';
    if (!isset($emailTo) || ($emailTo == ''))
    {
        $emailTo = get_option('admin_email');
    }

    $headers = 'From: P.J.Morris website <' . $emailTo . '>' . "\r\n" . 'Reply-To: ' . $emailTo;

    wp_mail($email, $subject, $body, $headers);
}
else if (isset($_POST['no']))
{
    //Update status to say they do not want publishing
    if (isset($_POST['review_id']))
    {
        $review_id = $_POST['review_id'];
    }

    update_review_status(3, $review_id);
}
?>

<?php get_header();
?>


	<main id="primary" class="site-main">

	    <header class="entry-header">

    		<?php the_title('<h1 class="entry-title">', '</h1>'); ?>

    	</header><!-- .entry-header -->

	    <div id="container">
			<div id="content">
				<?php the_post() ?>
				<div id="post-<?php the_ID() ?>" class="post">
					<div class="entry-content">
                    	<?php
global $current_user;
wp_get_current_user();

//Check if user logged in
if (is_user_logged_in())
{
?><div class="welcome"> 
								Welcome <?php echo $current_user->first_name ?>! <br>
								<?php
    if ($comment != "")
    {
?><p class="comment"><?php $comment ?></p><?php
    } ?>

								<!-- If there is no work in review then allow user to upload work -->
								<?php
    $count_open_review = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM " . $wpdb->prefix . "wr_review
								WHERE cust_id=%d AND closed = 0;", $current_user->ID));

    $count_review = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM " . $wpdb->prefix . "wr_review
								WHERE cust_id=%d;", $current_user->ID));

    $max_review = $wpdb->get_var($wpdb->prepare("SELECT contents FROM " . $wpdb->prefix . "wr_settings
								WHERE name='MAX_REVIEWS';"));

    if (($count_open_review == 0) and ($count_review < $max_review))
    {
?>
									Please upload your creative writing piece for review.<br>
									<form action="" enctype="multipart/form-data" method="post">
										<input id="fileToUpload" name="fileToUpload" type="file" accept=".pdf,.doc, .docx"> 
										<br>
										<label for="commentsText">Any comments:</label> 
									    <textarea name="comments" id="commentsText" rows="5" cols="30"></textarea>
										<input type="hidden" name="new_review" />
										<input name="submit" type="submit" value="Upload File">
									</form>
								<?php
    }
    else if ($count_open_review == 1)
    {
        $review_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wr_review
									WHERE cust_id=%d AND closed = 0;", $current_user->ID));

        $review_date = $wpdb->get_var($wpdb->prepare("SELECT date FROM " . $wpdb->prefix . "wr_review
									WHERE id=%d;", $review_id));

        $reviewer_id = $wpdb->get_var($wpdb->prepare("SELECT reviewer_ID FROM " . $wpdb->prefix . "wr_review
									WHERE id=%d AND closed = 0;", $review_id));

        $review_status = $wpdb->get_var($wpdb->prepare("SELECT status FROM " . $wpdb->prefix . "wr_review
									WHERE id=%d;", $review_id));

        //Update comments table to show comments have been viewed
        $update_comments = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_comments SET comment_read = 0 
									WHERE review_id = %d AND user_id != %d AND %d = (SELECT cust_id FROM " . $wpdb->prefix . "wr_review
									WHERE ID = %d);", $review_id, $current_user->ID, $current_user->ID, $review_id);

        $wpdb->query($update_comments); ?>

									<p class="curropen">Current Open Reviews</p>
									<p>Review ID: <?php echo $review_id . " - " . $review_date ?></p>

									<?php
        //Status of 1 is customer being asked if they'd like to be published
        if ($review_status == 1)
        {

            echo 'We would love to offer you the opportunity to have your work when the reviewing process is finished published on our website.
										If you would like your work to be published please agree to the terms and conditions and press "Publish me" button.';
?>
										<br>
										<form action='' method='POST'>
											<input type="checkbox" id="tandc" name="tandc" onclick="tandccheckbox(this)">
											<label for="tandc">I agree to the </label>
											<a href="javascript:void(0)" id="tandclink" onclick="tandclink()">Terms and Conditions</a>
											<br><br>
											
											<input type="hidden" name="review_id" value="<?php echo $review_id ?>" />
											<input type="hidden" name="reviewer_id" value="<?php echo $reviewer_id; ?>" />
											<input name="publishme" id="publishme" type="submit" value="Publish Me!">
											<input name="no" id="no" type="submit" value="No, thank you!">
											<br><br>
										</form>
									<?php
        }

        $review_details_list = $wpdb->get_results($wpdb->prepare("SELECT wr.ID AS ID, CONCAT(wd.url, '/' ,wd.doc_name) AS doc_location, wr.date AS review_date, wc.date AS comment_date,
										wc.comment, wc.user_id, wr.status, wc.comment_read, wr.reviewer_id, wd.id AS doc_id
										FROM " . $wpdb->prefix . "wr_review wr
										LEFT JOIN " . $wpdb->prefix . "wr_comments wc ON wc.review_id = wr.ID
										LEFT JOIN " . $wpdb->prefix . "wr_doc wd ON wd.review_ID = wr.ID AND wd.ID = wc.upload_id
										WHERE wr.cust_id = %d AND closed = 0 ORDER BY wc.comment_number ASC;", $current_user->ID));
?>

										<!--Display HTML table with comments for open review-->
										<table class="wr-table">
											<tr class="wr-tr">
												<th class="wr-th">Comment Date</th>
												<th class="wr-th">Author</th>
												<th class="wr-th">Comments</th>
												<th class="wr-th">Document</th>
											</tr>
											
										<?php
        foreach ($review_details_list as $review_details)
        {
?>
												<tr class="wr-tr">
													<!-- Retrieve all the details for the open query -->
													<?php
?>
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
            $review_doc_id = $review_details->doc_id;
?>
												</tr>
									
									<?php
        }
?>
									</table>
									<br>
									<br>
									<form action="" enctype="multipart/form-data" method="post">
										<label for="commentsText">Add comments:</label> 
										<textarea name="comments" id="commentsText" rows="5" cols="170"></textarea>
										<br>
										<input id="fileToUpload" name="fileToUpload" type="file" accept=".pdf,.doc, .docx"> 
										<br><br>
										<input type="hidden" name="review_doc_id" value="<?php echo $review_doc_id ?>" />
										<input type="hidden" name="review_id" value="<?php echo $review_id ?>" />
										<input name="submit" type="submit" value="Add comments/Upload File">
									</form>
									<br><br>
									
								<?php
    }
    else if ($count_review >= $max_review)
    {
        echo "I'm sorry you have reached the maximum allowed reviews to submit.";
    }
    else if ($count_open_review == 0)
    {
        echo "No open reviews";
    }

}
//Tell user if they're not logged in that they need to be
else
{
?><p>P.J.Morris believed that everyone should have free access to help improve their writing. 
								This section of the website is to help continue his work. </p>
							  <p>A creative writing piece can be submitted, one of our reviewers will then view the work
								and annotate the text with comments of how to improve it. </p>
							  <p> You must be logged in to submit you work or view feedback. </p>
							  <p> Please login. If you do not have an account please create one. </p>

							<a  href="<?php echo site_url('membership-registration'); ?>"><button>Register</button></a>
							<a  href="<?php echo site_url('membership-login/'); ?>"><button>Login</button></a>
							<?php
}
?>
					</div><!-- .entry-content -->
				</div><!-- .post-->
			</div><!-- #content -->
			<div id="tandcshow">
				<a href="javascript:void(0)" id="bclosetandc" onclick="closetandc()">&times;</a>
				<?php
$tandc_text = $wpdb->get_var($wpdb->prepare("SELECT contents FROM " . $wpdb->prefix . "wr_settings
					WHERE name='TERMS_AND_CONDITIONS'"));

echo nl2br($tandc_text);
?>
			</div>
		</div><!-- #container -->
	</main><!-- #main -->



<?php
function myFileUploaderRenderer()
{
    ob_start();
    myFileUploader();
    return ob_get_clean();
}

get_sidebar();

get_footer();

