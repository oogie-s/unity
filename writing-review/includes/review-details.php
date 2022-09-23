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

if (isset($_POST['submit']))
{
    wp_upload_bits($_FILES['fileToUpload']['name'], null, file_get_contents($_FILES['fileToUpload']['tmp_name']));

    //Create folder for user if it doesn't already exist in the writingreview director
    get_currentuserinfo();
    $upload_dir = wp_upload_dir();
    $user_dirname = $upload_dir['basedir'] . '/writingreview/' . $current_user->user_login;
    $user_urlname = $upload_dir['baseurl'] . '/writingreview/' . $current_user->user_login;

    if (!file_exists($user_dirname)) wp_mkdir_p($user_dirname);

    if (isset($_POST['submit']) && isset($_FILES['fileToUpload']))
    {

        //File has not been moved to the users folder
        //Write to review
        $query_review = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_review (date, cust_id, shared, closed) 
			VALUES (now(), %d, 0, 0, 1);", $current_user->ID);
        $wpdb->query($query_review);

        //Retreive the ID of the row just created
        $review_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wr_review 
			WHERE cust_id=%d AND closed = 0;", $current_user->ID));

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
            $query_doc = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_doc (location, doc_name, review_id, date, reviewed) 
				VALUES (%s, %s, %d, now(), 0);", $user_urlname, $name_file, $review_id);
            $wpdb->query($query_doc);

            //Retreive the ID of the row just created
            $review_doc_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wr_doc 
				WHERE location=%s AND doc_name=%s AND review_id=%d;", $user_urlname, $name_file, $review_id));

            //Write any comments to the comments table
            $query_add_comments = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "wr_comments (review_id, date, comment, comment_number, user_id, upload_id, comment_read) 
				VALUES (%d, now(), %s, 1, %d, %d, 0);", $review_id, $_POST['comments'], $current_user->ID, $review_doc_id);
            $wpdb->query($query_add_comments);

            echo "File was successfully uploaded. It will shortly be reviewed.";
        }
        else
        {
            echo "The file was not uploaded";
        }
    }
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
								Welcome, <?php echo $current_user->display_name ?>! <br>
								<!-- If there is no work in review then allow user to upload work -->

                                <!--Display HTML table with comments for open review-->
                                <table>
                                    <tr>
                                        <th>Review ID</th>
                                        <th>Review Date</th>
                                        <th>Comment Date</th>
                                        <th>Author</th>
                                        <th>Comments</th>
                                        <th>Document</th>
                                    </tr>
                                    <tr>
                                        <!-- Retrieve all the details for the open query -->
                                        <?php
    $review_details_list = $wpdb->get_results($wpdb->prepare("SELECT wr.ID, CONCAT(wd.location, '/' ,wd.doc_name) AS doc_location, wr.date AS review_date, wc.date AS comment_date,
                                        wc.comment, wc.user_id, wc.new_comment  
                                        FROM " . $wpdb->prefix . "wr_review wr
                                        JOIN " . $wpdb->prefix . "wr_comments wc ON wc.review_id = wr.ID
                                        JOIN " . $wpdb->prefix . "wr_doc wd ON wd.review_ID = wr.ID AND wd.ID = wc.upload_id
                                        WHERE wr.cust_id = %d AND closed = 0 ORDER BY wc.comment_number ASC;", $current_user->ID));

    foreach ($review_details_list as $review_details)
    {
?>
                                            <td>
                                                <a href="<?php echo admin_url('admin.php?page=wr-ind-review') ?>&id=<?php echo $review_details->id ?>">
                                                    <?php echo $review_details->ID ?>
                                                </a>
                                            </td>
                                            <td><?php echo $review_details->review_date ?></td>
                                            <td><?php echo $review_details->comment_date ?></td>
                                            <?php $comment_info = get_userdata($review_details->user_id); ?>
                                            <td><?php echo $comment_info->user_login; ?></td>
                                            <td><?php echo nl2br($review_details->comment) ?></td>
                                            <td><a href=<?php echo $review_details->doc_location ?> download>Download</a></td>
                                        <?php
        if ($review_details->comment_read == 1)
        {
            echo "<td class='new_comment'>!</td>";
        }
    }
?>
                                    </tr>
                                </table>

                                
                            <?php
}

//Tell user if they're not logged in that they need to be
else
{
?><p> You must be logged in to submit you work or view feedback. </p>
							  <p> Please login. If you do not have an account please create one. </p>

							<a  href="<?php echo site_url('/index.php/register'); ?>"><button>Register</button></a>
							<a  href="<?php echo site_url('/index.php/login'); ?>"><button>Login</button></a>
							<?php
}
?>
					</div><!-- .entry-content -->
				</div><!-- .post-->
			</div><!-- #content -->
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

