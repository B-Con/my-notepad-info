<?php
/**
 * API backend for My Notepad Info.
 *
 * Usage:
 * ?action=X&username=X&password=X&[...]
 *
 * @author  Brad Conte <brad@bradconte.com>
 * @since   January 2013
 * @link    http://mynotepad.info
 * @version 0.1
 */


// =============================================================================
// Documentation
// =============================================================================

/*
This is a basic RESTful API in PHP on top of MySQL.

The client makes AJAX queries to this API and the server returns XML. Each query
contains authentication and all relevant data. $_REQUEST is used so that GET and POST
data can be accepted.

A typical workflow is that the client first issues a "login" request to
populate the user's information, then issues "save" requests until they
logout (which does not require an API call).

DB structure overview:
	id                    // Unique, auto-increment.
	username              // Primary Key.
	password_hash
	salt                  // For simplicity and legacy support, the salt has its own field.
	pwd_version           // Tracks what version of the pwd hash derivation is being used.
	bad_login_counter
	bad_login_timestamp
	email
	email_verified        // Whether the supplied email address has been confirmed.
	notepad_data          // UTF-8_bin
	height
	width
	font_color
	background_color
	autosave              // Default: 1
	last_seen
	login_counter         // Default: 0. Helps track usage without being too detailed.

The current host is somewhat restrictive:
	* At most 18 concurrent database connections.
	    - Mitigated by failed connects going to randomly-timed backoff retries.
	* DB queries must be smaller than 1M.
	    - Mitigated by enforced size restrictions on notepad data.
*/


// =============================================================================
// Includes
// =============================================================================

require 'KLogger.php';         // Logger. From https://github.com/katzgrau/KLogger .
require 'config.php';          // Local configuration settings.
require 'api-lib.php';         // Constants, classes, and functions.


