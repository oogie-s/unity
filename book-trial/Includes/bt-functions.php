<?php 

    /*
    * Add my new menu to the Admin Control Panel
    */
    // Hook the 'admin_menu' action hook, run the function named 'btf_Add_My_Admin_Link()'
    add_action( 'admin_menu', 'btf_Add_My_Admin_Link' );
    // Add a new top level menu link to the ACP
    function btf_Add_My_Admin_Link()
    {
        add_menu_page
        (
            'Book Trial', // Title of the page
            'Book trial', // Text to show on the menu link
            'manage_options', // Capability requirement to see the link
             plugin_dir_path(__FILE__) .'btf-edit-page.php' // The 'slug' - file to display when clicking the link
        );
    }

    add_action('init', 'btf_create_page'); 
    //register_activation_hook( __FILE__, 'btf_create_page' );

    function btf_create_page()
    {
        $wordpress_page = array
        (
            'post_title'    => 'BOOK TRIAL',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type' => 'page',
            'page_template' =>  'templates/book-trial.php'
        );

        $page_exists = get_page_by_title( $wordpress_page['post_title'] );

        if ($page_exists == null)
        {
            // Page doesn't exist, so lets add it
            $insert = wp_insert_post( $wordpress_page );
            if ($insert)
            {
                // Page was inserted ($insert = new page's ID)
            }
        }
        else
        {
            // Page already exists
        }
    }

    /* Add page templates */
    add_filter( 'theme_page_templates', 'bt_register_page_template' );
    
    /**
     * Register Page Template: book-trial
     * @since 1.0.0
     */
    function bt_register_page_template($templates)
    {
        $templates['templates/book-trial.php'] = 'Book Trial';
        return $templates;
    }   

    add_filter('page_template', 'bt_redirect_page_template');

    function bt_redirect_page_template($template)
    {
        $post = get_post();
        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
        if ('book-trial.php' == basename ($page_template))
            $template = WP_PLUGIN_DIR . '/book-trial/includes/templates/book-trial.php';
        return $template;
    }

    /**
     * Enqueue scripts and styles.
     */
    add_action( 'admin_enqueue_scripts', '_bt_scripts' );

    function _bt_scripts()
    {
        wp_enqueue_style( 'styles', plugins_url( 'style.css', __FILE__ ) );
        wp_enqueue_script( 'iconify', "https://code.iconify.design/2/2.2.1/iconify.min.js" );
        wp_enqueue_script( 'openFormLocation', plugins_url( 'openFormLocation.js', __FILE__ ) );
        wp_enqueue_script( 'openFormCType', plugins_url( 'openFormCType.js', __FILE__ ) );
        wp_enqueue_script( 'openFormClass', plugins_url( 'openFormClass.js', __FILE__ ) );
        wp_enqueue_script( 'openFormExclude', plugins_url( 'openFormExclude.js', __FILE__ ) );
        wp_enqueue_script( 'bt-table-script', plugins_url( 'bt-table-script.js', __FILE__ ), array('jquery') );

        $localize = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
        wp_localize_script( 'bt-table-script', 'ajax_object', $localize );
    }


    add_action( 'wp_enqueue_scripts', 'bt_front_scripts' );
    function bt_front_scripts()
    {
        wp_enqueue_script( 'bt-dropdown-class', plugins_url( 'bt-dropdown-class.js', __FILE__ ), array('jquery'), null, true );  
        wp_enqueue_script( 'bt-select-classdate', plugins_url( 'bt-select-classdate.js', __FILE__ ), array('jquery'), null, true );

        $localize = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
        wp_localize_script( 'bt-dropdown-class', 'ajax_object', $localize );
        wp_localize_script( 'bt-select-classdate', 'ajax_object', $localize );
    }

    add_action( 'wp_ajax_load_classes', 'load_classes' );
    add_action( 'wp_ajax_nopriv_load_classes', 'load_classes' );

    function load_classes()
    {
        $birthDate = ($_POST['birthday']);        
        if (isset( $birthDate))
        {
            global $wpdb;
            $currentDate = date_create(date("Y-m-d"));
            $birthDate = date_create($birthDate);
            $diffage = date_diff($birthDate, $currentDate);
            $age = $diffage->y;

            //$age = format(date_diff(date_create($birthDate), date_create($currentDate)),"%y");
            $query = "SELECT ct.class_name, c.id, ct.id as class_type_id, d.day, c.class_time, c.class_day, cl.location
                FROM ".$wpdb->prefix."bt_class_type ct
                JOIN  ".$wpdb->prefix."bt_classes c ON ct.id = c.class_type_id
                JOIN  ".$wpdb->prefix."bt_class_locations cl ON cl.id = c.location_id
                JOIN ".$wpdb->prefix."bt_days d ON d.id=c.class_day
                WHERE %d BETWEEN ct.min_age AND ct.max_age";
            
            $results = $wpdb->get_results( $wpdb->prepare( $query, $age ) );

            wp_send_json( $results, 200 );
        }
        else
        {
            wp_send_json( array('message', 'Date of birth is required'), 400 );
        }
    }

    add_action( 'wp_ajax_load_dates', 'load_dates' );
    add_action( 'wp_ajax_nopriv_load_dates', 'load_dates' );

    function load_dates()
    {
        try
        {
            global $wpdb;
        
            $classid = ($_POST['classid']);
            $query = "SELECT value FROM ".$wpdb->prefix."bt_settings WHERE setting='WEEKS_AHEAD'";
            $weeks_ahead = $wpdb->get_var($query);
            
            $query = "SELECT btd.day_index FROM ".$wpdb->prefix."bt_days btd 
            JOIN ".$wpdb->prefix."bt_classes btc ON btd.id = btc.class_day WHERE btc.id = %s";

            $day = $wpdb->get_var( $wpdb->prepare( $query, $classid ));
            
            if(isset( $classid)) {         
                
                $query = "
                SET @weekDay = $day;
                SET @weekCount = $weeks_ahead;
                SET @indexDate = CAST('1900-01-01' AS DATE);
                SET @daysToWeek = CAST(DATEDIFF(CURDATE(), @indexDate) - MOD(DATEDIFF(CURDATE(), @indexDate), 7) AS INT);
                SET @firstDate = DATE_ADD(@indexDate, INTERVAL @daysToWeek + @weekDay DAY);
                SET @firstDate = CASE WHEN DATEDIFF(@firstDate, CURDATE()) > 0 THEN @firstDate ELSE DATE_ADD(@firstDate, INTERVAL 7 DAY) END;
                
                WITH RECURSIVE Dates AS
                (
                    SELECT @firstDate AS Date
                    UNION ALL
                    SELECT DATE_ADD(d.Date, INTERVAL 7 DAY)
                    FROM Dates d
                    WHERE DATEDIFF(d.Date, @firstDate) < @weekCount * 7
                )
                SELECT DATE_FORMAT(d.Date, '%d/%m/%Y') AS Date
                FROM Dates d WHERE Date NOT IN
                (SELECT exclude_date FROM ".$wpdb->prefix."bt_exclude_dates)
                AND
                Date NOT IN
                (SELECT a.class_date FROM (SELECT class_date, COUNT(cb.student_name) AS Students, c.max_students AS max_students 
                FROM ".$wpdb->prefix."bt_class_booked cb
                JOIN ".$wpdb->prefix."bt_classes c ON c.id = cb.class_id
                GROUP BY class_id, class_date
                HAVING Students >= max_students) a);";

                if (mysqli_multi_query($wpdb->dbh, $query)) {
                    $data = array();
                    
                    do {
                        if ($result = mysqli_store_result($wpdb->dbh)) {
                            //$data[$i] = mysqli_fetch_all($result);
                            //array_push($data, mysqli_fetch_all($result));
                            $data = mysqli_fetch_all($result);
                            mysqli_free_result($result);
                        }
                        if (!mysqli_more_results($wpdb->dbh)) {
                            break;
                        }
                    } while (mysqli_next_result($wpdb->dbh));
                }

                $json = array();
                foreach ($data as $datum)
                {
                    array_push($json, array("date" => $datum[0]));
                }

                wp_send_json($json, 200 );
            } else {
                wp_send_json( array('message', 'Class needs to be selected'), 400 );
            }
        }
        catch (Exception $e)
        {
            wp_send_json(array('message', $e->getMessage()), 200);
        }
    }

    add_action( 'wp_ajax_load_all_classes', 'load_all_classes' );
    add_action( 'wp_ajax_nopriv_load_all_classes', 'load_all_classes' );

    function load_all_classes()
    {
        global $wpdb;

        $query = "SELECT class_name, id
            FROM ".$wpdb->prefix."bt_class_type";
        
        $results = $wpdb->get_results( $wpdb->prepare( $query) );

        wp_send_json( $results, 200 );
    }

    add_action( 'wp_ajax_load_all_locations', 'load_all_locations' );
    add_action( 'wp_ajax_nopriv_load_all_locations', 'load_all_location' );

    function load_all_locations()
    {
        global $wpdb;

        $query = "SELECT *
            FROM ".$wpdb->prefix."bt_class_locations";
        
        $results = $wpdb->get_results( $wpdb->prepare( $query) );

        wp_send_json( $results, 200 );
    }

    add_action( 'wp_ajax_load_all_days', 'load_all_days' );
    add_action( 'wp_ajax_nopriv_load_all_days', 'load_all_days' );

    function load_all_days()
    {
        global $wpdb;

        $query = "SELECT *
            FROM ".$wpdb->prefix."bt_days";
        
        $results = $wpdb->get_results( $wpdb->prepare( $query) );

        wp_send_json( $results, 200 );
    }

    add_action( 'wp_ajax_save_row_class', 'save_row_class' );
    add_action( 'wp_ajax_nopriv_save_row_class', 'save_row_class' );

    function save_row_class()
    {
        global $wpdb;

        $data = json_decode(file_get_contents('php://input'), true); 
        $class_id = $data["ID"];
        $location_id = $data["location"];
        $class_type_id = $data["class"];
        $class_day = $data["day"];
        $class_time = $data["time"];
        $max_students = $data["max_students"];
    
        $query = "UPDATE ".$wpdb->prefix."bt_classes SET 
        location_id=%d, 
        class_type_id=%d, 
        class_day=%d, 
        class_time=%s, 
        max_students=%d
        WHERE id=%d";

        $wpdb->query( $wpdb->prepare( $query, $location_id, $class_type_id, $class_day, $class_time, $max_students, $class_id) );
        
        wp_send_json( "class updated", 200 );
    }

    add_action( 'wp_ajax_delete_curr_class', 'delete_curr_class' );
    add_action( 'wp_ajax_nopriv_delete_curr_class', 'delete_curr_class' );

    function delete_curr_class() {
        $data = json_decode(file_get_contents('php://input'), true); 
        $class_id = $data["ID"];

        global $wpdb;

        $query = "DELETE FROM ".$wpdb->prefix."bt_classes
                  WHERE id = %d";
        
        $wpdb->query( $wpdb->prepare( $query, $class_id) );
        wp_send_json( 'current class deleted', 200 );
    }

    add_action( 'wp_ajax_save_row_location', 'save_row_location' );
    add_action( 'wp_ajax_nopriv_save_row_location', 'save_row_location' );

    function save_row_location()
    {
        global $wpdb;

        $data = json_decode(file_get_contents('php://input'), true); 
        $location_id = $data["ID"];
        $location = $data["location"];
        $address = $data["address"];
        $postcode = $data["postcode"];

        $query = "UPDATE ".$wpdb->prefix."bt_class_locations SET 
        location = %s,
        address = %s,
        post_code = %s
        WHERE id=%d";

        $wpdb->query( $wpdb->prepare( $query, $location, $address, $postcode, $location_id) );
        
        wp_send_json( "class updated", 200 );
    }

    add_action( 'wp_ajax_delete_curr_location', 'delete_curr_location' );
    add_action( 'wp_ajax_nopriv_delete_curr_location', 'delete_curr_location' );

    function delete_curr_location() {
        $data = json_decode(file_get_contents('php://input'), true); 
        $location_id = $data["ID"];

        global $wpdb;

        $query = "DELETE FROM ".$wpdb->prefix."bt_class_locations
                  WHERE id = %d";
        
        $wpdb->query( $wpdb->prepare( $query, $location_id) );
        wp_send_json( 'current location deleted', 200 );
    }

    add_action( 'wp_ajax_save_row_class_type', 'save_row_class_type' );
    add_action( 'wp_ajax_nopriv_save_row_class_type', 'save_row_class_type' );

    function save_row_class_type()
    {
        global $wpdb;

        $data = json_decode(file_get_contents('php://input'), true); 
        $class_type_id = $data["ID"];
        $class_name = $data["class_name"];
        $min_age = $data["min_age"];
        $max_age = $data["max_age"];
    
        $query = "UPDATE ".$wpdb->prefix."bt_class_type SET 
        class_name = %s,
        min_age = %d,
        max_age = %d
        WHERE id=%d";

        $wpdb->query( $wpdb->prepare( $query, $class_name, $min_age, $max_age, $class_type_id) );
        
        wp_send_json( "class updated", 200 );
    }

    add_action( 'wp_ajax_delete_class_type', 'delete_class_type' );
    add_action( 'wp_ajax_nopriv_delete_class_type', 'delete_class_type' );

    function delete_class_type() {
        $data = json_decode(file_get_contents('php://input'), true); 
        $class_type_id = $data["ID"];

        global $wpdb;

        $query = "DELETE FROM ".$wpdb->prefix."bt_class_type
                  WHERE id = %d";
        
        $wpdb->query( $wpdb->prepare( $query, $class_type_id) );
        wp_send_json( 'current location deleted', 200 );
    }

    add_action( 'wp_ajax_delete_date', 'delete_date' );
    add_action( 'wp_ajax_nopriv_delete_date', 'delete_date' );

    function delete_date() {
        $data = json_decode(file_get_contents('php://input'), true); 
        $date_id = $data["ID"];

        global $wpdb;

        $query = "DELETE FROM ".$wpdb->prefix."bt_exclude_dates
                  WHERE id = %d";
        
        $wpdb->query( $wpdb->prepare( $query, $date_id) );
        wp_send_json( 'current location deleted', 200 );
    }
?>