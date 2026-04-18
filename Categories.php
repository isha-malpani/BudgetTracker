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

// Fetch user categories
$income_categories = [];
$expense_categories = [];

$res = mysqli_query($conn, "SELECT name FROM categories WHERE user_id=$user_id AND type='income'");
while ($row = mysqli_fetch_assoc($res)) {
    $income_categories[] = $row['name'];
}

$res = mysqli_query($conn, "SELECT name FROM categories WHERE user_id=$user_id AND type='expense'");
while ($row = mysqli_fetch_assoc($res)) {
    $expense_categories[] = $row['name'];
}

// Get total per category
function getCategoryTotal($conn, $user_id, $type, $category) {
    $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id=? AND type=? AND LOWER(category)=LOWER(?)");
    $stmt->bind_param("iss", $user_id, $type, $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Format INR
function formatIndianCurrency($amount) {
    $amount = number_format($amount, 2, '.', '');
    $exploded = explode('.', $amount);
    $decimal = $exploded[1];
    $num = $exploded[0];
    $lastThree = substr($num, -3);
    $restUnits = substr($num, 0, -3);
    if ($restUnits != '') {
        $lastThree = ',' . $lastThree;
    }
    $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
    return '₹' . $restUnits . $lastThree . '.' . $decimal;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories | Personal Budget Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #a18cd1, #fbc2eb);
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        .header, .footer {
            background: #7b2cbf;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .navbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            background: #9d4edd;
            padding: 10px;
            gap: 10px;
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
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }
        .category-section {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .category-section h2 {
            text-align: center;
            margin-bottom: 15px;
            color: white;
        }
        .category-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .category-card {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(6px);
            border-radius: 10px;
            padding: 10px 15px;
            width: 140px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .category-card:hover {
            transform: translateY(-4px);
        }
        .category-card p {
            font-weight: 500;
            color: #000; /* black category name */
            margin: 5px 0;
        }
        .category-amount {
            font-weight: bold;
            color: #007BFF; /* blue amount */
        }
        .footer {
            font-size: 14px;
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
    <section class="category-section">
        <h2>Income Categories</h2>
        <div class="category-list">
            <?php foreach ($income_categories as $category): 
                $amount = getCategoryTotal($conn, $user_id, 'income', $category);
                if ($amount > 0): ?>
                    <div class="category-card">
                        <p><?php echo htmlspecialchars($category); ?></p>
                        <div class="category-amount"><?php echo formatIndianCurrency($amount); ?></div>
                    </div>
                <?php endif; endforeach; ?>
        </div>
    </section>

    <section class="category-section">
        <h2>Expense Categories</h2>
        <div class="category-list">
            <?php foreach ($expense_categories as $category): 
                $amount = getCategoryTotal($conn, $user_id, 'expense', $category);
                if ($amount > 0): ?>
                    <div class="category-card">
                        <p><?php echo htmlspecialchars($category); ?></p>
                        <div class="category-amount"><?php echo formatIndianCurrency($amount); ?></div>
                    </div>
                <?php endif; endforeach; ?>
        </div>
    </section>
</main>

<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Krishna
</footer>
</body>
</html>
