<?php

global $wpdb, $table_prefix, $wp_query;
$table_threads 	= $table_prefix."forum_threads";
$table_posts 		= $table_prefix."forum_posts";
$table_forums 	= $table_prefix."forum_forums";
$table_groups 	= $table_prefix."forum_groups";
$forum_page_id 	= forum_get_page_id();

function forum_get_thread_count($forum){
	global $wpdb, $table_threads;
	return $wpdb->get_var("select count(*) from $table_threads where forum_id = $forum");
}

function forum_get_forums($group){
	global $wpdb, $table_forums;
	return $wpdb->get_results("SELECT * FROM $table_forums WHERE parent_id = $group ORDER BY sort DESC");
}

function forum_get_sticky_threads($forum){
	global $wpdb, $table_threads;
	return $wpdb->get_results("SELECT * FROM $table_threads WHERE forum_id = $forum AND status = 'sticky' 
		ORDER BY date DESC");
}

function forum_get_all_forums(){
	global $wpdb, $table_forums;
	return $wpdb->get_results("SELECT * FROM $table_forums ORDER BY sort DESC");
}

function forum_get_threads($forum){
	global $wpdb, $table_threads;
	$tpp = get_option('forum_threads_per_page');
	if(!isset($_GET['threadstart'])){
		$start = 0;
	}
	else{
		$start = $_GET['threadstart'];
	}
	$end = $start+$ppp;
	
	return $wpdb->get_results("SELECT * FROM $table_threads WHERE forum_id = $forum AND status <> 'sticky' ORDER BY date DESC LIMIT $start, $tpp");
}

function forum_get_thread_subject($thread){
	global $wpdb, $table_threads;
	return $wpdb->get_var("SELECT subject FROM $table_threads WHERE id = $thread");
}

function forum_get_forum_from_post($thread){
	global $wpdb, $table_threads;
	return $wpdb->get_var("SELECT forum_id FROM $table_threads WHERE id = $thread");
}

function forum_set_thread_status($thread, $status){
	global $wpdb, $table_threads;
	$wpdb->query("UPDATE $table_threads SET status = '$status' WHERE id = $thread");
}

function forum_get_thread_status($thread){
	global $wpdb, $table_threads;
	return $wpdb->get_var("SELECT status FROM $table_threads WHERE id = $thread");
}

function forum_get_forumname_from_post($thread){
	global $wpdb, $table_threads, $table_forums;
	$f =  $wpdb->get_var("SELECT forum_id FROM $table_threads WHERE id = $thread");
	return  $wpdb->get_var("SELECT name FROM $table_forums WHERE id = $f");
	
}

function forum_get_link_from_post($thread, $post = '', $start = 0){
	global $wpdb, $table_threads, $table_forums, $forum_page_id;
	$forum =  $wpdb->get_var("SELECT forum_id FROM $table_threads WHERE id = $thread");
	return  get_bloginfo('wpurl')."/?page_id=$forum_page_id&forumaction=showposts&forum=$forum&thread=$thread&start=$start";
}

function forum_get_posts($thread, $start){
	global $wpdb, $table_posts;
	$ppp = get_option('forum_posts_per_page');
	$end = $start+$ppp;
	//echo "SELECT * FROM $table_posts WHERE thread_id = $thread ORDER BY date ASC limit $start, $ppp";
	return $wpdb->get_results("SELECT * FROM $table_posts WHERE thread_id = $thread ORDER BY date ASC limit $start, $ppp");
}

function forum_get_single_post($post){
	global $wpdb, $table_posts;
	return $wpdb->get_row("SELECT * FROM $table_posts WHERE id = $post");
}

function forum_get_single_thread($thread){
	global $wpdb, $table_threads;
	return $wpdb->get_row("SELECT subject, status, id, forum_id FROM $table_threads WHERE id = $thread");
}

function forum_get_post_text($post){
	global $wpdb, $table_posts;
	
	return $wpdb->get_var("SELECT text FROM $table_posts WHERE id = $post");
}

function forum_get_groups(){
	global $wpdb, $table_groups;
	return $wpdb->get_results("select * from $table_groups ORDER BY sort DESC");
}

function forum_get_posts_in_forum_count($forum){
	global $wpdb, $table_threads, $table_posts;
	
	
	$threads = $wpdb->get_results("select id from $table_threads where forum_id = $forum");
	if(!$threads) return 0;
	foreach($threads as $thread)
		$c +=  $wpdb->get_var("select count(*) from $table_posts where thread_id = $thread->id");
	return $c;
}

function forum_get_posts_in_thread_count($thread){
	global $wpdb, $table_posts;
	return $wpdb->get_var("select count(*) from $table_posts where thread_id = $thread");
	
}

function forum_get_lastpost($thread, $f = true){
	global  $wpdb, $table_posts;
	$date = $wpdb->get_var("select date from $table_posts where thread_id = $thread order by date desc limit 1");
	if($f)
		return date(get_option('forum_date_format'), strtotime($date));
	else
		return $date;
}

function forum_get_lastpost_id($thread){
	global  $wpdb, $table_posts;
	$id = $wpdb->get_var("select id from $table_posts where thread_id = $thread order by date desc limit 1");
	return $id;
}

function forum_get_lastpost_poster($thread){
	global  $wpdb, $table_posts, $profile;
	$u = $wpdb->get_row("select author_id from $table_posts where thread_id = $thread order by date desc limit 1");
	if(!$u->author_id)
		return "Guest";
	$user = new WP_user($u->author_id);
	$r = "<a href='$profile$u->author_id'>$user->nickname</a>";
	return $r;
	
}
function forum_get_lastpost_in_forum($forum_id){
	global  $wpdb, $table_posts, $profile, $table_threads;

	$date = $wpdb->get_var("SELECT $table_posts.date FROM $table_posts INNER JOIN $table_threads ON $table_posts.thread_id=$table_threads.id WHERE $table_threads.forum_id = $forum_id ORDER BY $table_posts.date DESC");
	return date(get_option('forum_date_format'), strtotime($date));
}

function forum_get_lastpost_poster_in_forum($forum_id){
	global  $wpdb, $table_posts, $profile, $table_threads;

	$u = $wpdb->get_row("SELECT author_id FROM $table_posts INNER JOIN $table_threads ON $table_posts.thread_id=$table_threads.id WHERE $table_threads.forum_id = $forum_id ORDER BY $table_posts.date DESC");
	if(!$u->author_id)
		return $u->author_id;
	$user = new WP_user($u->author_id);
	$r = "<a href='$profile$u->author_id'>$user->nickname</a>";
	return $r;
}
function forum_get_lastpost_poster_id($thread){
	global  $wpdb, $table_posts;
	$u = $wpdb->get_var("select author_id from $table_posts where thread_id = $thread order by date desc limit 1");
	return $u;
}

function forum_get_gravatar($email){
	global $PLUGIN_PATH;
	if(get_option('forum_use_gravatar') == 'true'){
			$default = urlencode($PLUGIN_PATH."/$skin_dir/images/gravatar_default.png");
			$md5sum = md5($email);
			return "<p><img src='http://www.gravatar.com/avatar.php?gravatar_id=$md5sum&amp;size=42&amp;default=$default' class='forum-gravatar'/></p>";		
	}
}

function forum_get_group_name($group){
	global $wpdb, $table_groups;
	return $wpdb->get_var("SELECT name FROM $table_groups WHERE id = $group");
}

function forum_get_group_from_post($tread_id){
	
	return forum_get_group_name(forum_get_parent(forum_get_forum_from_post($tread_id)));
}

function forum_get_parent($forum){
	global $wpdb, $table_forums;
	return $wpdb->get_var("SELECT parent_id FROM $table_forums WHERE id = $forum");
}

function forum_get_forum_name($forum){
	global $wpdb, $table_forums;
	return $wpdb->get_var("SELECT name FROM $table_forums WHERE id = $forum");
}

function forum_get_forum_desc($forum){
	global $wpdb, $table_forums;
	return $wpdb->get_var("SELECT description FROM $table_forums WHERE id = $forum");
}

function forum_get_trail($forum="", $thread=""){
	global $forum_page_id, $PLUGIN_PATH, $skin_dir;
	
	$blog_url = get_bloginfo('home');
	$blog_name = get_bloginfo('name');
	$page = get_option('forum_page_title');
	
	$main_link_ = get_bloginfo('wpurl')."/?page_id=$forum_page_id";
	$blog = "<a href='$blog_url'>$blog_name</a>";
	$page = "<a href='$main_link_'>$page</a>";
	
	$trail = "
				<table>
					<tr>
						<td><img src='$PLUGIN_PATH/$skin_dir/images/navbits_start.gif' />
						$blog &raquo; $page";
	if($forum){
		$forum_link = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forum=$forum&amp;forumaction=showforum";
		$group = forum_get_group_name(forum_get_parent($forum));
		$forum = forum_get_forum_name($forum);
		
		if(!$thread){
			$trail .= "</td></tr><tr>
						<td><img src='$PLUGIN_PATH/$skin_dir/images/navbits_finallink.gif' /> <b>$group, $forum</b>";
		}
		else{
			$trail .= " &raquo; <a href='$forum_link'>$group, $forum</a>";
		}
	}
	if($thread){
		$post = forum_get_thread_subject($thread);
		$trail .= "</td></tr><tr>
					<td>
						<img src='$PLUGIN_PATH/$skin_dir/images/navbits_finallink.gif' /> <b>$post</b>";	
	}

		$trail .= "</td></tr>";

	$trail .= "</table>";
	
	//return stripslashes("<p>$q $a $b $c</p>");
	return stripslashes($trail);

}

function forum_feed_trail($forum="", $thread=""){
	global $forum_page_id;
	$blog_name = get_bloginfo('name');
	$page = get_option('forum_page_title');
	if($forum){
		$forum_link = get_bloginfo('wpurl')."/?page_id=$forum_page_id&forum=$forum&forumaction=showforum";
		$group = forum_get_group_name(forum_get_parent($forum));
		$forum = forum_get_forum_name($forum);
		$b = "$group, $forum";
		
	}
	if($thread){
		$post = forum_get_thread_subject($thread);
		$c = $post;
	}
		
	$q = $blog_name;
	$a = $page;
	
	return "$a >> $b >> $c";
}

function forum_get_post_pages($thread, $start = 0){
	global $forum_page_id;
	$ppp = get_option('forum_posts_per_page');
	$currentpage = ceil($start/$ppp)+1;
	$post_count = forum_get_posts_in_thread_count($thread);
	if($post_count < $ppp) 
		return "[1]";
	$pages = ceil($post_count/$ppp);
	$s = 0;
	for($i = 0; $i < $pages; ++$i){
		if($currentpage != ($i+1)){
			$location = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showposts&amp;forum=$_GET[forum]&amp;thread=$thread&amp;start=$s";
			$out .= "<a href='$location'>".($i+1)." </a>";
		}
		else{
			$out .= "[".($i+1)."] ";
		}
		$s = $s+$ppp;
	}
	
	return "$prev_link $out $next_link";
}

function forum_get_post_pages_thread($thread, $start = 0){
	global $forum_page_id;
	$ppp = get_option('forum_posts_per_page');
	$currentpage = ceil($start/$ppp)+1;
	$post_count = forum_get_posts_in_thread_count($thread);
	if($post_count < $ppp) 
		return "";
	$pages = ceil($post_count/$ppp);
	$s = 0;
	for($i = 0; $i < $pages; ++$i){
			$location = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showposts&amp;forum=$_GET[forum]&amp;thread=$thread&amp;start=$s";
			$out .= "<a href='$location'>".($i+1)."</a>, ";
	
		$s = $s+$ppp;
	}
	$o = substr_replace($out, "", strrpos ( $out, ","));
	return "<span>[Goto page: $o]</span>";
}

function forum_get_last_posts_page($thread){
	$ppp = get_option('forum_posts_per_page');
	$post_count = forum_get_posts_in_thread_count($thread);
	$pages = ceil($post_count/$ppp);
	
	return ceil($post_count/$ppp);//(($pages*$ppp)-$ppp);
}

function forum_get_thread_pages(){
	global $forum_page_id;
	$tpp = get_option('forum_threads_per_page');
	$threads_count = forum_get_thread_count($_GET['forum']);
	$pages = ceil($threads_count/$tpp);
	
	if($threads_count < $tpp)
		return;
		
	if(!isset($_GET['threadstart'])){
		$start = 0;
	}
	else{
		$start = $_GET['threadstart'];
	}
	$s = 0;
	$currentpage = ceil($start/$tpp)+1;
	
	for($i = 0; $i < $pages; ++$i){
		if($currentpage != ($i+1)){
			$location = get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showforum&amp;forum=$_GET[forum]&amp;threadstart=$s";
			$out .= "<a href='$location'>".($i+1)." </a>";
		}
		else{
			$out .= "[".($i+1)."] ";
		}
		$s = $s+$tpp;
	}
	if($currentpage < $pages){
		$next = $start+$tpp;
		$next_link = "<a href='".get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showforum&amp;forum=$_GET[forum]&amp;threadstart=$next'>Next &raquo;</a>";
	}
	if($currentpage != 1){
		$prev = $start-$tpp;
		$prev_link = "<a href='".get_bloginfo('wpurl')."/?page_id=$forum_page_id&amp;forumaction=showforum&amp;forum=$_GET[forum]&amp;threadstart=$prev'>&laquo; Prev</a>";
	}
	$m = "<table class='forum-meta'><tr><td align='center'><span>$prev_link $out $next_link</span></td></tr></table>";
	return $m;//"<center><p>$prev_link $out $next_link</p></center>";
}

function forum_get_profile($user){
	global $user_ID, $table_threads, $wpdb, $rss_link, $profile_link;
	$profile = new WP_User($user);

	$out .= forum_get_menu('none');
	
	$blogurl = get_bloginfo('wpurl');
	$level = $profile->user_level;

	if($level > 8){
		$w = "Administrator";
	}
	elseif(get_user_mod_forum($user)){
		$w = "Moderator";
	}
	else{
		$w = "Member";
	}	
	if($user_ID == $user){
		$f = substr_replace($profile->feeds, '', 0, 1);
		$ef = substr_replace($profile->email_feeds, '', 0, 1);
		$sub .= "<tr>
					<td valign='top' class='table_meta'>My subscriptions: </td>
					<td>";
		if($f){
			$feeds = $wpdb->get_results("SELECT  id, subject FROM $table_threads WHERE id IN ($f) ORDER BY subject DESC");
			
					$sub .=	"<p><a href='$rss_link$user_ID'>Link to your RSS subscriptions feed</a></p>
						<p><b>RSS Feeds</b></p>
						<ul>";
							$i = 0;
							foreach($feeds as $feed){
								$feedid = "feed".++$i;
								$sub .= "<li id='$feedid'><a href='".forum_get_link_from_post($feed->id)."'>$feed->subject</a> <a href='javascript:onclick=del_sub($i,$feed->id,$user_ID)'>(Unsubscribe)</a></li>";
							}
					$sub .=	"</ul>";
					
				
		}
		
		if($ef){
			$emailfeeds = $wpdb->get_results("SELECT  id, subject FROM $table_threads WHERE id IN ($ef) ORDER BY subject DESC");
			
			$sub .= "<p><b>Email Feeds</b></p>";
			
			$i = 0;
			$sub .= "<ul>";
			foreach($emailfeeds as $emailfeed){
				$email_feedid = "email_feed".++$i;
				$sub .= "<li id='$email_feedid'><a href='".forum_get_link_from_post($emailfeed->id)."'>$emailfeed->subject</a> <a href='javascript:onclick=del_emailsub($i,$emailfeed->id,$user_ID)'>(Unsubscribe)</a></li>";
			}
			$sub .= "</ul>";
		}
		

		else if(!$f && !$ef){
			$sub .=	"<p>You have no subscriptions.<p>";
			}
			$sub .= "</td></tr>";
	}
	if($user_ID == $user){
		$manage_profile = "<tr><td colspan='2'><a href='$profile_link'><b>Edit profile</b></a></td></tr>";
	}

	$out .= "<table  class='main_table'><tr><td>";
	$out .= "<table  cellspacing='0' class='user_table' >
			<tr><td class='table_header' colspan='2'>User profile for $profile->nickname</td></tr>
	<tr>
		<td colspan='2' align='left'><a href='javascript:onclick=sendemail($profile->id);' id='snd'>(Send email)</a>
		<input type='hidden' id='url' value='".get_bloginfo('wpurl')."' />
		<div id='sendemail'></div></td>
	</tr>$manage_profile
		<tr>
			<td class='table_meta'>Name: </td><td  width='100%'>$profile->first_name $profile->last_name</td>
		</tr>
		<tr>
			<td class='table_meta'>Alias: </td><td>$profile->nickname</td>
		</tr>
		<tr>
			<td class='table_meta'>Forum Status: </td><td>$w</td>
		</tr>
		<tr>
			<td class='table_meta'>Posts:</td><td>".forum_get_user_post_count($user)."</td>
		</tr>
		<tr>
			<td class='table_meta'>Web site:</td><td ><a href='$profile->user_url' rel='nofollow'>$profile->user_url</a></td>
		</tr>
		$sub
		<tr>
			<td valign='top' class='table_meta'>Gravatar: </td><td>".forum_get_gravatar($profile->user_email)."</td>
		</tr>
		<tr>
			<td class='table_meta'>AIM:</td><td>$profile->aim</td>
		</tr>
		<tr>
			<td nowrap  class='table_meta'>Jabber / Google Talk:</td><td>$profile->jabber</td>
		</tr>
		<tr>
			<td  class='table_meta'>Yahoo IM:</td><td>$profile->yim</td>
		</tr>
		<tr>
			<td class='table_meta'>Description:</td><td>$profile->description</td>
		</tr>
		
		<tr>
			<td valign='top'  class='table_meta'>Recent posts: </td><td>".forum_get_posts_by_user($user, 10)."</td>
		</tr>
		
	</table></td></tr></table>";
	return $out;
	
	
	
}

function forum_get_user_desc($user){
	return get_usermeta($user, 'description');
}

function forum_get_posts_by_user($user_id, $limit){
	global $wpdb, $table_posts, $forum_page_id, $user_ID, $PLUGIN_PATH, $search_link, $skin_dir;
	$posts = $wpdb->get_results("SELECT * FROM $table_posts WHERE author_id = $user_id ORDER BY date DESC LIMIT $limit");
	$o = "<table class='recent_table' >\n";
	if($user_ID){
		$last = forum_get_lastvisit($user_ID, false);
		$username = get_usermeta($user_ID, 'nickname');
	}
	foreach((array)$posts as $post){
		$link =  forum_get_link_from_post($post->thread_id, $post->id);
		$post_date = date("U", strtotime($post->date));
		$img = (($post_date > $last))?"folder_new.gif":"folder.gif";
		
		
		$o .= "<tr>
				<td class='table_meta'>
					<img src='$PLUGIN_PATH/$skin_dir/images/$img' />
				<td class='table_meta'  width='100%'>
					<a href='$link'>$post->subject</a> <span>
					Posted at: ".date(get_option('forum_date_format'), strtotime($post->date))."</span>
				</td>
				<tr>
					<td></td>
					<td>$post->text</td>
				</tr>\n";
	}	
	$o .= "</table>\n
	<p><img src='$PLUGIN_PATH/$skin_dir/images/icons/search.png' /><a href='$search_link&amp;user=$username'> Search for all user posts</a></p>";
	return forum_output_filter($o);
}

function forum_get_user_post_count($user){
	global $wpdb, $table_posts;
	return $wpdb->get_var("SELECT count(*) FROM $table_posts WHERE author_id = $user");
}

function forum_get_thread_starter($thread){
	global $wpdb, $table_threads;	
	return $wpdb->get_var("SELECT starter FROM $table_threads WHERE id = $thread");
}

function forum_get_thread_starter_name($thread){
	global $wpdb, $table_threads;	
	$usr = $wpdb->get_var("SELECT starter FROM $table_threads WHERE id = $thread");
	if(!$usr)return "guest";
	$starter = new WP_User($usr);
	return $starter->nickname;
}

function forum_update_forum_views($forum){
	global $wpdb, $table_forums;
	$wpdb->query("UPDATE $table_forums SET views = views+1 WHERE id = $forum");
}

function forum_update_thread_views($thread){
	global $wpdb, $table_threads;
	$wpdb->query("UPDATE $table_threads SET views = views+1 WHERE id = $thread");
}

function forum_get_forum_views($forum){
	global $wpdb, $table_forums;
	return $wpdb->get_var("SELECT views FROM $table_forums  WHERE id = $forum");
}

function forum_get_thread_views($thread){
	global $wpdb, $table_threads;
	return $wpdb->get_var("SELECT views FROM $table_threads  WHERE id = $thread");
}

function forum_get_forums_for_search(){
		global $wpdb, $table_forums, $table_groups;
		return $wpdb->get_results("SELECT $table_groups.name as f_group, $table_forums.name as f_forum, $table_forums.id as f_forum_id FROM  $table_groups LEFT JOIN $table_forums ON  $table_groups.id = $table_forums.parent_id");
}

function forum_members(){
	
	global $wpdb, $tableusers;
	return $wpdb->get_var("SELECT count(id) FROM $tableusers");
}

function forum_get_page_id(){
	global $wpdb, $tableposts;
	return $wpdb->get_var("SELECT ID FROM $tableposts WHERE post_content LIKE '%<!--WPFORUM-->%'");
}

function forum_redirect(){
	global $page_id, $reg_link, $login_link, $logout_link ;
	if(!$_SERVER['QUERY_STRING'])
		$redirect = get_permalink($page_id);
	else
		$redirect = urlencode(get_bloginfo('wpurl')."/?".$_SERVER['QUERY_STRING']);/*get_permalink($page_id)*/

	$reg_link 				.= $redirect;//get_bloginfo('wpurl')."/wp-register.php?redirect_to=";
	$login_link 			.= $redirect;//get_bloginfo('wpurl')."/wp-login.php?redirect_to=";
	$logout_link 			.= $redirect;//get_bloginfo('wpurl')."/wp-login.php?action=logout&amp;redirect_to=";
}

function get_moderators($forum_id){
	global $wpdb, $profile;
	$moderators = $wpdb->get_results("SELECT user_id, user_login, meta_value FROM $wpdb->usermeta 
	INNER JOIN $wpdb->users ON $wpdb->usermeta.user_id=$wpdb->users.ID WHERE meta_key = 'moderator' AND meta_value LIKE '%$forum_id%'");
	if($moderators){
		$a .= "<span>Moderators: ";
		foreach((array)$moderators as $m){
			$u = new WP_user($m->user_id);
			$b .= "<a href='$profile$m->user_id'>$u->nickname</a>, ";
		}
		$c .= "</span>";
	}
	$b = substr_replace($b,"", -2);
	return $a.$b.$c;
}

function get_user_mod_forum($user){
	return get_usermeta($user, 'moderator');
}

function forum_get_menu($what, $feed_link = '', $mark_as_solved = '', $mark_as_unsolved = '', $thread = ''){
	global 	$user_ID, $login_link, $logout_link, 
			$profile_link, $reg_link, $search_link, 
			$PLUGIN_PATH, $search_link, $user_level,
			$forum_page_id, $main_link, $skin_dir, $profile;
	
	
	$u = get_userdata($user_ID);
	$user_name = ($user_ID == 0)?"Guest":$u->nickname;
	
	
	$memb = forum_members()." Registered users.";
	if($what == 'thread' || $what == 'post')
		$mods = get_moderators($_GET['forum']);
	$r = "$memb<br />$mods";
	if($user_ID && $what == 'post'){
		$feed_link = " $main_link/?page_id=$forum_page_id
		&amp;forumaction=dosubscribe&amp;thread=$_GET[thread]&amp;forum=$_GET[forum]&amp;start=$_GET[start]";
		$email_link = " $main_link/?page_id=$forum_page_id
		&amp;forumaction=emailsubscribe&amp;thread=$_GET[thread]&amp;forum=$_GET[forum]&amp;start=$_GET[start]";
	}
	$login 		= "<a href='$login_link' class='menu_link'>Login</a>";
	$logout 	= "<a href='$logout_link' class='menu_link'>Logout</a>";
	$reg 		= "<a href='$reg_link' class='menu_link'>Register</a>";
	if($user_ID) $sub  = " | <a href='$feed_link' class='menu_link'>RSS Feed</a>";
	$prof 		= "<a href='$profile$user_ID' class='menu_link'>My Profile</a>";
	$search 	= "<a href='$search_link' class='menu_link'>Search</a>";
	if($user_ID) $email_sub 	= " | <a href='$email_link' class='menu_link'>Email Feed</a>";
	
	if($what == 'thread')
		$newpost = " | <a href='#newpost'>New thread</a>";
		
	if($what == 'post'){
		$starter = forum_get_thread_starter($thread);
		$newpost = " | <a href='#newpost' class='menu_link'>Post reply</a>  $sub  $email_sub";
	
		if(forum_get_thread_status($thread) != 'sticky'){
			if(($user_ID == $starter || user_can_moderate() ) && forum_get_thread_status($thread) != 'solved'){
				$solve = " | <a href='$mark_as_solved' class='menu_link'>Mark as solved</a>";
			}
			if(($user_ID == $starter || user_can_moderate() ) && forum_get_thread_status($thread) == 'solved'){
				$solve = " | <a href='$mark_as_unsolved' class='menu_link'>Mark as un-solved</a>";
			}
		
		}
	}
	if($what != 'none'){
		$more = "<span><img src='$PLUGIN_PATH/$skin_dir/images/folder.gif' align='absmiddle' /> No new posts.<br /> 
			<img src='$PLUGIN_PATH/$skin_dir/images/folder_new.gif' align='absmiddle' /> New posts since last visit.";
			if($thread)
				$more .= (forum_get_thread_status($thread) == 'solved')?"<br />Thread is solved":'';
		$more .= "</span>";
				
	}
	$t1 .= "<table cellpadding='0' cellspacing='0'><tr>";
	if(forum_get_reg() == true){
		if($user_ID){
			$visit = "<span><br />You last visited: ".date(get_option('forum_date_format'), get_usermeta($user_ID, 'lastvisit'))."</span>";
			$wel .= "Welcome, $user_name. $visit";
			$menu .= "$m $logout | $prof | $search $newpost $solve";
		}
		else{
			$wel .= "You are not logged in.
			<br />Posting in this forum require registration.";
			$menu .= "$m $login | $reg | $search";
		}
	}
	else{
		if($user_ID){
			$visit = "<span><br />You last visited: ".date(get_option('forum_date_format'), get_usermeta($user_ID, 'lastvisit'))."</span>";
			$wel .= "Welcome, $user_name. $visit";
			$menu .= "$m $logout | $prof | $search $newpost $solve";		
		}
		else{
			$wel .= "You are not logged in.</br /> Posting as Guest.";
			$menu .= "$m $login | $reg | $search $newpost";
		}
	}
	$t2 .= "</tr></table>";

	
	//return $t1.$menu.$wel.$t2;
	
	return  "<table cellpadding='0' cellspacing='0' class='forum_menu'>
				<tr>
					<td width='100%'>".forum_get_trail($_GET['forum'], $_GET['thread'])."</td>
					<td nowrap='nowrap' class='user_meta'>$wel</td>
				</tr>
				
			</table>
			<table cellpadding='0' cellspacing='0'>
				<tr>
					<td width='100%'>$r</td>
					<td nowrap='nowrap'>$more</td>
					
				</tr>
				<tr>
					<td valign='bottom' width='100%' colspan='2' class='menutd'>$menu</td>
				</tr>
			</table>";
}



function forum_get_post_form($what, $location, $thread){
	global 	$user_ID, $login_link, $logout_link, $user_email,
			$profile_link, $reg_link, $search_link, $PLUGIN_PATH, $user_login, $user_url, $user_level;
	
	$u = new WP_user($user_ID);	
	
	$out .= "\n<form method='post' action='$location' id='postreply'>";
	$out .= "<table class='main_table' width='50%'><tr><td>";
	
	$out .= "<table cellspacing='1' id='forum-reply' class='reply_table' >\n
				<tr>
					<td class='table_header' colspan='2'>Post reply</td>
				</tr>
				<tr>";
		if($user_ID)
			$out .= "<td class='table_meta'><b>Name:</b></td><td>You are logged in as <strong><a href='$profile_link'>$u->nickname</a></strong>. <a href='$logout_link'>Logout </a></td>\n";
		else 
			$out .= "<td class='table_meta'><b>Name:</b></td><td>You are posting as guest. <a href='$login_link'>Log in</a></td>\n";
			
		$out .= "</tr>\n";
		
		if(user_can_moderate() && $what == 'thread'){
			$out .=  "<tr>
				<td class='table_meta' nowrap='nowrap'><b>Thread is sticky:</b></td><td><input type='checkbox' name='sticky' value='sticky' id='sticky' /></td>\n
			</tr>\n";
		}
		$out .= "<tr>\n
			<td class='table_meta'><b>Subject:</b></td>\n";
			if($what != 'thread')
				$out .= "<td><input type='text' name='forumsubject' value='".forum_get_thread_subject($thread)."' /></td>\n";
			else
				$out .= "<td><input type='text' name='forumsubject' value='' /></td>\n";
				
		$out .= "</tr>\n
		<tr>\n
			<td valign='top' class='table_meta' ><b>Message:</b></td><td width='100%'><textarea name='forumtext' id='forumtext'></textarea></td>\n
		</tr>\n
		<tr>\n
			<td colspan='2'>\n
				<input type='submit' id='replysubmit' value='Say it' />\n";
				if($what == 'thread')
					$out .= "<input type='submit' name ='preview' id='preview_post' value='Preview' />\n";
				else
					$out .= "<input type='submit' name ='preview_post' id='preview_post' value='Preview' />\n";
		
			$out .= "</td>\n
		</tr>\n
	</table>\n";
	if($what == 'thread'){
		$out .= "<input type='hidden' name='forum' value='$thread' />\n";
	}
	else
		$out .= "<input type='hidden' name='thread' value='$thread' />\n";
		
	// 1.7.3	
	//$out .= "<input type='hidden' name='back' value='$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]' />\n";
	
	$out .= "</td></tr></table></form>";

	
	return $out;
}

function forum_add_subscription(){
	global $user_ID, $profile, $rss_link;
	$thread = $_GET['thread'];
	
	// Manage feeds
	$feeds = get_usermeta($user_ID, 'feeds');
	$f = explode(',', $feeds);
	if(!in_array($thread, $f)){
		$feeds .= ",$thread";
		update_usermeta($user_ID, 'feeds', $feeds);
	}
	

	$thread_name = forum_get_thread_subject($thread);

	$out .= "<div id='subscript'>
				<h4>Thread added to your subscriptions</h4>
				<p><a href='$rss_link$user_ID'>Link to your subscriptions feed</a><br />
				<a href='$profile$user_ID'>Manage subscriptions</a></p>
			</div>";
	return $out;
}

function forum_email_subscription(){
	global $user_ID, $profile, $rss_link;
	$thread = $_GET['thread'];
	
	// Manage feeds
	$feeds = get_usermeta($user_ID, 'email_feeds');
	$f = explode(',', $feeds);
	if(!in_array($thread, $f)){
		$feeds .= ",$thread";
		update_usermeta($user_ID, 'email_feeds', $feeds);
	}
	

	$thread_name = forum_get_thread_subject($thread);

	$out .= "<div id='subscript'>
				<h4>Thread added to your email subscriptions</h4>
				<p>
				<a href='$profile$user_ID'>Manage subscriptions</a></p>
			</div>";
	return $out;
}

function forum_email_subs($thread){
	global $wpdb, $table_posts;
	$res = $wpdb->get_results("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'email_feeds' AND meta_value LIKE '%$thread%'"); 

	$last_page = forum_get_last_posts_page($thread);
	$ppp = get_option('forum_posts_per_page');
	
	$post_start = ceil($ppp*$last_page)-$ppp;
	$post_id = forum_get_lastpost_id($thread);
	$link = html_entity_decode(forum_get_link_from_post($thread, '', $post_start))."#msg-$post_id";
	$thread_name = forum_get_thread_subject($thread);
	
	$from = "forum@".get_bloginfo('blogname');
	$subject = "Forum subscription";
	$message = "There is a new post in \"$thread_name\"\n\nClick on the link below to view.\n\n $link";
	$headers = "From: " . $from . "\r\n" .
	   "X-Mailer: PHP/" . phpversion();
	
	foreach((array)$res as $r){
		$user = new WP_user($r->user_id);
		$to      = $user->user_email;
		
			
				
		mail($to, $subject, $message, $headers);
	}
}

function user_can_moderate($user_id = ''){
	global $user_ID, $user_level;
	if($user_id){
		$mods = get_usermeta($user_id, "moderator");
		if(strpos($mods, $_GET['forum']))
			return true;
		else
			return false;
	}
	$mods = get_usermeta($user_ID, "moderator");
	//if(isset($_GET['thread']))
	//	$p = forum_get_single_thread($_GET['thread']);
	
	// Not logged in
	if($user_ID == 0)
		return false;
		
	// Admin has always moderation capabilities.
	if($user_level > 8)
		return true;
		
	// User is moderator
	if(stripos($mods, $_GET['forum']))
		return true;
	
	// User is the poster
	//if(isset($_GET['post']))
	//	if($user_ID == $p->author_id);
	//		return true;
	
	return false;
}

// Ripped from WordPress check_comment()
function forum_check_spam_words($text){

	if(get_option('forum_use_spam')){
		if ( preg_match_all("|(href\t*?=\t*?['\"]?)?(https?:)?//|i", $text, $out) >= get_option('comment_max_links') )
			return true; // Check # of external links

			$mod_keys = trim( get_option('moderation_keys') );
			if ( !empty($mod_keys) ) {
				$words = explode("\n", $mod_keys );

				foreach ($words as $word) {
					$word = trim($word);

					// Skip empty lines
					if (empty($word)) { continue; }
					
					// Do some escaping magic so that '#' chars in the 
					// spam words don't break things:
					$word = preg_quote($word, '#');
			
					$pattern = "#$word#i"; 
					if ( preg_match($pattern, $text) ) return true;
				}
			}
		return false;
	}
	return false;
}

















?>