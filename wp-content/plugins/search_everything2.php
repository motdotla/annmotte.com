<?php
/*
Plugin Name: Search Everything
Plugin URI: http://dancameron.org/wordpress/wordpress-plugins/search-everything-wordpress-plugin/
Description: Adds search functionality with little setup. Including the option to search pages, attachments, drafts, comments and custom fields (metadata).  wp-admin options page added to customize default searches.
Heavy props to <a href="http://kinrowan.net">Cori Schlegel</a> for making the options panel and additional searches possible. 
Version: 2.3 Jan2
Author: Dan Cameron
Author URI: http://dancameron.org
*/

/*  Copyright © 2005-06, Daniel Cameron  (email : dancameron@gmail.com)
	Portions Copyright © 2006, Jan (email: jan at geheimwerk dot de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/




//add filters based upon option settings

//logging
$logging = 0;

function SE2_log($msg) {
	global $logging;
	if ($logging) {
		$fp = fopen("logfile.log","a+");
		$date = date("Y-m-d H:i:s ");
		$source = "search_everything_2 plugin: ";
		fwrite($fp, "\n\n".$date."\n".$source."\n".$msg);
		fclose($fp);
	}
	return true;
	}



//add filters based upon option settings
if ("true" == get_option('SE2_use_page_search')) {
	add_filter('posts_where', 'SE2_search_pages');
	SE2_log("searching pages");
	}

if ("true" == get_option('SE2_use_comment_search')) {
	add_filter('posts_where', 'SE2_search_comments');
	add_filter('posts_join', 'SE2_comments_join');
	SE2_log("searching comments");
	}

if ("true" == get_option('SE2_use_draft_search')) {
	add_filter('posts_where', 'SE2_search_draft_posts');
	SE2_log("searching drafts");
	}

if ("true" == get_option('SE2_use_attachment_search')) {
	add_filter('posts_where', 'SE2_search_attachments');
	SE2_log("searching attachments");
	}

if ("true" == get_option('SE2_use_metadata_search')) {
	add_filter('posts_where', 'SE2_search_metadata');
	add_filter('posts_join', 'SE2_search_metadata_join');
	SE2_log("searching metadata");
	}


//search pages
function SE2_search_pages($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where = str_replace(' AND (post_status = "publish"', ' AND (post_status = "publish" or post_status = "static"', $where);
	}

	SE2_log("pages where: ".$where);
	return $where;
}

//search drafts
function SE2_search_draft_posts($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where = str_replace(' AND (post_status = "publish"', ' AND (post_status = "publish" or post_status = "draft"', $where);
	}

	SE2_log("drafts where: ".$where);
	return $where;
}

//search attachments
function SE2_search_attachments($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where = str_replace(' AND (post_status = "publish"', ' AND (post_status = "publish" or post_status = "attachment"', $where);
		$where = str_replace('AND post_status != "attachment"','',$where);
	}

	SE2_log("attachments where: ".$where);
	return $where;
}

//search comments
function SE2_search_comments($where) {
global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where .= " OR (comment_content LIKE '%" . $wp_query->query_vars['s'] . "%') ";
	}

	SE2_log("comments where: ".$where);

	return $where;
}

//join for searching comments
function SE2_comments_join($join) {
	global $wp_query, $wpdb;

	if (!empty($wp_query->query_vars['s'])) {

		if ('true' == get_option('SE2_approved_comments_only')) {
			$comment_approved = " AND comment_approved =  '1'";
  		} else {
			$comment_approved = '';
    	}

		$join .= "LEFT JOIN $wpdb->comments ON ( comment_post_ID = ID " . $comment_approved . ") ";
	}
	SE2_log("comments join: ".$join);
	return $join;
}

//search metadata
function SE2_search_metadata($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where .= " OR meta_value LIKE '%" . $wp_query->query_vars['s'] . "%' ";
	}

	SE2_log("metadata where: ".$where);

	return $where;
}

//join for searching metadata
function SE2_search_metadata_join($join) {
	global $wp_query, $wpdb;

	if (!empty($wp_query->query_vars['s'])) {

		$join .= "LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
	}
	SE2_log("metadata join: ".$join);
	return $join;
}


//build admin interface
function SE2_option_page() {

global $wpdb, $table_prefix;

	if ( isset($_POST['SE2_update_options']) ) {

		$errs = array();

		if ( !empty($_POST['search_pages']) ) {
			update_option('SE2_use_page_search', "true");
		} else {
			update_option('SE2_use_page_search', "false");
		}

		if ( !empty($_POST['search_comments']) ) {
			update_option('SE2_use_comment_search', "true");
		} else {
			update_option('SE2_use_comment_search', "false");
		}

		if ( !empty($_POST['appvd_comments']) ) {
			update_option('SE2_approved_comments_only', "true");
		} else {
			update_option('SE2_approved_comments_only', "false");
		}

		if ( !empty($_POST['search_drafts']) ) {
			update_option('SE2_use_draft_search', "true");
		} else {
			update_option('SE2_use_draft_search', "false");
		}

		if ( !empty($_POST['search_attachments']) ) {
			update_option('SE2_use_attachment_search', "true");
		} else {
			update_option('SE2_use_attachment_search', "false");
		}

		if ( !empty($_POST['search_metadata']) ) {
			update_option('SE2_use_metadata_search', "true");
		} else {
			update_option('SE2_use_metadata_search', "false");
		}

		if ( empty($errs) ) {
			echo '<div id="message" class="updated fade"><p>Options updated!</p></div>';
		} else {
			echo '<div id="message" class="error fade"><ul>';
			foreach ( $errs as $name => $msg ) {
				echo '<li>'.wptexturize($msg).'</li>';
			}
			echo '</ul></div>';
	 }
	} // End if update

	//set up option checkbox values
	if ('true' == get_option('SE2_use_page_search')) {
		$page_search = 'checked="true"';
	} else {
		$page_search = '';
	}

	if ('true' == get_option('SE2_use_comment_search')) {
		$comment_search = 'checked="true"';
	} else {
		$comment_search = '';
	}

	if ('true' == get_option('SE2_approved_comments_only')) {
		$appvd_comment = 'checked="true"';
	} else {
		$appvd_comment = '';
	}

	if ('true' == get_option('SE2_use_draft_search')) {
		$draft_search = 'checked="true"';
	} else {
		$draft_search = '';
	}

	if ('true' == get_option('SE2_use_attachment_search')) {
		$attachment_search = 'checked="true"';
	} else {
		$attachment_search = '';
	}

	if ('true' == get_option('SE2_use_metadata_search')) {
		$metadata_search = 'checked="true"';
	} else {
		$metadata_search = '';
	}

	?>

	<div style="width:75%;" class="wrap" id="SE2_options_panel">
	<h2>Search Everything</h2>

	<div id="searchform">
		<form method="get" id="searchform" action="<?php bloginfo('home'); ?>">
			<div><input type="text" value="<?php echo wp_specialchars($s, 1); ?>" name="s" id="s" />
				<input type="submit" id="searchsubmit" value="Search" />
			</div>
		</form>
	</div>

	<p>Select the options you want to enable for seaching.<br />
	Any items selected here will be searched in every search query on the site; in addition to the built-in post search.<br />
	use the search box above to test your results (this may not work with some themes).</p>

	<form method="post">

	<table id="search_options" cell-spacing="2" cell-padding="2">
		<tr>
			<td class="col1"><input type="checkbox" name="search_pages" value="<?php echo get_option('SE2_use_page_search'); ?>" <?php echo $page_search; ?> /></td>
			<td class="col2">Search Every Page</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_comments" value="<?php echo get_option('SE2_use_comment_search'); ?>" <?php echo $comment_search; ?> /></td>
			<td class="col2">Search Every Comment</td>
		</tr>
		<tr class="child_option">
			<td>&nbsp;</td>
			<td>
				<table>
					<tr>
						<td class="col1"><input type="checkbox" name="appvd_comments" value="<?php echo get_option('SE2_approved_comments_only'); ?>" <?php echo $appvd_comment; ?> /></td>
						<td class="col2">Search only Approved comments only?</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_drafts" value="<?php echo get_option('SE2_use_draft_search'); ?>" <?php echo $draft_search; ?> /></td>
			<td class="col2">Search Every Draft</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_attachments" value="<?php echo get_option('SE2_use_attachment_search'); ?>" <?php echo $attachment_search; ?> /></td>
			<td class="col2">Search Every Attachment</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_metadata" value="<?php echo get_option('SE2_use_metadata_search'); ?>" <?php echo $metadata_search; ?> /></td>
			<td class="col2">Search Custom Fields (Metadata)</td>
		</tr>
	</table>

	<p class="submit">
	<input type="submit" name="SE2_update_options" value="Update &raquo;"/>
	</p>
	</form>

	</div>

	<?php
}	//end SE2_option_page

function SE2_add_options_panel() {
	add_options_page('Search Everything', 'Search Everything', 1, 'SE2_options_page', 'SE2_option_page');
}
add_action('admin_menu', 'SE2_add_options_panel');

//styling options page
function SE2_options_style() {
	?>
	<style type="text/css">

	table#search_options {
		table-layout: auto;
 	}


 	#search_options td.col1, #search_options th.col1 {
		width: 30px;
		text-align: left;
  	}

 	#search_options td.col2, #search_options th.col2 {
		width: 220px;
		margin-left: -15px;
		text-align: left;
  	}

  	#search_options tr.child_option {
		margin-left: 15px;
		margin-top; -3px;
   }

   #SE2_options_panel p.submit {
		text-align: left;
   }

	div#searchform div {
		margin-left: auto;
		margin-right: auto;
		margin-top: 5px;
		margin-bottom: 5px;
 	}

 	</style>

<?php
}


add_action('admin_head', 'SE2_options_style');

?>
