<?php
session_start();
$accountCreated ="";

if(isset($_SESSION['account_created'])){
    $accountCreated = $_SESSION['account_created'];
    unset($_SESSION['account_created']);
}
$loginFailed = (isset($_SESSION['login failed'])) && $_SESSION['login failed'] == 1;
unset($_SESSION["login failed"]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <title>User Login</title>
    <link rel="stylesheet" href="css/scsu.css">
</head>

<body>
    <header class="scsu-header">
        <div class="scsu-header-content">

            <div class="scsu-brand">
                <img src="image/SCSU.png" alt="SCSU logo" class="scsu-logo">

                <div class="scsu-brand-text">
                    <h2>SCSU Appointment Scheduling System</h2>
                </div>
            </div>

        </div>
    </header>
    <main class="container login-container">
        <section class="card login-card">
            <h1>Login</h1>
            <?php if (!empty($accountCreated)): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($accountCreated); ?>
                </div>
            <?php endif; ?>
            <form action="account_login_authenticate.php" method="post">
                <div class="form-group">
                    <label for="text"> Username: </label>
                    <input type="text" name="username" id = "username" required> <br>
                    <label for="password"> Password: </label>
                    <input type="password" name="password" id = "password" required> <br>
                </div>
                    
                <button type="submit" class="primary-button">Submit</button>
                <br>
                <div class="create-account-link">
                    <p>Don't have an account?</p>
                    <a href="account_create.php">
                        Create an Account
                    </a>
                </div>
            </form>
        </section>
    </main>

            

</body>

</html>