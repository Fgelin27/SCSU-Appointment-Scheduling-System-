
<?php
session_start();

header('Content-Type: application/json');

// Verify login
if (!isset($_SESSION['accountID'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Not authenticated.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'POST requests only.']));
}

require_once __DIR__ . '/db.php';

// Use the logged-in account
$provider = (int) $_SESSION['accountID'];

// Read FormData fields
$id = (int) ($_POST['availability_id'] ?? 0);
$date = $_POST['availability_date'] ?? '';
$start = $_POST['start_time'] ?? '';
$end = $_POST['end_time'] ?? '';

$dateObj = DateTime::createFromFormat('!Y-m-d', $date);

if (
    $id < 1 ||
    $provider < 1 ||
    !$dateObj ||
    $dateObj->format('Y-m-d') !== $date ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start) ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end) ||
    $start >= $end
) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid input.']));
}

try {
    // Check overlapping availability, excluding this record
    $check = $conn->prepare(
        'SELECT availability_id
         FROM availability_periods
         WHERE provider_id = ?
           AND availability_date = ?
           AND start_time < ?
           AND end_time > ?
           AND availability_id <> ?
         LIMIT 1'
    );

    $check->bind_param(
        'isssi',
        $provider,
        $date,
        $end,
        $start,
        $id
    );

    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        http_response_code(409);
        exit(json_encode([
            'error' => 'Updated period overlaps existing availability.'
        ]));
    }

    // Update record belonging to logged-in provider
    $stmt = $conn->prepare(
        'UPDATE availability_periods
         SET availability_date = ?,
             start_time = ?,
             end_time = ?
         WHERE availability_id = ?
           AND provider_id = ?'
    );

    $stmt->bind_param(
        'sssii',
        $date,
        $start,
        $end,
        $id,
        $provider
    );

    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $verify = $conn->prepare(
            'SELECT availability_id
             FROM availability_periods
             WHERE availability_id = ?
               AND provider_id = ?'
        );

        $verify->bind_param('ii', $id, $provider);
        $verify->execute();

        if (!$verify->get_result()->num_rows) {
            http_response_code(404);
            exit(json_encode([
                'error' => 'Availability record not found.'
            ]));
        }
    }

    echo json_encode([
        'message' => 'Availability updated successfully.'
    ]);

} catch (mysqli_sql_exception $e) {
    error_log('Update availability error: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Database operation failed.'
    ]);
}
