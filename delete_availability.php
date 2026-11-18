
<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['accountID'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Not authenticated.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'POST requests only.']));
}

require_once __DIR__ . '/db.php';

$id = filter_input(INPUT_POST, 'availability_id', FILTER_VALIDATE_INT);

// Always use the logged-in account
$provider = (int) $_SESSION['accountID'];

if (!$id || $id < 1) {
    http_response_code(400);
    exit(json_encode(['error' => 'Valid availability ID is required.']));
}

try {
    $stmt = $conn->prepare(
        'DELETE FROM availability_periods
         WHERE availability_id = ? AND provider_id = ?'
    );

    $stmt->bind_param('ii', $id, $provider);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        exit(json_encode(['error' => 'Availability record not found.']));
    }

    echo json_encode(['message' => 'Availability deleted.']);

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database operation failed.']);
}
