<?php
$host = 'localhost';
$dbname = 'soa_dossier_service_db';
$user = 'root';
$password = '';

try {
    $dossier_db = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $dossier_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connexion échouée: " . $e->getMessage()]);
    exit;
}
