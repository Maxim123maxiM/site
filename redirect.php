<?php

	//Access point
	define("_BOARD_VALID_", 1);

	//Settings
	require_once(dirname(__FILE__)."/settings.php");

	$row = $db->query_fetch_row("SELECT id, target_link, delay_link, delay_link_sec FROM ".TABLES_PREFIX."_arm_links WHERE local_link = '".ROOT_URL.$db->safe($_GET['l'])."' AND status = 1 AND (end_date = 0 OR end_date > ".time().")");
	if($row) {
		if($row['delay_link'] != "http://" && !empty($row['delay_link']) && $row['delay_link_sec'] > 0) {
			$db->query("UPDATE ".TABLES_PREFIX."_arm_links SET clicks_count = clicks_count + 1 WHERE id = ".$row['id']);
			?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<style>
			html, body {
				height: 100%;
				overflow: hidden;
			}
			div {
				background-color: #ff3333;
				color: #ffffff;
				font-weight: bold;
			}
		</style>
		<meta http-equiv="refresh" content="<?php echo $row['delay_link_sec'];?>; url=<?php echo htmlspecialchars($row['target_link'], ENT_QUOTES, "UTF-8");?>">
	</head>
	<body marginheight="0" marginwidth="0" onload="delay_sec = <?php echo $row['delay_link_sec'] - 1;?>; setInterval('document.getElementById(\'delay\').innerHTML = delay_sec; delay_sec--;', 1000);">
		<div align="center"><?php echo __("You will redirected after", "arm"); ?>: <span id="delay"><?php echo $row['delay_link_sec'];?></span> <?php echo __("seconds", "arm"); ?></div>
		<iframe id="iframe" style="width: 100%; height: 100%; scroll: none;"  frameborder="0" hspace="0" vspace="0" marginheight="0" marginwidth="0" src="<?php echo htmlspecialchars($row['delay_link'], ENT_QUOTES, "UTF-8");?>"></iframe>
	</body>
</html><?php
		} else {
			$db->query("UPDATE ".TABLES_PREFIX."_arm_links SET clicks_count = clicks_count + 1 WHERE id = ".$row['id']);
			header('Location: '.htmlspecialchars($row['target_link'], ENT_QUOTES, "UTF-8"));
		}
		exit;
	} else die(__("Link not found"));
?>
