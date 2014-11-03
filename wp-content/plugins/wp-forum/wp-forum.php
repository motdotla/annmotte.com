<?php
/*
Plugin Name: WP-Forum
Plugin URI: http://www.fahlstad.se/wp-plugins/wp-forum
Description: A simple forum plugin, posting requires registration on the Wordpress blog.
Author: Fredrik Fahlstad, Thanks to Stefan Lewandowski @ http://www.3form.net for security code.
Version: 1.7.4
Author URI: http://www.fahlstad.se
*/
include( 'cookie.php' );
include("forum-functions.php");
include( 'bbcode.php' );
/* 
	Todo	File upload
	Todo	Flas threads
*/
// Globals
add_option('forum_skin', 'default');

$redirect				= "";
$PLUGIN_PATH 			= get_bloginfo('wpurl')."/wp-content/plugins/wp-forum";
$reg_link 				= get_bloginfo('wpurl')."/wp-register.php?redirect_to=";
$login_link 			= get_bloginfo('wpurl')."/wp-login.php?redirect_to=";
$profile_link 			= get_bloginfo('wpurl')."/wp-admin/profile.php";
$logout_link 			= get_bloginfo('wpurl')."/wp-login.php?action=logout&amp;redirect_to=";
$profile 				= get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showprofile&amp;user=";
$search_link			= get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=search";
$rss_link 				= $PLUGIN_PATH."/forum_feed.php?user=";
$main_link  			= get_bloginfo('wpurl');
$skin_dir				= "skins";

function get_version(){
	$plugin_data = implode('', file("wp-content/plugins/wp-forum/wp-forum.php"));
	if (preg_match("|Version:(.*)|i", $plugin_data, $version)) {
			$version = $version[1];
	}
	return $version;
}

