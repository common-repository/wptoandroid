<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	if (wp_verify_nonce( $_REQUEST['_wpnonce'], 'tell_friend_' )) {

		if (isset($_POST) && isset($_POST['email'])) {
			$email = sanitize_email($_POST['email']);
			$message = sanitize_text_field($_POST['texto']);
			$message_email = "<html>
							<head>
							</head>
							<body>
								<div>
									<strong>Please Download this awesome plugin!</strong>
									<table cellpadding='3'>
										<tr style='padding-top:10px;'>
											<td><strong>Please download this plugin which is usefull to bring you a mobile experience!!</strong></td>
										</tr>
										".$message."
									</table>
									<br/>
								</div>
							</body>
						</html>";

			$emails = explode(",", $email);
			foreach ($emails as $email_aux) {
				$subject = "Please ".$email_aux." check this plugin for your wordpress site!";
				$mail_sent = @mail( $email_aux, $subject, $message_email);
			}
		}

		echo "success";
	} else {

		die();
	}
?>