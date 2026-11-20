<?php
require_once 'db_login.php';
$key = '0CVwf74NxScWWzwanssafFVf9dNdNTEt';
$conn = new mysqli($hn, $un, $pw, $db);
if($conn -> connect_error) die ("Fatal Error");

if(isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role'];

    $account_statement = "INSERT INTO account (Username, Password, First_name, Last_name, Role) VALUES ('$username', AES_ENCRYPT('$password', '$key'), '$first_name', '$last_name', '$role')";


    if($conn->query($account_statement)) {
        session_start();
        $_SESSION['account_created'] = "New account created successfully";

        header("Location: account_login.php");
        exit;
    } else {
        echo "Error: could not create account.";
    }
    $conn->close();
}
?>