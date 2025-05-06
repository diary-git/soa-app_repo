<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once "db_notif.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["status" => "error", "message" => "Méthode non autorisée"]));
}

$data = json_decode(file_get_contents("php://input"), true);
foreach (['employe_id', 'destinataire', 'type', 'contenu'] as $f) {
    if (empty($data[$f])) {
        http_response_code(400);
        exit(json_encode(["status" => "error", "message" => "Champ manquant : $f"]));
    }
}

try {
    // 1) Persistena notification
    $stmt = $notif_db->prepare(
        "INSERT INTO notifications (employe_id, destinataire, typeNotif, contenu)
       VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([
        htmlspecialchars($data['employe_id']),
        htmlspecialchars($data['destinataire']),
        htmlspecialchars($data['type']),
        htmlspecialchars($data['contenu'])
    ]);

    // 2) Envoi du mail
    $destinataires = explode(',', $data['destinataire']);
    foreach ($destinataires as $destinataire) {
        $destinataire = trim($destinataire);
        envoyerEmail($destinataire, $data['contenu']);
    }

    echo json_encode([
        "status" => "ok",
        "message" => "Notification enregistrée et envoyée",
        "id_notification" => $notif_db->lastInsertId()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur BD : " . $e->getMessage()]);
}

function envoyerEmail($destinataire, $contenu)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '------------------';
        $mail->Password = '----------------';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataire
        $mail->setFrom('test@example.com', 'Service RH - SOA');
        $mail->addAddress($destinataire);
        // Contenu du mail
        $mail->isHTML(true);
        $mail->Subject = 'Notification';
        $mail->Body = $contenu;


        $mail->send();
    } catch (Exception $e) {

        error_log("Erreur lors de l'envoi de l'email à $destinataire: {$mail->ErrorInfo}");
    }
}
