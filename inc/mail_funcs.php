<?php
require_once("PHPMailer-5.2.21/class.phpmailer.php");
require_once("PHPMailer-5.2.21/class.smtp.php");

function ValidateEmail($email)
{
 // Create the syntactical validation regular expression
 $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";

 // Presume that the email is invalid
 $valid = 0;

 // Validate the syntax
 if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // syntax OK
    $valid = 1;
 } else {
    $valid = 0;
 }
 return $valid;
}

function MyPHPMailer(
  $recipient_email,
  $recipient_name,
  $subject,
  $msg,
  $format='html',
  $options='',
  $sender='',
  $sender_name='')
  {
  $mail = new PHPMailer();
  $mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Debugoutput = 'html';
  // From is the name/email of the logged in user
  $mail->From = (!empty($sender)) ? $sender : "webmaster@scata.org.uk";
  if (!empty($sender_name)) {
    $mail->FromName = $sender_name;
    $mail->AddReplyTo($sender, $sender_name);
  } else {
    $mail->FromName = "SCATA Mailer";
    $mail->AddReplyTo("webmaster@scata.org.uk");
  }
  if ($_SERVER["SERVER_NAME"] == "scata.local")
    $mail->Host = "mail.scata.org.uk";
  else $mail->Host = "127.0.0.1";
  // SMTP Authentication
  $mail->SMTPAuth	= true;
  $mail->SMTPDebug = 0;
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;
  $mail->SMTPOptions = array (
      'ssl' => array(
      'verify_peer'  => true,
      'verify_depth' => 3,
      'allow_self_signed' => false,
      'peer_name' => 'mail.scata.org.uk')
  );
  $mail->Username = "webmaster@scata.org.uk";
  $mail->Password = "auratus0";

  if ($format == 'html')  {
    $mail->IsHTML(true);
    $mail->AltBody = strip_tags($msg) . "\r\nTo view HTML messages properly, please use an HTML compatible email viewer!\r\n"; // optional, comment out and test
  } else $mail->IsHTML(false);
  $mail->Subject = $subject;
  $mail->AddAddress($recipient_email, $recipient_name);

  $mail->Body = $msg;

  if(!$mail->Send()) {
    return $mail->ErrorInfo;
  } else {
    return "";
    }
}

?>
