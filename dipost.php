<?php
// dipost.php
session_start();
header('Content-Type: application/json');

// must be logged in
if (empty($_SESSION['userID'])) {
    echo json_encode(['success'=>false,'message'=>'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$amount = floatval($input['amount'] ?? 0);

if ($amount <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid amount']);
    exit;
}

// No DB here—just echo success
echo json_encode([
    'success' => true,
    'message' => "Thank you for your payment of USD {$amount}!"
]);
exit;
