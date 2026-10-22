<html>
    <header> Account Creator
    </header> <br>
<body>

<form action="account_create_get.php" method="post">
    <label for="username">Username:</label>
    <input name="username" id="username" type="text"> <br>

    <label for="password">Password:</label>
    <input name="password" id="password" type="text"> <br>

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
    <button type="submit">Submit</button>
</form>

</body>
</html>
