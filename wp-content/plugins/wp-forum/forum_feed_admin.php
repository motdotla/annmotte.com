<?php
		require_once("../../../wp-blog-header.php");
		require_once("forum-functions.php");
		header ("Content-type: application/rss+xml");    
		echo ("<?xml version=\"1.0\" encoding=\"".get_bloginfo('charset')."\"?>\n");
		global $wpdb, $table_prefix;
		$table_posts = $table_prefix."forum_posts";
		$table_threads = $table_prefix."forum_threads";
		
		?>
		<rss version='2.0'>
		<channel>
		<title><?php bloginfo('name');?> - Support forum</title>
		<description><?php bloginfo('name');?> - Feed from fahlstad.se Support Forum.</description>
		<link><?php bloginfo('siteurl');?>/forum_feed_admin.php</link>
		<language>en-us</language>
		<?php
		
		$posts = $wpdb->get_results("SELECT * FROM $table_posts ORDER BY id DESC LIMIT 0,40");
		
		
	
		foreach($posts as $p)
		{
	
			$thread = $wpdb->get_var("SELECT forum_id FROM $table_threads WHERE id = $p->thread_id LIMIT 1");
	
			$lnk = forum_get_link_from_post($p->thread_id, $p->id);
			$u = new WP_user($p->author_id);
			
		echo "<item>\n
			<title>" . htmlspecialchars(forum_feed_trail($thread, $p->thread_id), ENT_NOQUOTES) . "</title>\n
			<description>".htmlspecialchars($p->text, ENT_NOQUOTES)."</description>\n
			<author>$u->user_email ($u->nickname)</author>
			<link>".htmlspecialchars($lnk)."</link>\n
			<pubDate>".date("r", strtotime($p->date))."</pubDate>\n
			<guid>".htmlspecialchars($lnk."&guid=$p->id")."</guid>
			</item>\n\n";
		}
		?>
		</channel>
		</rss>
		
	