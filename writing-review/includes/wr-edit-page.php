<div class="wrap">           
    <?php
require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
global $wpdb;
global $current_user;
?>

    <!-- Code for the assign button -->
    <?php
if (isset($_POST['assign']))
{
    //If the button is pressed then assign that particular review
    $query_assign = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_review SET reviewer_id = %d
            WHERE id=%d;", $current_user->ID, $_POST['assign']);
    $wpdb->query($query_assign);
}
?>

    <h1>Writing Review</h1> 
    <p><b>Your Reviews</b></p>
    <table class="wr-table">
        <tr class="wr-tr">
            <th class="wr-th">Review ID</th>
            <th class="wr-th">Review Date</th>
            <th class="wr-th">Author Name</th>
            <th class="wr-th">Latest Comment Date</th>
            <th class="wr-th">Last Commenter</th>

        </tr>
        <tr class="wr-tr">
            <!-- Retrieve all the details for reviews assigend to the reviewer -->
            <?php
$review_details_list = $wpdb->get_results($wpdb->prepare("SELECT
                wr.ID
                , wr.date AS review_date
                , wr.cust_id
                , wr.closed
                , COALESCE(wc.comment_read, 0) AS comment_read
                , wc.date AS comment_date
                , wc.user_id AS commenter
            FROM " . $wpdb->prefix . "wr_review wr
            LEFT OUTER JOIN
            (
                SELECT
                    c.review_id
                    , c.date
                    , c.user_id
                    , MAX(CASE c.user_id WHEN %d THEN 0 ELSE c.comment_read END) OVER (PARTITION BY c.review_id) AS comment_read
                    , ROW_NUMBER() OVER (PARTITION BY c.review_id ORDER BY c.date DESC) AS rank_order
                FROM " . $wpdb->prefix . "wr_comments c
            ) wc ON wr.id = wc.review_id
                AND wc.rank_order = 1
                WHERE wr.reviewer_id = %d
	            AND wr.closed = 0;", $current_user->ID, $current_user->ID));

foreach ($review_details_list as $review_details)
{
?>
                <td class="wr-td">
                    <a href="<?php echo admin_url('admin.php?page=wr-ind-review') ?>&id=<?php echo $review_details->ID ?>">
                        <?php echo $review_details->ID ?>
                    </a>
                </td>
                <td class="wr-td"><?php echo $review_details->review_date ?></td>
                <?php $author_info = get_userdata($review_details->cust_id); ?>
                <td class="wr-td"><?php echo $author_info->user_login; ?></td>
                <td class="wr-td"><?php echo $review_details->comment_date; ?></td>
                <?php $commenter_info = get_userdata($review_details->commenter); ?>
                <td class="wr-td"><?php echo $commenter_info->first_name . " " . $commenter_info->last_name ?></td>
                <?php
    if ($review_details->comment_read == 1)
    {
        echo "<td class='wr-td new_comment'>!</td>";
    }
}
?>
        </tr>
    </table>

    <br><br><br><br>

    <!-- Retrieve all unassgined reviews -->
    <p><b>Unassigned Reviews</b></p>
    <table class="wr-table">
        <tr class="wr-tr">
            <th class="wr-th">Review ID</th>
            <th class="wr-th">Review Date</th>
            <th class="wr-th">Author</th>
            <th class="wr-th">Download</th>
        </tr>
        <tr class="wr-td">
            <?php
$review_details_list = $wpdb->get_results($wpdb->prepare("SELECT wr.ID AS ID, CONCAT(wd.url, '/' ,wd.doc_name) AS doc_location,
            wr.date AS review_date, wr.cust_id  
            FROM " . $wpdb->prefix . "wr_review wr
            JOIN " . $wpdb->prefix . "wr_doc wd ON wd.review_ID = wr.ID
            WHERE wr.reviewer_id IS NULL AND closed = 0;"));

foreach ($review_details_list as $review_details)
{
?>
                <td class="wr-td"><?php echo $review_details->ID ?></td>
                <td class="wr-td"><?php echo $review_details->review_date ?></td>
                <?php $comment_info = get_userdata($review_details->cust_id); ?>
                <td class="wr-td"><?php echo $comment_info->first_name . " " . $comment_info->last_name; ?></td>
                <td class="wr-td"><a href=<?php echo $review_details->doc_location ?> download>Download</a></td>
                <td class="wr-td">
                    <form action='' method='POST'>
                    <button type='submit' name='assign' value=<?php echo $review_details->ID ?>>Assign</button>
                </td>
            <?php
}
?>
        </tr>
    </table>


</div>
