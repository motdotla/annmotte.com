var currentdiv = "";
function sendemail(user) {
	var url = $F('url') + '/wp-content/plugins/wp-forum/sendmail.php?user=' + user;
	new Ajax.Updater('sendemail', url, {onComplete: function(){new Effect.Highlight('sendemail')} });
	$('sendemail').style.display = 'block';
	
}

function doSend () {
	var body = "";
	var url = $F('url') + "/wp-content/plugins/wp-forum/sendmail.php";
	
	new Ajax.Request(url, 
										{
											method: 'post',
											onLoading: email_loading, 
											onComplete: email_complete,
											postBody: "sender=" + $F('sender') + 
																"&&email=" + $F('email') +
																"&&message=" + $('message').value +
																"&&replyto=" + $F('replyto') +
																"&&subject=" + $F('subject') +
																"&&submit=dosubmit",
											asynchronous:true
											});
}

function email_loading () {
	$('sendemail').innerHTML = "<p><b>Sending email...</b><img src='"+$F('url')+"/wp-content/plugins/wp-forum/images/indicator.gif' /></p>";
}
function email_complete (response) {
	$('sendemail').innerHTML = response.responseText;
		
}

function del_sub(div, id, user){
	var url = $F('url') + "/wp-content/plugins/wp-forum/sendmail.php";
	var pars = "action=delsub&sub="+id+"&user="+user;
	
	currentdiv = "feed"+div;
	new Ajax.Request(url, {method: 'get', parameters: pars, onComplete: del_complete});
}
function del_emailsub(div, id, user){
	var url = $F('url') + "/wp-content/plugins/wp-forum/sendmail.php";
	var pars = "action=del_emailsub&sub="+id+"&user="+user;
	
	currentdiv = "email_feed"+div;
	new Ajax.Request(url, {method: 'get', parameters: pars, onComplete: del_complete});
}
function quote(id){
	var url = $F('url') + '/wp-content/plugins/wp-forum/sendmail.php?action=quote&id=' + id;
	new Ajax.Updater('forumtext', url, {onComplete:function(){new Effect.ScrollTo('forumtext')} });
}

function del_complete (response) {
	$(currentdiv).innerHTML = response.responseText;
	new Effect.Highlight(currentdiv);
	new Effect.Fade(currentdiv);
}
