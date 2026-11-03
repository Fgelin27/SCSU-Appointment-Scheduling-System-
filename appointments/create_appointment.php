
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'POST requests only.']));
}

require_once __DIR__ . '/../db.php';

$student = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$provider = filter_input(INPUT_POST, 'provider_id', FILTER_VALIDATE_INT);
$availability = filter_input(INPUT_POST, 'availability_id', FILTER_VALIDATE_INT);
$date = $_POST['appointment_date'] ?? '';
$start = $_POST['start_time'] ?? '';
$end = $_POST['end_time'] ?? '';

$d = DateTime::createFromFormat('!Y-m-d', $date);

if (
    !$student || !$provider || !$availability ||
    !$d || $d->format('Y-m-d') !== $date ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start) ||
    !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $end) ||
    $start >= $end
) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid appointment details.']));
}

try {
    $conn->begin_transaction();

    // Lock the availability record during booking.
    $check = $conn->prepare(
        "SELECT availability_id
         FROM availability_periods
         WHERE availability_id = ?
           AND provider_id = ?
           AND availability_date = ?
           AND start_time <= ?
           AND end_time >= ?
         FOR UPDATE"
    );

    $check->bind_param(
        'iisss',
        $availability,
        $provider,
        $date,
        $start,
        $end
    );

    $check->execute();

    if (!$check->get_result()->num_rows) {
        throw new RuntimeException('Appointment is outside provider availability.');
    }

    // Prevent provider or student scheduling conflicts.
    $conflict = $conn->prepare(
        "SELECT appointment_id
         FROM appointments
         WHERE appointment_date = ?
           AND status IN ('pending', 'confirmed')
           AND (
                (provider_id = ? AND start_time < ? AND end_time > ?)
                OR
                (student_id = ? AND start_time < ? AND end_time > ?)
           )
         LIMIT 1
         FOR UPDATE"
    );

    $conflict->bind_param(
        'sississ',
        $date,
        $provider,
        $end,
        $start,
        $student,
        $end,
        $start
    );

    $conflict->execute();

    if ($conflict->get_result()->num_rows) {
        throw new RuntimeException('Appointment conflicts with an existing booking.');
    }

    $insert = $conn->prepare(
        "INSERT INTO appointments
         (student_id, provider_id, availability_id,
          appointment_date, start_time, end_time)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $insert->bind_param(
        'iiisss',
        $student,
        $provider,
        $availability,
        $date,
        $start,
        $end
    );

    $insert->execute();
    $id = $conn->insert_id;

    $conn->commit();

    http_response_code(201);
    echo json_encode([
        'message' => 'Appointment created.',
        'appointment_id' => $id
    ]);

} catch (Throwable $e) {
    $conn->rollback();

    $status = $e instanceof RuntimeException ? 409 : 500;
    http_response_code($status);

    echo json_encode(['error' => $status === 409
        ? $e->getMessage()
        : 'Appointment creation failed.'
    ]);
}
