<?php
/*
Plugin Name: Simples Google Analytics
Plugin URI: https://github.com/SpottedPaint/Simples-Analytics/
Description: Adds Google Analytics to site. 1) Sign Up For Google Analytics 2) copy your analytics Id 3) Activate plugin  4) Go to <a href="./admin.php?page=analytics.php">Simple Analytics config page</a> and enter/paste your Google Analytics Id there, Simples
Version: 1.3
Author: Adam
Author URI: http://spottedpaint.com
License: The Software shall be used for Good, not Evil.
*/

function isHiddenDomain($options){
  if($options['hidden_domain'] !== ''){
		return (strpos($_SERVER['SERVER_NAME'],$options['hidden_domain']) !== False);
	}
	return False;
}

function isHiddenIP($options){
	return ($_SERVER['REMOTE_ADDR'] === $options['hidden_ip']);
}
	
function showAnalytics(){
	$options = get_option('simple-analytics-options','simple-analytics');
	if (is_array($options) && !isHiddenDomain($options) && !isHiddenIP($options) ) { 
		return True;
	}else{
		return False;
	}
}

function addAnalytics(){
	if(showAnalytics()){
		$options = get_option('simple-analytics-options','simple-analytics');
$analytics_code = <<<ANALYTICS_CODE
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '$options[analytics_id]']);
	_gaq.push(['_trackPageview']);
	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
ANALYTICS_CODE;
echo $analytics_code.PHP_EOL;
	}	
}

add_action( 'wp_head', 'addAnalytics' );

// create custom plugin settings menu
add_action('admin_menu', 'analyticsCreateMenu');

function analyticsCreateMenu() {
	//create new top-level menu
	add_menu_page('Analytics Settings', 'Simples Analytics', 'administrator', __FILE__, 'analyticsSettingsPage', plugins_url('/images/icon.png', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'registerMySettings' );
}

function registerMySettings() {
	//register our settings
	add_option('simple-analytics-options', array('analytics_id' => 'UA-XXXXXX','hidden_domain' => '','hidden_ip' => ''));
}

function analyticsSettingsPage() {
	$saved = false;
	
	if(isset($_POST['update_simple_analytics'])) {
		/* update options */
		update_option('simple-analytics-options',array('analytics_id' => $_POST['analytics_id'],'hidden_domain' => $_POST['hidden_domain'], 'hidden_ip' => $_POST['hidden_ip']));
		$analytics_options = get_option('simple-analytics-options');
		
		$saved = true;
	}

?>
<div class="wrap">
<h2>Simples Analytics Settings</h2>
<?php
if(!get_option('simple-analytics-options')){
	add_option('simple-analytics-options', array('analytics_id' => 'UA-XXXXXX','hidden_domain' => '','hidden_ip' => ''));
}
$analytics_options = get_option('simple-analytics-options');
if($saved){
	echo "<b>Your analytics settings have been updated!</b>";
}
?>
<form method="post" action="admin.php?page=analytics.php">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Google ID </th>
			<td><input type="text" name="analytics_id" value="<?php echo $analytics_options['analytics_id']; ?>" /></td>
			<td>eg:"UA-27872064-1"</td>		   
		</tr>
		<tr valign="top">
			<th scope="row">Domain Name from which to turn off analytics gathering (optional)</th>
			<td><input type="text" name="hidden_domain" value="<?php echo $analytics_options['hidden_domain']; ?>" /></td>
			<td>So if I use .toby for my local domain name when working on a site I don't want my constant editing and the time I spend testing pages to be recorded as a real visitor. 
			So I enter '.toby' then analytics turns off when I'm running the site on my testing server.</td>		
			</tr>
		<tr valign="top">
			<th scope="row">IP from which to turn off analytics gathering (optional)</th>
			<td><input type="text" name="hidden_ip" value="<?php echo $analytics_options['hidden_ip']; ?>" /></td>
			<td>Stops me, when testing site on live server, appearing on stats.</td>		  
		</tr>
	</table>
	<p class="submit">
		<input type="submit" name="update_simple_analytics" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
</form>
</div>
<?php } ?>
