<?php
// send-contact.php - Handle contact form submission and send email

declare(strict_types=1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$message = trim($_POST["message"] ?? "");

// Validation
$errors = [];
if ($name === "") $errors[] = "שם חובה";
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "אימייל לא תקין";
if ($phone === "") $errors[] = "טלפון חובה";
if ($message === "") $errors[] = "הודעה חובה";

if (!empty($errors)) {
    header("Content-Type: application/json");
    http_response_code(400);
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

// Send email
$to = "team@flavorforge.com";
$subject = "הודעה חדשה מ-The Flavor Forge: " . htmlspecialchars($name);
$body = "שם: " . htmlspecialchars($name) . "\n";
$body .= "אימייל: " . htmlspecialchars($email) . "\n";
$body .= "טלפון: " . htmlspecialchars($phone) . "\n";
$body .= "הודעה:\n" . htmlspecialchars($message);

$headers = "From: " . htmlspecialchars($email) . "\r\n";
$headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$success = mail($to, $subject, $body, $headers);

header("Content-Type: application/json");
if ($success) {
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "ההודעה נשלחה בהצלחה! תודה על יצירת הקשר."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "שגיאה בשליחת ההודעה. אנא נסה שוב מאוחר יותר."
    ]);
}
exit;
?>
