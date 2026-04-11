<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = "localhost";
$dbname = "u748897370_mon_stock_db";
$username = "u748897370_baptisterouxel";
$password = "B@ptiste_22";


try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Connexion BDD impossible",
        "details" => $e->getMessage()
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

function getJsonBody() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true);
}

if ($action === 'get_stock_pro') {
    try {
        $stmt = $pdo->query("SELECT * FROM stock_pro ORDER BY updated_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Erreur lecture stock pro",
            "details" => $e->getMessage()
        ]);
    }
    exit;
}

if ($action === 'save_product_pro') {
    $data = getJsonBody();

    if (!$data || !isset($data['id']) || !isset($data['nom'])) {
        echo json_encode([
            "success" => false,
            "error" => "Données invalides"
        ]);
        exit;
    }

    try {
        $sql = "
            INSERT INTO stock_pro (
                id, nom, categorie, unite, quantite_initiale, quantite_restante,
                seuil_alerte, prix_total_achat, prix_unitaire, fournisseur,
                date_achat, date_peremption, note
            ) VALUES (
                :id, :nom, :categorie, :unite, :quantite_initiale, :quantite_restante,
                :seuil_alerte, :prix_total_achat, :prix_unitaire, :fournisseur,
                :date_achat, :date_peremption, :note
            )
            ON DUPLICATE KEY UPDATE
                nom = VALUES(nom),
                categorie = VALUES(categorie),
                unite = VALUES(unite),
                quantite_initiale = VALUES(quantite_initiale),
                quantite_restante = VALUES(quantite_restante),
                seuil_alerte = VALUES(seuil_alerte),
                prix_total_achat = VALUES(prix_total_achat),
                prix_unitaire = VALUES(prix_unitaire),
                fournisseur = VALUES(fournisseur),
                date_achat = VALUES(date_achat),
                date_peremption = VALUES(date_peremption),
                note = VALUES(note)
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $data['id'],
            ':nom' => $data['nom'],
            ':categorie' => $data['categorie'] ?? 'Autres',
            ':unite' => $data['unite'] ?? 'unite',
            ':quantite_initiale' => $data['quantite_initiale'] ?? 0,
            ':quantite_restante' => $data['quantite_restante'] ?? 0,
            ':seuil_alerte' => $data['seuil_alerte'] ?? 0,
            ':prix_total_achat' => $data['prix_total_achat'] ?? 0,
            ':prix_unitaire' => $data['prix_unitaire'] ?? 0,
            ':fournisseur' => $data['fournisseur'] ?? '',
            ':date_achat' => !empty($data['date_achat']) ? $data['date_achat'] : null,
            ':date_peremption' => !empty($data['date_peremption']) ? $data['date_peremption'] : null,
            ':note' => $data['note'] ?? null
        ]);

        echo json_encode([
            "success" => true
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Erreur sauvegarde produit pro",
            "details" => $e->getMessage()
        ]);
    }
    exit;
}

if ($action === 'delete_product_pro') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode([
            "success" => false,
            "error" => "ID manquant"
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM stock_pro WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode([
            "success" => true
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Erreur suppression produit pro",
            "details" => $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    "success" => false,
    "error" => "Action inconnue"
]);
