
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST requests only.']);
    exit;
}

require_once __DIR__ . '/db.php';

$providerId = filter_input(INPUT_POST, 'provider_id', FILTER_VALIDATE_INT);
$date = $_POST['availability_date'] ?? '';
$start = $_POST['start_time'] ?? '';
$end = $_POST['end_time'] ?? '';

$dateObj = DateTime::createFromFormat('!Y-m-d', $date);
$dateValid = $dateObj && $dateObj->format('Y-m-d') === $date;

$timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';

if (
    !$providerId || $providerId < 1 ||
    !$dateValid ||
    !preg_match($timePattern, $start) ||
    !preg_match($timePattern, $end) ||
    $start >= $end
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid provider, date, or time range.']);
    exit;
}

try {
    // Check for overlapping availability for this provider.
    $check = $conn->prepare(
        'SELECT availability_id
         FROM availability_periods
         WHERE provider_id = ?
           AND availability_date = ?
           AND start_time < ?
           AND end_time > ?
         LIMIT 1'
    );

    $check->bind_param('isss', $providerId, $date, $end, $start);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode([
            'error' => 'This period overlaps existing availability.'
        ]);
        exit;
    }

    $insert = $conn->prepare(
        'INSERT INTO availability_periods
         (provider_id, availability_date, start_time, end_time)
         VALUES (?, ?, ?, ?)'
    );

    $insert->bind_param('isss', $providerId, $date, $start, $end);
    $insert->execute();

    http_response_code(201);
    echo json_encode([
        'message' => 'Availability saved.',
        'availability_id' => $conn->insert_id
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database operation failed.']);
}
