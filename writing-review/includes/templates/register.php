<?php  
/* 
Template Name: Register 
*/  
   
get_header();   
?>
<header class="entry-header">

  <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

</header><!-- .entry-header -->
<?php
global $wpdb, $user_ID;  
   
    $errors = array();  
   
    if( $_SERVER['REQUEST_METHOD'] == 'POST' ) 
      {  
   
        // Check username is present and not already in use  
        $username = $wpdb->escape($_REQUEST['username']);  
        if ( strpos($username, ' ') !== false )
        {   
            $errors['username'] = "Sorry, no spaces allowed in usernames";  
        }  
        if(emptyempty($username)) 
        {   
            $errors['username'] = "Please enter a username";  
        } elseif( username_exists( $username ) ) 
        {  
            $errors['username'] = "Username already exists, please try another";  
        }  
   
        // Check email address is present and valid  
        $email = $wpdb->escape($_REQUEST['email']);  
        if( !is_email( $email ) ) 
        {   
            $errors['email'] = "Please enter a valid email";  
        } elseif( email_exists( $email ) ) 
        {  
            $errors['email'] = "This email address is already in use";  
        }  
   
        // Check password is valid  
        if(0 === preg_match("/.{6,}/", $_POST['password']))
        {  
          $errors['password'] = "Password must be at least six characters";  
        }  
   
        // Check password confirmation_matches  
        if(0 !== strcmp($_POST['password'], $_POST['password_confirmation']))
         {  
          $errors['password_confirmation'] = "Passwords do not match";  
        }  
   
        // Check terms of service is agreed to  
        if($_POST['terms'] != "Yes")
        {  
            $errors['terms'] = "You must agree to Terms of Service";  
        }  
   
        if(0 === count($errors)) 
         {  
   
            $password = $_POST['password'];  
   
            $new_user_id = wp_create_user( $username, $password, $email );  
   
            // You could do all manner of other things here like send an email to the user, etc. I leave that to you.  
   
            $success = 1;  
   
            //header( 'Location:' . get_bloginfo('url') . '/login/?success=1&u=' . $username );  
   
        } 
    }
?>  
  
<form id="wp_signup_form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">  
  
        <label for="username">Username: </label>  
        <input type="text" name="username" id="username"><br><br>
        <label for="email">Email address: </label>  
        <input type="text" name="email" id="email">  <br><br>
        <label for="password">Password: </label>  
        <input type="password" name="password" id="password"><br><br>  
        <label for="password_confirmation">Confirm Password: </label>  
        <input type="password" name="password_confirmation" id="password_confirmation">  <br><br>
  
        <input name="terms" id="terms" type="checkbox" value="Yes">  
        <label for="terms">I agree to the Terms of Service</label>  
  
        <input type="submit" id="submitbtn" name="submit" value="Sign Up" />  
  
</form>  
  
<br><br><br>

<?php get_footer(); ?>