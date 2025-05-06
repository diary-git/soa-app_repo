<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db_dossier.php";

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données JSON invalides"]);
    exit;
}

// Extraire les données
$employe_id = htmlspecialchars($data['employe_id']) ?? null;
$nom = htmlspecialchars($data['nom']) ?? null;

if (!$employe_id || !$nom) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données manquantes"]);
    exit;
}

// Récupérer l'email de l'employé depuis gateway
$curl_auth = curl_init("http://localhost/soa-app/api-gateway/employes?num_emp=$employe_id");
curl_setopt($curl_auth, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_auth, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
$response_auth = curl_exec($curl_auth);
curl_close($curl_auth);

$employe_data = json_decode($response_auth, true);

//si numero_employe non trouvé dns BD 
if (!isset($employe_data['numero_employe'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Employé non trouvé"]);
    exit;
}

// Vérifier si un bénéficiaire existe déjà pour cet employé
$checkStmt = $dossier_db->prepare("SELECT employe_id FROM beneficiaires WHERE employe_id = ?");
$checkStmt->execute([$employe_id]);
$existing = $checkStmt->fetch();

if ($existing) {
    // Mise a jour
    $stmt = $dossier_db->prepare("UPDATE beneficiaires SET nom = ? WHERE employe_id = ?");
    $stmt->execute([
        htmlspecialchars($nom),
        htmlspecialchars($employe_id)
    ]);
    $message = "Bénéficiaire mis à jour.";
} else {
    // Insertion
    $stmt = $dossier_db->prepare("INSERT INTO beneficiaires (nom, employe_id) VALUES (?, ?)");
    $stmt->execute([
        htmlspecialchars($nom),
        htmlspecialchars($employe_id)
    ]);
    $message = "Bénéficiaire ajouté.";
}

$email_employe = $employe_data['email'];
$mail_assurance = "-----------"; // Adresse email de la compagnie d'assurance

// Contenu de l' email
$contenu_txt_employe = "Le changement de nom de votre bénéficiaire a été bien enregistré, votre nouvelle bénéficiaire est $nom.";
$contenu_txt_assurance = "Un changement de bénéficiaire a été enregistré pour l'employé $employe_id.\nNom du nouveau bénéficiaire est $nom.";

function envoyerNotification($employe_id, $type, $contenu, $destinataire)
{
    $avis = [
        "employe_id" => $employe_id,
        "type" => $type,
        "contenu" => htmlspecialchars($contenu),
        "destinataire" => htmlspecialchars($destinataire)
    ];

    $curl = curl_init("http://localhost/soa-app/api-gateway/envoyer-mail");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($avis));
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_exec($curl);
    curl_close($curl);
}

envoyerNotification($employe_id, "notification", $contenu_txt_employe, $email_employe);
envoyerNotification($employe_id, "avis_assurance", $contenu_txt_assurance, $mail_assurance);

echo json_encode(["status" => "success", "message" => $message]);
