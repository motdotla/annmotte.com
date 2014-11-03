<?php
include( '../../../wp-blog-header.php' );
include( 'wp-forum.php' );


	forum_insert_post();
	$_POST = array();
	header("Location:".$_POST['back']);



?>