<?php

	defined("_BOARD_VALID_") or die("Direct Access to this location is not allowed.");

	if(session_id() == "") session_start();

	//Config
	require_once(dirname(__FILE__)."/config.php");

	//Data base
	if(file_exists(dirname(__FILE__)."/sql/".DATABASE.".php"))
		require_once(dirname(__FILE__)."/sql/".DATABASE.".php");
	else die("Error, the file ./sql/".DATABASE.".php don't exists, wrong configuration.");

	//Settings loading
	$query = $db->query("SELECT settings_name, settings_value FROM ".TABLES_PREFIX."_settings");
	$settings = array();
	while($row = $db->fetch($query)){
		$settings[$row['settings_name']] = $row['settings_value'];
	}

	//URL
	$script_url = ROOT_URL;

	//Images url
	$images_url = $script_url."images";

	//Load modules
	require_once(dirname(__FILE__)."/modules/modules.php");

	//Localization
	$lang = array();

	if(file_exists(dirname(__FILE__)."/lang/".$settings['website_language'].".php")) {
		require_once(dirname(__FILE__)."/lang/".$settings['website_language'].".php");
	}

	foreach($Mod->modules as $name => $mod) {
		if(file_exists(dirname(__FILE__)."/modules/".$mod->name."/lang/".$settings['website_language'].".php")) {
			require_once(dirname(__FILE__)."/modules/".$mod->name."/lang/".$settings['website_language'].".php");
		}
	}

	//Localization function
	function __($text, $module = "Core") {
		global $lang;
		return isset($lang[md5($module.$text)]) ? $lang[md5($module.$text)] : (isset($lang[md5($text)]) ? $lang[md5($text)] : $text);
	}

	//Email classes
	require_once(dirname(__FILE__)."/htmlMimeMail5/htmlMimeMail5.php");

	function fxn_send($to, $subject, $body, $from = "") {
		global $settings;

		if(preg_match("/.*?<\s*(.*?)\s*>/i", htmlspecialchars_decode($to, ENT_QUOTES), $match)) {
			$to = $match[1];
		}

		$mail = new htmlMimeMail5();
		$mail->setHeadCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setFrom($from);
		$mail->setSubject($subject);
		$mail->setHTML($body."<br />".htmlspecialchars_decode($settings['website_email_signature'], ENT_QUOTES));
		$mail->send(array($to));

	}

	/*
	 * Защита от не корректных данных
	 * $var String
	 * return Безопасные данные
	 */
	function safe($var) {
		if(is_array($var)) {
			if(get_magic_quotes_gpc()) {
				foreach($var as $key => $val) {
					$var[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
				}
			} else {
				foreach($var as $key => $val) {
					$var[$key] = addslashes(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'));
				}
			}
			return $var;
		} else {
			if(get_magic_quotes_gpc()) {
				return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
			}
			return addslashes(htmlspecialchars($var, ENT_QUOTES, 'UTF-8'));
		}
	}

?>
