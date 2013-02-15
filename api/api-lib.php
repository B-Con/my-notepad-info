<?php

// =============================================================================
// Constants
// =============================================================================

/*
 * Site info.
 */
define('SITE_NAME', 'My Notepad Info');
define('SITE_DOMAIN_NAME', 'MyNotepad.Info');
define('SITE_ADMIN_EMAIL', 'admin@mynotepad.info');

/*
 * Password hashing info.
 */
define('CURRENT_PWD_VERSION',             2);
define('SALT_LENGTH',                     22);      // 22 is required by bcrypt.
define('PWD_WORK_FACTOR',                 '11');    // A string to ensure proper string length.
define('TMP_PWD_LENGTH',                  10);      // Number characters in temp passwords.
define('MAX_LOGIN_FAILURE_LOCKOUT',       3);       // Number failed logins before being frozen.
define('LOGIN_COOL_DOWN_PERIOD',          240);     // 4 minutes.

define('SELF_TIME_INVERVAL',              40);      // For if we time the script's execution time.

define('DEV_URANDOM',              '/dev/urandom'); // Host's entropy source.

define('DB_TRY_LIMIT',                    3);       // Coordinate with the backoff time.

define(MAX_NOTEPAD_SIZE,                  900000);  // 900 K

/*
 * API response messages.
 */
define('ERR_NOTEPAD_TOO_BIG', 'Notepad contents are too large. Please keep it under ' .
                              (MAX_NOTEPAD_SIZE / 1000) . 'k.');
define('ERR_SAVE_LOGIN_FAILED', 'If you have unsaved notes <b>copy them elsewhere</b> ' .
                                "before you log in again or you'll lose them.");
define('ERR_UPDATING_PROFILE', 'There was a failure updating your profile. No changes were made. ' .
                               'The admin staff has been alerted.');
define('ERR_INVALID_USERNAME', 'Your username is invalid. Please choose a valid one.');
define('ERR_INVALID_PASSWORD', 'Your password cannot be blank. Please choose a valid one.');
define('SUC_REGISTERED', 'You have registered. :-) Log in on the ' .
                         "<a href='index.php'>home page</a> to access your notepad. " .
                         "A few tips for using it efficiently <a href='details.php'>are " .
                         'available</a>.');
define('ERR_USERNAME_TAKEN', 'Sorry, that username has already been taken. Please try a ' .
                             'different one.');
define('EMAIL_PWD_RESET', "Your password reset for My Notepad Info",
                          'This is an automatically generated e-mail confirming your password ' .
                          "reset for http://MyNotepad.Info .\n\n" .
                          "Your account with username %s has had its password reset to the " .
                          "following:\n\n" .
                          "%s" .
                          "\n\nYou can use this password to access your notepad and choose a new " .
                          'password here: http://mynotepad.info/account.php');
define('SUC_PWD_RESET', "You've successfully reset your password, check your e-mail for a new " .
                        'temporary password. If you do not recieve an e-mail you  can reset your ' .
                        'password again.');
define('ERR_PWD_RESET_SEND_EMAIL', 'Could not send password reset confirmation e-mail to the ' .
                                   'e-mail address on record. Password remains unchanged.');
define('ERR_PWD_RESET', 'There was an error resetting your password, it remains unchanged.');
define('ERR_PWD_RESET_NO_EMAIL', 'Sorry, that username has no e-mail address on record. You can ' .
                                 'create a new notepad, this time you might want to provide an ' .
                                 'e-mail.');
define('EMAIL_NOTEPAD', "%s,\n\n" .
						'You requested your notepad contents on My Notepad Info' .
						"(http://mynotepad.info) be e-mailed to you. Here they are:\n" .
						"=============\n" .
						"%s");
define('SUC_NOTEPAD_EMAIL', "Your e-mail should be on its way. (Check your SPAM if you don't " .
                            'see it.');
define('SUC_NOTEPAD_EMAIL_LOGIN', "Your e-mail should be on it's way to <b>%s</b>. (Check " .
                                  "your SPAM if you don't see it.)");
define('ERR_NOTEPAD_EMAIL', 'E-mail could not be delivered to the e-mail address associated with ' .
                            'this account.');
