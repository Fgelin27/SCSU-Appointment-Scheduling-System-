<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles.css">
<meta charset="UTF-8">
<title>User Login</title>
</head>

<body>
    <h1>
        SCSU Appointment Scheduling System
    </h1>
    <h2>
        <form action="account_login_authenticate.php" method="post">
        <label for="text"> Username </label>
        <input type="text" name="username" id = "username" required> <br>
        <label for="password"> Password </label>
        <input type="password" name="password" id = "password" required> <br>
        <button type="submit">Submit</button>
        </form>

        <?php
            session_start();
            if(isset($_SESSION['login failed'])) {
                if($_SESSION['login failed'] == 1) {
                    echo "Login Failed";
                }
            }

        ?>
    </h2>

</body>

</html>