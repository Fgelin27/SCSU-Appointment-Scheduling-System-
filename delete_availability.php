
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'POST requests only.']));
}

require_once __DIR__ . '/db.php';

$id = filter_input(INPUT_POST, 'availability_id', FILTER_VALIDATE_INT);
$provider = filter_input(INPUT_POST, 'provider_id', FILTER_VALIDATE_INT);

if (!$id || !$provider) {
    http_response_code(400);
    exit(json_encode(['error' => 'Valid IDs are required.']));
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
        echo json_encode(['error' => 'Availability record not found.']);
        exit;
    }

    echo json_encode(['message' => 'Availability deleted.']);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database operation failed.']);
}
