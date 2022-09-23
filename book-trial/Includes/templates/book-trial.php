<?php

/**

 * Template Name: Book Trial

 *

 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/

 *

 * @package _s

 */

?>

<?php
global $nameError;
global $emailError;
global $commentError;
global $emailSent;
global $contactNumberError;
global $birthdayError;
global $classError;
global $dateError;


if(isset($_POST['submitted'])) {
	if(trim($_POST['contactName']) === '') {
		$nameError = 'Please enter your name.';
		$hasError = true;
	} else {
		$contactname = trim($_POST['contactName']);
	}

	$childname = trim($_POST['childName']);

	if(trim($_POST['birthday']) === '') {
		$birthdayError = 'Please enter birthday.';
		$hasError = true;
	} else {
		$birthday = trim($_POST['birthday']);
	}

	if(trim($_POST['class']) === '') {
		$classError = 'Please select a class.';
		$hasError = true;
	} else {
		$class = trim($_POST['class']);
	}

	if(trim($_POST['date']) === '') {
		$dateError = 'Please select a class date.';
		$hasError = true;
	} else {
		$date = trim($_POST['date']);
	}

	if(trim($_POST['email']) === '')  {
		$emailError = 'Please enter your email address.';
		$hasError = true;
	} else if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($_POST['email']))) {
		$emailError = 'You entered an invalid email address.';
		$hasError = true;
	} else {
		$email = trim($_POST['email']);
	}

    if(trim($_POST['contactNumber']) === '')  {
		$contactNumberError = 'Please enter your contact number.';
		$hasError = true;
	} else {
		$contactNumber = trim($_POST['contactNumber']);
	}

	if(trim($_POST['comments']) === '') {
		if(function_exists('stripslashes')) {
			$comments = stripslashes(trim($_POST['comments']));
		} else {
			$comments = trim($_POST['comments']);
		}
	}

	if(!isset($hasError)) {
		//Send email to owner
		$emailTo = get_option('tz_email');
		if (!isset($emailTo) || ($emailTo == '') ){
			$emailTo = get_option('admin_email');
		}

		$query = "SELECT * FROM ".$wpdb->prefix."bt_classes c 
		JOIN ".$wpdb->prefix."bt_class_locations l ON c.location_id = l.id
		JOIN ".$wpdb->prefix."bt_class_type ct ON ct.id = c.class_type_id
		WHERE c.id = %d";

		$class_list = $wpdb->get_results( $wpdb->prepare( $query, $class ) );

		$subject = 'New student booked in for trial ';

		foreach($class_list as $class_info) {
			$body = "Name: $contactname \n\nChild Name: $childname \n\nBirth date: $birthday \n\nClass: $class_info->class_name \n\n $class_info->class_time \n\n $class_info->location \n\nDate: $date \n\nEmail: $email \n\n Phone: $contactNumber \n\nComments: $comments";
		}

		$headers = 'From: '.$contactname.' <'.$email.'>' . "\r\n" . 'Reply-To: ' .$emailTo ;
		
		wp_mail($emailTo, $subject, $body, $headers);


		//Send email to customer
		$subject = 'You are booked in for a trial class with South Bucks TKD';
		$body = "Thank you for booking a trial lesson with us. We look forward to meeting you \n\n";
		if (isset( $childname)) {
			$body = $body."Please make sure you wear comfortable athletic clothing. And bring water.\n\n";
		}		 
		else {
			$body = $body."Please make sure your child wears comfortable athletic clothing. And has a bottle of water.\n\n";
		}

		$body = $body."Class Date: $date\n\n";

		foreach($class_list as $class_info) {
			$body = $body."Class Time: $class_info->class_time\n\n Class Location: $class_info->location\n\n$class_info->address\n\n$class_info->post_code\n\n If you have any questions you can reply to this email or call us on 07412 750000";
		}

		$headers = 'From: South Bucks Tkd <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;

		wp_mail($email, $subject, $body, $headers);

		$emailSent = true;

		$datetoinsert = date("Y-m-d", strtotime(strtr($date, '/', '-')));

		
		//$datetoinsert = date_format($date, "Y-m-d H:i:s");
		$trial_query = $wpdb->prepare("INSERT INTO ".$wpdb->prefix."bt_class_booked (class_date, class_id, email, student_name)
		VALUES (%s, %d, %s, %s)", $datetoinsert, $class, $email, $childname);
        $wpdb->query($trial_query);
	}

} ?>


<?php get_header();?>


	<main id="primary" class="site-main">

	    <header class="entry-header">

    		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

    	</header><!-- .entry-header -->

	    <div id="container">
			<div id="content">
				<?php the_post() ?>
				<div id="post-<?php the_ID() ?>" class="post">
					<div class="entry-content">
						<?php 
							if($emailSent) { ?>
								<span class="sent"><b>  Thank you for booking your trial session. We look forward to meeting you.</b> </span>
							<?php }?>

						<p>Please fill in your details and select which class you'd like a trial for </p>
						<form action="<?php the_permalink(); ?>" id="contactForm" method="post">

									<label for="contactName">Parent/Guardian Name*:</label>
									<input class="nameonform" type="text" name="contactName" id="contactName" value="<?php if(isset($_POST['contactName'])) echo $_POST['contactName'];?>" class="required requiredField" />
									<?php if($nameError != '') { ?>
										<span class="error"><?=$nameError;?></span>
									<?php } ?>

									<br><br>

									<label for="childName">Child Name*:</label>
									<input type="text" name="childName" id="childName" value="<?php if(isset($_POST['childName'])) echo $_POST['childName'];?>" />

									<br><br>

									<label for="birthday">Birthday*:</label>
									<input type="date" id="birthday" name="birthday">
									<?php if($birthdayError != '') { ?>
										<span class="error"><?=$birthdayError;?></span>
									<?php } ?>
									<br><br>	
									
									<label for="class">Class*:</label>
									<select for="class" name="class" id="class">Class- Day, Time, Location</label>
										<option disabled="" selected="">--Select Class--</option>
									</select>

									<br><br>

									<label for="date">Free Trial Date*:</label>
									<select for="date" name="date" id="date"></label>
										<option disabled="" selected="">--Date--</option>
									</select>

									<br><br>

									<label for="email">Email*: </label>
									<input type="text" name="email" id="email" value="<?php if(isset($_POST['email']))  echo $_POST['email'];?>" class="required requiredField email" />
									<?php if($emailError != '') { ?>
										<span class="error"><?=$emailError;?></span>
									<?php } ?>
									
									<br><br>

                                    <label for="contactNumber">Contact Number*:  </label>
									<input type="number" name="contactNumber" id="contactNumber" value="<?php if(isset($_POST['contactNumber']))  echo $_POST['contactNumber'];?>" class="required requiredField contactNumber" />
									<?php if($contactNumberError != '') { ?>
										<span class="error"><?=$contactNumberError;?></span>
									<?php } ?>
									
									<br><br>

									<label for="commentsText">Any additional details/medical conditions:</label> 
									<textarea name="comments" id="commentsText" rows="5" cols="30"><?php if(isset($_POST['comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['comments']); } else { echo $_POST['comments']; } } ?></textarea>

									<br>

									<button type="submit">Submit details</button>

							<input type="hidden" name="submitted" id="submitted" value="true" />
							<br><br>
						</form>

					</div><!-- .entry-content -->
				</div><!-- .post-->
			</div><!-- #content -->
		</div><!-- #container -->
	</main><!-- #main -->



<?php

get_sidebar();

get_footer();

