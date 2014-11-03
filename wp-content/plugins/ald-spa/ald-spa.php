<?php
/*
Plugin Name: Snap Preview Anywhere<sup>TM</sup> Plugin
Version: 1.0
Plugin URI: http://ajaydsouza.com/wordpress/plugins/snap-preview-anywhere/
Description: Add Snap Preview Anywhere(TM) to your blog. Go to <a href="options-general.php?page=spa_options">Options &gt;&gt; SPA</a> to configure.
Author: Ajay D'Souza
Author URI: http://ajaydsouza.com/
*/ 

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

define('ALD_SPA_DIR', dirname(__FILE__));

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/
function ald_spa()
{
	$spa_settings = spa_read_options();
	
	if($spa_settings[key]=='')
	{
		$str = 'Please visit WP-Admin &gt; Options &gt; SPA and enter the key. Don\'t have a key? <a href="http://www.snap.com/about/spa1.php">Signup here</a>';
	}
	else
	{
		$str = '<script type="text/javascript" src="http://spa.snap.com/snap_preview_anywhere.js?ap=1&amp;key=';
		$str .= $spa_settings[key] . '&amp;sb=';
		
		if ($spa_settings[searchbox]) {
			$str .= '1';
		} else {
			$str .= '0';
		}
		$str .= '&amp;domain='. $spa_settings[url] . '"></script>';
		
	}
	
	return $str;
}

function spa_read_options() 
{
	if(!is_array(get_option('ald_spa_settings')))
	{
		$spa_settings = spa_default_options();
		update_option('ald_spa_settings', $spa_settings);
	}
	else
	{
		$spa_settings = get_option('ald_spa_settings');
	}
	return $spa_settings;
}

// Functions to echo the necessary code
add_action('wp_footer', 'ald_spa_display');
function ald_spa_display($force = false) {
	$spa_settings = spa_read_options();

	// Disable Internal Links Preview
	if(($force || $spa_settings['footer']) && (!$spa_settings[locallinks]) && ($spa_settings[key] != ''))
	{
	$url = $spa_settings[url];
	$url = str_replace(".", "\.", $url); // Replace . with \.
	$url = str_replace("/", "\/", $url); // Replace / with \/
?>
<script type="text/javascript">
//<![CDATA[
    //change sites internal links to class "snap_nopreview"
    var links = document.getElementsByTagName('a');
    for (var l = 0; l < links.length; l++) {
        if(links[l].href.match(/^http:\/\/<?php echo $url ?>/)){
            links[l].className += " snap_nopreview";
        }
    }
//]]>
</script>
<?php
	}
	if ($force || $spa_settings['footer'])
	{
		echo ald_spa();
	}
}

// Add an action called echo_spa so that it can be called using do_action('echo_spa');
add_action('echo_spa', 'echo_spa_function');
function echo_spa_function() {
	$spa_settings = spa_read_options();
	if (!$spa_settings['footer'])
	{
		ald_spa_display(true);
	}
}

// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_SPA_DIR . "/admin.inc.php");
}


?>