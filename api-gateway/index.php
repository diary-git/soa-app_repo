<?php
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace("/soa-app/api-gateway", "", $uri);


$routes = [
    // AUTH-SERVICE (validation d'identité & gestion des employés)
    'POST' => [
        '/valider-identite' => '../auth-service/validerIdentite.php',
        '/employes' => '../auth-service/gererEmploye.php',
        '/envoyer-mail' => '../notification-service/envoyerMail.php',
    ],
    'GET' => [
        '/employes' => '../auth-service/gererEmploye.php',
        '/beneficiaires' => '../dossier-service/getBeneficiaire.php',
    ],
    'PUT' => [
        '/employes' => '../auth-service/gererEmploye.php',
        '/beneficiaires/update' => '../dossier-service/updateBeneficiaire.php',
    ],
    'DELETE' => [
        '/employes' => '../auth-service/gererEmploye.php',
    ]
];

// Route vers le bon fichier
if (isset($routes[$method][$uri])) {
    require $routes[$method][$uri];
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Route non trouvée : $method $uri"]);
}
