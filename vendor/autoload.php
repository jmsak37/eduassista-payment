<?php
// index.php

// 1) Manual .env loader
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $vars = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    foreach ($vars as $key => $val) {
        // trim quotes/spaces
        $val = trim($val, " \t\n\r\0\x0B\"'");
        putenv("$key=$val");
    }
} else {
    die("Missing .env file");
}

// 2) Include PHPMailer classes directly
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3) Only handle POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// 4) Validate inputs
$userEmail = filter_var($_POST['userEmail'] ?? '', FILTER_VALIDATE_EMAIL);
$subject   = trim($_POST['subject']   ?? '');
$message   = trim($_POST['message']   ?? '');

if (!$userEmail || !$subject || !$message) {
    echo '<p>Invalid input. <a href="index.html">Go back</a>.</p>';
    exit;
}

// 5) Setup PHPMailer
$mail = new PHPMailer(true);
try {
    // Debug off in production (0 = off, 2 = verbose)
    $mail->SMTPDebug   = 0;
    $mail->Debugoutput = 'html';

    // SMTP config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Headers & body
    $mail->setFrom($userEmail);
    $mail->addAddress('jmsak37@gmail.com', 'EduAssista Support');
    $mail->Subject = $subject;
    $mail->Body    = "Message from: {$userEmail}\n\n{$message}";
    $mail->isHTML(false);

    // Attachments
    if (!empty($_FILES['attachments']['tmp_name'][0])) {
        foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {
            if (is_uploaded_file($tmp)) {
                $mail->addAttachment($tmp, $_FILES['attachments']['name'][$i]);
            }
        }
    }

    // Send!
    $mail->send();
    $success = true;
} catch (Exception $e) {
    // Show error
    echo '<div style="font-family:Arial,sans-serif;padding:1rem;">'
       .  '<h2>Mail Error</h2>'
       .  '<pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>'
       .  '<p><a href="index.html">Back to form</a></p>'
       .  '</div>';
    exit;
}

// 6) Success HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Message Sent</title>
  <style>
    body{display:flex;align-items:center;justify-content:center;
         height:100vh;background:#eef7ef;margin:0;font-family:Arial,sans-serif;}
    .msg{background:#fff;border:2px solid #c8e6c9;padding:2rem;
         border-radius:8px;text-align:center;max-width:400px;
         animation:fadeIn 0.5s ease-in;}
    .msg h2{color:#2e7d32;margin-bottom:1rem;}
    .msg p{margin-bottom:1.5rem;}
    .msg a{display:inline-block;padding:0.5rem 1rem;
           background:#00796b;color:#fff;text-decoration:none;
           border-radius:4px;transition:background 0.2s;}
    .msg a:hover{background:#00564d;}
    @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
  </style>
</head>
<body>
  <div class="msg">
    <h2>Thank you!</h2>
    <p>Your message has been sent successfully.</p>
    <a href="index.html">Back to Home</a>
  </div>
</body>
</html>
