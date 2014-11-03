<?php

add_option('forum_posts_per_page', 10);
add_option('forum_threads_per_page', 20);
add_option('forum_require_registration', true);
add_option('forum_date_format', 'Y-m-d H:i:s');
//add_option('forum_page_created', false);
add_option('forum_page_title', 'Forums');
add_option('forum_use_gravatar', true);
//add_option('forum_moderator_level', 8);
add_option('forum_skin', 'default');
add_option('forum_allow_post_in_solved', true);
add_option('set_sort', false);
add_option('forum_use_spam', false);
add_option('forum_use_bbcode', false);


global $wpdb, $table_prefix;

if(isset($_POST['add_moderator'])){
	
	$forum = $_POST['forum'];
	$user = $_POST['user'];
	$mods = get_usermeta($user, 'moderator');
	$f = explode(',', $mods);
	
	if($forum == 0){
		$forums = forum_get_all_forums();
		foreach($forums as $ff){
				$mod .= ",$ff->id";
		}
		update_usermeta($user, 'moderator', $mod);
	}
	else{
		if(!in_array($forum, $f)){
			$mods .= ",$forum";
			update_usermeta($user, 'moderator', $mods);
		}
	}
	echo "<div class='updated fade'><p>Moderator successfully added.</p></div>";	
	
}
if(isset($_GET['addgroupsubmit'])){
	
	$table_groups = $table_prefix."forum_groups";
	$max = $wpdb->get_var("SELECT MAX(sort) from $table_groups");
	$max = $max + 1;
	$insert = "INSERT INTO $table_groups (name, sort) VALUES('$_GET[groupname]', '$max')";
	$wpdb->query($insert);
	echo "<div class='updated fade'><p>Group successfully added.</p></div>";	
}
if(isset($_GET['editforumsubmit'])){
	
	$table_forums = $table_prefix."forum_forums";
	$max = $wpdb->get_var("SELECT MAX(sort) from $table_forums WHERE parent_id = $_GET[forumparent]");
	$max = $max + 1;
	$update =  "UPDATE $table_forums SET sort = $max, name = '$_GET[forumname]', parent_id = '$_GET[forumparent]', description = '$_GET[forumdesc]' WHERE id = '$_GET[forum]'";
	$wpdb->query($update);
	echo "<div class='updated fade'><p>Forum changed successfully.</p></div>";	
}
if(isset($_GET['editgroupsubmit'])){
	
	$table_groups = $table_prefix."forum_groups";
	$update = "UPDATE $table_groups SET name = '$_GET[groupname]' WHERE id = '$_GET[group]'";
	$wpdb->query($update);
	echo "<div class='updated fade'><p>Forum changed successfully.</p></div>";	
}
if(isset($_GET['addforumsubmit'])){
	
	$table_forums = $table_prefix."forum_forums";
	$max = $wpdb->get_var("SELECT MAX(sort) from $table_forums WHERE parent_id = $_GET[forumparent]");
	$max = $max + 1;
	$insert = "INSERT INTO $table_forums (name, description, parent_id, sort) VALUES('$_GET[forumname]', '$_GET[forumdesc]', '$_GET[forumparent]', '$max')";
	$wpdb->query($insert);
	
	echo "<div class='updated fade'><p>Forum added successfully.</p></div>";	
}
if(isset($_GET['action']) && $_GET['action'] == "activateskin"){
	update_option('forum_skin', $_GET['skin']);
	echo "<div class='updated fade'><p>Skin $_GET[skinname] activated.</p></div>";
}
if(isset($_GET['forumupdate_submit'])){
	update_option('forum_posts_per_page', $_GET['forum_posts_per_page']);
	update_option('forum_threads_per_page', $_GET['forum_threads_per_page']);
	update_option('forum_require_registration', $_GET['forum_require_registration']);
	update_option('forum_date_format', $_GET['forum_date_format']);
	update_option('forum_page_title', $_GET['forum_page_title']);
	update_option('forum_allow_post_in_solved', $_GET['forum_allow_post_in_solved']);
	update_option('forum_use_gravatar', $_GET['forum_use_gravatar']);
	update_option('forum_use_spam', $_GET['forum_use_spam']);
	update_option('forum_use_bbcode', $_GET['forum_use_bbcode']);
	
	//update_option('forum_moderator_level', $_GET['forum_moderator_level']);
	
	echo "<div class='updated fade'><p>Options updated.</p></div>";	
}
if(isset($_GET['delete_group'])){
	$table_forums = $table_prefix."forum_forums";
	$table_groups = $table_prefix."forum_groups";
	$table_threads = $table_prefix."forum_threads";
	$table_posts = $table_prefix."forum_posts";
	
	$forums_sql = "SELECT id FROM $table_forums WHERE parent_id = $_GET[delete_group]";
	$forums = $wpdb->get_results($forums_sql);
	
	foreach($forums as $f){
		$threads = $wpdb->get_results("SELECT id FROM $table_threads WHERE forum_id = $f->id");
		$wpdb->query("DELETE FROM $table_threads WHERE forum_id = $f->id");
		foreach($threads as $t){
			$posts = $wpdb->get_results("SELECT id FROM $table_posts WHERE thread_id = $t->id");
			$wpdb->query("DELETE FROM $table_posts WHERE thread_id = $t->id");
		}
		$wpdb->query("DELETE FROM $table_forums WHERE parent_id = $_GET[delete_group]");
	}
	$wpdb->query("DELETE FROM $table_groups WHERE id = $_GET[delete_group]");
	
}
if(isset($_GET['delete_forum'])){
	forumadmin_delete_forum($_GET['delete_forum']);
}
if(isset($_GET['action']) && $_GET['action'] == "delete_mod"){
	if($_GET['forum'] == "all"){
		update_usermeta($_GET['user'], 'moderator', "");
	}
	else{
		$curr_feed = ",".$_GET['forum'];

		$f = get_usermeta($_GET['user'], 'moderator');

		$f = str_replace($curr_feed, '', $f);
		update_usermeta($_GET['user'], 'moderator', $f);
	}
	echo "<div class='updated fade'><p>Moderator deleted.</p></div>";	
}

