<?php
/**
 * @package CampusNet_Authentication
 * @version 0.1
 */
/*
Plugin Name: CampusNet Authentication
Plugin URI: 
Description: Use your universities CampusNet login via their API. It requires a user to be a member of a specific CampusNet group (defineable) to get access, that way, the administration of users is kept in one place (or, use group/elementid 0 to not use a group). For each user logging in, it makes sure that a wordpress user is created/exists, so if the CampuseNet API ever breaks, you can disable it and the users can login using the username and password they used earlier. NOTE: since it tries to detect if a user is a student by checking if the first letter is s, you cannot have normal users with usernames that start with s.
Author: Christian Kjaer Laustsen
Version: 0.1
Author URI: http://codetalk.io
*/


/**
 * The content of the admin settings page.
 */
function cn_auth_admin() {
	include('cn_admin.php');
}

/**
 * Add the plugin as a menu item under settings.
 */
function cn_auth_admin_actions() {
	add_options_page("CampusNet Authentication", "CampusNet Authentication", 'manage_options', "campusnet_authentication", "cn_auth_admin");
}

/* Add settings menu item for the plugin */
add_action('admin_menu', 'cn_auth_admin_actions');


/**
 * Performs a cURL call to the url with the specified fields and headers,
 * expecting XML to be returned.
 *
 * @param string $url URL.
 * @param array $fields Array of post fieldname => fieldvalue.
 * @param array $headers Array of headers.
 * @return SimpleXMLElement A SimpleXMLElement object with the result.
 */
function curlXMLCall($url, $fields = NULL, $headers = NULL) {	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_URL, $url);
	if (!is_null($fields)) {
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
	}
	if (!is_null($headers)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	
	$result = curl_exec($ch);
	curl_close($ch);
	return new SimpleXMLElement($result);
}

/**
 * Uses the campusnet auth API to get the users protected access password.
 *
 * @param string $uni The university the user is using.
 * @param string $username The users username.
 * @param string $password The users password.
 * @return array An array with the status and data (TRUE if successfully
 * 				 retrived the password, else FALSE).
 */
function getPasswordFromXML($uni, $username, $password) {
	$xml = curlXMLCall(
		'https://auth.' . $uni . '.dk/' . $uni . '/mobilapp.jsp',
		array(
			'username' => $username,
			'password' => $password
		)
	);
	if ($xml->BlockedAccess) {
		$ip = NULL;
		$reason = NULL;
		$tryAgainIn = "00:00:00";
		foreach($xml->BlockedAccess->attributes() as $key => $val) {
			if ($key === 'Ip') {
				$ip = (string)$val;
			} elseif ($key === 'Reason') {
				$reason = (string)$val;
			} elseif ($key === 'TryAgainIn') {
				$tryAgainIn = (string)$val;
			}
		}
		return array(
			'status' => FALSE,
			'data' => "Wrong credentials (or you're trying way to fast), please wait " 
					  . $tryAgainIn . " seconds and try again"
		);
	} elseif ($xml->LimitedAccess) {
		$status = TRUE;
		$password = NULL;
		foreach($xml->LimitedAccess->attributes() as $key => $val) {
			if ($key === 'Password') {
				$password = (string)$val;
			}
		}
		if (is_null($password)) {
			$status = FALSE;
		}
		return array(
			'status' => $status,
			'data' => $password
		);
	}
	return array(
		'status' => FALSE,
		'data' => ''
	);
}

/**
 * Uses the campusnet API to check if a user is in a group.
 *
 * @param string $uni The university the user is using.
 * @param string $appname The app name registered.
 * @param string $apptoken The app token registered with the app name.
 * @param string $elementid The group/element id to check in.
 * @param string $username The users username.
 * @param string $password The users password.
 * @return boolean TRUE if the user exists, otherwise FALSE.
 */
function isInCampusNetGroup($uni, $appname, $apptoken, $elementid, $username, $password) {
	$xml = curlXMLCall(
		'https://' . $username . ':' . $password . '@www.campusnet.' . $uni . '.dk/data/CurrentUser/Elements/' . $elementid . '/FrontPage',
		NULL,
		array(
			'X-appname: ' . $appname,
			'X-token: ' . $apptoken
		)
	);
	if ($xml->Name == 'NoAccess') {
		return FALSE;
	}
	return TRUE;
}

/**
 * Uses the campusnet API to fetch the users info.
 *
 * @param string $uni The university the user is using.
 * @param string $appname The app name registered.
 * @param string $apptoken The app token registered with the app name.
 * @param string $username The users username.
 * @param string $password The users password.
 * @return boolean TRUE if the user exists, otherwise FALSE.
 */
