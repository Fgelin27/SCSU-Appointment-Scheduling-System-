
<?php
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

$providerId = filter_input(INPUT_GET, 'provider_id', FILTER_VALIDATE_INT);
$date = $_GET['date'] ?? '';

$dateObj = DateTime::createFromFormat('!Y-m-d', $date);
$dateValid = $dateObj && $dateObj->format('Y-m-d') === $date;

if (!$providerId || $providerId < 1 || !$dateValid) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Provide a valid provider_id and date (YYYY-MM-DD).'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare(
        'SELECT availability_id, provider_id, availability_date,
                start_time, end_time
         FROM availability_periods
         WHERE provider_id = ?
           AND availability_date = ?
         ORDER BY start_time'
    );

    $stmt->bind_param('is', $providerId, $date);
    $stmt->execute();

    $result = $stmt->get_result();
    $availability = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'provider_id' => $providerId,
        'date' => $date,
        'availability' => $availability
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not retrieve availability.']);
}
