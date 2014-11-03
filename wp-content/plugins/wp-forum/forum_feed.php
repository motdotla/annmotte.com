<?php
		include( '../../../wp-blog-header.php' );
		if(isset($_GET['user'])){
			$feeds = get_usermeta($_GET['user'], 'feeds');
			$f = substr_replace($feeds, '', 0, 1);
		}
		header ("Content-type: application/rss+xml");    
		echo ("<?xml version=\"1.0\" encoding=\"".get_bloginfo('charset')."\"?>\n");
		global $wpdb, $table_prefix;
		$table_posts = $table_prefix."forum_posts";
		$table_threads = $table_prefix."forum_threads";
		?>
		<rss version='2.0'>
		<channel>
		<title><?php bloginfo('name');?> - Forum - Feed</title>
		<description><?php bloginfo('name');?> - Feed></description>
		<link><?php bloginfo('siteurl');?>/forum_feed.php</link>
		<language>en-us</language>
		<?php
		if(isset($_GET['user']))
			$posts = $wpdb->get_results("SELECT * FROM $table_posts WHERE thread_id IN ($f) ORDER BY `date` DESC  ");
		else
			$posts = $wpdb->get_results("SELECT * FROM $table_posts WHERE thread_id = $_GET[thread] ORDER BY `date` DESC  ");
			
		foreach($posts as $p)
		{
			$thread = $wpdb->get_var("SELECT forum_id FROM $table_threads WHERE id = $p->thread_id LIMIT 1");
			$lnk = forum_get_link_from_post($p->thread_id, $p->id);
			$u = new WP_user($p->author_id);
		echo "<item>\n
			<title>" . htmlspecialchars(forum_feed_trail($thread, $p->thread_id), ENT_NOQUOTES) . "</title>\n
			<description>".htmlspecialchars($p->text, ENT_NOQUOTES)."</description>\n
			<author>$u->user_email ($p->nickname)</author>\n
			<link>".htmlspecialchars($lnk)."</link>\n
			<pubDate>".date("r", strtotime($p->date))."</pubDate>\n
			<guid>".htmlspecialchars($lnk."&guid=$p->id")."</guid>
			</item>\n\n";
		}
		?>
		</channel>
		</rss>