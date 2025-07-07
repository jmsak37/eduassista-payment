<?php
header('Content-Type: application/json');

// 1) Load .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo json_encode(['success'=>false,'message'=>'Configuration error.']);
    exit;
}
$vars = parse_ini_file($envFile, false, INI_SCANNER_RAW);
foreach ($vars as $k => $v) {
    putenv("$k=" . trim($v, " \t\n\r\0\x0B\"'"));
}

// 2) Include PHPMailer
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3) Validate POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}
$userEmail = filter_var($_POST['userEmail'] ?? '', FILTER_VALIDATE_EMAIL);
$subject   = trim($_POST['subject'] ?? '');
$message   = trim($_POST['message'] ?? '');
if (!$userEmail || !$subject || !$message) {
    echo json_encode(['success'=>false,'message'=>'All fields are required.']);
    exit;
}

// 4) Send mail
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($userEmail);
    $mail->addAddress('jmsak37@gmail.com', 'EduAssista Support');
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->isHTML(false);

    // Attach files
    if (!empty($_FILES['attachments']['tmp_name'][0])) {
        foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {
            if (is_uploaded_file($tmp)) {
                $mail->addAttachment($tmp, $_FILES['attachments']['name'][$i]);
            }
        }
    }

    $mail->send();
    echo json_encode(['success'=>true,'message'=>'Your message has been sent successfully.']);
} catch (Exception $e) {
    error_log('Mail error: ' . $mail->ErrorInfo);
    echo json_encode(['success'=>false,'message'=>'Mail Error: ' . $mail->ErrorInfo]);
}
