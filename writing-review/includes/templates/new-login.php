<?php  
/** 
 * Template Name: login page 
 */  
 get_header();   
?>
<header class="entry-header">

  <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

</header><!-- .entry-header -->
<?php   
if($_POST) 
{  
   
    global $wpdb;  
   
    //We shall SQL escape all inputs  
    $username = $wpdb->escape($_REQUEST['username']);  
    $password = $wpdb->escape($_REQUEST['password']);  
    $remember = $wpdb->escape($_REQUEST['rememberme']);  
   
    if($remember) $remember = "true";  
    else $remember = "false";  
   
    $login_data = array();  
    $login_data['user_login'] = $username;  
    $login_data['user_password'] = $password;  
    $login_data['remember'] = $remember;  
   
    $user_verify = wp_signon( $login_data, false );   
   
    if ( is_wp_error($user_verify) )   
    {  
        echo "Invalid login details";  
       // Note, I have created a page called "Error" that is a child of the login page to handle errors. This can be anything, but it seemed a good way to me to handle errors.  
     } else
    {    
       echo "<script type='text/javascript'>window.location.href='". home_url() ."'</script>";  
       exit();  
     }  
   
} else 
{  
   
    // No login details entered - you should probably add some more user feedback here, but this does the bare minimum  
   
    //echo "Invalid login details";  
   
}  
 ?>  
  
<form id="login1" name="form" action="<?php echo home_url(); ?>/login/" method="post">  
  
        <label for="username">Username: </label><input id="username" type="text" placeholder="Username" name="username"><br><br>  
        <label for="password">Password: </label><input id="password" type="password" placeholder="Password" name="password">
        <a  href="<?php echo site_url('/index.php/register'); ?>">Forgotten password</a>
        <br><br>  
        <input id="submit" type="submit" name="submit" value="Submit">  
        <p>If you do not have an account <a  href="<?php echo site_url('/index.php/register'); ?>">Register</a></p>
</form>    
<?php get_footer(); ?>  