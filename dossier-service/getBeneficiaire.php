<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db_dossier.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $employe_id = $_GET['employe_id'] ?? null;

    if (!$employe_id) {
        http_response_code(400);
        // echo json_encode(["status" => "error", "message" => "ID employé manquant"]);
        try {
            $stmt = $dossier_db->query("SELECT * FROM beneficiaires");
            $beneficiaire = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($beneficiaire);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erreur BD : " . $e->getMessage()]);
        }
        exit;
    }

    try {
        $stmt = $dossier_db->prepare("SELECT * FROM beneficiaires WHERE employe_id = ? ORDER BY employe_id ASC");
        $stmt->execute([$employe_id]);
        $beneficiaire = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "exists" => !!$beneficiaire,
            "beneficiaire" => $beneficiaire ? $beneficiaire['nom'] : null
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erreur BD : " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
}