if(isset($_GET['do'])){
	switch($_GET['do']){
	case "group_down":
		$ginfo = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_groups WHERE id = '".($_GET['id']*1)."'", ARRAY_A);
		$above = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_groups WHERE sort < '".$ginfo['sort']."' ORDER BY sort DESC", ARRAY_A);
		if ($above['id']>0){
			$wpdb->query("UPDATE {$table_prefix}forum_groups SET sort = '".$above['sort']."' WHERE id = '".($_GET['id']*1)."'");
			$wpdb->query("UPDATE {$table_prefix}forum_groups SET sort = '".$ginfo['sort']."' WHERE id = '".$above['id']."'");
		}
		$msg = "Group Moved Down";
	break;
	case "forum_down":
		$ginfo = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_forums WHERE id = '".($_GET['id']*1)."'", ARRAY_A);
		$above = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_forums WHERE parent_id = '".$ginfo['parent_id']."' && sort < '".$ginfo['sort']."' ORDER BY sort DESC", ARRAY_A);
		if ($above['id']>0){
			$wpdb->query("UPDATE {$table_prefix}forum_forums SET sort = '".$above['sort']."' WHERE id = '".($_GET['id']*1)."'");
			$wpdb->query("UPDATE {$table_prefix}forum_forums SET sort = '".$ginfo['sort']."' WHERE id = '".$above['id']."'");
		}
		$msg = "Forum Moved Down";
	break;
	case "group_up":
		$ginfo = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_groups WHERE id = '".($_GET['id']*1)."'", ARRAY_A);
		$above = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_groups WHERE sort > '".$ginfo['sort']."' ORDER BY sort ASC", ARRAY_A);
		if ($above['id']>0){
			$wpdb->query("UPDATE {$table_prefix}forum_groups SET sort = '".$above['sort']."' WHERE id = '".($_GET['id']*1)."'");
			$wpdb->query("UPDATE {$table_prefix}forum_groups SET sort = '".$ginfo['sort']."' WHERE id = '".$above['id']."'");
		}
		$msg = "Group Moved Up";
	break;
	case "forum_up":
		$ginfo = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_forums WHERE id = '".($_GET['id']*1)."'", ARRAY_A);
		$above = $wpdb->get_row("SELECT * FROM {$table_prefix}forum_forums WHERE parent_id = '".$ginfo['parent_id']."' && sort > '".$ginfo['sort']."' ORDER BY sort ASC", ARRAY_A);
		if ($above['id']>0){
			$wpdb->query("UPDATE {$table_prefix}forum_forums SET sort = '".$above['sort']."' WHERE id = '".($_GET['id']*1)."'");
			$wpdb->query("UPDATE {$table_prefix}forum_forums SET sort = '".$ginfo['sort']."' WHERE id = '".$above['id']."'");
		}
		$msg = "Forum Moved Up";
	break;
}
	echo "<div class='updated fade'><p>$msg</p></div>";
}


