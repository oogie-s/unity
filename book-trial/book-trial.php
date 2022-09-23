<?php
/*
  Plugin Name: Book Trial
  Description: Book trial
  Version: 1.0
  Author: Olga Schtulman
 */

global $wpdb;

register_activation_hook( __FILE__, 'bt_install_tables' );


    //Create tables
    function bt_install_tables()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');



        //Create table to store types of classes
        $table1 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_class_type (
            id bigint(100) NOT NULL AUTO_INCREMENT,
            class_name varchar(255) NOT NULL,
            min_age int NOT NULL,
            max_age int NOT NULL,
            PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        
        //Create table to store locations
        $table2 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_class_locations (
            id bigint(100) NOT NULL AUTO_INCREMENT,
            location varchar(255) NOT NULL,
            address varchar(255) NOT NULL, 
            post_code varchar(255) NOT NULL,
            PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        //Create table to store which location has which class
        $table3 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_classes (
            id bigint(100) NOT NULL AUTO_INCREMENT,
            location_id int NOT NULL,
            class_type_id int NOT NULL,
            class_day int NOT NULL,
            class_time varchar(255) NOT NULL,
            no_students int NOT NULL,
            max_students int NOT NULL,
            PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        
        //Create table to store date, time of registered student
        $table4 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_class_booked (
            id bigint(100) NOT NULL AUTO_INCREMENT,
            class_id int NOT NULL,
            class_date DATE,
            student_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        
        //Create table to store date, time of registered student
        $table5 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_days (
          id bigint(100) NOT NULL AUTO_INCREMENT,
          day varchar(255) NOT NULL UNIQUE,
          day_index bigint(100) NOT NULL,
          PRIMARY KEY  (id)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        dbDelta($table1); 
        dbDelta($table2); 
        dbDelta($table3);
        dbDelta($table4);
        dbDelta($table5);

        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Monday', 0)";
        dbDelta($table6);
        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Tuesday', 1)";
        dbDelta($table6);
        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Wednesday', 2)";
        dbDelta($table6);
        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Thursday', 3)";
        dbDelta($table6);
        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Friday', 4)";
        dbDelta($table6);
        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Saturday', 5)";
        dbDelta($table6);
        $table6 = "INSERT INTO ".$wpdb->prefix."bt_days (day, day_index) VALUES ('Sunday', 6)";
        dbDelta($table6);

        $table7 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_settings (
          setting varchar(255) NOT NULL UNIQUE,
          value varchar(255) NOT NULL UNIQUE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        dbDelta($table7);

        $table8 = "INSERT INTO ".$wpdb->prefix."bt_settings (setting, value) VALUES ('WEEKS_AHEAD', '2')";
        dbDelta($table8);

        //Create table to store dates to exclude 
        $table9 = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."bt_exclude_dates (
            id bigint(100) NOT NULL AUTO_INCREMENT,
            exclude_date DATE NOT NULL, 
            PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        
        dbDelta($table9);
    }

// Include bt-functions.php, use require_once to stop the script if bt-functions.php is not found
require_once plugin_dir_path(__FILE__) . 'includes/bt-functions.php';


