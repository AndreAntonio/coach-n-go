<?php

  //ENTER THE NAME OF THE FILE INTO WHICH YOU WOULD LIKE TO SAVE THE EMAIL ADDRESSES OF SUBSCRIBERS
  $emailsFile = 'email-list.txt';

  //ENTER YOUR EMAIL ADDESS TO GET NOTIFIED EVERY TIME A SUBSCRIPTION IS DONE BY THE VISITORS.
  $myEmail = '';


  ob_start();

  function response($responseStatus, $responseMsg) {
    $out = json_encode(array('responseStatus' => $responseStatus, 'responseMsg' => $responseMsg));

    ob_end_clean();
    die($out);
  }

  // AJAX CALLBACK
  if (!isset($_SERVER['X-Requested-With']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    response('err', 'ajax');
  }

  // ERROR SYNTAX WHEN REQUITED EMAILS FILE CANNOT READ or WRITE.
  if (($file = fopen($emailsFile, 'r+')) == false) {
    response('err', 'fileopen');
  }

  // ERROR SYNTAX FOR INVALID NAME
  if(!isset($_POST['newsletter-name'])
     || !trim($_POST['newsletter-name'])
     || strtolower($_POST['newsletter-name']) == 'name'
     || strlen($_POST['newsletter-name']) < 3) {
    response('err', 'name');
  }
  
  // ERROR SYNTAX FOR INVALID EMAIL ADDRESS
  if(!isset($_POST['newsletter-email']) || !preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', trim($_POST['newsletter-email']))) {
    response('err', 'email');
  }

  // DUPLICATING ENTRY
  $name = trim(ucfirst($_POST['newsletter-name']));
  $emailAddress = trim(strtolower($_POST['newsletter-email']));
  while($line = fgets($file)) {
    $line = explode(' ', trim($line));
    $email = $line[0];
    if ($email == $emailAddress) {
      response('err', 'duplicate');
    }
  }//END WHILE

  // WRITE EMAIL TO FILE
  fseek($file, 0, SEEK_END);
  if (fwrite($file, $emailAddress . ' - ' . $name . PHP_EOL) == strlen($emailAddress . ' - ' . $name . PHP_EOL)) {
    
	
    if (preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', trim($myEmail))) {
        $headers  = "MIME-Version: 1.0 \n";
        $headers .= "Content-type: text/plain; charset=UTF-8 \n";
        $headers .= "X-Mailer: PHP " . PHP_VERSION . "\n";
        $headers .= "From: {$myEmail} \n";
        $headers .= "Return-Path: {$myEmail} \n";
        $message = 'The following person was kind enough to subscribe to your newsletter:' . PHP_EOL . $name . ' - ' . $emailAddress;
        @mail($myEmail, 'You have a new newsletter subscriber', $message, $headers);
    }
    response('ok', 'subscribed');
  } else {
    response('err', 'filewrite');
  }

  response('err', 'undefined');
?>