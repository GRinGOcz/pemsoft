<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Ošetření OPTIONS požadavku (pre-flight check, který dělají prohlížeče)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

function detectMailTransportProblem(): ?string {
    $sendmailPath = trim((string) ini_get('sendmail_path'));

    if ($sendmailPath === '') {
        return null;
    }

    $parts = preg_split('/\s+/', $sendmailPath);
    $binary = $parts[0] ?? '';

    if ($binary === '') {
        return null;
    }

    if (!file_exists($binary)) {
        return "Konfigurovany sendmail binarni soubor neexistuje: {$binary}";
    }

    if (!is_executable($binary)) {
        return "Konfigurovany sendmail binarni soubor neni spustitelny: {$binary}";
    }

    return null;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($data)) {
    $to = "petrmeca2@gmail.com";
    $subject = "=?UTF-8?B?".base64_encode("Nová poptávka z webu PeMSoft")."?="; // Správné kódování češtiny v předmětu

    $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
    // Tady pozor: Pokud v Reactu posíláš { message: ... }, musí tu být $data["message"]
    $message = htmlspecialchars($data["zprava"] ?? $data["message"] ?? "");

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        // Doporučení: From by měl být e-mail tvého webu
        $headers = "From: info@pemsoft.cz\r\n"; // Sem dej adresu své domény
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $body = "E-mail od odesílatele: " . $email . "\n\n";
        $body .= "Zpráva:\n" . $message;

        $mailError = null;

        set_error_handler(static function (int $severity, string $errorMessage) use (&$mailError): bool {
            $mailError = $errorMessage;
            return true;
        });

        $mailSent = mail($to, $subject, $body, $headers);
        restore_error_handler();

        if ($mailSent) {
            http_response_code(200);
            echo json_encode(["status" => "success"]);
        } else {
            $transportProblem = detectMailTransportProblem();

            if ($transportProblem !== null) {
                error_log($transportProblem);
                http_response_code(500);
                echo json_encode([
                    "status" => "error",
                    "message" => "Server nema nakonfigurovane odchozi e-maily."
                ]);
                exit();
            }

            if ($mailError !== null) {
                error_log("mail() selhal: " . $mailError);
            }

            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Odeslání selhalo na straně serveru."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Neplatný e-mail nebo prázdná zpráva."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Metoda nepovolena."]);
}
?>

