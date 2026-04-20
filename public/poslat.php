<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($data)) {
  $to = "petrmeca2@gmail.com";
  $subject = "Nová poptávka z webu";

  $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
  $message = htmlspecialchars($data["zprava"]);

  if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
  $headers = "From: " . $email . "\r\n";
  $headers .= "Reply-To: " . $email . "\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();

  $body = "E-mail: " . $email . "\n\n";
  $body .= "Zpráva:\n" . $message;

  if (mail($to, $subject, $body, $headers)) {
    http_response_code(200);
    echo json_encode(["status" => "success"]);
  } else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to send."]);
  }
  } else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
  }
} else {
  http_response_code(405);
  echo json_encode(["status" => "error", "message" => "Method not allowed."]);
}
?>
