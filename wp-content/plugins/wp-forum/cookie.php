<?php
function ul($user_id){
	if(!isset($_COOKIE['session'])){
		update_usermeta( $user_id, 'lastvisit', time() );
		return true;
	}
	return false;
	
}

function forum_get_lastvisit($user, $formatted = true){
	$s = get_usermeta($user, 'lastvisit');
	
	if(!$formatted)
		return $s;
	else
		return @date("Y-m-d H:i:s", $s);
}

function forum_set_cookie(){
	setcookie('session', 's', 0, '/');
	
}
?>