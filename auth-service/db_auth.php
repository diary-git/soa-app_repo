<?php
$host = 'localhost';
$dbname = 'soa_auth_service_db';
$user = 'root';
$password = '';

try {
    $auth_db = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $auth_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connexion échouée: " . $e->getMessage()]);
    exit;
}
