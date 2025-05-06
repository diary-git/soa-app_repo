<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once "db_auth.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['num_emp'])) {
            $num_emp_sec = htmlspecialchars($_GET['num_emp']);
            $stmt = $auth_db->prepare("SELECT * FROM employes WHERE numero_employe = ?");
            $stmt->execute([$num_emp_sec]);

            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            break;
        }
        if (isset($_GET['id'])) {
            $id_sec = htmlspecialchars($_GET['id']);
            $stmt = $auth_db->prepare("SELECT * FROM employes WHERE id = ?");
            $stmt->execute([$id_sec]);

            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $auth_db->query("SELECT * FROM employes");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO employes (nom, numero_employe, adresse, nas, email) VALUES (?, ?, ?, ?,?)";
        $stmt = $auth_db->prepare($sql);
        $stmt->execute([
            htmlspecialchars($data['nom']),
            htmlspecialchars($data['numero_employe']),
            htmlspecialchars($data['adresse']),
            htmlspecialchars($data['nas']),
            htmlspecialchars($data['email'])
        ]);

        echo json_encode(["status" => "ok", "message" => "Employé ajouté"]);
        break;

    case 'PUT':
        $id_emp = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents("php://input"), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(["status" => "error", "message" => "Données JSON invalides"]);
            http_response_code(400);
            break;
        }
        if (!$id_emp) {
            echo json_encode(["status" => "error", "message" => "ID manquant"]);
            break;
        }

        $sql = "UPDATE employes SET nom = ?, numero_employe = ?, adresse = ?, nas = ?, email = ? WHERE id = ?";
        $stmt = $auth_db->prepare($sql);
        $stmt->execute([
            htmlspecialchars($data['nom']),
            htmlspecialchars($data['numero_employe']),
            htmlspecialchars($data['adresse']),
            htmlspecialchars($data['nas']),
            htmlspecialchars($data['email']),
            htmlspecialchars($id_emp)
        ]);
        echo json_encode(["status" => "ok", "message" => "Employé mis à jour"]);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        $idd_sec = htmlspecialchars($id);
        if ($id) {
            $stmt = $auth_db->prepare("DELETE FROM employes WHERE id = ?");
            $stmt->execute([$idd_sec]);

            echo json_encode(["status" => "ok", "message" => "Employé supprimé"]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID manquant"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Méthode non supportée"]);
}