function getCampusNetUserInfo($uni, $appname, $apptoken, $username, $password) {
	$xml = curlXMLCall(
		'https://' . $username . ':' . $password . '@www.campusnet.' . $uni . '.dk/data/CurrentUser/UserInfo',
		NULL,
		array(
			'X-appname: ' . $appname,
			'X-token: ' . $apptoken
		)
	);
	$userinfo = array(
		'firstname' => (string)$xml->attributes()->GivenName,
		'lastname' => (string)$xml->attributes()->FamilyName,
		'email' => (string)$xml->attributes()->Email
	);
	if ($xml->Name == 'NoAccess') {
		return FALSE;
	}
	return $userinfo;
}

/**
 * Make sure the user exists in the Wordpress database, else, create a new user.
 *
 * @param string $username The users username.
 * @param string $password The users password.
 * @param array $userinfo An array containing first, lastname and email.
 * @return string|WP_Error A userid if successfull, otherwise a WP_Error.
 */
function makeSureUserExistsInWordpress($username, $password, $userinfo) {
	$userid = username_exists($username);
	if(!$userid) {
		$userid = wp_create_user($username, $password, $userinfo['email']);
	}
	if (!is_wp_error($userid)) {
		$result = wp_insert_user(array(
			'ID' => $userid,
			'user_login' => $username,
			'user_pass' => $password,
			'user_email' => $userinfo['email'],
			'first_name' => $userinfo['firstname'],
			'last_name' => $userinfo['lastname'],
			'role' => 'administrator',
			'display_name' => $userinfo['firstname'] . ' ' . $userinfo['lastname']
		));
	}
	return $userid;
}

/**
 * Authenticate the user at CampusNet and check if he is in the needed group.
 *
 * @param string $username The users username.
 * @param string $password The users password.
 * @return WP_User|WP_Error A WP_User object if successfull, otherwise a WP_Error.
 */
function authenticateAtCampusNet($username, $password) {
	$uni = get_option('cn_auth_uni');
	$appname = get_option('cn_auth_appname');
	$apptoken = get_option('cn_auth_apptoken');
	$elementid = get_option('cn_auth_elementid');
	$isFound = FALSE;
	
	$protectedAccessPassword = getPasswordFromXML(
		$uni,
		$username,
		$password
	);
	if ($elementid != '0') {
		if ($protectedAccessPassword['status']) {
			$isFound = isInCampusNetGroup(
				$uni,
				$appname,
				$apptoken,
				$elementid,
				$username,
				$protectedAccessPassword['data']
			);
			if (!$isFound) {
				$error = new WP_Error();
				$error->add('not_in_group', __('<strong>ERROR</strong>: You couldn\'t be found in the group needed for access to the administration panel.'));
				return $error;
			}
		} else {
			$error = new WP_Error();
			$error->add('wait_to_try_again', __('<strong>ERROR</strong>: ' . $protectedAccessPassword['data']));
			return $error;
		}
	}
	if ($isFound || $elementid == '0') {
		$userinfo = getCampusNetUserInfo(
			$uni,
			$appname,
			$apptoken,
			$username,
			$protectedAccessPassword['data']
		);
		$userid = makeSureUserExistsInWordpress($username, $password, $userinfo);
		if (!is_wp_error($userid)) {
			$user =  new WP_User($userid);
			return $user;
		}
	}
}

/**
 * Check if a user has access to the system using the CampusNet API.
 *
 * @param WP_User $user WP_user object (if the user is already logged in).
 * @param string $username User's username
 * @param string $password User's password
 * @return WP_Error|WP_User WP_User object if login successful, otherwise WP_Error object.
 */
function campusnetAuthenticationHook($user, $username, $password) {
	if (is_a($user, 'WP_User')) { 
		return $user; 
	}
	// Make sure that username and password ain't empty
	if (empty($username) || empty($password)) {
		$error = new WP_Error();
		if (empty($username)) {
			$error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));
		}
		if (empty($password)) {
			$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));
		}
		return $error;
	}
	
	// If the username starts with s, we use the CampusNet Authentication
	if (strpos($username, 's') === 0) {
		try {
			return authenticateAtCampusNet($username, $password);
		} catch (Exception $e) { 
			$error = new WP_Error();
			$error->add('xml_error', __('<strong>ERROR</strong>: There was an error with the XML. Make sure all needed settings are set for the CampusNet plugin.'));
			return $error;
		}
		
	}
}

/* Hook into the authentication filter */
add_filter('authenticate', 'campusnetAuthenticationHook', 10, 3);


?>