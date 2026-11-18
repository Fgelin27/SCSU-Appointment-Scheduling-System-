
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['accountID'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Not authenticated.']));
}

require_once __DIR__ . '/db.php';

$providerId = (int) $_SESSION['accountID'];

try {
    $stmt = $conn->prepare(
        'SELECT availability_id, provider_id, availability_date,
                start_time, end_time
         FROM availability_periods
         WHERE provider_id = ?
         ORDER BY availability_date, start_time'
    );

    $stmt->bind_param('i', $providerId);
    $stmt->execute();

    $result = $stmt->get_result();

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Could not retrieve availability.']);
}
