<?php
header("Content-Type: application/json");
require_once "db_auth.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Vérifier que toutes les infos sont là
$champs = ['nom', 'numero_employe', 'adresse', 'nas'];
foreach ($champs as $champ) {
    if (empty($data[$champ])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Champ manquant : $champ"]);
        exit;
    }
}

try {
    $stmt = $auth_db->prepare("SELECT * FROM employes WHERE nom = ? AND numero_employe = ? AND adresse = ? AND nas = ?");
    $stmt->execute([
        $data['nom'],
        $data['numero_employe'],
        $data['adresse'],
        $data['nas']
    ]);

    $employe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employe) {
        echo json_encode(["status" => "ok", "message" => "Identité confirmée", "employe" => $employe]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Identité non confirmée"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}