function wp_forum($content){
	
	get_currentuserinfo();
	
	global $user_ID, $user_login, $forum_page_id, $user_level, $wpforum, $login_link, $logout_link, $reg_link;
	if($user_ID)
		ul($user_ID);
	
	
	if(!preg_match("|<!--WPFORUM-->|", $content))
		return $content;
		
		if($_GET['forum'])
			{
				if (!preg_match("/^[0-9]{1,20}$/", $_GET['forum'])) die("Bad forum,
		please re-enter.");
			}

			if($_GET['thread'])
			{
				if (!preg_match("/^[0-9]{1,20}$/", $_GET['thread'])) die("Bad
		thread, please re-enter.");
			}

			if($_GET['threadstart'])
			{
				if (!preg_match("/^[0-9]{1,20}$/", $_GET['threadstart'])) die("Bad
		threadstart, please re-enter.");
			}

			if($_GET['start'])
			{
				if (!preg_match("/^[0-9]{1,20}$/", $_GET['start'])) die("Bad
		threadstart, please re-enter.");
			}

			if($_GET['forumpost'])
			{
				if (!preg_match("/^[0-9]{1,20}$/", $_GET['forumpost'])) die("Bad
		forumpost, please re-enter.");
			}
		
// Override $forum_page_id
	if(isset($_GET['forumaction'])){
	
		switch($_GET['forumaction']){
		
			case 'dosubscribe':
				$out .= forum_add_subscription();
				$out .=  forum_show_thread($_GET['thread']);
				break;
			case 'emailsubscribe':
				$out .= forum_email_subscription();
				$out .=  forum_show_thread($_GET['thread']);
				break;
			case "showforum":
				$out .= forum_show_forum($_GET['forum']);
				break;
			case "showposts":
				$out .=  forum_show_thread($_GET['thread']);
				break;
			case "post":
				$out .= forum_insert_post();
				if(!isset($_POST['preview_post']))
					$out .=  forum_show_thread($_GET['thread']);
				break;
			case "thread":
				$out .= forum_insert_thread();
				if(!isset($_POST['preview']))
					$out .= forum_show_forum($_GET['forum']);
				break;
			case "edit":
				$out .= forum_edit_post();
				break;
			case "editsubject":
				$out .= forum_edit_post();
				break;
			case "doeditpost":
				forum_edit_post();
				$out .=  forum_show_thread($_GET['thread']);
				break;
			case "doeditsubject":
				forum_edit_post();
				$out .= forum_show_forum($_GET['forum']);
				break;
			case "deletepost":
				if(user_can_moderate()){
					forum_delete_post();
					$out .=  forum_show_thread($_GET['thread']);
				}
				break;
			case "deletethread":
				if(user_can_moderate()){
					forum_delete_thread();
					$out .= forum_show_forum($_GET['forum']);
				}
				break;
			case "showprofile":
				$out .= forum_show_profile();
			 	break;
			case "search":
				$out .= forum_search();
				break;
			}
		}
	else
		$out .= forum_show_main();
	
	$out .= "<p class='author'>WordPress forum plugin by <a href='http://www.fahlstad.se'>Fredrik Fahlstad.</a> Version: ".get_version().".</p>";	
	$m .= "<div id='forum'>";
		$m .= "<div id='wp-forum'>$out</div>";
		//$m .= "<div id='forummeta'>".forum_meta($_GET['forum'], $_GET['thread'])."</div>";
	$m .= "</div>";

	return preg_replace("|<!--WPFORUM-->|", $m, $content);
}

function forum_show_main(){
	
	global 	$wpdb ,$main_link, $table_prefix, $forum_page_id, $user_level, $post, 
			$PLUGIN_PATH, $user_ID, $user_login, $user_email, 
			$reg_link, $login_link, $profile_link, $logout_link, $user_url, $search_link, $main_link, $skin_dir;
	get_currentuserinfo();
	
	$groups = forum_get_groups();

	$out .= forum_get_menu('none');
	
	$out .= "<table class='main_table'  border='0' cellspacing='0' cellpadding='0'>
				<tr>
					<td>
					<table  border='0' cellspacing='0' cellpadding='0' class='group_table'>
			<tr>
				<td class='table_header' colspan='2'>&nbsp;Forums&nbsp;</td>
				<td class='table_header'>&nbsp;Threads&nbsp;</td>
				<td class='table_header'>&nbsp;Posts&nbsp;</td>
				<td class='table_header' nowrap='nowrap'>&nbsp;Last post&nbsp;</td>
			</tr>";
	
	foreach((array)$groups as $g){
	
		$forums = forum_get_forums($g->id);
		$out .= "<tr><td colspan='5' class='group_header'>$g->name</td></tr>";
		foreach((array)$forums as $f){
			$link = "$main_link/?page_id=$forum_page_id&amp;forumaction=showforum&amp;forum=$f->id";
			
			$out .= "
		<tr>
			<td width='25'><img src='$PLUGIN_PATH/$skin_dir/images/folder_big.gif' /></td>
			<td width='100%'><b><a href='$link' class='forum_link'>$f->name</a></b><br />".forum_get_forum_desc($f->id) . "<br />
			".get_moderators($f->id)."</td>
			<td class='table_meta' align='center' valign='middle' height='50'><span>".forum_get_thread_count($f->id)."</span></td>
			<td class='table_meta' align='center' valign='middle' height='50'><span>".forum_get_posts_in_forum_count($f->id)."</span></td>
			<td class='table_meta' nowrap='nowrap' align='center' valign='middle' height='50'><span>".forum_get_lastpost_in_forum($f->id)."<br />By: ".forum_get_lastpost_poster_in_forum($f->id)."</span></td>
		</tr>\n";
		}
	}
	$out .= "</table></td></tr></table>";
	
	return $out;
}

function forum_show_forum($forum){
	
	global 	$wpdb, $main_link, $table_prefix, $forum_page_id, $user_level, 
			$PLUGIN_PATH, $user_ID, $user_login, $user_email, 
			$reg_link, $login_link, $profile_link, $logout_link, $user_url, $search_link, $profile, $skin_dir;	
	get_currentuserinfo();
	forum_update_forum_views($forum);

	$location = "$main_link/?page_id=$forum_page_id&amp;forumaction=thread&amp;forum=$forum";
	
	$threads = forum_get_threads($forum);
	$sticky = forum_get_sticky_threads($forum);	

	$out .= forum_get_menu('thread');		
			
		
	$out .= "<table class='main_table'  border='0' cellspacing='0' cellpadding='0'>
				<tr>
					<td><table cellspacing='0' cellpadding='0' border='0' class='forum_table'>";
	$out .= "<tr>
				<td class='table_header' colspan='2' >&nbsp;Subject&nbsp;</td>
				<td class='table_header'>&nbsp;Author&nbsp;</td>
				<td class='table_header'>&nbsp;Replies&nbsp;</td>
				<td class='table_header'>&nbsp;Views&nbsp;</td>
				<td class='table_header'>&nbsp;Last Post&nbsp;</td>
			</tr>";
			
	if(!isset($_GET['threadstart'])){
		$threadstart = 0;
	}
	else{
		$threadstart = $_GET['threadstart'];
	}
	if($_GET['forumaction'] == "thread"){
		$out .= "<tr><td colspan='6' class='post-submitted'><b><em>Thread succesfully submitted.</em></b></td></tr>";
	}
	
	// Sticky threads
	foreach((array)$sticky as $st){
		$starter = forum_get_thread_starter_name($st->id);
		$class = ($class == 'o')?'e':'o';
		$edit_link = 	"$main_link/?page_id=$forum_page_id&amp;forumaction=editsubject&amp;thread=$st->id&amp;forum=$forum&amp;threadstart=$threadstart";
		$link = 		"$main_link/?page_id=$forum_page_id&amp;forumaction=showposts&amp;forum=$st->forum_id&amp;thread=$st->id&amp;start=0";
		$out .= "<tr class='$class sticky'>
		<td  width='19'><img src='$PLUGIN_PATH/$skin_dir/images/folder_sticky.gif'  border='0' /></td>
		<td  width='100%'>";
		$pages = forum_get_post_pages_thread($st->id);
	
		$out .= "<div style='float:left'><b>Sticky: <a href='$link'> ".stripslashes($st->subject)."</a></b><br />$pages</div>";
		
		
			
		if(user_can_moderate()){
			$out .= "<div style='float:right'><a href='$edit_link'><span> (Edit)</span></a></div>";
		}
		$out .= "</td>";
		
		$s = forum_get_thread_starter($st->id);
		if($starter == 'guest')
			$start = 'Guest';
		else $start = "<a href='$profile$s'>$starter</a>";
			
		$out .= "<td nowrap='nowrap' class='table_meta' align='center' valign='middle'>";
		$out .= "<span>$start</span></td>";
		$out .= "<td align='center' valign='middle' class='table_meta'><span>" . (forum_get_posts_in_thread_count($st->id)-1) . "</span></td>";
		$out .= "<td align='center' valign='middle' class='table_meta'><span>" . forum_get_thread_views($st->id) . "</span></td>";
		$out .= "<td nowrap='nowrap' align='center' valign='middle' class='table_meta'><span>" . forum_get_lastpost($st->id) ."<br />By: ".forum_get_lastpost_poster($st->id)."</span></td>";
	}
	
	// Open threads
	foreach((array)$threads as $t){
		$status = (forum_get_thread_status($t->id) == 'solved')?'<strong>Solved: </strong>':"";
		$starter = forum_get_thread_starter_name($t->id);
		$class = ($class == 'o')?'e':'o';
		
		$edit_link = 	"$main_link/?page_id=$forum_page_id&amp;forumaction=editsubject&amp;thread=$t->id&amp;forum=$forum&amp;threadstart=$threadstart";
		$link = 		"$main_link/?page_id=$forum_page_id&amp;forumaction=showposts&amp;forum=$t->forum_id&amp;thread=$t->id&amp;start=0";
		
		$out .= "<tr class='$class'>";
		
		if($user_ID){
			
				$lastvisit = forum_get_lastvisit($user_ID, false);
				$lastpost = date("U", strtotime(forum_get_lastpost($t->id, false)));
				if($lastpost > $lastvisit)
					$out .= "<td width='19'><img src='$PLUGIN_PATH/$skin_dir/images/folder_new.gif' alt='Unread post' align='absmiddle' /></td>";
				else{
					$out .= "<td  width='19'><img src='$PLUGIN_PATH/$skin_dir/images/folder.gif'  border='0' /></td>";
				}
		}
		else
			$out .= "<td  width='19'><img src='$PLUGIN_PATH/$skin_dir/images/folder.gif'  border='0' /></td>";
		
			$pages = forum_get_post_pages_thread($t->id);
			
		$out .= "<td width='100%'>";
		$out .= "<div style='float:left'><b>$status <a href='$link'> ".stripslashes($t->subject)."</a></b><br />$pages</div>";
		
		if(user_can_moderate()){
			$out .= " <div style='float:right'><a href='$edit_link'><span> (Edit)</span></a></div>";
		}
		$s = forum_get_thread_starter($t->id);
		if($starter == 'guest')
			$start = 'Guest';
		else $start = "<a href='$profile$s'>$starter</a>";
		
		$lastposter = forum_get_lastpost_poster($t->id);
		$out .= "</td>";
		$out .= "<td nowrap='nowrap' class='table_meta' align='center' valign='middle'>";
		$out .= "<span>$start</span</td>";
		$out .= "<td align='center' valign='middle' class='table_meta'><span>" . (forum_get_posts_in_thread_count($t->id)-1) . "</span></td>";
		$out .= "<td align='center' valign='middle' class='table_meta'><span>" . forum_get_thread_views($t->id) . "</span></td>";
		$out .= "<td nowrap='nowrap'  align='center' valign='middle' class='table_meta'><span>" . forum_get_lastpost($t->id) ."<br />By: $lastposter</span></td>";
		
	}
	$out .= "</table></td></tr></table>";
	
	$out .= forum_get_thread_pages();
	if($user_ID || forum_get_reg() == false){ 
		$out .= "<h4><a name='newpost'></a>Start new thread</h4>";
		$out .= forum_usage();
		$out .= forum_get_post_form('thread', $location, $forum);
	}

	
	return $out;
}

function forum_show_thread($thread){
	
	
	
	global 	$wpdb ,$main_link, $table_prefix, $forum_page_id, $user_level, 
			$PLUGIN_PATH, $user_ID, $user_login, $user_email, $table_threads, $user_description,
			$reg_link, $login_link, $profile_link, $logout_link, $user_url, $profile, $search_link, $skin_dir;
			
	if(isset($_GET['do']) && $_GET['do'] == "markassolved"){
		forum_set_thread_status($thread, $_GET['status']);
	}	

	get_currentuserinfo();
	forum_update_thread_views($thread);
	$feed_link = "$PLUGIN_PATH/forum_feed.php?thread=$thread&amp;forum_page=$forum_page_id";
	
	$mark_as_solved = 	"$main_link/?page_id=$forum_page_id&amp;forumaction=showposts&amp;forum=$_GET[forum]&amp;thread=$thread&amp;start=$_GET[start]&amp;do=markassolved&amp;status=solved";
	$mark_as_unsolved = "$main_link/?page_id=$forum_page_id&amp;forumaction=showposts&amp;forum=$_GET[forum]&amp;thread=$thread&amp;start=$_GET[start]&amp;do=markassolved&amp;status=open";
	
	
	$location = "$main_link/?page_id=$forum_page_id&amp;forumaction=post&amp;forum=$_GET[forum]&amp;thread=$thread&amp;start=$_GET[start]";
	
	$posts = forum_get_posts($thread, $_GET['start']);

	
					
	$out .= forum_get_menu('post', $feed_link, $mark_as_solved, $mark_as_unsolved, $thread);			

	$out .= "<table class='forum-meta' cellpadding='0' cellspacing='0' border='0'>
				<tr>
					<td>
						<span>
						<b>Replies: </b>".forum_get_posts_in_thread_count($thread);
						$pages = forum_get_post_pages($thread , $_GET['start']);
					
						 if($pages)
							$out .= "<b> - Pages: </b>$pages";
					
						$out .= "<b> - Last reply: </b>" .forum_get_lastpost($thread) ."<b> - By: </b>". forum_get_lastpost_poster($thread); 
						$out .= "</span></td>";
						if(($user_ID || !forum_get_reg()) && 
							(get_option('forum_allow_post_in_solved') || forum_get_thread_status($thread) != 'solved')
							)
							$out .= "<td align='right'><a href='#newpost'><img src='$PLUGIN_PATH/$skin_dir/images/reply.gif' /> <span>Post reply</span></a> </td>";
						
						$out .=	"</tr>";
					
						if($_GET['forumaction'] == "post"){
							$out .= "<tr><td class='post-submitted'><b><em>Post succesfully submitted.</em></b>
							</td></tr>";
						}
	$out .= "</table>";
	$out .= "<table cellspacing='0' cellpadding='0' class='main_table' >
			<tr><td>";
	$out .= "<table cellspacing='0' cellpadding='0' class='posts_table' >
			<tr>
				<td class='table_header'>Author</td>
				<td class='table_header'>Message</td>
			</tr>";
	foreach((array)$posts as $p){
		$class = ($class == 'o')?'e':'o';
		
		$out .=	"
				<tr id='msg-$p->id'>
					<td rowspan='2' valign='top' class='post-gravatar $class' nowrap='nowrap'  width='100'>";
												
						if($p->author_id){
							$u = new WP_user($p->author_id);
							$level = $u->user_level;
							
							if($level > 8){
								$w = "<br />(Admin)";
							}
							elseif(user_can_moderate($p->author_id)){
								$w = "<br />(Moderator)";
							}
							else{
								$w = "<br />(Member)";
							}
							$out .= "<p><span><a href='$profile$p->author_id'><b>$u->nickname</b></a>$w</span></p>";
							$out .= forum_get_gravatar($u->user_email);
							
							$out .= "<p><span>Posts: ".forum_get_user_post_count($p->author_id). "<br />";
							$out .= "Registered:<br />" .date(get_option('forum_date_format'), strtotime($u->user_registered));
							$out .= "</span></p>" ;
						}
						else{
							$out .= "<span>(Guest)</span>";
						}
					$out .= "</td><td valign='top' class='$class'>";
					if($user_ID){
						if($p->date > forum_get_lastvisit($user_ID)){	
							$new = "<img src='$PLUGIN_PATH/$skin_dir/images/folder_new.gif' align='left'/>&nbsp;";
						}
					}
					$out .= "<span class='post_meta'>$new<strong>".stripslashes($p->subject)."</strong><br />Posted: " .date(get_option('forum_date_format'), strtotime($p->date));
					if( (user_can_moderate() ) || ($user_ID == $p->author_id)){
						$edit = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=edit&amp;forum=$_GET[forum]&amp;thread=$thread&amp;start=$_GET[start]&amp;forumpost=$p->id";
						$out .= "<a href='$edit'> (Edit)</a>";
					}
					$out .= "</span>";
					if(($user_ID || !forum_get_reg()) && 
						(get_option('forum_allow_post_in_solved') || forum_get_thread_status($thread) != 'solved')
						){		
						$out .= "<form class='quote' onSubmit='quote($p->id); return false;' method='post' action=''>
									<input type='submit' value='Quote' />
									<input type='hidden' value='".get_bloginfo('home')."' id='url' name='url' />
								</form>";
					}
					$out .= "</td></tr>
				<tr>
					<td class='post-text $class' valign='top' width='100%'>" . forum_output_filter(forum_get_post_text($p->id))."</td>
				</tr>";
				
				$out .= "<tr><td colspan='2' class='divider'></td></tr>";
				
		
	}
	$out .= "</table></td></tr></table>";
	
	$out .= "<table class='forum-meta'>
			<tr>
				<td align='center'><span>".forum_get_post_pages($thread , $_GET['start'])."</span></td>
			</tr>
			</table>";
			
	if(get_option('forum_allow_post_in_solved') != true && forum_get_thread_status($thread) == 'solved'){
		$out .= "<p><a name='newpost'></a>This thread is marked as solved and posting is closed.</p>";
	}	
	if(($user_ID || !forum_get_reg()) && 
		(get_option('forum_allow_post_in_solved') || forum_get_thread_status($thread) != 'solved')
		){ 
		$out .= "<h4><a name='newpost'></a>Post a reply</h4>";
		$out .= forum_usage();
		$out .= forum_get_post_form('post', $location, $thread);
	}
		
	return $out;
}

function forum_search(){
	global 	$wpdb ,$main_link, $table_prefix, $forum_page_id, $user_level, $profile, 
			$PLUGIN_PATH, $user_ID, $user_login, $user_email,$search_link, 
			$reg_link, $login_link, $profile_link, $logout_link, $user_url, $table_posts,$table_threads;
			
	$action	= get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=search";
		
	$out .= forum_get_menu('none');
	
		
	if(isset($_POST['forum_search_submit'])){
		
		$query 	= $_POST['forum_query'];
		$out .= "<h4>Search results for \"$query\".</h4>
		<p><a href='$search_link'>Search again</a></p>";
		if($_POST['forum_query_forum'] != -1){
			$forum = $_POST['forum_query_forum'];
			$cond = "AND thread_id IN (SELECT id FROM $table_threads WHERE forum_id = $forum)";
		}
		if($_POST['forum_user_query'] != ""){
			$author_name = $_POST['forum_user_query'];
			
			if(isset($_POST['exact'])){
				$aa = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE display_name = '$author_name'");
				$name = "= $aa";
			}
			else{
				$aa = $wpdb->get_results("SELECT ID FROM $wpdb->users WHERE display_name LIKE '%$author_name%'");
				foreach($aa as $a)
					$aut .= ",$a->ID";
				$name = "IN ('".substr($aut, 1)."')";
			}
			/*if(isset($_POST['exact'])){
				$name = " = '$author_name'";
			}*/
		/*	else
				$name = " LIKE '%$author_name%'";*/
			$cond .= " AND author_id $name";
		}
		
		
		$order_by = "ORDER BY ".$_POST['forum_sort_by']." ".$_POST['order'];
			
		$wpdb->hide_errors();
		
		//$out .= "SELECT * FROM $table_posts WHERE text LIKE '%$query%' $cond $order_by";
		$res = $wpdb->get_results("SELECT * FROM $table_posts WHERE text LIKE '%$query%' $cond $order_by");
		
		if(!$res){
			$out .= "<p>No posts met your search criteria.</p><p><a href='$search_link'>Search again</a></p>";
			return $out;
		}
		$out .= "<table class='main_table' ><tr><td>";
		$out .= "<table cellspacing='1' class='search_results_table' >
				<tr>
					<td colspan='3' class='table_header'>Search results</td>
				</tr>
				<tr>
					<td class='group_header'><strong>Subject</strong></td>
					<td class='group_header'><strong>Author</strong></td>
					<td class='group_header'<strong>Date</strong></td>
				</tr>";
		foreach($res as $r){
			$starter = forum_get_thread_starter($r->id);
			$class = ($class == 'o')?'e':'o';
			$forum = forum_get_forum_from_post($r->thread_id);
			$u = new WP_user($r->author_id);
			$thread_link = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showposts&amp;thread=$r->thread_id&amp;forum=$forum&amp;start=0";
			$out .= "<tr class='$class'>
					<td><a href='$thread_link'>".stripslashes($r->subject)."</a></td>
					<td><a href='$profile$r->author_id'>$u->nickname</a></td>
					<td>".date(get_option('forum_date_format'), strtotime($r->date))."</td>
				</tr>";			
		}
		$out .= "</table></td></tr></table>";
		
	}
	else{
		$forums = forum_get_forums_for_search();		
	
	if(forum_get_reg() == true && !$user_ID){
		return "<h4>You must be logged in to search this forum</h4>
				<p><a href='$login_link'>Log in</a> | <a href='$reg_link'>Register</a></p>";
	}
	else{
		$out .= "<form method='POST' action='$action'>";
		
		$out .= "<table class='main_table' width='50%'><tr><td>";
		
			$out .= "
					<table class='search_table' >
						<tr><td colspan='2' class='table_header'>Search</td></tr>	
						<tr>
							<td colspan='2' class='group_header'>Search Keywords</td>
						</tr>
						<tr>
							<td class='search-head'>Search by Keywords</td>
							<td class='search-head'>Filter by user (Optional)</td>
						</tr>
						<tr>
							<td>
								<input type='text' name='forum_query' />
								<p>Enter a keyword or phrase to search by.</p>
							</td>
							<td>
								<input type='text' name='forum_user_query' value='$_GET[user]'/>								
								<p>Exact name: <input type='checkbox' name='exact' value='true' /></p>
							</td>
						</tr>
						<tr>
							<td colspan='2' class='group_header'>Search Options</td>
						</tr>
						<tr>
							<td class='search-head'>Search Where</td>
							<td class='search-head'>Refine Search</td>
						</tr>
						<tr>
							<td>Forum: 
							<select name='forum_query_forum'>
							
								<option selected value='-1'>All available</option>";

									foreach($forums as $f){
										if($f->f_group != $old){
											$out .= "<option disabled> $f->f_group</option>";
											$out .= "<option value='$f->f_forum_id'>-- $f->f_forum</option>";

										}
										else{
											$out .= "<option value='$f->f_forum_id'>-- $f->f_forum</option>";
										}
										$old = $f->f_group;
									}

							       $out .= "</select>
							</td>
							<td>
								<select name='forum_sort_by'>
									<option value='date' selected>Post Time</option>
									<option value='subject'>Post Subject</option>
						        </select><br />
						
								<input name='order' type='radio' checked='checked' value='asc' />
						        Ascending <br />
						        <input name='order' type='radio' value='desc' />
						        Descending
						
							</td>
						</tr>
						<tr>
							<td colspan='2' align='center'><input type='submit' name='forum_search_submit' value='Start Search' /></td>
						</tr>
			";
			
		
		$out .= "</table></td></tr></table></form>";
	}
	}
	return $out;
}

function forum_insert_post(){

	global $wpdb ,$main_link, $table_prefix, $forum_page_id, $user_ID, $error;
	$table_posts = $table_prefix."forum_posts";
	if(forum_check_spam_words($_POST['forumtext']) || forum_check_spam_words($_POST['forumsubject'])){
		die ("<h2>Spam word warning</h2>
		<p>Your post contains words associated with spam and will not be posted.<p>
		<p>We are sorry for this inconvenience but this is a precaution we are forced to take to make this forum a pleasant stay. </p>");
	}
	if(isset($_POST['preview_post']))
		return forum_preview();
	else{
		if(!$_POST['forumtext'])
			die("MESSAGE CANNOT BE EMPTY");
		if(!$_POST['forumsubject'])
			die("SUBJECT CANNOT BE EMPTY");
		/*if(!$_POST['forumname'])
			die("NAME CANNOT BE EMPTY");*/
		
		$forumtext = forum_insert_filter($_POST['forumtext']);
	
		$forumsubject = $wpdb->escape(strip_tags($_POST['forumsubject']));
		//$forumname = $wpdb->escape(strip_tags($_POST['forumname']));
	
		$date = date("Y-m-d H:i:s", time());
		$insert = "INSERT INTO $table_posts 
					(text, thread_id, date, author_id, subject) 
					VALUES('$forumtext', '$_POST[thread]','$date','$user_ID', '$forumsubject')";
		$wpdb->query($insert);
		forum_email_subs($_POST['thread']);
		//header("Location: /");
	}
	
}

function forum_preview(){
	global $user_ID, $profile, $profile_link, $logout_link, $forum_page_id, $user_login, $user_url, $user_email, $user_level, $main_link;
	if(isset($_POST['preview_post']))
		$location = "$main_link/?page_id=$forum_page_id&amp;forumaction=post&amp;forum=$_GET[forum]&amp;thread=$_GET[thread]&amp;start=0";
	elseif(isset($_POST['preview']))
		$location = "$main_link/?page_id=$forum_page_id&amp;forumaction=thread&amp;forum=$_GET[forum]";
	
	$subject = $_POST['forumsubject'];
	$name = $_POST['forumname'];
	$msg = $_POST['forumtext'];
	$u = new WP_user($user_ID);
	
	if($user_ID){
		$p .= "<span><a href='$profile$user_ID'><b>$u->nickname</b></a><br />";
		$p .= "Posts: ".forum_get_user_post_count($user_ID). "<br />";
		$p .= "Registered: " .date(get_option('forum_date_format'), strtotime($u->user_registered));
		$p .= "</span>" ;
	}
	else{
		$p .= "(Guest)";
	}
	
	$out .= forum_usage();
	$out .= "<table class='main_table'  >
			<tr><td>";
	$out .= "<table class='posts_table' cellspacing='0' cellpadding='0' >	
				<tr><td class='table_header' colspan='2'>Preview</td></tr>
				<tr>
					<td rowspan='2' valign='top' width='100'>$p</td>
					<td><span><strong>$subject</strong><br />Posted: " .date(get_option('forum_date_format'), time())."</td>
				</tr>
				<tr>
					<td class='post-text' valign='top'><p>" . forum_output_filter($msg) . "</p></td>
				</tr>
			</table>";
	$out .= "</td></tr></table>";
	$out .= "<p>If you are satisfied click \"Say it\", otherwise edit your post and click \"Reload\" to see the changes.</p>
		<form action='$location' method='post'>";
		$out .= "<table class='main_table'  width='50%'>
				<tr><td>
		<table class='reply_table' cellpadding='0' cellspacing='0'  >
		<tr><td class='table_header' colspan='2'>Post reply</td></tr>";
		
		if($user_ID){
			$out .= "<tr>
			<td><b>Name:</b></td><td width='100%'>You are logged in as <strong><a href='$profile_link'>$u->nickname</a></strong>. <a href='$logout_link'>Logout </a></td>
			</tr>
			<input type='hidden' name='forumname' value='$u->nickname' />
			<input type='hidden' name='forumemail' value='$user_email' />
			<input type='hidden' name='forumweb' value='$user_url' />";
			if(user_can_moderate()){
				if(isset($_POST['sticky'])){			
					$out .= "<input type='hidden' name='sticky' value='sticky' />";
					$out .= "<tr><td></td>&nbsp;<td nowrap='nowrap'><b>This thread is marked as sticky.</b></td></tr>";
				}
			}
		}
		else{
			/*$out .= "<tr>
						<td><b>Name:</b></td><td><input type='text' name='forumname' id='forumname' value='$name' /></td>
					</tr>";*/;
		}
			$out .= "<tr>
						<td><b>Subject:</b></td><td><input type='text' name='forumsubject' id='forumsubject' value='$subject' /></td>
					</tr>
					<tr>
						<td valign='top'><b>Message:</b></td><td><textarea name='forumtext' id='forumtext' rows='8' cols='40'>".stripslashes($msg)."</textarea></td>
					</tr>
					<tr>
						<td colspan='2'>
							<input type='submit' id='replysubmit' value='Say it'>";
					if(isset($_POST['preview_post']))			
						$out .= "<input type='submit' name='preview_post' id ='preview_post' value='Reload' />";
					else
						$out .= "<input type='submit' name='preview' id='preview' value='Reload' />";
			
					$out .= "</td>
					</tr>	
					<input type='hidden' name='thread' value='$_GET[thread]' />
					<input type='hidden' name='forum' value='$_GET[forum]' />
			
					</form></table></td></tr></table>";
	
	return $out;
}

function forum_insert_thread(){
	global $wpdb ,$main_link, $table_prefix, $forum_page_id, $user_ID;
	$table_posts = $table_prefix."forum_posts";
	$table_threads = $table_prefix."forum_threads";
	if(forum_check_spam_words($_POST['forumtext']) || forum_check_spam_words($_POST['forumsubject'])){
		die ("<h2>Spam word warning</h2>
		<p>Your post contains words associated with spam and will not be posted.<p>
		<p>We are sorry for this inconvenience but this is a precaution we are forced to take to make this forum a pleasant stay. </p>");
	}
	if(isset($_POST['preview']))
		return forum_preview();
	else{
		if(!$_POST['forumsubject'])
			die("SUBJECT CANNOT BE EMPTY");
		if(!$_POST['forumtext'])
			die("MESSAGE CANNOT BE EMPTY");
		/*if(!$_POST['forumname'])
			die("NAME CANNOT BE EMPTY");*/
	
			
		$forumsubject = $wpdb->escape(strip_tags($_POST['forumsubject']));
		$forummessage = forum_insert_filter($_POST['forumtext']);
		//$forumname = $wpdb->escape(strip_tags($_POST['forumname']));
		if(isset($_POST['sticky']))
			$status = $_POST['sticky'];
		else
			$status = "open";
		$date = date("Y-m-d H:i:s", time());
	
		$insert_thread = "INSERT INTO $table_threads (subject, forum_id, date, status, starter) 
		VALUES(	'$forumsubject', '$_POST[forum]','$date', '$status', '$user_ID')";
	
		$wpdb->query($insert_thread);
		//$id = $wpdb->get_var("SELECT id FROM $table_threads WHERE subject = '$_POST[forumsubject]' AND date = '$date'");
		
		$id = mysql_insert_id();
		
		$insert_post = "INSERT INTO $table_posts ( text, thread_id, date, author_id, subject) 
		VALUES('$forummessage', '$id','$date', '$user_ID', '$forumsubject')";
	
		$wpdb->query($insert_post);
	}
	
}

function forum_delete_post(){
	global $wpdb ,$main_link, $table_prefix, $forum_page_id;
	$table_posts = $table_prefix."forum_posts";
	
	$delete = "DELETE FROM $table_posts WHERE id = $_GET[forumpost]";
	$wpdb->query($delete);
}

function forum_delete_thread(){
	global $wpdb ,$main_link, $table_prefix, $forum_page_id;
	$table_posts = $table_prefix."forum_posts";
	$table_threads = $table_prefix."forum_threads";
		
	$delete_posts = "DELETE FROM $table_posts WHERE thread_id = $_GET[thread]";
	$wpdb->query($delete_posts);
	
	$delete_thread = "DELETE FROM $table_threads WHERE id = $_GET[thread]";
	$wpdb->query($delete_thread);
}

function forum_edit_post(){
	
	global $wpdb ,$main_link, $table_prefix, $forum_page_id, $user_ID, $user_level;
	$table_posts = $table_prefix."forum_posts";
	$table_threads = $table_prefix."forum_threads";
	
	if(isset($_GET['forumaction']) && $_GET['forumaction'] == 'doeditpost'){
		if(!$_POST['forumtext'])
			die("MESSAGE CANNOT BE EMPTY");
		
		$forumstext = forum_insert_filter($_POST['forumtext']);
		$update = "UPDATE $table_posts SET text='$forumstext' WHERE id='$_GET[forumpost]'";
		$wpdb->query($update);
	}
	if(isset($_GET['forumaction']) && $_GET['forumaction'] == 'doeditsubject'){
		
		if(!$_POST['forumsubject'])
			die("SUBJECT CANNOT BE EMPTY");

		$forumsubject = $wpdb->escape(strip_tags($_POST['forumsubject']));
		if(isset($_POST['sticky']))
			$sticky = ", status = 'sticky'";
		else $sticky = ", status = 'open'";		
				
		$update = "UPDATE $table_threads SET subject='$forumsubject', forum_id = '$_POST[move_thread]' $sticky WHERE id='$_GET[thread]'";
		$wpdb->query($update);
	}
	if(isset($_GET['forumaction']) && $_GET['forumaction'] == 'editsubject'){
	
		$location = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=doeditsubject&amp;forum=$_GET[forum]&amp;thread=$_GET[thread]&amp;threadstart=$_GET[threadstart]";
		$delete_link = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=deletethread&amp;forum=$forum$_GET[forum]&amp;thread=$_GET[thread]&amp;threadstart=$_GET[threadstart]";
		
		$thread = forum_get_single_thread($_GET['thread']);
		$groups = forum_get_groups();
		$select = "<select name='move_thread'>";
		foreach((array)$groups as $g){

			$forums = forum_get_forums($g->id);
			foreach((array)$forums as $f){
				$s = ($thread->forum_id == $f->id)?"selected":"";
				$select .= "<option value='$f->id' $s>$g->name &raquo; $f->name</option>";
			}
		}
		$select .= "</select>";
		
		if($thread->status == 'sticky'){
			$c = "checked='checked'";
		}
		
		$out .= "<form method='POST' action='$location'>
				<table class='main_table' width='50%'>
					<tr><td>
				<table cellspacing='1' class='reply_table' >
					<tr><td class='table_header'>Edit subject</td></tr>
					<tr>
						<td><b>Move thread to:</b> <br />$select</td>
					</tr>
					<tr>
						<td><b>Subject:</b><br />
						<input type='text' name='forumsubject' value='".stripslashes($thread->subject)."' /></td>
					</tr>
					<tr>
						<td><b>Thread is sticky: </b><input type='checkbox' $c name='sticky' value='sticky' id='sticky' /></td>
					</tr>
					<tr><td><input type='submit' id='replysubmit' value='Edit thread'></td></tr>
				</table></td></tr></table>	
				</form>";
				
		$out .= "<a href='$delete_link'>Delete this thread</a>";
		return $out;
		
	}	
	
	if(isset($_GET['forumaction']) && $_GET['forumaction'] == 'edit'){	
		$location = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=doeditpost&amp;forum=$forum$_GET[forum]&amp;thread=$_GET[thread]&amp;start=$_GET[start]&amp;forumpost=$_GET[forumpost]";
		$delete_link = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=deletepost&amp;forum=$forum$_GET[forum]&amp;thread=$_GET[thread]&amp;start=$_GET[start]&amp;forumpost=$_GET[forumpost]";
		$post = forum_get_single_post($_GET['forumpost']);
	
		
	
		$out .= "<form method='POST' action='$location'>
		<table class='main_table' width='50%'>
		
			<tr><td>
			<table cellspacing='1' class='reply_table' >
			<tr><td class='table_header'>Edit message</td></tr>
			
				<tr>
					<td><b>Message:</b><br /><textarea name='forumtext' id='forumtext'>".stripslashes($post->text)."</textarea></td>
				</tr>
			
				<tr><td><input type='submit' id='replysubmit' value='Edit message'></td></tr>
			</table>	
			</form></td></tr></table>";
			
			$out .= "<a href='$delete_link'>Delete this message</a>";
			return $out;
	}
}

function forum_show_profile(){
	return forum_get_profile($_GET['user']);
}

function forum_head(){
	global $post, $pp, $skin_dir;
	if(preg_match('|<!--WPFORUM-->|', $post->post_content))	:
		forum_redirect();?>
		<script src="<?php bloginfo('wpurl');?>/wp-content/plugins/wp-forum/js/prototype.js" type="text/javascript" ></script>
		<script src="<?php bloginfo('wpurl');?>/wp-content/plugins/wp-forum/js/scriptaculous.js?load=effects" type="text/javascript" ></script>
		<script src="<?php bloginfo('wpurl');?>/wp-content/plugins/wp-forum/js/script.js" type="text/javascript" ></script>
		<?php $skin_dir .= "/". get_option('forum_skin');?>
		<link rel="stylesheet" href="<?php bloginfo('wpurl');?>/wp-content/plugins/wp-forum/<?php echo $skin_dir;?>/style.css" type="text/css"  />
<?php endif;
}

function forum_get_reg(){
	return get_option('forum_require_registration');
}

function wp_forum_install(){

	global $table_prefix, $wpdb ,$main_link, $user_level;
	$table_threads = $table_prefix."forum_threads";
	$table_posts = $table_prefix."forum_posts";
	$table_forums = $table_prefix."forum_forums";
	$table_groups = $table_prefix."forum_groups";	
	get_currentuserinfo();

	if ($user_level < 8) { return; }
	else{
		
		$sql1 = "

		CREATE TABLE IF NOT EXISTS $table_forums (
		  id int(11) NOT NULL auto_increment,
		  `name` varchar(255) NOT NULL default '',
		  parent_id int(11) NOT NULL default '0',
		  description varchar(255) NOT NULL default '',
		  views int(11) NOT NULL default '0',
		  PRIMARY KEY  (id)
		);";

		$sql2 = "
		CREATE TABLE IF NOT EXISTS $table_groups (
		  id int(11) NOT NULL auto_increment,
		  `name` varchar(255) NOT NULL default '',
		  PRIMARY KEY  (id)
		);";

		$sql3 = "
		CREATE TABLE IF NOT EXISTS $table_posts (
		  id int(11) NOT NULL auto_increment,
		  `text` longtext,
		  thread_id int(11) NOT NULL default '0',
		  `date` datetime NOT NULL default '0000-00-00 00:00:00',
		  author_id int(11) NOT NULL default '0',
		  `subject` varchar(255) NOT NULL default '',
		  views int(11) NOT NULL default '0',
		  PRIMARY KEY  (id)
		);";


		$sql4 = "
		CREATE TABLE IF NOT EXISTS $table_threads (
		  id int(11) NOT NULL auto_increment,
		  forum_id int(11) NOT NULL default '0',
		  views int(11) NOT NULL default '0',
		  `subject` varchar(255) NOT NULL default '',
		  `date` datetime NOT NULL default '0000-00-00 00:00:00',
		  `status` varchar(20) NOT NULL default 'open',
		  starter int(11) NOT NULL,
		  PRIMARY KEY  (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    	
		dbDelta($sql1);
		dbDelta($sql2);
		dbDelta($sql3);
		dbDelta($sql4);
		
		// 1.7.3
		maybe_add_column($table_groups, 'sort', "ALTER TABLE $table_groups ADD sort int( 11 ) NOT NULL");
		maybe_add_column($table_forums, 'sort', "ALTER TABLE $table_forums ADD sort int( 11 ) NOT NULL");
	
	}
	
}

function forum_page_titlechange($title){
	return str_replace("[[WPFORUM]]", get_option('forum_page_title'), $title);
}

if (isset($_GET['activate']) && $_GET['activate'] == 'true') {

	add_action('init', 'wp_forum_install');
}

function wp_forum_init() {
	if (function_exists('add_management_page')) 
	{
		//add_options_page('wp-forum options', 'wp-forum', 9, 'wp-forum.php');
		add_management_page('Manage wp-forum', 'WP-Forum', 9, 'wp-forum/wp-forum-manage.php');
	}
}

function forum_insert_filter($text){
	/*$forumtext =	apply_filters('comment_text', $text);
	$forumtext =	apply_filters('pre_comment_content', $forumtext);
	return $forumtext;*/
	return $text;
}


function forum_output_filter($text){
	
	if(get_option('forum_use_bbcode'))
		return stripslashes(apply_filters('comment_text', FF_BBCode($text)));
	else 
		return stripslashes(apply_filters('comment_text', $text));
}

function forum_usage(){
	if(get_option('forum_use_bbcode'))
		return "<p>Keep it polite and on topic.<br />You can use <a href='http://en.wikipedia.org/wiki/BBCode'>BBCode</a></p>";
	else
		return "<p>Keep it polite and on topic.<br />You can use the same formatting as for WordPress commenting.</p>";
}

function forum_latest_activity($limit = '10'){
	global $wpdb ,$main_link, $table_posts, $table_threads, $profile;
	$threads = $wpdb->get_results("SELECT * FROM $table_threads ORDER BY date DESC LIMIT $limit");
	
	$out .= "<ul id='forum_latest'>";
	
	foreach($threads as $t){
		$link =  forum_get_link_from_post($t->id);
				$out .= "<li><a href='$link'>$t->subject</a> By: ".get_usermeta(forum_get_thread_starter($t->id), 'nickname')."</li>";
	}
	$out .= " </ul>";

	echo $out;
}

function limit_string($string, $charlimit){
   if(substr($string,$charlimit-1,1) != ' ')
   {
       $string = substr($string,'0',$charlimit);
       $array = explode(' ',$string);
       $new_string = implode(' ',$array);

       return $new_string.'...';
   }
   else
   {    
       return substr($string,'0',$charlimit-1).'...';
   }
}

add_action('admin_menu', 'wp_forum_init');
add_filter('the_content', 'wp_forum');
add_filter('wp_head', 'forum_head');
add_action('init', 'forum_set_cookie');

// Obsolete
add_filter("wp_list_pages", "forum_page_titlechange");
add_filter("the_title", "forum_page_titlechange");
add_filter("single_post_title", "forum_page_titlechange");

?>