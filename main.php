<?php
/*
Plugin Name: eAssistance Pro 
Plugin URI: https://www.eassistancepro.com/
Description: eAssitance Pro is an amazing solution for website owners across any size and any niche to incorporate Live Chat services on their websites effortlessly. Install it on your Wordpress powered websites for free now! - <a href='options-general.php?page=eassistancepro'>Configure</a>
Version: 1.0
Author: Lepide Software Pvt Ltd
Author URI: https://www.eassistancepro.com/
License: GPL2
*/

$display = "";

add_action('admin_init', 'eassistancepro_admin_inits');
add_action('admin_menu', 'eassistancepro_admin_add_page');

$options_code_location = get_option('eassistancepro_code_location');
if($options_code_location == "a") {
	add_action('wp_head', 'eassistancepro_code');
}

function get_remote_code($url, $port=80) {
	global $display;
	
	if(function_exists('curl_init')) {
		$ckfile = tempnam("/tmp", "CURLCOOKIE");
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
	} else {
		try {
			$data = file_get_contents($url);
		} catch (Exception $e) {
			$display = '<div id="message" class="updated fade"><p>'.$e->getMessage().'</p></div>';
		}
	}
	
	return $data;
}

function eassistancepro_admin_inits() {
	global $display;
	
	$response_errors = array ("Invalid email address<br />", "error:Can't find account, please contact support");
	
	if(isset($_GET['reset_settings'])) {
		update_option('eassistancepro_account_email', '');
		update_option('eassistancepro_account_password', '');
		update_option('eassistancepro_code_script', '');
		update_option('eassistancepro_code_location', 'a');
		wp_redirect(admin_url().'options-general.php?page=eassistancepro&reset_success=1');
	}
	
	if(isset($_GET['reset_success'])) {
		$display = '<div id="message" class="updated fade"><p>'.__('Reset success', 'eassistancepro').'</p></div>';
	}
		
	if(isset($_GET['connect_success'])) {
		$display = '<div id="message" class="updated fade"><p>'.__('Connected successfully, chat is installed', 'eassistancepro').'</p></div>';
	}
	
	if(isset($_GET['connect_account_direct']) && count($_POST)) {
		$response = $_POST["eassistancepro_code_script"];
		$response = htmlspecialchars_decode($response, ENT_QUOTES);
		$response = stripslashes($response);
		
		update_option('eassistancepro_code_script', $response);
		update_option('eassistancepro_code_location', $_POST["eassistancepro_code_location"]);
		wp_redirect(admin_url().'options-general.php?page=eassistancepro&connect_success=1');
	}
	
	if(isset($_GET['connect_account']) && count($_POST)) {
		if($_POST['eassistancepro_account_email']!="" && $_POST['eassistancepro_account_password']!="") {
			$response = get_remote_code('https://www.eassistancepro.com/app_login.php?email='.$_POST['eassistancepro_account_email'].'&password='.$_POST['eassistancepro_account_password']);
			$response = htmlspecialchars_decode($response, ENT_QUOTES);
			
			if(!strstr($response, "Error")===false) {
				$display = '<div id="message" class="updated fade"><p>'.$response.'</p></div>';
			} else {
				update_option('eassistancepro_account_email', $_POST['eassistancepro_account_email']);
				update_option('eassistancepro_account_password', $_POST['eassistancepro_account_password']);
				update_option('eassistancepro_code_script', urldecode($response));
				update_option('eassistancepro_code_location', 'a');
				wp_redirect(admin_url().'options-general.php?page=eassistancepro&connect_success=1');         
			}
		} else {
			$display = '<div id="message" class="updated fade"><p>'.__('Email address or password missing', 'eassistancepro').'</p></div>';
		}
	}
	
	register_setting('eassistancepro_account', 'eassistancepro_account_email');
	register_setting('eassistancepro_account', 'eassistancepro_account_password');
	register_setting('eassistancepro_code', 'eassistancepro_code_script');
	register_setting('eassistancepro_location', 'eassistancepro_code_location');
}

function eassistancepro_admin_add_page() {
	add_options_page('eAssistance Pro Live Chat', 'eAssistance Pro', 'manage_options', 'eassistancepro', 'eassistancepro_options_page');
}

function eassistancepro_code() {	
	if($script = get_option('eassistancepro_code_script')) {
		echo $script;
	}
}