function forumadmin_delete_forum($forum){
	global $table_prefix, $wpdb;
	
	$table_forums = $table_prefix."forum_forums";
	$table_posts = $table_prefix."forum_posts";
	$table_threads = $table_prefix."forum_threads";
	
	$del_threads = "DELETE FROM $table_threads WHERE id = $forum";
	$del_posts = "DELETE FROM $table_posts WHERE thread_id = $forum";
	$del_forum = "DELETE FROM $table_forums WHERE id = $forum";
	
	$threads = $wpdb->get_results("SELECT id FROM $table_threads WHERE forum_id = $forum");
	
	foreach($threads as $t){
		$posts = $wpdb->get_results("SELECT id FROM $table_posts WHERE thread_id = $t->id");
		$wpdb->query("DELETE FROM $table_posts WHERE thread_id = $t->id");
		$wpdb->query("DELETE FROM $table_threads WHERE id = $t->id");
		
	}
	$wpdb->query("DELETE FROM $table_forums WHERE id = $forum");
}
{
$path = "edit.php?page=wp-forum/wp-forum-manage.php&args=";

$admin_links = array(
					array(name=>'Settings',arg=>'wpforumsettings'),
					array(name=>'Structure',arg=>'wpforumstructure'),
					array(name=>'Skins',arg=>'wpforumskins'),
					array(name=>'Moderators',arg=>'wpforumusers')
					
					
					);  ?>
					
	<div class="wrap">
	<ul id="submenu">	
	<?php 
		for ($i=0; $i<count($admin_links); $i++)
		{
			$tlink = $admin_links[$i];
			if ($tlink['arg']==$_GET['args'] || (!$_GET['args'] && $i==0)){
				$sel = " class=\"current\"";
				$pagelabel = $tlink['name'];
			} 
			else {
				$sel = "";
			} ?>
			<li><a href='<?php echo $path.$tlink['arg'];?>'<?php echo $sel;?>><?php echo __($tlink['name']);?></a></li>
		<?php } ?>
	
	</ul>
		<h2><?php echo __($pagelabel);?></h2>
		<?php
			switch ($_REQUEST['args']){
				case "wpforumsettings":
				default:
					wpforumsettings();
				break;
				case "wpforumstructure":
					wpforumstructure();
				break;
				case "wpforumthreads":
					wpforumthreads();
				break;
				case "wpforumskins":
					wpforumskins();
				break;
				case "wpforumusers":
					wpforumusers();
				break;
				
			}
		?>
	</div>
<?php
}
function wpforumsettings(){
	echo "<h3>Options</h3><form action='' method='get'>
		<table class='optiontable'>
		<tr>
			<th>Forum page title:</th>
			<td><input type='text' name='forum_page_title' value='".get_option('forum_page_title')."' /></td>
		</tr>
		<tr>
			<th>Posts per page:</th>
			<td><input type='text' name='forum_posts_per_page' value='".get_option('forum_posts_per_page')."' /></td>
		</tr>
		<tr>
			<th>Threads per page:</th>
			<td><input type='text' name='forum_threads_per_page' value='".get_option('forum_threads_per_page')."' /></td>
		</tr>
		
		
		
		<tr>
			<th>Allow new posts in solved threads:</th>
		  	<td><input type='checkbox' name='forum_allow_post_in_solved' value='true'";
		 if(get_option('forum_allow_post_in_solved') == 'true') 
			echo "checked='checked'";
		echo "/></td>
		</tr>
		
		<tr>
			<th>Show <a href='http://www.gravatar.com'>Gravatars</a> in the forum:</th>
		  	<td><input type='checkbox' name='forum_use_gravatar' value='true'";
		 if(get_option('forum_use_gravatar') == 'true') 
			echo "checked='checked'";
		echo "/></td>
		</tr>
		
			<tr>
				<th>Registration required to post:</th>
			  	<td><input type='checkbox' name='forum_require_registration' value='true'";
			 if(get_option('forum_require_registration') == 'true') 
				echo "checked='checked'";
			echo "/></td></tr>
			
			<tr>
				<th>Filter spam words when posting:</th>
			  	<td><input type='checkbox' name='forum_use_spam' value='true'";
			 if(get_option('forum_use_spam') == 'true') 
				echo "checked='checked'";
			echo "/></td>
			</tr>
			
			<tr>
				<th>Use BBCode for posts:</th>
			  	<td><input type='checkbox' name='forum_use_bbcode' value='true'";
			 if(get_option('forum_use_bbcode') == 'true') 
				echo "checked='checked'";
			echo "/></td>
			</tr>
			";
			
			/*$modlevel = get_option('forum_moderator_level');
			switch($modlevel){
				case 8: $admin_check = "checked='checked'"; break;
				case 5: $editor_check = "checked='checked'"; break;
				case 2: $author_check = "checked='checked'"; break;
				case 1: $cont_check = "checked='checked'"; break;
			}

		echo "<tr>
				<th valign='top'>Moderator <a href='http://codex.wordpress.org/Roles_and_Capabilities'>user level</a>:</th>
				<td valign='top'>
					<input type='radio' name='forum_moderator_level' $admin_check value='8' /> Administrator<br/>
					<input type='radio' name='forum_moderator_level' $editor_check value='5' /> Editor<br/>
					<input type='radio' name='forum_moderator_level' $author_check value='2' /> Author<br/>
					<input type='radio' name='forum_moderator_level' $cont_check value='1' /> Contributor<br/>
				</td>
			</tr>";		*/ 
			
			echo "<tr>
				<th valign='top'>Date format:</th><td><input type='text' name='forum_date_format' value='".get_option('forum_date_format')."'  /> <p>Default date: \"Y-m-d, H:i:s\". <br />Check <a href='http://www.php.net'>http://www.php.net</a> for date formatting.</p></td>
			</tr>
			<tr>
			<td></td>
			<td><input type='submit' name='forumupdate_submit' value='Update options'  /></td>
		</tr>
		</table>"; ?>
		<input type="hidden" name="args" value="wpforumsettings" />
		<input type="hidden" name="page" value="wp-forum/wp-forum-manage.php" />
		
		</form>
		
	<?php
}
function wpforumstructure(){
	
	global $wpdb, $path, $table_prefix;
	$table_groups = $table_prefix."forum_groups";
	$table_forums = $table_prefix."forum_forums";

	$groups = forum_get_groups();
	
	if(!isset($_GET['action'])){
	?>
		<p>
		<input type="button" value="Add Group" onclick="document.location.href='<?php echo $path;?>wpforumstructure&amp;action=addgroup'">
		<?php if($groups){ ?>
			<input type="button" value="Add Forum" onclick="document.location.href='<?php echo $path;?>wpforumstructure&amp;action=addforum'">
			<?php } ?>
		</p>
	<?php
	}
	if(isset($_GET['action']) && ($_GET['action'] == "addgroup" || $_GET['action'] == "editgroup")){
		
		if($_GET['action'] == "addgroup")
			echo "<h3>Add group</h3>";
		if($_GET['action'] == "editgroup"){
			echo "<h3>Edit group</h3>";
			$groupname = forum_get_group_name($_GET['group']);
		}?>
		
		<form action="" metod="get">
		<table border='0' cellpadding='3' cellspacing='3'>
			<tr>
				<td align='right'>Name:</td>
				<td><input type='text' name='groupname' value="<?php echo $groupname; ?>"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<?php 
					if($_GET['action'] == "addgroup")
						echo "<input type='submit' value='Save Group' name='addgroupsubmit'>";
					if($_GET['action'] == "editgroup")
						echo "<input type='submit' value='Save Group' name='editgroupsubmit'>";?>
					
				</td>
			</tr>

		</table>
		<input type="hidden" name="args" value="wpforumstructure">
		<input type="hidden" name="page" value="wp-forum/wp-forum-manage.php">
		<?php
		if($_GET['action'] == "editgroup"){
			echo "<input type='hidden' name='group' value='$_GET[group]'>";
		} ?>
		</form>
		<?php
		if($_GET['action'] == "editgroup"){
			$delete_link = $path."wpforumstructure&delete_group=$_GET[group]";
			echo "<a href='$delete_link'>Delete this group</a>";
		}
	}
	if(isset($_GET['action']) && ($_GET['action'] == "addforum" || $_GET['action'] == "editforum")){ 
		
			if($_GET['action'] == "addforum")
				echo "<h3>Add forum</h3>";
			if($_GET['action'] == "editforum"){
				echo "<h3>Edit forum</h3>";
				$forum_name = forum_get_forum_name($_GET['forum']);
				$forum_desc = forum_get_forum_desc($_GET['forum']);
				$forum_group_id = forum_get_parent($_GET['forum']);
			}?>
		
		<form action="" metod="get">
	
		<table border='0' cellpadding='3' cellspacing='3'>
			<tr>
				<td align='right'>Name:</td>
				<td><input type='text' name='forumname' value="<?php echo $forum_name ;?>" ></td>
			</tr>
			<tr>
				<td align='right'>Description:</td>
				<td><input type='text' name='forumdesc' value="<?php echo $forum_desc ;?>"></td>
			</tr>
			<tr>
				<td align='right'>Parent Group:</td>
				<td> <select name="forumparent">
					<?php foreach($groups as $group){
						if($group->id == $forum_group_id)
							echo "<option selected value='$group->id'>$group->name</option>";
						else
							echo "<option value='$group->id'>$group->name</option>";
					} ?>
					</select>
				</td>
			</tr>
				<tr>
					<td>&nbsp;</td>
					<td><?php
					if($_GET['action'] == "addforum")
						echo "<input type='submit' value='Save Forum' name='addforumsubmit'>";
					if($_GET['action'] == "editforum")
						echo "<input type='submit' value='Save Forum' name='editforumsubmit'>";?>
						
						</td>
				</tr>
		</table>
		<input type="hidden" name="args" value="wpforumstructure">
		<input type="hidden" name="page" value="wp-forum/wp-forum-manage.php">
		<?php
		if($_GET['action'] == "editforum"){
			echo "<input type='hidden' name='forum' value='$_GET[forum]'>";
		}?>
		
		</form>
		<?php
		if($_GET['action'] == "editforum"){
			$delete_link = $path."wpforumstructure&delete_forum=$_GET[forum]";
			echo "<a href='$delete_link'>Delete this forum</a>";
		}
	 }
	if(!isset($_GET['action'])){
	echo "<table border='0' cellpadding='5' cellspacing='3' >
	
		<tr>
			<th align='left'>Group/Forum</th>
			<th align='left'>Description</th>
			<th align='left'>Threads</th>
			<th align='left'>Posts</th>
			<th>&nbsp;</th>
		</tr>
		<tr><td colspan='5'><hr></td></tr>";
		
		$is_sorted = get_option('set_sort');
		$i = 1;
		$j = 1;
	foreach((array)$groups as $group){ 
		
		$forums = forum_get_forums($group->id);
		$edit_link = $path."wpforumstructure&action=editgroup&group=$group->id";
		
		$up_link 	= $path."wpforumstructure&do=group_up&id=$group->id";
		$down_link 	= $path."wpforumstructure&do=group_down&id=$group->id";
		if(!$is_sorted)
			$wpdb->query("UPDATE $table_groups SET sort = '$i' WHERE id = $group->id");
		
		$down = "";
		echo "<tr><td><a href='$edit_link'>$group->sort $group->name</a></td><td><a href='$up_link'>&#x2191;</a> | <a href='$down_link'>&#x2193;</a></td>";
		echo "<td colspan='3'>&nbsp;</td></tr>";
		++$i;
		foreach((array)$forums as $forum){
			$up_link 	= $path."wpforumstructure&do=forum_up&id=$forum->id";
			$down_link 	= $path."wpforumstructure&do=forum_down&id=$forum->id";
			if(!$is_sorted)
				$wpdb->query("UPDATE $table_forums SET sort = '$j' WHERE id = $forum->id");
			$threads = forum_get_thread_count($forum->id);
			$posts = forum_get_posts_in_forum_count($forum->id);
			$edit_link = $path."wpforumstructure&action=editforum&forum=$forum->id";
			echo "<tr>
				<td> -- <a href='$edit_link'>$forum->name</a></td>
				<td>$forum->sort $forum->description</td>
				<td>$threads</td>
				<td>$posts</td>
				<td><a href='$up_link'>&#x2191;</a> | <a href='$down_link'>&#x2193;</a></td>
			</tr>";
			++$j;
			
		}
		
	}
	echo "</table>";
	}
	update_option('set_sort', true);
}
function wpforumskins(){
	global $PLUGIN_PATH;
	
	$dir = ABSPATH."wp-content/plugins/wp-forum/skins/";
	
	// Find all skins within directory
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
	   if ($dh = opendir($dir)) {
		
		echo "<table width='100%' cellpadding='5' cellspacing='3'>
				<tr>
					<th>Skin name</th>
					<th>Version</th>
					<th>Description</th>
					<th>Action</th>
				</tr>";
	       while (($file = readdir($dh)) !== false) {
				if(filetype($dir . $file) == "dir" && $file != ".." && $file != "."){
					$p = file($dir.$file."/style.css");
					
					$skin_name = forum_get_skinmeta($file."/style.css", "Skin name");
					$skin_desc = forum_get_skinmeta($file."/style.css", "Description");
					$skin_author = forum_get_skinmeta($file."/style.css", "Author");
					$skin_author_url = forum_get_skinmeta($file."/style.css", "Author url");
					$skin_version = forum_get_skinmeta($file."/style.css", "Version");
					
					$plugin_url = "http://www.fahlstad.se/wp-plugins/wp-forum";
					$class = ($class == "alternate")?"":"alternate";
					
								if($file == get_option('forum_skin'))
									echo "<tr class='$class active'>";
								else
									echo "<tr class='$class'>";
									
								echo "<td class='name'><b>$skin_name</b></td>";
									
								echo "<td class='vers'>$skin_version</td>
								<td class='desc'>$skin_desc By: <a href='$skin_author_url'>$skin_author</a></td>";
								if($file == get_option('forum_skin'))
									echo  "<td class='togl'>In use</td>";
								else
									echo "<td class='togl'><a href='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&action=activateskin&skin=$file&skinname=$skin_name'>Activate</a></td>
							</tr>";
					
				}
	       }
	
	       closedir($dh);
	   }
		echo "</table>";
	}
	else
		echo "<p>No skin directory</p>";
}


