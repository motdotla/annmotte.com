<?php
include( '../../../wp-blog-header.php' );

if(isset($_GET['action']) && $_GET['action'] == "delsub"){
	
	$user = $_GET['user'];
	$curr_feed = ",".$_GET['sub'];
	
	$f = get_usermeta($user, 'feeds');
		
	$f = str_replace($curr_feed, '', $f);
	update_usermeta($user, 'feeds', $f);
	echo "Delete successful";
}
else if(isset($_GET['action']) && $_GET['action'] == "del_emailsub"){
	
	$user = $_GET['user'];
	$curr_feed = ",".$_GET['sub'];
	
	$f = get_usermeta($user, 'email_feeds');
	
//	echo "FEED 1: $f<br>";
	
	$f = str_replace($curr_feed, '', $f);
	
//	echo "FEED 2: $f<br>";
	
	/*
	echo "FEED 1: $feed<br>";
	echo "USER: $user<br>";
	echo "SUBS: $f<br>";
	echo "QUERY: ", $_SERVER['QUERY_SRING'];
	*/
	update_usermeta($user, 'email_feeds', $f);
	echo "Delete successful";
}
// Let's send the email...
else if(isset($_POST['submit'])){
	$sender 	= $_POST['sender'];
	$email 		= $_POST['email'];
	$message 	= $_POST['message'];
	$subject 	= $_POST['subject'];
	$replyto 	= $_POST['replyto'];
	
	$headers = "MIME-Version: 1.0\r\n" .
		"From: $sender\n" . 
		"Reply-To: $replyto" . "\r\n" .		
		"Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\r\n";
	sleep(1);
	//echo "$sender<br>$email<br>$message<br>$subject<br>$replyto<br>$headers";
	if(!wp_mail($email, $subject, $message, $headers))
		echo "<p><b>ERROR: Email delivery failure<b></p>";
	else
		echo "<p><b>Email delivery success.<b></p>";
		
}

else if(isset($_GET['action']) && $_GET['action'] == 'quote'){
	
	global $wpdb, $table_posts;
	$id = $_GET['id'];

	$text = $wpdb->get_row("SELECT text, author_id, date FROM $table_posts WHERE id = $id");
	$u = new WP_user($text->author_id);
	echo htmlentities("<blockquote><b>QUOTE</b> ($u->nickname @ ".date(get_option('forum_date_format'), strtotime($text->date)).")\n $text->text</blockquote>");
}	

else{
	$user = new WP_user($_GET['user']);?>

	<h4>Send email to <?php echo $user->nickname;?></h4>
	<form action="" method="post" onsubmit="doSend(); return false;">
	
	<p>
		<label for="sender">Sender:</label><br />
		<input type="text" name="sender" value="" id="sender">
	</p>
	<p>
		<label for="replyto">Reply address:</label><br />
		<input type="text" name="replyto" value="" id="replyto">
	</p>
	<p>
		<label for="subject">Subject:</label><br />
		<input type="text" name="subject" value="" id="subject">
	</p>
	<p>
		<label for="message">Message:</label><br />
		<textarea name="message" id="message" rows="8" cols="40"></textarea>
	</p>
	<input type="hidden" name="email" value="<?php echo $user->user_email;?>" id="email">
	<input type="hidden" name="url" value="<?php echo get_bloginfo('wpurl')?>" id="url">
	
	<p><input type="submit" value="Send" id="dosubmit" name="dosubmit"></p>
	</form>
<?php } ?>