define('ERR_NOTEPAD_EMAIL_LOGIN', "Could not send e-mail to <b>%s</b> associated with this account.");
define('ERR_EMAIL_RATE_LIMIT', 'To avoid e-mail abuse, we limit the number of e-mails that can ' .
                               'be sent to an account since no password is necessary to request ' .
                               'an e-mail). Wait X_TIME and try again.');
define('ERR_NOTEPAD_NO_EMAIL', 'There is no e-mail address associated with that account, so we ' .
                               "can't e-mail your notepad to you. You can add an e-mail to your ' .
                               'account <a href='account.php'>here</a>.");
define('SUC_SUBMIT_FEEDBACK', 'Thanks for submitting feedback. If appropriate (and you provided ' .
                              "your e-mail address) I'll get back to you as soon as possible. " .
                              'Thanks for taking the time to give me your thoughts. I hope you ' .
                              'enjoy My Notepad Info!');
define('ERR_SUBMIT_FEEDBACK', 'Sorry, there was an error sending your feedback. You can send an ' .
                              'e-mail manually to: ' . SITE_ADMIN_EMAIL . '.');
define('ERR_GENERIC_SERVER', 'Sorry, there was a server error. The admins have been alerted.');
define('ERR_INVALID_LOGIN', 'Username/password combination is incorrect.');

// Default notepad and settings for an account.
$default_notepad_text = "Congratulations on setting up your My Notepad account! The size and ".
                        "color controls you see allow you to customize the basic look of your " .
                        "notepad. You can also resize it using your browser's click-n-drag " .
                        "corner.\n\n" .
                        "Your settings and notepad will be auto-saved periodically, but you " .
                        "should explicitly save them before you leave.\n\n" .
                        "Note that you will not stay logged in if you leave this page!\n\n" .
                        "Now delete this nonsense and start using your notepad. :-)";
$defaults = array (
	'notepad_data' => $default_notepad_text,
	'height' => 200,
	'width' => 750,
	'font_color' => '#000000',
	'background_color' => '#f2f2f2'
);


// =============================================================================
// Globals
// =============================================================================

$logger  = null;         // Logging.
$db_link = null;         // The database wrapper.


// =============================================================================
// Classes
// =============================================================================

// This is a simple database wrapper so that a little bit extra DB logic can be encapsulated.
class DBWrapper
{
	// -------------------------------------------------------------------------
	// Methods
	// -------------------------------------------------------------------------
	public function __construct()
	{
		$this->connect();
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function connect()
	{
		global $logger;
		global $db_host, $db_name, $db_username, $db_password;

		/*
		 * Connect to the DB.
		 * Retry connecting, there seem to be random database failures on the host.
		 */
		for ($try_count = 1; !$success && $try_count <= DB_TRY_LIMIT; $try_count++) {
			$success = TRUE;

			try {
				$this->_pdo_link = @new PDO("mysql:host=$db_host;dbname=$db_name", $db_username,
				                            $db_password);
			}
			catch (PDOException $e) {
				$success = FALSE;

				$error_code = $e->getCode();
				$error_msg = $e->getMessage();
				$logger->LogError('Could not connect to database, PDO MySQL error: ' .
				                  "code=[$error_code], msg=[$error_msg]");

				// Small backoff, scaled up each try, for all but the last attempt.
				// Experimental timings put the vast majority of script execution times between
				// 5 and 20 milliseconds, so have a backoff bettween 10 and 30 ms to distribute
				// a glut of clients.
				if ($try_count < DB_TRY_LIMIT)
					usleep(rand(1000, 3000) * $try_count);
			}
		}

		if ($success === TRUE) {
			$this->_valid = TRUE;
		}
		else {
			$this->_valid = FALSE;
		}
	}

	public function disconnect()
	{
		if ($this->isValid()) {
			$this->_valid = FALSE;
			$this->_pdo_link = null;   // Force the PDO destructor to be called.
		}
	}

	// Execute a prepared query. Use this whenever user or non-trivial input is involved.
	// SELECT-style queries return result as array. Otherwise return bool; failure returns FALSE.
	// Mute error output so we don't return it to the client.
	public function safeQuery($query, $args, $returns_row = TRUE)
	{
		global $logger;

		$result = FALSE;

		$stmt = @$this->_pdo_link->prepare($query);
		if ($stmt !== FALSE) {
			$result = @$stmt->execute($args);

			// If we're returning query data, fetch it. Otherwise, leave return value as a bool.
			if ($result) {
				if ($returns_row) {
					$result = $stmt->fetch(PDO::FETCH_ASSOC);

					// fetch() won't return arrays with 0 elements.
					if (!is_array($result)) {
						$result = array();
					}
				}
			}
			else {
				$logger->LogError("Error: Failed to do a DB statement->execute():\n" .
						          print_r($stmt->errorInfo(), TRUE));
			}

			$stmt->closeCursor();
		}
		else {
			$logger->LogError("Error: Failed to do a DB statement->prepare():\n" .
			                  print_r($stmt->errorInfo(), TRUE));
		}

		return $result;
	}

	// Returns whatever the query would've returned.
	public function query($query)
	{
		global $logger;

		$db_result = $this->_pdo_link->query($query);
		if ($db_result === FALSE) {
			$text = "Error: Failed to do a DB->query():\n" . print_r($_pdo_link->errorInfo(), TRUE);
			$logger->LogError($text);
		}

		return $db_result;
	}

	public function beginTransaction()
	{
		return $this->_pdo_link->beginTransaction();
	}

	public function commit()
	{
		return $this->_pdo_link->commit();
	}

	public function rollBack()
	{
		return $this->_pdo_link->rollBack();
	}

	// Emulate MySQL's legacy API real_escape_string().
	public function mysqlRealEscapeString($str)
	{
		return $this->_pdo_link->quote($str);
	}

	public function isValid()
	{
		return $this->_valid;
	}


	// -------------------------------------------------------------------------
	// Variables
	// -------------------------------------------------------------------------

	// The DB link.
	private $_pdo_link = null;

	// Whether the DB connection is good.
	private $_valid = FALSE;
}

// This generates the API response, which is very basic XML.
class ApiResponse
{
	/*
	Basic XML structure:
	<notepad_form>
		<response_code>TEXT</response_code>
		<status>TEXT</status>
		<notepad_data>TEXT</notepad_data>
		[...other fields...]
	</notepad_form>
	*/

	// -------------------------------------------------------------------------
	// Methods
	// -------------------------------------------------------------------------

	public function setField($key, $value)
	{
		$inserted = FALSE;

		if (array_key_exists($key, $this->_output_fields)) {
			$this->_output_fields[$key] = $value;
			$inserted = TRUE;
		}

		return $inserted;
	}

	// Have a special method for this combination, it is the most used.
	public function setCodeAndStatus($code, $status)
	{
		$this->_output_fields['response_code'] = $code;
		$this->_output_fields['status'] = $status;
	}

	// Sets a generic status message for when something not directly relevant to the user fails.
	public function setGenericError()
	{
		$this->_output_fields['response_code'] = 'failure';
		$this->_output_fields['status'] = ERR_GENERIC_SERVER;
	}

	// Append text to the existing status. Usually used so levels of the call
	// stack can add more information.
	public function appendStatus($status)
	{
		$this->_output_fields['status'] .= ' ' . $status;
	}

	// Returns the API response in XML structure.
	public function getXML()
	{
		global $logger;

		$output_xml = new DOMDocument('1.0', 'utf-8');
		$root_container = $output_xml->createElement('notepad_form');

		foreach ($this->_output_fields as $key => $value) {
			// These are the fields that the client assumes *must* be defined.
			// They should always have been explicitly set prior to output, but
			// we provide default fallbacks here.
			if (strlen($value) == 0) {
				if ($key == 'response_code') {
					$value = 'failure';  // Presumably we wouldn't succeed and not set the response.
					$logger->LogWarn("Sent response without key $key");
				}
				else if ($key == 'status') {
					$value .= ERR_GENERIC_SERVER;
					$logger->LogWarn("Sent response without key $key");
				}
			}

			$root_container->appendChild($output_xml->createElement($key, $value));
		}

		$output_xml->appendChild($root_container);
		return $output_xml->saveXML();
	}


	// -------------------------------------------------------------------------
	// Variables
	// -------------------------------------------------------------------------

	// The array keys are the names of their corresponding XML members.
	private $_output_fields = array(
		'response_code' => '',
		'status' => '',
		'notepad_data' => '',
		'username' => '',
		'height' => '',
		'width' => '',
		'font_style' => '',
		'font_color' => '',
		'background_color' => '',
		'autosave' => ''
	);
}


// =============================================================================
// Function declarations
// =============================================================================

function setOutputHeaders()
{
	header("Content-Type: text/xml");

	// API responses obviously should not be cached.
	header("Cache-Control: no-cache,must-revalidate");
}

// Send a generic e-mail to the admin e-mail address.
function emailAdmin($title, $body = '', $log_failure = TRUE)
{
	global $logger;

	// Include the time in the same format that the logger uses, so that error e-mails
	// can be easily checked against the logs.
	$success = mail(SITE_ADMIN_EMAIL,
		            "My Notepad Info message: $title",
		            "This is an auto-generated e-mail for My Notepad Info, triggered at " .
		            date('Y-m-d H:i:s') . ":\n\n" . $body,
		            "From: My Notepad Info <admin@MyNotepad.Info>\r\n");

	if ($success === TRUE) {
		$logger->LogInfo("Sent e-mail to the admin with title: $title");
	}
	else {
		// This check is necessary because the logger sends e-mails for errors.
		// If e-mail fails and causes an erro to be logged, it would cause an infinite loop.
		if ($log_failure)
			$logger->LogError('Failed to send administrative email to ' . SITE_ADMIN_EMAIL);
	}

	return $success;
}

function emailUser($username, $email, $title, $body)
{
	global $logger;

	$mail_sent = FALSE;

	// TODO: e-mail verification
	//if ($email_verified)

	$mail_sent = mail("$username <$email>",
		              "My Notepad Info: $title",
		              $body,
		              "From: My Notepad Info <admin@MyNotepad.Info>\r\n");

	if ($mail_sent)
		$logger->LogInfo("Sent e-mail to a user $username.");
	else
		$logger->LogError("Failed to send email to user $username at $email");

	return $mail_sent;
}

// Emulate MySQL's real_escape_string().
function legacyEscapeString($string)
{
	global $db_link;

	return $db_link->mysqlRealEscapeString($string);
}

// Use a URL-safe version of Base64.
function base64URLEncode($string)
{
	$base_64_url = array("*", "-");
	$base_64_norm = array("+", "/");
	$new_str = str_replace($base_64_norm, $base_64_url, base64_encode($string));

	return($new_str);
}

function base64URLDecode($string)
{
	$base_64_url = array("*", "-");
	$base_64_norm = array("+", "/");
	$new_str = base64_decode(str_replace($base_64_url, $base_64_norm, $string));

	return($new_str);
}

function respondAndDie($response)
{
	global $db_link;

	$db_link->disconnect();
	die($response->getXML());
}

// Generate a securily random string of a given length.
function generateRandomString($length /* <= 32*/)
{
	global $logger;

	$return_rand_str = FALSE;

	if ($num_bytes <= 32) {
		$fp = @fopen('/dev/urandom', 'rb');

		if ($fp !== FALSE) {
			$rand_str = (string)@fread($fp, 32);
			@fclose($fp);

			// Include a precise timestamp. Basic precaution against output failure from the
			// high-entropy source by preventing the same salt from being generated.
			$rand_str .= ':' . date('U');
			$rand_str = hash('sha256', $rand_str, FALSE);
			$return_rand_str = substr($rand_str, 0, $length);
		}
		else {
			$logger->LogCrit('Failed to open ' . DEV_URANDOM);
		}
	}
	else {
		$logger->LogError("Failed to generate random string of invalid length $length");
	}

	if (strlen($return_rand_str) != $length) {
		$logger->LogError('Generated random string of wrong length: ' . strlen($return_rand_str) .
			              ' instead of ' . $length);
		$return_rand_str = FALSE;
	}

	$logger->LogDebug("Random string: $return_rand_str");

	return $return_rand_str;
}

// Generate a salt for the current password scheme.
function generateNewSalt()
{
	global $logger;

	$new_salt = generateRandomString(SALT_LENGTH);

	$logger->LogDebug("Generated new salt $new_salt, len = " . strlen($new_salt));

	if ($new_salt !== FALSE)
		$new_salt = '$2a$' . PWD_WORK_FACTOR . '$' . $new_salt;

	return $new_salt;
}

// A bcrypt wrapper. crypt() will auto-choose bcrypt internally given the format
// of the "salt", which was generated elsewhere.
function bcrypt($password, $salt)
{
	return crypt($password, $salt);
}

function derivePasswordHash($password, $salt, $pwd_version = CURRENT_PWD_VERSION)
{
	global $logger;

	$return_hash = FALSE;

	switch ($pwd_version) {
		case 1:
			// This scheme was a quick hack/placeholder from the initial
			// hack-together of this project, before I thought the project would
			// stay live. For shame.
			//     The password was run through real_escape_string() along with all the other
			// input, despite never being directly put in the database. We have to emulate
			// that now that we use PDO.
			$return_hash = md5(legacyEscapeString($password) . $salt);
		break;
		case 2:
			$return_hash = bcrypt($password, $salt);
		break;
		default:
			$logger->LogCrit("User has pwd_version=[$pwd_version], which is not handled.");
	}

	//$logger->LogDebug("Password derivation: salt=[$salt], pwd=[$password], hash=[$return_hash]");

	return $return_hash;
}

// Modify a user's password. Always change it to the current password scheme.
function changeUserPassword($username, $new_password)
{
	global $logger;
	global $db_link;

	$success = FALSE;
	$logger->LogInfo("Changing password for $username");

	// Always generate new salts for new passwords.
	$new_salt = generateNewSalt();
	if ($new_salt !== FALSE) {
		$new_password_hash = derivePasswordHash($new_password, $new_salt);

		$q = "UPDATE users SET password_hash=:new_password_hash,salt=:new_salt," .
		     "pwd_version='" . CURRENT_PWD_VERSION . "' WHERE username=:username LIMIT 1";
		$args = array(':new_password_hash' => $new_password_hash,
		              ':new_salt' => $new_salt,
		              ':username' => $username);
		$result = $db_link->safeQuery($q, $args, FALSE);

		$logger->LogDebug("User update query: $q");

		$success = $result;
	}

	return $success;
}

// Returns a DB row for the user, or FALSE if the user doesn't exist.
// Is responsible for providing the correct response message if the caller provides the ability.
function loadUserInfo($username, $response = null)
{
	global $db_link;

	$return = FALSE;

	$q = 'SELECT * FROM users WHERE username=:username LIMIT 1';
	$args = array(':username' => $username);
	$db_row = $db_link->safeQuery($q, $args);

	if ($db_row === FALSE) {
		if ($response != null)
			$response->setGenericError();
	}
	else if (count($db_row) === 0) {
		if ($response != null)
			$response->setCodeAndStatus('failure', 'Unknown username.');
	}
	else {
		$return = $db_row;
	}

	return $return;
}

function createUser($username, $password, $email)
{
	global $db_link;
	global $logger;
	global $defaults;

	$result = FALSE;

	$salt = generateNewSalt();

	if ($salt !== FALSE) {
		$password_hash = derivePasswordHash($password, $salt);

		$q = "INSERT INTO users (" .
		     "username," .
		     "password_hash," .
		     "salt," .
		     "pwd_version," .
		     "email," .
		     "height," .
		     "width," .
		     "notepad_data," .
		     "font_color," .
		     "background_color," .
		     "last_seen," .
		     "login_counter," .
		     "autosave) " .
		     "VALUES (" .
		     ":username," .
		     ":password_hash," .
		     ":salt," .
		     ":cur_pwd_ver," .
		     ":email," .
		     ":height," .
		     ":width," .
		     ":notepad_data," .
		     ":font_color," .
		     ":background_color," .
		     ":date," .
		     ":login_counter," .
		     ":autosave)";
		$args = array(':username' => $username,
					  ':password_hash' => $password_hash,
					  ':salt' => $salt,
					  ':cur_pwd_ver' => (string)CURRENT_PWD_VERSION,
					  ':email' => $email,
					  ':height' => $defaults['height'],
					  ':width' => $defaults['width'],
					  ':notepad_data' => $defaults['notepad_data'],
					  ':font_color' => $defaults['font_color'],
					  ':background_color' => $defaults['background_color'],
					  ':date' => date('Y-m-d'),
					  ':login_counter' => 0,
					  ':autosave' => 'true');
		$result = $db_link->safeQuery($q, $args, false);

		if ($result) {
			$logger->LogInfo("Succesfully registered user $username");
		}
		else {
			$logger->LogError("Failed to create new user '$username'.");
		}
	}
	else {
		$logger->LogError('Failed to generate salt for creating new user.');
	}

	return $result;
}

/* Authenticates the password, upgrades it if need be, and records the login
 * failure response.
 *     Only provides a response for failures.
 * Return value: FALSE for any failure, a database row on success.
 */
function loginUser($username, $password, $response, $update_login_ctr = false)
{
	global $logger;
	global $db_link;

	$login_result = FALSE;

	$logger->LogDebug("Attempting to login $username");

	$db_row = loadUserInfo($username, null);
	if ($db_row !== FALSE) {
		$bad_login_timestamp = $db_row['bad_login_timestamp'];
		$bad_login_counter = $db_row['bad_login_counter'];
		$current_time = time();

		// See if the user has invalid logins causing a cooloff period.
		$too_many_bad_logins = ($bad_login_counter >= MAX_LOGIN_FAILURE_LOCKOUT) ? TRUE : FALSE;
		$too_soon = ($current_time <= ($bad_login_timestamp + LOGIN_COOL_DOWN_PERIOD)) ? TRUE : FALSE;
		if ($too_many_bad_logins && $too_soon) {
			$logger->LogInfo("User $username hit login cooldown threshhold");
			$backoff_sec = $bad_login_timestamp + LOGIN_COOL_DOWN_PERIOD - $current_time;
			$backoff_min = (int)ceil($backoff_sec / 60);

			$response->setCodeAndStatus('failure', 'You have failed to login too many times. ' .
			                                       "Wait $backoff_min minutes and try again.");
		}
		// Try to log the user in.
		else {
			$salt = $db_row['salt'];
			$pwd_version = $db_row['pwd_version'];
			$password_hash_db = $db_row['password_hash'];

			$password_hash = derivePasswordHash($password, $salt, $pwd_version);

			// Successful login.
			if ($password_hash == $password_hash_db) {
				$logger->LogDebug("Logged in $username with pwd_version=$pwd_version");
				$login_result = $db_row;

				// Perform account updates that happen every login.
				$last_seen_date = date('Y-m-d');

				if ($update_login_ctr) {
					$logged_in_ctr = $db_row['login_counter'] + 1;
					$q = "UPDATE users SET last_seen='$last_seen_date'," .
					     "login_counter=$logged_in_ctr,bad_login_counter=0 " .
					     "WHERE username=:username LIMIT 1";
				}
				else {
					$q = "UPDATE users SET last_seen='$last_seen_date'," .
					     "bad_login_counter=0 WHERE username=:username LIMIT 1";
				}
				$args = array(':username' => $username);
				$db_link->safeQuery($q, $args, false);

				// Auto-migrate users to the latest password scheme.
				if ($pwd_version < CURRENT_PWD_VERSION) {
					if (changeUserPassword($username, $password) === TRUE)
						$logger->LogInfo("Upgraded user password version for $username");
					else
						$logger->LogError("Failed to upgrade password for user $username");
				}
			}
			// Invalid password. Record the failure counter and reset the threshhold timestamp if
			// need be.
			else {
				$logger->LogDebug("Failed login #" . ($bad_login_counter + 1) .
				                  " for $username at time $current_time");
				$response->setCodeAndStatus('failure', ERR_INVALID_LOGIN);

				// Already confirmed current time is outside threshhold, so reset the
				// threshhold time if we're rolling over or starting new.
				if ($bad_login_counter >= MAX_LOGIN_FAILURE_LOCKOUT || $bad_login_counter == 0) {
					$q = "UPDATE users SET bad_login_counter=1," .
					     "bad_login_timestamp='$current_time' " .
					     "WHERE username=:username";
				}
				else {
					$q = "UPDATE users SET bad_login_counter=" .
					     ($bad_login_counter + 1) . ' ' .
					     "WHERE username=:username";
				}
				$args = array(':username' => $username);
				$db_link->safeQuery($q, $args);
			}
		}
	}
	// Handle the response up here so we don't give an indication whether username or pwd failed.
	else {
		$response->setCodeAndStatus('failure', ERR_INVALID_LOGIN);
	}

	return $login_result;
}

?>
