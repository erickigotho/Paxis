<?php
$temp = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$arrUrl = explode('/', $temp);
$resultArr = array();

array_pop($arrUrl);

foreach($arrUrl as $data) {
	array_push($resultArr, $data);
}

echo 'http://' . implode('/', $resultArr);

// $cstrong = "Pax1SpUNchL1stPr0";

// $bytes = openssl_random_pseudo_bytes(16, $cstrong);
// $hex   = bin2hex($bytes);

// echo  $cstrong;
// echo "<br/>";
// echo "<br/>";
// echo  $hex;

/*
 require_once "Mail.php";
 
 $from = "Wendell Malpas <wendell.malpas@gmail.com>";
 $to = "Wendell Malpas <wendell.malpas@gmail.com>";
 $subject = "Test email, please ignore.";
 $body = "Hi from PEAR mail.";
 
 $host = "smtp.gmail.com";
 $port = "465";
 $username = "wendell.malpas@gmail.com";
 $password = "bionBi0n1";
 
 $headers = array ('From' => $from,
   'To' => $to,
   'Subject' => $subject);
 $smtp = Mail::factory('smtp', array( 'host' => $host,
									'port' => $port,
									'auth' => true,
									'username' => $username,
									'password' => $password)
						);
 
 $mail = $smtp->send($to, $headers, $body);
 
 if (PEAR::isError($mail)) {
   echo("<p>" . $mail->getMessage() . "</p>");
  } else {
   echo("<p>Message successfully sent!</p>");
  
  */
 ?>

<?php
/*
$mailHeaders = "From: Wendell Malpas <wendell.malpas@verifone.com> \r\n";
$mailHeaders .= "Reply-To:  Wendell Malpas <wendell.malpas@verifone.com>\r\n";
$mailHeaders .= "Return-Path:  Wendell Malpas <wendell.malpas@verifone.com>\r\n";
// $mailHeaders .= "Bcc: Wendell Malpas <wendell.malpas@gmail.com>\r\n";
$mailHeaders .= "X-Mailer: PHP v" .phpversion(). "\r\n";
$mailHeaders .= "MIME-Version: 1.0\r\n";
$mailHeaders .= "Content-Type: text/html; charset=utf-8";

if(mail("wendell.malpas@verifone.com", "Test Email", "Testing, please ignore.", $mailHeaders)) {
	echo "Email sent!";
} else {
	echo "Sorry, email not sent this time.";
}
*/
?>