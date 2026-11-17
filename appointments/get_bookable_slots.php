
<?php
header('Content-Type: application/json');
session_start();

if (
    empty($_SESSION['accountID']) ||
    strtolower(trim($_SESSION['role'] ?? '')) !== 'student'
) {
    http_response_code(401);
    exit(json_encode(['error' => 'Sign in with a student account to view appointments.']));
}

$provider = filter_input(INPUT_GET, 'provider_id', FILTER_VALIDATE_INT);
$date = $_GET['date'] ?? '';
$dateObject = DateTime::createFromFormat('!Y-m-d', $date);

if (!$provider || !$dateObject || $dateObject->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
    http_response_code(400);
    exit(json_encode(['error' => 'Choose a valid provider and date.']));
}

require_once __DIR__ . '/../db.php';

try {
    $availabilityQuery = $conn->prepare(
                "SELECT availability.availability_id, availability.start_time, availability.end_time
                 FROM availability_periods AS availability
                 JOIN account AS provider_account
                     ON provider_account.AccountID = availability.provider_id
                 WHERE availability.provider_id = ?
                     AND availability.availability_date = ?
                     AND LOWER(provider_account.Role) <> 'student'
                 ORDER BY availability.start_time"
    );
    $availabilityQuery->bind_param('is', $provider, $date);
    $availabilityQuery->execute();
    $availability = $availabilityQuery->get_result()->fetch_all(MYSQLI_ASSOC);

    $appointmentsQuery = $conn->prepare(
        "SELECT start_time, end_time
         FROM appointments
         WHERE provider_id = ? AND appointment_date = ?
           AND status IN ('pending', 'confirmed')"
    );
    $appointmentsQuery->bind_param('is', $provider, $date);
    $appointmentsQuery->execute();
    $appointments = $appointmentsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

    $slots = [];
    foreach ($availability as $period) {
        $start = new DateTime($period['start_time']);
        $end = new DateTime($period['end_time']);

        while ((clone $start)->modify('+30 minutes') <= $end) {
            $slotEnd = (clone $start)->modify('+30 minutes');
            $slotStartText = $start->format('H:i:s');
            $slotEndText = $slotEnd->format('H:i:s');
            $isBooked = $date === date('Y-m-d') && $start <= new DateTime();

            foreach ($appointments as $appointment) {
                if ($appointment['start_time'] < $slotEndText && $appointment['end_time'] > $slotStartText) {
                    $isBooked = true;
                    break;
                }
            }

            if (!$isBooked) {
                $slots[] = [
                    'availability_id' => (int) $period['availability_id'],
                    'start_time' => $start->format('H:i'),
                    'end_time' => $slotEnd->format('H:i')
                ];
            }

            $start = $slotEnd;
        }
    }

    echo json_encode(['provider_id' => $provider, 'date' => $date, 'slots' => $slots]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not retrieve appointment times.']);
}