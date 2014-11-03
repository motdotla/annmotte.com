<?php
/**********************************************************************
*					Admin Page							*
*********************************************************************/
function spa_default_options() {
	$spa_settings = 	Array (
						url => str_replace("http://", "", get_bloginfo('wpurl')),
						key => '',
						searchbox => true,
						locallinks => true,
						footer => true
						);
	
	return $spa_settings;
}

function spa_options() {
	
	$spa_settings = spa_read_options();

	if($_POST['spa_save']){
		$spa_settings[key] = $_POST['Key'];
		
		if ($_POST['SearchBox']) {
			$spa_settings[searchbox] = true;
		} else {
			$spa_settings[searchbox] = false;
		}
		if ($_POST['LocalLinks']) {
			$spa_settings[locallinks] = true;
		} else {
			$spa_settings[locallinks] = false;
		}
		if ($_POST['Footer']) {
			$spa_settings[footer] = true;
		} else {
			$spa_settings[footer] = false;
		}
		
		update_option('ald_spa_settings', $spa_settings);
		
		echo '<div id="message" class="updated fade"><p>Options saved successfully.</p></div>';
	}
	
	if ($_POST['spa_default']){
	
		$spa_settings = spa_default_options();
		update_option('ald_spa_settings', $spa_settings);
		
		echo '<div id="message" class="updated fade"><p>Options set to Default.</p></div>';
	}
?>


<div class="wrap">
  <h2>
    <?php _e("Snap Preview Anywhere<sup>TM</sup>"); ?>
  </h2>
  <div style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Support the Development'); ?>
    </h3>
    </legend>
    <p><?php _e('If you find my'); ?> <a href="http://ajaydsouza.com/wordpress/plugins/snap-preview-anywhere/">Snap Preview Anywhere<sup>TM</sup> Plugin</a> <?php _e('useful, please do'); ?> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate@ajaydsouza.com&amp;item_name=Snap%20Preview%20Anywhere%20Plugin%20(From%20WP-Admin)&amp;no_shipping=1&amp;return=http://ajaydsouza.com/wordpress/plugins/snap-preview-anywhere/&amp;cancel_return=http://ajaydsouza.com/wordpress/plugins/snap-preview-anywhere/&amp;cn=Note%20to%20Author&amp;tax=0&amp;currency_code=USD&amp;bn=PP-DonationsBF&amp;charset=UTF-8" title="Donate via PayPal"><?php _e('drop in your contribution'); ?></a>. (<a href="http://ajaydsouza.com/donate/" title="Some reasons why you should donate"><?php _e('Why should you?'); ?></a>)</p>
    </fieldset>
  </div>
  <form method="post" id="spa_options" name="spa_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:'); ?>
    </h3>
    </legend>
	<p>
		<label for="Key"><?php _e('SPA Key:'); ?></label>
		<input type="text" name="Key" id="Key" value="<?php echo $spa_settings[key]; ?>" size="40" maxlength="32" />
		<?php _e('Don\'t have a key? <a href="http://www.snap.com/about/spa1.php">Signup here</a>'); ?>
	</p>
	<p>
		<label><input type="checkbox" name="SearchBox" id="SearchBox" value="true" <?php if ($spa_settings['searchbox']) { ?> checked="checked" <?php } ?> />
		<?php _e('Display Snap Searcbox below the thumbnail?'); ?></label>
	</p>
	<p>
		<label><input type="checkbox" name="LocalLinks" id="LocalLinks" value="true" <?php if ($spa_settings['locallinks']) { ?> checked="checked" <?php } ?> />
		<?php _e('Display Preview for local links?'); ?></label>
	</p>
	<p>
		<label><input type="checkbox" name="Footer" id="Footer" value="true" <?php if ($spa_settings['footer']) { ?> checked="checked" <?php } ?> />
		<?php _e('Automatically add the code to your footer?'); ?></label>
	</p>
	<p>
	    <input type="submit" name="spa_save" id="spa_save" value="Save Options" style="border:#00CC00 1px solid" />
        <input name="spa_default" type="submit" id="spa_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('Do you want to set options to Default?')) return false;" />
	</p>
    </fieldset>
  </form>
</div>
<?php

}


function spa_adminmenu() {
	if (function_exists('current_user_can')) {
		// In WordPress 2.x
		if (current_user_can('manage_options')) {
			$spa_is_admin = true;
		}
	} else {
		// In WordPress 1.x
		global $user_ID;
		if (user_can_edit_user($user_ID, 0)) {
			$spa_is_admin = true;
		}
	}

	if ((function_exists('add_options_page'))&&($spa_is_admin)) {
		add_options_page(__("SPA"), __("SPA"), 9, 'spa_options', 'spa_options');
		}
}

add_action('admin_menu', 'spa_adminmenu');

?>