function forum_get_skinmeta($skin, $what){
	$file = implode('', file(ABSPATH."wp-content/plugins/wp-forum/skins/".$skin));
	
	if (preg_match("|$what:(.*)|i", $file, $meta)) {
			return trim($meta[1]);
	}
	else
		return "";
}

function wpforumusers(){
	global $wpdb, $table_prefix;
	$users = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users ORDER BY user_login ASC");
	$moderators = $wpdb->get_results("SELECT user_id, user_login, meta_value FROM $wpdb->usermeta 
	INNER JOIN $wpdb->users ON $wpdb->usermeta.user_id=$wpdb->users.ID WHERE meta_key = 'moderator' AND meta_value <> ''");
	$table = $table_prefix."forum_forums";
	$forums = forum_get_all_forums();
	//$groups =  forum_get_groups();
	foreach($forums as $forum){
		$all .= ",$forum->id";
	}
	echo "<h3>Current moderators</h3>";
		foreach($moderators as $moderator){
			$c = "";
				$fo = $wpdb->get_results("SELECT * FROM $table WHERE id IN (".substr_replace($moderator->meta_value, "", 0, 1).") ORDER BY id DESC");
				echo "<h3>".get_usermeta($moderator->user_id, 'nickname')."</h3>";
				$delete_link_all = $_SERVER['PHP_SELF']."?page=wp-forum/wp-forum-manage.php&args=wpforumusers&action=delete_mod&user=$moderator->user_id&forum=all";
				echo "<a href='$delete_link_all'>(Delete from all forums)</a>";
				echo "<table width='400' cellpadding='9' cellspacing='2'>";
				echo "<tr><th>Forum</th><th>Action</th>";
				foreach($fo as $f){
					$c = ($c == "")?"alternate":"";
					$delete_link = $_SERVER['PHP_SELF']."?page=wp-forum/wp-forum-manage.php&args=wpforumusers&action=delete_mod&user=$moderator->user_id&forum=$f->id";
					echo "<tr class='$c'><td width='100%'>".forum_get_group_name($f->parent_id)." &raquo; <a href='".forum_get_link_from_post($f->id)."'>$f->name</a></td>";
					echo "<td><a href='$delete_link'>Delete</a></td></tr>";
				}
				
				echo "</table>";
		}
	
	echo "<h3>Add moderator</h3>";
	
	echo "<form action='' method='post'>";
	echo "<table cellpadding='5'>
			<tr>
				<td><b>User: </b></td><td>";
	
	echo "<select name='user'>";
	
	foreach($users as $user)
		echo "<option value='$user->ID'>".get_usermeta($user->ID, 'nickname')."</option>";
	
	echo "</select>
			</td></tr><tr>
				<td><b>Forum: </b></td><td>";
			
			
			echo "<select name='forum'>";
			echo "<option value='0' selected >All Forums</option>";

			foreach($forums as $forum)
				echo "<option value='$forum->id'>".forum_get_group_name($forum->parent_id)." &raquo; ".$forum->name."</option>";

			echo "</select>
					</td></tr>";		

	echo "<tr><td colspan='2'><input type='submit' value='Add moderator' name='add_moderator' /></td></tr>";
	
	echo "</table>";
	echo "</form>";
}
























