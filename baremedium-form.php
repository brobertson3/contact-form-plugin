<?php
/*
Plugin Name: BareMedium Contact Form
Plugin URI: 
Description: Simple non-bloated WordPress Contact Form
Version: 1.0
Author: Brent Robertson
Author URI: http://baremedium.co
*/

add_action('wp_enqueue_scripts', 'load_scripts');

function load_scripts() {
	// Load the CSS file to style the plugin
	// plugins_url is the base folder of all plugins
    wp_register_style( 'base_style', plugins_url('baremedium-form/css/style.css'));
    wp_enqueue_style( 'base_style' );
    //wp_enqueue_script( 'namespaceformyscript', 'http://locationofscript.com/myscript.js', array( 'jquery' ) );
}

function html_form_code() {
	// esc_url - sanitizes urls (rejects incorrect protocol, removes invalid/dangerous chars)
	//$_SERVER['REQUEST_URI'] grabs the URI of the current page 
	echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<p>';
	echo 'Your Name (required) <br/>';
	// Regex, only letters, spaces, and numbers allowed / must match atleast once
	// esc_attr - sanitizes text to be put into HTML (invalid chars, etc.)
	// required - HTML5 check to make sure imput not empty
	echo '<input type="text" name="cf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ) . '" size="40" required />';
	echo '</p>';
	echo '<p>';
	echo 'Your Email (required) <br/>';
	echo '<input type="email" name="cf-email" value="' . ( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ) . '" size="40" required />';
	echo '</p>';
	echo '<p>';
	echo 'Subject (required) <br/>';
	echo '<input type="text" name="cf-subject" pattern="[a-zA-Z ]+" value="' . ( isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' ) . '" size="40" required />';
	echo '</p>';
	echo '<p>';
	echo 'Your Message (required) <br/>';
	echo '<textarea rows="10" cols="35" required name="cf-message">' . ( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ) . '</textarea>';
	echo '</p>';
	echo '<p><input class="button" type="submit" name="cf-submitted" value="Send"></p>';
	echo '</form>';
}

/*function dental_wp_mail_from($content_type) {
  return $_POST["cf-email"];
}*/

function deliver_mail() {

	// if the submit button is clicked, send the email
	if ( isset( $_POST['cf-submitted'] ) ) {

		// sanitize form values
		$name    = sanitize_text_field( $_POST["cf-name"] );
		$email   = sanitize_email( $_POST["cf-email"] );
		$subject = sanitize_text_field( $_POST["cf-subject"] );
		$message = esc_textarea( $_POST["cf-message"] );

		/* Internal hook to allow plugin to modify the 'From:' value in the sent email
		 * runs the dental_wp_mail_from($content_type) function to get the email 
		 * address. Not sure if this would really work since I have to use WP Mail 
		 * SMTP plugin to send email from localhost and that plugin defaults the
		 * sent email address
		/*add_filter('wp_mail_from','dental_wp_mail_from');*/

		/* Double check to make sure email is validated
		 * HTML5 email type validation does this check already but it seems to check
		 * if there is an @ symbol, and not an actual .xxx extension. This checks for
		 * a valid domain as well. 
		 */
		if (is_email($email)) {
			// get the blog administrator's email address - set to mine for testing
			$to = "brent.robertson3@gmail.com"; //get_option( 'admin_email' );
			/* I can update the CC: value though to capture the user's email address
			 * in the header of the sent email.
			 */ 
			$headers = "CC: " . $name . " <" . $email . ">" . "\r\n";

			// If email has been processed for sending, display a success message
			if ( wp_mail( $to, $subject, $message, $headers ) ) {
				echo '<div>';
				echo '<p>Thanks for contacting me, expect a response soon.</p>';
				echo '</div>';
			} else {
				echo 'An unexpected error occurred';
			}

			// Clear all of the fields in the form on submission
			$_POST["cf-name"] = "";
			$_POST["cf-email"] = "";
			$_POST["cf-subject"] = "";
			$_POST["cf-message"] = "";
		} else {
		 	echo '<p>A valid email address should be provided</p>';
		}
	}
	
}

// Creates the shortcode to be placed anywhere on any page.
function cf_shortcode() {
	ob_start();
	deliver_mail();
	html_form_code();

	return ob_get_clean();
}

add_shortcode( 'baremedium_contact_form', 'cf_shortcode' );

?>