<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_sql = "SELECT * FROM users WHERE email='$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('An account with this email already exists. Please login.'); window.location.href='login.php';</script>";
        exit();
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
            exit();
        } else {
            echo "<script>alert('Database error occurred. Please try again.'); window.location.href='signup.php';</script>";
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Signup | Personal Budget Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #a18cd1, #fbc2eb);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
    width: 90%;
    max-width: 400px;
}
.container h1 {
    color: #fff;
    margin-bottom: 10px;
}
.container h2 {
    color: #fff;
    margin-bottom: 20px;
}
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
form input {
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
}
form button {
    padding: 12px;
    background: #8e6dfd;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}
form button:hover {
    background: #7b2cbf;
    transform: translateY(-2px);
}
form p {
    color: #fff;
}
form p a {
    color: #007BFF;
    text-decoration: none;
}
form p a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="container">
    <h1>Personal Budget Tracker</h1>
    <h2>Sign Up</h2>
    <form action="signup.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Sign Up</button>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>
</body>
</html>
