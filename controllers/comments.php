<?php

function post() {
	if ($_SESSION['userid'] == '') {
		echo "0";
		exit;
	}

	global $template;
	global $noheader;
	$noheader = true;

	$id = sanitize($_POST['id'],"string");
	$type = substr($id,0,1);
	$typeid = substr($id,1);
	if ($type == 'q') {
		$type = 0;
	} else {
		$type = 1;
	}

	$comment = sanitize($_POST['comment'],"comment");

	if (strlen($comment) < 10 || strlen($comment) > 600) {
		echo "0An error has occurred. Please try again later";
		exit;
	}
	
	$sql = ("insert into comments (type,comment,votes,created,userid,typeid) values ('".escape($type)."','".escape($comment)."','0',NOW(),'".escape($_SESSION['userid'])."','".escape($typeid)."')");
	$query = mysql_query($sql);
 
	$template->set('comment',$comment);
	
	$firstname = $_SESSION['name'];
	$pos = strpos($_SESSION['name'],' ');
	if ($pos > 0) {
		$firstname = substr($_SESSION['name'],0,$pos);
	}

	$template->set('username',$firstname);
	$template->set('userid',$_SESSION['userid']);

    if(COMMENT_EMAIL_NOTIFICATION){
        if($type == 0){
            $sql = ("
            select q.userid, u.email, q.id, q.slug
            from questions q
            join users u on u.id = q.userid
            where q.id = ".$typeid.";");
        }else{
            $sql = ("
            select a.userid, u.email, q.id, q.slug
            from answers a
            join users u on u.id = q.userid
            join questions q on q.id = a.questionid
            where a.id = ".$typeid.";");
        }
        $query = mysql_query($sql);
        while ($row = mysql_fetch_array($query)) {
            if($row['userid'] != $_SESSION['userid']){
                $url = 'http://'.$_SERVER['HTTP_HOST'].basepath().'/questions/view/'.$row['id'].'/'.$row['slug'];
                sendNotification('A new comment on your question !',$url,$row['email'],$comment);
            }

        }
    }

}

function vote() {
	if ($_SESSION['userid'] == '') {
		echo "0Please login to vote";
		exit;
	}

	$id = sanitize($_POST['id'],"int");

	$sql = ("select userid from comments where id = '".escape($id)."'");
	$query = mysql_query($sql);
	$comment = mysql_fetch_array($query);

	if ($comment['userid'] == $_SESSION['userid']) {
		echo "0You cannot upvote your own comment";
		exit;
	}

	$sql = ("select * from comments_votes where commentid = '".escape($id)."' and userid = '".escape($_SESSION['userid'])."'");
	$query = mysql_query($sql);
	$result = mysql_fetch_array($query);


	if ($result['id'] > 0) { 

		$sql = ("delete from comments_votes where commentid = '".escape($id)."' and userid = '".escape($_SESSION['userid'])."'");
		$query = mysql_query($sql);
		$sql_nest = ("update comments set votes = votes-1 where id = '".escape($id)."'");
		$query_nest = mysql_query($sql_nest);
		score('c_upvoted_removed',$id,$comment['userid']);
	
	} else {
		$sql = ("insert into comments_votes (commentid,userid) values ('".escape($id)."','".escape($_SESSION['userid'])."')");
		$query = mysql_query($sql);
		$sql_nest = ("update comments set votes = votes+1 where id = '".escape($id)."'");
		$query_nest = mysql_query($sql_nest);
		score('c_upvoted',$id,$comment['userid']);
	}

	echo "1";
	exit;

}

function del() {

	$id = sanitize($_POST['id'],"int");

	$sql = ("delete from comments where id = '".escape($id)."' and userid = '".escape($_SESSION['userid'])."'");
	$query = mysql_query($sql);

	echo "1Comment successfully deleted";
	exit;

}