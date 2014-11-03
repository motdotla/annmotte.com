=== Snap Preview Anywhere(TM) Plugin ===
Tags: snap, thumbnail, search, plugin
Contributors: Ajay D'Souza

Add Snap Preview Anywhere(TM) to your blog


== Installation ==

1. Signup for SPA at http://www.snap.com/about/spa1.php and generate the code. In the code you will find a 32-digit parameter that follows key=. This will be needed for configuring the display. 
2. Download Snap Preview Anywhere(TM) Plugin. 
3. Extract the contents of ald-spa.zip to wp-content/plugins/ folder. You should get a folder called ald-spa.
4. Activate the Plugin in WP-Admin. 
5. Goto Options > SPA. If you are upgrading from an earlier version, it is recommended that you click Default Options so as to clean up any old options. 
6. You can configure the plugin to automatically add the code to wp_footer() (This needs to be present in your theme). Alternatively add <?php do_action('echo_spa'); ?> just before the </body>. 


== Frequently Asked Questions ==

= What are the requirements for this plugin? =

WordPress 1.5 or above


= Can I customize what is displayed? =

Yes you can. Visit Options > SPA
1. Key: The unique that you get from Snap on registration
2. Display Preview for local links: Select if you want a thumbnail to display when hovering over internal links. (Default selected)
3.Display Snap Searcbox below the thumbnail: Select if you want to display the small box at the bottom of the thumbnail. (Default selected)
4. Automatically add the code to your footer: Select if you want the code to be added to the footer automatically. You need to have wp_footer() in your theme file. Alternatively add <?php do_action('echo_spa') ?> just before </body>


For more information, please visit http://ajaydsouza.com/wordpress/plugins/snap-preview-anywhere/#function

= Do I really need this plugin? =
If you want to add SPA to your blog easily and you don't want to bother adding the code manually and still have a lot of customization options, then this plugin is for you.
