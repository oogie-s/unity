<?php

/*
 * Add my new menu to the Admin Control Panel
*/
// Hook the 'admin_menu' action hook
add_action('admin_menu', 'wr_menu');
// Add a new top level menu link to the ACP
function wr_menu()
{
    add_menu_page('Writing Review', // Title of the page
    'Writing Review', // Text to show on the menu link
    'wr_manage_options', // Capability requirement to see the link
    //plugin_dir_path(__FILE__) .'wr-edit-page.php', // The 'slug' - file to display when clicking the link
    'wr-review-main', //menu slug
    'wr_review_main_page', //function
    'dashicons-media-code', //icon url
    '4'
    //position
    );

    add_submenu_page('wr-review-main', 'Individual Review', 'Individual Review', 'ind_review', 'wr-ind-review', //menu slug
    'wr_review_ind', //function
    '');

    add_submenu_page('wr-review-main', 'Review Settings', 'Review Settings', 'rev_settings', 'wr-review-settings', //menu slug
    'wr_review_settings', //function
    '');
}

function wr_review_main_page()
{
    require_once plugin_dir_path(__FILE__) . 'wr-edit-page.php';
}

function wr_review_ind()
{
    require_once plugin_dir_path(__FILE__) . 'wr-ind-review.php';
}

function wr_review_settings()
{
    require_once plugin_dir_path(__FILE__) . 'wr-review-settings.php';
}

