<?php

function getUser($id) {
	global $helper;

	$id = sanitize($id,"int");
	$sql = ("select * from users where id = '".escape($id)."'");
	$query = mysql_query($sql);
	$result = mysql_fetch_array($query);
	
	$helper->set('user',$result);
	return $helper->render();
}


function sendNotification($subject,$link,$recipient,$desc){
    // Plusieurs destinataires
    $to  = $recipient;

    // Sujet
    $subject = $subject;

    // message
    $message = '
     <html>
      <head>
       <title>Qwench Notification</title>
      </head>
      <body>
       <p>
        Some news on Qwench!
        <br />
        Please go on this link : <a href="'.$link.'">Click here</a>
       </p>
       <p>Description :</p>
       <span>'.$desc.'</span>
       <p>From Qwench website.</p>
      </body>
     </html>
     ';

    // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

    // En-têtes additionnels
    $headers .= 'To: '. $recipient . "\r\n";
    $headers .= 'From: Qwench <'.EMAIL_SENDER.'>' . "\r\n";

    // Envoi
    mail($to, $subject, $message, $headers);
}
 