// =============================================================================
// Main program
// =============================================================================

	// Randomly time ourselves from start to finish.
	if (rand() % SELF_TIME_INVERVAL == 1) {
		$timed_self = TRUE;
		$time_start = microtime(true);
	}
	else {
		$timed_self = FALSE;
	}

	// Set the relevant headers before any type of output, including errors.
	setOutputHeaders();

	// Start the response to the client.
	$response = new APIResponse();

	// Set up logging. Never use lower than level "Info" in real environment; level
	// "Debug" exposes sensative information like passwords!
	// Object currently doesn't offer a way to see if it was created successfully.
	$logger = new KLogger('../logs/', KLogger::INFO);
	if (FALSE) {
		emailAdmin('Logging failure', 'Failed to initialize logger.');
		$response->set_generic_error();
		respondAndDie($response);
	}
	$logger->setDateFormat('Y-m-d H:i:s');

	$logger->LogDebug("API startup");

	// Connect to the database.
	$db_link = new DBWrapper();
	if (!$db_link->isValid()) {
		$logger->LogCrit('Failed to connect to database.');
		$response->set_generic_error();
		respondAndDie($response);
	}

	/*
	 * A long API handler block.
	 * For each action retrieve the necessary parameters, do error checking, then process request.
	 */

	$api_action = strtolower($_REQUEST['action']);
	$logger->LogDebug("Handling API $api_action");

	switch ($api_action) {

		// -------------------------------------------------------------------------
		// LOG A USER IN: Verify the user's credentials and return their notepad and settings.
		// -------------------------------------------------------------------------
		case 'login':
			$username = base64URLDecode($_REQUEST['username']);
			$password = base64URLDecode($_REQUEST['password']);

			$db_row = loginUser($username, $password, $response, TRUE);
			if ($db_row !== FALSE) {
				// In case the username contains special HTML characters, display them properly.
				$username = base64URLEncode(htmlentities($db_row['username']));

				// Ensure the notepad field isn't blank, or the client XML parser will skip it.
				if (strlen($db_row['notepad_data']) > 0)
					$notepad_data = base64URLEncode($db_row['notepad_data']);
				else
					$notepad_data = base64URLEncode(' ');

				$response->setCodeAndStatus('success', 'You are logged in.');
				$response->setField('notepad_data', $notepad_data);
				$response->setField('username', $username);
				$response->setField('height', $db_row['height']);
				$response->setField('width', $db_row['width']);
				$response->setField('font_style', $db_row['font_style']);
				$response->setField('font_color', $db_row['font_color']);
				$response->setField('background_color', $db_row['background_color']);
				$response->setField('autosave', ($db_row['autosave'] == 1) ? 'true' : 'false');

				$logger->LogInfo('User ' . $db_row['username'] . ' logged in.');
			}
			else {
				$logger->LogInfo("Failed login attempt for username $username");
			}
		break;


		// -------------------------------------------------------------------------
		// SAVE NOTEPAD DATA: The most used API. Verify user and return the notepad and settings.
		// -------------------------------------------------------------------------
		case 'save':
			if (siteIsReadOnly($response)) {
				break;
			}

			$username = base64URLDecode($_REQUEST['username']);
			$password = base64URLDecode($_REQUEST['password']);

			$db_row = loginUser($username, $password, $response);
			if ($db_row !== FALSE) {
				$saved_verbage = ($_REQUEST['mode'] == 'auto') ? 'autosaved' : 'saved';

				$height = $_REQUEST['height'];
				$width = $_REQUEST['width'];

				// Colors are stored lower-case, sans '#'.
				$font_color = '#' . strtolower($_REQUEST['font_color']);
				$background_color = '#' . strtolower($_REQUEST['background_color']);

				// Misc santity checks. If something looks insane/invalid, override it.
				if ($height < 50 || $height > 3000)
					$height = $defaults['height'];
				if ($width < 50 || $width > 3000)
					$width = $defaults['width'];

                $args = array(':notepad_data' => base64URLDecode($_REQUEST['notepad_data']),
                    ':height' => $height,
                    ':width' => $width,
                    ':font_color' => $font_color,
                    ':background_color' => $background_color,
                    ':autosave' => ($_REQUEST['autosave'] == 'true') ? 1 : 0,
                    ':username' => $username);
				if (strlen($args[':notepad_data']) < MAX_NOTEPAD_SIZE) {
					$q = 'UPDATE users SET ' .
						 "notepad_data=:notepad_data," .
						 "height=:height," .
						 "width=:width," .
						 "font_color=:font_color," .
						 "background_color=:background_color," .
						 "autosave=:autosave " .
						 "WHERE username=:username LIMIT 1";
					$result = $db_link->safeQuery($q, $args, false);

					if ($result === TRUE) {
						// Client appends the timestamp to the status.
						$response->setCodeAndStatus('success', "Notepad $saved_verbage ");
					}
					else {
						$response->setCodeAndStatus('failure', 'Notepad could not be ' .
						                            $saved_verbage . '.');
					}
				}
				else {
					$logger->LogInfo('Attempted save of too long a notepad.');
					$response->setCodeAndStatus('failure', ERR_NOTEPAD_TOO_BIG);
				}
			}
			else {
				$logger->LogWarn("User $username attempted to save with invalid credentials. " .
				                 'Password ' . (strlen($password) ? 'was' : "wasn't") . ' present.');
				$response->appendStatus(ERR_SAVE_LOGIN_FAILED);
			}
		break;


		// -------------------------------------------------------------------------
		// CHANGE A USER'S PROFILE INFO: Update all the fields they explicitly set.
		// -------------------------------------------------------------------------
		case 'change_profile':
			if (siteIsReadOnly($response)) {
				break;
			}

			$username = base64URLDecode($_REQUEST['username']);
			$old_password = base64URLDecode($_REQUEST['old_password']);
			$new_password = base64URLDecode($_REQUEST['new_password']);
			$new_email = base64URLDecode($_REQUEST['new_email']);

			if (loginUser($username, $old_password, $response) !== FALSE) {
				$success = TRUE;

				// Update all of the profile or none at all.
				$db_link->beginTransaction();

				// Change the password if it was specified.
				if (strlen($new_password) > 0) {
					if (changeUserPassword($username, $new_password) === TRUE) {
						$logger->LogInfo("User $username has successfully changed their password");
					}
					else {
						$success = FALSE;
						$logger->LogCrit("Error changing $username 's password");
						$response->setCodeAndStatus('failure', ERR_UPDATING_PROFILE);
					}
				}

				// Change the email if it was specified.
				if (strlen($new_email) > 0 && $success === TRUE) {
					$q = "UPDATE users SET email=:new_email WHERE username=:username LIMIT 1";
					$args = array(':new_email' => $new_email,
								  ':username' => $username);
					$result = $db_link->safeQuery($q, $args, FALSE);

					if ($result === TRUE) {
						$logger->LogInfo("User $username has successfully changed their email");
					}
					else {
						$success = FALSE;
						$logger->LogError("Error changing $username e-mail address to $new_email");
						$response->setCodeAndStatus('failure', ERR_UPDATING_PROFILE);
					}
				}

				if ($success === TRUE) {
					$db_link->commit();
					$response->setCodeAndStatus('success', 'Your profile has been updated.');
				}
				else {
					$db_link->rollBack();
				}

			}
			else {
				$logger->LogInfo("User $username supplied incorrect credentials trying to reset " .
								 'their password.');
				$response->appendStatus('Password was not reset.');
			}
		break;


		// -------------------------------------------------------------------------
		// REGISTER A NEW USER: Create a new user with a unique username and ID.
		// -------------------------------------------------------------------------
		case 'register':
			if (siteIsReadOnly($response)) {
				break;
			}

			$username = base64URLDecode($_REQUEST['username']);
			$password = base64URLDecode($_REQUEST['password']);
			$email = base64URLDecode($_REQUEST['email']);

			// Verify that the username and password are valid. 50 is arbitrary.
			if (strlen($username) == 0 || strlen($username) > 50) {
				$response->setCodeAndStatus('failure', ERR_INVALID_USERNAME);
			}
			// Don't impose stupid password limitations. Allow *all* passwords.
			else if (strlen($password) == 0) {
				$response->setCodeAndStatus('failure', ERR_INVALID_PASSWORD);
			}
			else {
				// Verify that the username is unused.
				if (loadUserInfo($username) === FALSE) {
					if (createUser($username, $password, $email))
						$response->setCodeAndStatus('success', SUC_REGISTERED);
					else
						$response->set_generic_error();
				}
				else {
					$logger->LogInfo("Attempted duplicate username registration for $username");
					$response->setCodeAndStatus('failure', ERR_USERNAME_TAKEN);
				}
			}
		break;


		// -------------------------------------------------------------------------
		// RESET A PASSWORD: Reset a password based on a username. Requires them to have an e-mail.
		// -------------------------------------------------------------------------
		case 'reset_pwd':
			if (siteIsReadOnly($response)) {
				break;
			}

			$username = base64URLDecode($_REQUEST['username']);

			$db_row = loadUserInfo($username, $response);
			if ($db_row !== FALSE) {
				$email = $db_row['email'];

				if ($email != '') {
					$logger->LogInfo("User $username is resetting their password.");

					// Roll back the transaction if anything fails.
					$db_link->beginTransaction();

					/*
					 * Generate a new random password and send it to them.
					 */

					$new_password = generateRandomString(TMP_PWD_LENGTH);
					$logger->LogDebug("New password: $new_password");

					$result = changeUserPassword($username, $new_password);

					if ($result === TRUE) {
						// We need to ensure the e-mail was successfully sent, otherwise they won't
						// be able to log in.
						$result = emailUser($username, $email, 'Your password reset'
						                     sprintf(EMAIL_PWD_RESET, $username, $new_password));

						if ($result === TRUE)
							$response->setCodeAndStatus('success', SUC_PWD_RESET);
						else
							$response->setCodeAndStatus('failure', ERR_PWD_RESET_EMAIL);
					}
					else {
						$response->setCodeAndStatus('failure', ERR_PWD_RESET);
						$logger->LogCrit("Failed to update user $username 's password");
					}

					if ($result === TRUE)
						$db_link->commit();
					else
						$db_link->rollBack();
				}
				else {
					$logger->LogInfo("User $username has tried to reset their password but has " .
									 'no e-mail address.');

					$response->setCodeAndStatus('failure', ERR_PWD_RESET_NO_EMAIL);
				}
			}
		break;


		// -------------------------------------------------------------------------
		// EMAIL NOTEPAD: E-Mail a notepad content's to a user based on their username.
		// -------------------------------------------------------------------------
		case 'email_notepad':
			// We don't require a password to do this, for recovery reasons. So
			// don't return any account-specific information in the status, such
			// as the e-mail address.
			$username = base64URLDecode($_REQUEST['username']);
			$password = base64URLDecode($_REQUEST['password']);

			$logger->LogDebug("User $username is requesting their notepad e-mailed to them.");

			$db_row = loadUserInfo($username, $response);
			$user_logged_in = loginUser($username, $password, $response) ? TRUE : FALSE;

			if ($db_row !== FALSE) {
				$email = $db_row['email'];

				if ($email != '') {
					$notepad_data = $db_row['notepad_data'];

					$mail_sent = emailUser($username, $email, 'Your Notepad Info',
					                       sprintf(EMAIL_NOTEPAD, $username, $notepad_data));

					if ($mail_sent === TRUE) {
						$logger->LogInfo("Sent notepad data e-mail to $username @ $email");

						// If they sent the request with a password, tell them what e-mail it was
						// sent to. Ignore a failed login... login isn't required anyway.
						if ($user_logged_in)
							$response->setCodeAndStatus('success',
							                            sprintf(SUC_NOTEPAD_EMAIL_LOGIN, $email));
						else
							$response->setCodeAndStatus('success', SUC_NOTEPAD_EMAIL);
					}
					else {
						$logger->LogError("Could not send notepad data e-mail to $username");

						if ($user_logged_in)
							$response->setCodeAndStatus('failure',
							                            sprintf(ERR_NOTEPAD_EMAIL_LOGIN, $email));
						else
							$response->setCodeAndStatus('failure', ERR_NOTEPAD_EMAIL);
					}
				}
				else {
					$logger->LogInfo("Notepad e-mail requested by user $username, but they had " .
					                 'no e-mail.');
					$response->setCodeAndStatus('failure', ERR_NOTEPAD_NO_EMAIL);
				}
			}

		break;


		// -------------------------------------------------------------------------
		// SUBMIT FEEDBACK: Dispatch feedback to the admin, submitted by anyone.
		// -------------------------------------------------------------------------
		case 'submit_feedback':
			$name = base64URLDecode($_REQUEST['name']);
			$email = base64URLDecode($_REQUEST['email']);
			$subject = base64URLDecode($_REQUEST['subject']);
			$message = base64URLDecode($_REQUEST['message']);

			$logger->LogInfo("Submitting user feedback.");
			$success = emailAdmin("Feedback: $subject",
								   "Feedback on My Notepad Info.\n\n" .
								   "Name: $name \n" .
								   "E-Mail: $email \n" .
								   "Subject: $subject \n" .
								   "Message: \n" .
								   $message);

			if ($success === TRUE) {
				$logger->LogInfo("User feedback submitted from $name.");
				$response->setCodeAndStatus('success', SUC_SUBMIT_FEEDBACK);
			}
			else {
				$logger->LogError('User feedback failed.');
				$response->setCodeAndStatus('failure', ERR_SUBMIT_FEEDBACK);
			}
		break;

		// -------------------------------------------------------------------------
		// LOG A MESSAGE: Write a message to server's log. Allows client-side errors to be logged.
		// -------------------------------------------------------------------------
		case 'log':
			/* TODO: Implement this. Accept two args, "log level" and "message". Flag it as a
			 * client-initiated log so a client can't insert their own logs that maliciously look
			 * like server logs. Consider further security ramifications.
			 */
			/*

			$log_level = $REQUEST['log_level'];
			$log_msg = $REQUEST['log_msg'];

			// Verify $log_level

			$logger->Log('Client log msg: ' . $log_message);

			*/
		break;


		// -------------------------------------------------------------------------
		// The API requested is not handled. Maybe programmer error, or possibly fuzzing.
		// -------------------------------------------------------------------------
		default:
			$logger->LogWarn("Unknown API request from client: $api_action");
			$response->setCodeAndStatus('failure', 'Unknown action requested. If you recieved ' .
										   'this error through normal operation, please ' .
										   "<a href='about.php'>contact</a> the admin staff.");
	}

	/*
	 * Finish up and send the response back to the client.
	 */

	$logger->LogDebug("API wind down");

	$db_link->disconnect();

	echo $response->getXML();

	if ($timed_self) {
		$time_stop = microtime(true);
		$exec_time = ($time_stop - $time_start) * 1000;

		// Special characters make "cut"-ing easier.
		$logger->LogInfo("API execution time for <$api_action> took [$exec_time] milliseconds.");
	}
?>
