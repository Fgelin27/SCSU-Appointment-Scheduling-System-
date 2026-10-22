<?php
# Connect to mysql database (Requires an already created database with the name in db_login.php)
require_once 'db_login.php'; #db_login.php stores the database hostname, username, password, and db name
$key = '0CVwf74NxScWWzwanssafFVf9dNdNTEt'; #$key is used for encrypting the entered password
$conn = new mysqli($hn, $un, $pw, $db);
if($conn -> connect_error) die ("Fatal Error");

$query = "SELECT * FROM account";
$result = $conn -> query($query);
if(!$result) die ("Fatal Error");
$rows = $result -> num_rows;
if($_SERVER["REQUEST_METHOD"] == "POST") {
    # Get username and password from the form in account_login.php
    $username = $_POST['username'];
    $password = $_POST['password'];

    # Prepares statement for mysql, retrieves all accounts where username and password match (Two accounts shouldn't share same user and pass)
    $login_statement=$conn->prepare("SELECT * FROM account WHERE Username = ? AND Password = AES_ENCRYPT(?,?);");
    $login_statement->bind_param("sss", $username, $password, $key);
    $login_statement->execute();
    $statement_result = $login_statement->get_result();

    # If there is a row in the result, then an account matches the entered username and password
    if($statement_result->num_rows > 0) {
        # Store the account's info to the session so other pages can access it
        $result_row = mysqli_fetch_assoc($statement_result);
        $_SESSION['accountID'] = $result_row['AccountID'];
        $_SESSION['firstName'] = $result_row['First_name'];
        $_SESSION['lastName'] = $result_row['Last_name'];
        echo "Welcome " . $_SESSION['firstName'] . " " .$_SESSION['lastName'] . "!";    # After main page is created, replace with header redirect
        # header("Location: ___.php"); # Redirect after successful login                # and display welcome in main
    } else {
        echo "An invalid username or password was entered.";
    }
    /* Debug messages to test reading data from form and storing it in session
    echo "$username";
    echo "$password";
    echo "$key";
    echo $statement_result->num_rows;
    echo $_SESSION['accountID'];
    echo $_SESSION['firstName'];
    echo $_SESSION['lastName'];
    */
}
?>