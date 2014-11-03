<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');
        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
				?>
				<p class="comments-closed">This post is password protected. Enter the password to view comments.<p>
				<?php
				return;
            }
        }
		/* This variable is for alternating comment background */
		$oddcomment = 'alt';
?>
<?php if ($comments) : ?>

<div id="comments">

	<h3 class="comments-title">Comments</h3>
	<ol class="commentlist">
		<?php foreach ($comments as $comment) : ?>
		<?php if (get_comment_type() == "comment"){ ?>
		<li class="comment-item <?php echo $oddcomment; ?><?php if ($comment->comment_author_email == "ashhaque@gmail.com") echo " author"; ?>" id="comment-<?php comment_ID() ?>">
			<div class="comment-text">
				<?php if ($comment->comment_approved == '0') : ?>
				<em>Your comment is awaiting moderation.</em>
				<?php endif; ?>
				<?php comment_text() ?>
			</div>
			<div class="comment-info">
				<span class="comment-author">By <?php comment_author_link() ?></span>
				<span class="comment-date">on <a href="#comment-<?php comment_ID() ?>" title="Permanent link to comment #<?php comment_ID() ?>"><?php comment_date('F jS, Y') ?> at <?php comment_time() ?></a><?php edit_comment_link('Edit',' | ',''); ?></span>
			</div>
		</li>
		<?php /* Changes every other comment to a different class */ if ('alt' == $oddcomment) $oddcomment = ''; else $oddcomment = 'alt'; ?>
		<?php } ?>
		<?php endforeach; /* end for each comment */ ?>
	</ol><!-- /commentslist -->

	<h3 class="comments-title">Trackbacks / Pings</h3>
	<ol class="trackbacklist">
		<li class="trackback-item"><span class="trackback-link"><a href="<?php trackback_url(); ?>">Trackback URl &rarr;</a></span></li>
		<?php foreach ($comments as $comment) : ?>
		<?php if (get_comment_type() != "comment"){ ?>
		<li class="trackback-item <?php echo $oddcomment; ?>" id="trackback-<?php comment_ID() ?>">
			<span class="trackback-url"><?php comment_author_link() ?></span>
			<span class="trackback-date"><?php comment_date('F d Y \a\t h:i A') ?></span>
		</li>
		<?php /* Changes every other comment to a different class */ if ('alt' == $oddcomment) $oddcomment = ''; else $oddcomment = 'alt'; ?>
		<?php } ?>
		<?php endforeach; /* end for each comment */ ?>
	</ol><!-- /trackbacklist -->

</div><!-- /comments -->
<?php else : // this is displayed if there are no comments so far ?>

<?php if ('open' == $post-> comment_status) : ?> 
<!-- If comments are open, but there are no comments. -->
<?php else : // comments are closed ?>
<p class="comments-closed">Comments are closed</p>
<?php endif; ?>
<?php endif; ?>

<?php if ('open' == $post-> comment_status) : ?>

<div id="respond">

	<h3 class="replies-title">Leave a Reply</h3>
	
	<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
	<p class="login-required">You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
	<?php else : ?>
	
	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
	<?php if ( $user_ID ) : ?>
	
		<p class="logged-in">Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout</a></p>
		<?php else : ?>
		
		<p><label for="author">Name <?php if ($req) echo "(required)"; ?></label><br /><input type="text" class="commentform-input" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" /></p>
		
		<p><label for="email">E-mail <?php if ($req) echo "(required)"; ?> (will not be published)</label><br /><input type="text" class="commentform-input" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" /></p>
		
		<p><label for="url">Website</label><br /><input type="text" class="commentform-input" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" /></p>
		<?php endif; ?>
		
		<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->

		<p><label for="url">Comment</label><br /><textarea class="commentform-textarea" name="comment" id="comment" cols="60" rows="10" tabindex="4"></textarea></p>
		
		<p><input type="submit" class="commentform-button" name="commentform-submit" id="commentform-submit" tabindex="5" value="Submit Comment" /><input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" /></p>
		<?php do_action('comment_form', $post->ID); ?>

	</form>

</div><!-- /replies -->
<?php endif; // If registration required and not logged in ?>
<?php endif; // if you delete this the sky will fall on your head ?>