add_action('init', 'wr_create_page_main');
//register_activation_hook( __FILE__, 'btf_create_page' );
function wr_create_page_main()
{
    $wordpress_page = array(
        'post_title' => 'WRITING REVIEW',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'page',
        'page_template' => 'templates/writing-review.php'
    );

    $page_exists = get_page_by_title($wordpress_page['post_title']);

    if ($page_exists == null)
    {
        // Page doesn't exist, so lets add it
        $insert = wp_insert_post($wordpress_page);
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

add_action('init', 'wr_review_details');

function wr_review_details()
{
    $wordpress_page = array(
        'post_title' => 'WRITING REVIEW- Review Details',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'page',
        'page_template' => 'templates/review-details.php'
    );

    $page_exists = get_page_by_title($wordpress_page['post_title']);

    if ($page_exists == null)
    {
        // Page doesn't exist, so lets add it
        $insert = wp_insert_post($wordpress_page);
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

/**
 * Enqueue scripts and styles.
 */
add_action('admin_enqueue_scripts', 'wr_scripts');

function wr_scripts()
{
    wp_enqueue_style('styles', plugins_url('style.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'wr_front_scripts');
function wr_front_scripts()
{
    wp_enqueue_script('wr-tandc-check', plugins_url('wr-tandc-check.js', __FILE__) , '', null, true);
    wp_enqueue_script('wr-tandc-link', plugins_url('wr-tandc-link.js', __FILE__) , '', null, true);
    wp_enqueue_style('wr-style-cust', plugins_url('style-cust.css', __FILE__));
}

add_action('init', 'wr_create_page_login');
//register_activation_hook( __FILE__, 'btf_create_page' );
function wr_create_page_login()
{
    $wordpress_page = array(
        'post_title' => 'Login',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'page',
        'page_template' => 'templates/new-login.php'
    );

    $page_exists = get_page_by_title($wordpress_page['post_title']);

    if ($page_exists == null)
    {
        // Page doesn't exist, so lets add it
        $insert = wp_insert_post($wordpress_page);
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

add_action('init', 'wr_create_page_register');
//register_activation_hook( __FILE__, 'btf_create_page' );
function wr_create_page_register()
{
    $wordpress_page = array(
        'post_title' => 'Register',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type' => 'page',
        'page_template' => 'templates/register.php'
    );

    $page_exists = get_page_by_title($wordpress_page['post_title']);

    if ($page_exists == null)
    {
        // Page doesn't exist, so lets add it
        $insert = wp_insert_post($wordpress_page);
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

if (!function_exists('populate_roles'))
{
    require_once (ABSPATH . 'wp-admin/includes/schema.php');
}

populate_roles();

/* Add new role type so they can be a reviewer */
// Add a custom user role
$result = add_role('reviewer', __('Reviewer') , array(
    /* List of what the reviewer has access to */
    'read' => true,
    'edit_dashboard' => true,
    'edit_posts' => true,
    'create_posts' => true,
    'publish_posts' => true,
    'wr_manage_options' => true, //the capability to edit writing review
    'ind_review' => true,
    'edit_themes' => false, // false denies this capability. User can’t edit your theme
    'edit_others_posts' => true
    // Allows user to edit others posts not just their own
    
));

/* Add the wr_manage_options capability to admin */
function add_review_caps()
{
    // gets the author role
    $role = get_role('administrator');

    // This only works, because it accesses the class instance.
    // would allow the author to edit others' posts for current theme only
    $role->add_cap('wr_manage_options');
    $role->add_cap('ind_review');
    $role->add_cap('rev_settings');

}
add_action('admin_init', 'add_review_caps');

/* Add page templates */
add_filter('theme_page_templates', 'wr_register_page_template');

/**
 * Register Page Template: writing review
 * @since 1.0.0
 */
function wr_register_page_template($templates)
{
    $templates['templates/writing-review.php'] = 'Writing Review';
    return $templates;
}

/* Add page templates */
add_filter('theme_page_templates', 'wr_register_page_template_login');

/**
 * Register Page Template: login
 * @since 1.0.0
 */
function wr_register_page_template_login($templates)
{
    $templates['templates/new-login.php'] = 'New Login';
    return $templates;
}

/* Add page templates */
add_filter('theme_page_templates', 'wr_register_page_template_register');

/**
 * Register Page Template: register
 * @since 1.0.0
 */
function wr_register_page_template_register($templates)
{
    $templates['templates/register.php'] = 'Register';
    return $templates;
}

add_filter('page_template', 'wr_redirect_page_template');

function wr_redirect_page_template($template)
{
    $post = get_post();
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);
    if ('writing-review.php' == basename($page_template)) $template = WP_PLUGIN_DIR . '/writing-review/includes/templates/writing-review.php';
    return $template;
}

add_filter('page_template', 'wr_redirect_page_template_login');

function wr_redirect_page_template_login($template)
{
    $post = get_post();
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);
    if ('new-login.php' == basename($page_template)) $template = WP_PLUGIN_DIR . '/writing-review/includes/templates/new-login.php';
    return $template;
}

add_filter('page_template', 'wr_redirect_page_template_register');

function wr_redirect_page_template_register($template)
{
    $post = get_post();
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);
    if ('register.php' == basename($page_template)) $template = WP_PLUGIN_DIR . '/writing-review/includes/templates/register.php';
    return $template;
}

/*add_action( 'wp', 'redirect_to_writing_review' );
    function redirect_to_writing_review() {
        $post = get_post();
        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );

        //You can use also is_page() function to check for specific page instead for a page template
        if( (is_user_logged_in()) && ('new-login.php' == basename ($page_template) OR ('register.php' == basename ($page_template)))) {
            wp_redirect( site_url('/index.php/student-writing-review'));
            exit();
        }
    }*/

function member_only_shortcode($atts, $content = null)
{
    if (is_user_logged_in() && !is_null($content) && !is_feed())
    {
        return $content;
    }
}
add_shortcode('member_only', 'member_only_shortcode');

function not_member_only_shortcode($atts, $content = null)
{
    if (!is_user_logged_in())
    {
        return $content;
    }
}
add_shortcode('not_member', 'not_member_only_shortcode');

function writing_review_activate()
{

    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/writingreview';
    if (!is_dir($upload_dir))
    {
        mkdir($upload_dir, 0700);
    }
}

register_activation_hook(__FILE__, 'writing_review_activate');

function update_review_status($status, $review_id)
{
    /*Different permissions.
    0- Permission not asked
    1- Permission asked
    2- Allowed to publish
    3- Not allowed to publish
    4- Published*/

    global $wpdb;

    $query_update_status = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_review SET status = %d WHERE ID= %d;", $status, $review_id);
    $wpdb->query($query_update_status);
}

function wr_publish_post($post_title, $post_content, $cust_id)
{
    global $user_ID;
    $new_post = array(
        'post_title' => $post_title,
        'post_content' => $post_content,
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s') ,
        'post_author' => $cust_id,
        'post_type' => 'creativewriting',
        'post_category' => array(
            0
        ) ,
    );
    $post_id = wp_insert_post($new_post);
}

function wr_close_review($review_id)
{
    global $wpdb;
    $close_review = $wpdb->prepare("UPDATE " . $wpdb->prefix . "wr_review SET closed = 1 
            WHERE id = %d;", $review_id);
    $wpdb->query($close_review);
}

?>
