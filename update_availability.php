
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'POST requests only.']));
}

require_once __DIR__ . '/db.php';

$id = filter_input(INPUT_POST, 'availability_id', FILTER_VALIDATE_INT);
$provider = filter_input(INPUT_POST, 'provider_id', FILTER_VALIDATE_INT);
$date = $_POST['availability_date'] ?? '';
$start = $_POST['start_time'] ?? '';
$end = $_POST['end_time'] ?? '';

$d = DateTime::createFromFormat('!Y-m-d', $date);

if (
    !$id || !$provider ||
    !$d || $d->format('Y-m-d') !== $date ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start) ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end) ||
    $start >= $end
) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid input.']));
}

try {
    $check = $conn->prepare(
        'SELECT availability_id FROM availability_periods
         WHERE provider_id = ? AND availability_date = ?
         AND start_time < ? AND end_time > ?
         AND availability_id <> ? LIMIT 1'
    );
    $check->bind_param('isssi', $provider, $date, $end, $start, $id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        http_response_code(409);
        exit(json_encode(['error' => 'Updated period overlaps availability.']));
    }

    $stmt = $conn->prepare(
        'UPDATE availability_periods
         SET availability_date = ?, start_time = ?, end_time = ?
         WHERE availability_id = ? AND provider_id = ?'
    );
    $stmt->bind_param('sssii', $date, $start, $end, $id, $provider);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $verify = $conn->prepare(
            'SELECT availability_id FROM availability_periods
             WHERE availability_id = ? AND provider_id = ?'
        );
        $verify->bind_param('ii', $id, $provider);
        $verify->execute();

        if (!$verify->get_result()->num_rows) {
            http_response_code(404);
            exit(json_encode(['error' => 'Availability record not found.']));
        }
    }

    echo json_encode(['message' => 'Availability updated.']);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database operation failed.']);
}
