<?php
/**
 * The plugin bootstrap file
 *
 * @link              TODO
 * @since             1.0.0
 * @package           Writing_review
 *
 * @wordpress-plugin
 * Plugin Name:       Writing Review
 * Plugin URI:        TODO
 * Description:       Submit work for review and get feedback
 * Version:           0.1.0
 * Author:            Olga Schtulman
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}

// We *could* use an autoloader here but I'm not sure everyone has read the series.
foreach (glob(plugin_dir_path(__FILE__) . 'admin/*.php') as $file)
{
    include_once $file;
}

// Include bt-functions.php, use require_once to stop the script if wr-functions.php is not found
require_once plugin_dir_path(__FILE__) . 'includes/wr-functions.php';

//include php to allow the pdf to be read
require_once plugin_dir_path(__FILE__) . 'includes/pdf2text.php';

register_activation_hook(__FILE__, 'wr_install_tables');

//Create tables
function wr_install_tables()
{
    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

    global $wpdb;

    //Create table to store where the document is
    $table1 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wr_doc (
        id bigint(100) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        dir varchar(255) NOT NULL,
        doc_name varchar(255) NOT NULL,
        review_id int NOT NULL,
        date date,
        reviewed boolean,
        PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    dbDelta($table1);

    //Create table for a list of all documents submitted for review
    $table2 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wr_review (
        id bigint(100) NOT NULL AUTO_INCREMENT,
        date DATE,
        cust_id INT NOT NULL,
        status INT NOT NULL DEFAULT 0,
        closed BOOLEAN,
        reviewer_id INT,
        PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    dbDelta($table2);

    //Create table for a list of all comments
    $table3 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wr_comments(
        id bigint(100) NOT NULL AUTO_INCREMENT,
        review_id INT NOT NULL,
        date DATETIME NOT NULL,
        comment VARCHAR(65534) NOT NULL,
        comment_number INT,
        user_id INT NOT NULL, 
        upload_id INT, 
        comment_read BOOLEAN,
        PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    dbDelta($table3);

    //Create table for a history of who assigned which review to do when
    $table4 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wr_review_history(
        id bigint(100) NOT NULL AUTO_INCREMENT,
        review_id INT NOT NULL,
        reviewer_id INT NOT NULL,
        date DATETIME NOT NULL,
        PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    dbDelta($table4);

    //Create table for the settings
    $table5 = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wr_settings(
        id bigint(100) NOT NULL AUTO_INCREMENT,
        name VARCHAR(65534) NOT NULL UNIQUE,
        contents VARCHAR(65534) NOT NULL,
        description VARCHAR(65534) NOT NULL,
        PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    dbDelta($table5);

    $query_review = "INSERT IGNORE INTO " . $wpdb->prefix . "wr_settings (name, contents, description) 
			VALUES ('MAX_REVIEWS', 999, 'The maximum number of reviews that a user can have open at one time');";
    $wpdb->query($query_review);

    $query_review = "INSERT IGNORE INTO " . $wpdb->prefix . "wr_settings (name, contents, description) 
			VALUES ('TERMS_AND_CONDITIONS', 'Terms and Conditions go here', 'The terms and conditions under which work is published');";
    $wpdb->query($query_review);
}

