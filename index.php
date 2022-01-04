<?php

	//Access point
	define("_BOARD_VALID_", 1);

	//Settings
	require_once(dirname(__FILE__)."/settings.php");

	if(isset($_GET['quit']) && $_GET['quit'] == "yes") {
		unset($_SESSION['fxn_arm_admin']);
	} else if(isset($_SESSION['fxn_arm_admin']) && isset($_POST['save_parameters'])) {
		//Save settings
		$query = $db->query("SELECT settings_name FROM ".TABLES_PREFIX."_settings");
		while($row = $db->fetch($query)) {
			$val = $row['settings_type'] != "textarea" ? safe($_POST[$row['settings_name']]) : $_POST[$row['settings_name']];
			$db->query("UPDATE ".TABLES_PREFIX."_settings SET settings_value = '".$db->safe($val)."' WHERE settings_name = '".$row['settings_name']."'");
		}
		$information = __("Parameters saved!");
	}

	if(isset($_SESSION['fxn_arm_admin']) && isset($_GET['download_stat']) && $_GET['download_stat'] == "csv") {
		// Get data
		$query = $db->query("SELECT al.*, ac.id AS cat_id, ac.title AS cat_title FROM ".TABLES_PREFIX."_arm_links AS al
							INNER JOIN ".TABLES_PREFIX."_arm_categories_links AS acl
							ON acl.link_id = al.id
							INNER JOIN ".TABLES_PREFIX."_arm_categories AS ac
							ON acl.category_id = ac.id
							ORDER BY al.id DESC");

		$data = "ID,Category,Title,Local link,Target link,Clicks,Status\n";
		while($row = $db->fetch($query)) {
			$data .= $row['id'].",'".$row['cat_title']."','".$row['title']."','".$row['local_link']."','".$row['target_link']."',".$row['clicks_count'].",".$row['status']."\n";
		}
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=arm.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		//Send
		die($data);
	}

	$tab = "dashboard";
	if(isset($_GET['tab'])) {
		$tab = safe($_GET['tab']);
	} else if(method_exists($Mod->modules["arm"], "onControl")) {
		$tab = $_GET['tab'] = "control";
		$_GET['mod'] = "arm";
		$_GET['func'] = "onControl";
	}

?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<link rel="shortcut icon" type="x-image" href="<?php echo $script_url;?>/favicon.ico" />
		<title><?php echo __("AutoRedirectMaster"); ?></title>
		<link rel="stylesheet" href="<?php echo $script_url;?>css/style.css" type="text/css" media="all" />
		<script src="<?php echo $script_url;?>js/jquery.mini.js"></script>
	</head>
	<body>
		<?php if(!isset($_SESSION['fxn_arm_admin']) && (!isset($_POST['login']) || !isset($_POST['pass']) || md5($_POST['login']) != ADMIN_NAME || md5($_POST['pass']) != ADMIN_PASSWORD)): ?>
			<div id="login_form">
				<div id="top_title"><?php echo __("AutoRedirectMaster"); ?></div>
				<p align="center"><font color="aa0000"><?php if(isset($_POST['pass']) || isset($_POST['login'])):?><?php echo __("Authorization error");?><?php endif;?></font></p>
				<form action="" method="post">
					<span><?php echo __("Login"); ?>:</span> <input type="text" name="login" value="<?php echo isset($_POST['login']) ? safe($_POST['login']) : ""?>" />
					<br />
					<span><?php echo __("Password"); ?>:</span> <input type="password" name="pass" value="" />
					<br />
					<input type="submit" name="submit" value="<?php echo __("Enter"); ?>" />
					<br style="clear: both;" />
				</form>
			</div>
		<?php else:
			$_SESSION['fxn_arm_admin'] = true;
			$control = array();
			$places = array();
			$logs = array();
			foreach($Mod->modules as $name => $mod) {
				$menu = $mod->onMenu();
				$control[$name] = $menu['control'];
			}
		?>
			<div id="stat">
				<div id="top_title"><?php echo __("Admin Panel"); ?></div>
				<div id="conteiner">
					<div align="right"><a href="?quit=yes"><?php echo __("Quit"); ?></a></div>
					<ul>
						<li <?php echo $tab == "dashboard" ? 'class="active_tab"' : "";?>><a href="?tab=dashboard"><?php echo __("DashBoard"); ?></a></li>
						<li <?php echo $tab == "control" ? 'class="active_tab"' : "";?>>
							<span><?php echo __("Statistics and Control"); ?></span>
							<ul>
								<?php foreach($control as $name => $val):?>
									<li><a href="?tab=control&mod=<?php echo $name;?>&func=<?php echo $val[1];?>"><?php echo $val[0];?></a></li>
								<?php endforeach;?>
							</ul>
						</li>
						<li <?php echo $tab == "parameters" ? 'class="active_tab"' : "";?>><a href="?tab=parameters"><?php echo __("Parameters"); ?></a></li>
						<li <?php echo $tab == "modules" ? 'class="active_tab"' : "";?>><a href="?tab=modules"><?php echo __("Modules"); ?></a></li>
					</ul>
					<br style="clear: left;"/>
					<?php
						switch($tab) {
							case 'parameters':
								require_once(dirname(__FILE__)."/parameters.php");
								break;
							case 'control':
								if(method_exists($Mod->modules[$_GET['mod']], $_GET['func'])) {
									$Mod->modules[$_GET['mod']]->$_GET['func']();
								}
								break;
							case 'modules':
								$Mod->onModules();
								break;
							case 'dashboard':
							default:
								require_once(dirname(__FILE__)."/dashboard.php");
								break;
						}
					?>
					<br />
					<div align="right"><?php echo __("Powered by"); ?> <a href="http://find-xss.net" target="_black">Find-XSS.net</a> , <?php echo __("Design by"); ?> <a href="http://kasyanov.info" target="_black">Kasyanov.info</a></div>
					<?php if($tab == 'control'): $transactions = isset($transactions) ? $transactions : 0;?>
						<a href="?tab=<?php echo $tab;?>&mod=arm&func=onControl&p=<?php echo isset($_GET['p']) && $_GET['p'] > 1 ? ($_GET['p'] - 1) : 0; ?>"><<</a> Page <?php echo isset($_GET['p']) ? (intval($_GET['p']) + 1) : 1; ?> / <?php echo ceil($transactions/15); ?> <a href="?tab=<?php echo $tab;?>&mod=arm&func=onControl&p=<?php echo (!isset($_GET['p']) && ($transactions/15) < 1) ? 0 : (isset($_GET['p']) ? ($_GET['p'] < floor($transactions/15) ? $_GET['p'] + 1 : intval($_GET['p'])) : 1); ?>">>></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</body>
</html>

