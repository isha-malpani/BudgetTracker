<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    header('Location: login.html');
    exit();
}
$username = $_SESSION['username'];
$email = $_SESSION['email'];

include 'db.php';

// Get user ID
$user_query = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
$user_data = mysqli_fetch_assoc($user_query);
$user_id = $user_data['id'];

// Add Category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $type = $_POST['type'];
    $name = ucfirst(strtolower(trim($_POST['name'])));

    $stmt = $conn->prepare("SELECT * FROM categories WHERE user_id = ? AND type = ? AND LOWER(name) = LOWER(?)");
    $stmt->bind_param("iss", $user_id, $type, $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO categories (user_id, type, name) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $user_id, $type, $name);
        $insert->execute();
        $_SESSION['msg'] = "Category added successfully.";
    } else {
        $_SESSION['msg'] = "Category already exists.";
    }
    header("Location: manage_categories.php");
    exit();
}

// Delete Category
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM categories WHERE id = $id AND user_id = $user_id");
    $_SESSION['msg'] = "Category deleted successfully.";
    header("Location: manage_categories.php");
    exit();
}

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE user_id = $user_id ORDER BY type, name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Categories | Personal Budget Tracker</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #a18cd1, #fbc2eb);
        color: #333;
    }
    .header, .footer {
        background: #7b2cbf;
        color: white;
        text-align: center;
        padding: 20px;
    }
    .navbar {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        background: #9d4edd;
        padding: 10px;
    }
    .navbar a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px; /* uniform padding */
        font-size: 16px; /* consistent font size */
        line-height: 1.5; /* consistent vertical alignment */
        border-radius: 8px; /* consistent rounded corners */
        color: white;
        background: #c77dff;
        text-decoration: none;
        transition: background 0.3s, transform 0.2s;
        min-width: 120px; /* ensures consistent width on all pages */
    }

    .navbar a i {
        margin-right: 8px; /* consistent icon spacing */
    }

    .navbar a:hover {
        background: #7b2cbf;
        transform: translateY(-2px);
    }

    .dashboard {
        max-width: 900px;
        margin: 20px auto;
        padding: 0 15px;
    }
    .category-container {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
    }
    .category-container h2 {
        color: #fff;
        margin-bottom: 15px;
    }
    form {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    form select, form input {
        padding: 10px;
        border-radius: 6px;
        border: none;
        width: 180px;
    }
    form button {
        background: #8e6dfd;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s, transform 0.2s;
    }
    form button:hover {
        background: #7b2cbf;
        transform: translateY(-2px);
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(8px);
        border-radius: 8px;
        overflow: hidden;
    }
    th, td {
        padding: 12px;
        text-align: center;
        color: #000;
    }
    th {
        background: #7b2cbf;
        color: white;
    }
    tr:nth-child(even) {
        background: rgba(255,255,255,0.1);
    }
    .delete-btn {
        color: #dc3545;
        text-decoration: none;
        font-weight: 500;
    }
    .delete-btn:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<header class="header">
    <h1>Personal Budget Tracker</h1>
    <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
</header>
<nav class="navbar">
    <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
    <a href="overview.php"><i class="fas fa-chart-pie"></i> Overview</a>
    <a href="limit.php"><i class="fas fa-bullseye"></i> Limit</a>
    <a href="graph.php"><i class="fas fa-chart-line"></i> Graph</a>
    <a href="categories.php"><i class="fas fa-list"></i> Categories</a>
    <a href="manage_categories.php"><i class="fas fa-cog"></i> Manage</a>
</nav>

<main class="dashboard">
    <?php if (isset($_SESSION['msg'])): ?>
        <script>alert('<?php echo $_SESSION['msg']; ?>');</script>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>
    <div class="category-container">
        <h2>Add New Category</h2>
        <form method="POST">
            <select name="type" required>
                <option value="">Select Type</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
            <input type="text" name="name" placeholder="Category Name" required>
            <button type="submit" name="add_category">Add Category</button>
        </form>
        <h2>Existing Categories</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td><?php echo htmlspecialchars(ucfirst($row['type'])); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><a class="delete-btn" href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</main>
<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Isha
</footer>
</body>
</html>