function eassistancepro_options_page() {
	global $display;
	
	$options_account_email = get_option('eassistancepro_account_email');
	$options_account_password = get_option('eassistancepro_account_password');
	$options_code_script = get_option('eassistancepro_code_script');
	$options_code_location = get_option('eassistancepro_code_location');
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>custom.css" type="text/css" media="screen" />
<script language="javascript" type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) ?>script.js"></script>
<div id="chatcode_div" class="wrap">
  <h2><?php _e('eAssistance Pro Live Chat'); ?></h2>
	<ul>
		<li>eAssistance Pro is a light, easily customizable and remarkably fast chat service provider to aid your online communication services. Allow eAssistance Pro on your website to enable your visitors to contact you in real-time and get answers instantly. Assist them for whatever they need and ensure better growth and productivity. Track visitors, get details, know their location and gain conspicuous benefits to retain customers before they bump on other sites for better service and support.</li>
		<li>Try it free with all functional benefits for 30 days!</li>
		<li>Take a tour of all amazing features that eAssistance Pro provides: <a href="https://www.eassistancepro.com/features.php" target="_blank">eAssistance Pro Features</a></li>
	</ul>
	
	<?php if($display != "") { echo $display; } ?>
	
	<?php if(!empty($options_account_email) && !empty($options_account_password)) { ?>
	<div class="heading"><?php _e('Connect', 'eassistancepro'); ?></div>
	<div class="panel">
		<form method="post" action="https://www.eassistancepro.com/weblogin.php" target="_blank">
			<input type='hidden' name='logout_to' value='https://www.eassistancepro.com/login.php' />
			<input type='hidden' name='email' value='<?php echo $options_account_email?>' />
			<input type='hidden' name='password' value='<?php echo $options_account_password?>' />
			<table width="100%" border="0" cellspacing="10" cellpadding="0">
				<tr>
					<td width="130"><?php _e('Username or Email', 'eassistancepro'); ?></td>
					<td><input id="eassistancepro_account_email" name="email_dummy" size="40" type="text" value="<?php echo $options_account_email?>" disabled /></td>
				</tr>
				<tr>
					<td><?php _e('Password', 'eassistancepro'); ?></td>
					<td><input id="eassistancepro_account_password" name="password_dummy" size="40" type="password" value="<?php echo $options_account_password?>" disabled />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
					<td><input type="button" name="reset" onclick="location.href='<?php echo admin_url(); ?>options-general.php?page=eassistancepro&reset_settings=1'" value="<?php _e("Reset", 'eassistancepro'); ?>" class="button" /> <input type="submit" name="submit" value="<?php _e("Open Operator Panel", 'eassistancepro'); ?>" class="button" /></td>
				</tr>
			</table>
		</form>
	</div>
	
	<?php } else { ?>
	
	<div class="heading"><?php _e('Connect', 'eassistancepro'); ?></div>
	<div class="panel">
		<form method="post" action="<?php echo admin_url(); ?>options-general.php?page=eassistancepro&connect_account=1">
			<table width="100%" border="0" cellspacing="10" cellpadding="0">
				<tr>
					<td width="130"><?php _e('Username or Email', 'eassistancepro'); ?></td>
					<td><input id="eassistancepro_account_email" name="eassistancepro_account_email" size="40" type="text" value="<?php echo $options_account_email; ?>" /></td>
				</tr>
				<tr>
					<td><?php _e('Password', 'eassistancepro'); ?></td>
					<td><input id="eassistancepro_account_password" name="eassistancepro_account_password" size="40" type="password" value="<?php echo $options_account_password; ?>" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="submit" value="<?php _e("Connect", 'eassistancepro'); ?>" class="button" /></td>
				</tr>
			</table>
		</form>
	</div>
		
	<?php } ?>
	
	<div class="heading"><?php _e('Code', 'eassistancepro'); ?></div>
	<div class="panel">
		<form method="post" action="<?php echo admin_url(); ?>options-general.php?page=eassistancepro&connect_account_direct=1">
			<?php settings_fields('eassistancepro_code'); ?>
			<?php settings_fields('eassistancepro_location'); ?>
			<table width="100%" border="0" cellspacing="10" cellpadding="0">
				<tr>
					<td>Advanced users - log into your account, go to Home->Get Chat Button to customize the implementation, then copy and paste the HTML code below and click on "Update Code".</td>
				</tr>
				<tr>
					<td><textarea id="eassistancepro_code_script" name="eassistancepro_code_script" cols="100" rows="5"><?php echo trim($options_code_script); ?></textarea></td>
				</tr>
				<tr>
					<td><input type="radio" name="eassistancepro_code_location" id="eassistancepro_location_auto" value="a" <?php echo ($options_code_location == "a") ? "checked" : ""; ?> /> Automatically insert in page
						&nbsp;
						<input type="radio" name="eassistancepro_code_location" id="eassistancepro_location_manually" value="m" <?php echo ($options_code_location == "m") ? "checked" : ""; ?> /> Manually insert in page
					</td>
				</tr>
				<tr id="codesnipt" <?php echo ($options_code_location == "a") ? "style=\"display:none\";" : ""; ?>>
					<td><div class="notif">Copy and paste below code anywhere in the page (after &lt;body&gt;)</div>
					<textarea rows="1" cols="100">&lt;?php if(function_exists('eassistancepro_code')) { eassistancepro_code(); } ?></textarea></td>
				</tr>
				<tr>
					<td><input type="submit" name="submit" value="<?php _e("Update Code", 'eassistancepro'); ?>" class="button" /></td>
				</tr>
			</table>
		</form>
	</div>
	
</div>
<?php } ?>
