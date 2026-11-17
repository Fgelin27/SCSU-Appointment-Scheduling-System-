
<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	http_response_code(405);
	exit(json_encode(['error' => 'GET requests only.']));
}

if (
	empty($_SESSION['accountID']) ||
	strtolower(trim($_SESSION['role'] ?? '')) !== 'student'
) {
	http_response_code(401);
	exit(json_encode(['error' => 'Sign in with a student account to view providers.']));
}

require_once __DIR__ . '/../db.php';

try {
	$providers = $conn->query(
		"SELECT AccountID, First_name, Last_name, Role
		 FROM account
		 WHERE Role IS NOT NULL AND LOWER(Role) <> 'student'
		 ORDER BY Last_name, First_name"
	)->fetch_all(MYSQLI_ASSOC);

	echo json_encode(['providers' => $providers]);
} catch (mysqli_sql_exception $e) {
	http_response_code(500);
	echo json_encode(['error' => 'Could not retrieve providers.']);
}
