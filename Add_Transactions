<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.html');
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];

    // Fetch user ID
    $user_query = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    $user_data = mysqli_fetch_assoc($user_query);
    $user_id = $user_data['id'];

    // Fetch form data
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $category_input = trim($_POST['category']);
    $note = $_POST['note'];

    // Check if category exists (case-insensitive) and fetch correct casing
    $stmt = $conn->prepare("SELECT name FROM categories WHERE user_id = ? AND type = ? AND LOWER(name) = LOWER(?)");
    $stmt->bind_param("iss", $user_id, $type, $category_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $category = $row['name']; // Use correct casing from DB

        // Insert transaction
        $insert_stmt = $conn->prepare("INSERT INTO transactions (user_id, type, category, amount, note) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("issds", $user_id, $type, $category, $amount, $note);

        if ($insert_stmt->execute()) {
            echo "<script>alert('Transaction added successfully.'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Error adding transaction.'); window.location.href='dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Category does not exist. Please add it first in Manage Categories.'); window.location.href='manage_categories.php';</script>";
    }
}
?>
