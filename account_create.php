<html>
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
    <main class="container creator-container">
        
        <section class="card creator-card">

            <h1>Account Creator</h1>
            <form action="account_create_get.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input name="username" id="username" type="text"> <br>

                    <label for="password">Password:</label>
                    <input name="password" id="password" type="password" required>  <br>

                    <label for="first_name">First name:</label>
                    <input name="first_name" id="first_name" type="text"> <br>

                    <label for="last_name">Last name:</label>
                    <input name="last_name" id="last_name" type="text"> <br>

                    <label for="role">Role:</label>
                    <select name="role" id="role">
                        <option value="Student">Student</option>
                        <option value="Professor">Professor</option>
                        <option value="Advisor">Advisor</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Tutor">Tutor</option>
                    </select> <br>
                </div>

                <button type="submit" class="primary-button">Submit</button>
            </form>
        </section>
    </main>

    

</body>
</html>

