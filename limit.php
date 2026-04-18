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

// Add/Update Limit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_limit'])) {
    $category = ucfirst(strtolower(trim($_POST['category'])));
    $limit_amount = $_POST['limit_amount'];

    $stmt = $conn->prepare("SELECT * FROM limits WHERE user_id=? AND category=?");
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO limits (user_id, category, limit_amount) VALUES (?, ?, ?)");
        $insert->bind_param("isd", $user_id, $category, $limit_amount);
        $insert->execute();
    } else {
        $update = $conn->prepare("UPDATE limits SET limit_amount=? WHERE user_id=? AND category=?");
        $update->bind_param("dis", $limit_amount, $user_id, $category);
        $update->execute();
    }
    $_SESSION['msg'] = "Limit set successfully.";
    header("Location: limit.php");
    exit();
}

// Fetch limits
$limits_query = mysqli_query($conn, "SELECT * FROM limits WHERE user_id=$user_id ORDER BY category ASC");
$limits = [];
while ($row = mysqli_fetch_assoc($limits_query)) {
    $limits[] = $row;
}

function getSpent($conn, $user_id, $category) {
    $stmt = $conn->prepare("SELECT SUM(amount) AS spent FROM transactions WHERE user_id=? AND type='expense' AND LOWER(category)=LOWER(?)");
    $stmt->bind_param("is", $user_id, $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['spent'] ?? 0;
}

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
    <title>Limit | Personal Budget Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a18cd1, #fbc2eb);
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
        .limit-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .limit-container h2 { color: #fff; margin-bottom: 15px; }
        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        form input {
            padding: 10px;
            border-radius: 6px;
            border: none;
            width: 180px;
        }
        button {
            background: #8e6dfd;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s, transform 0.2s;
        }
        button:hover {
            background: #7b2cbf;
            transform: translateY(-2px);
        }
        .limit-item {
            position: relative;
            background: #fff;
            border-radius: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            margin: 15px 0;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .limit-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .progress-fill {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            border-radius: 50px;
            z-index: 1;
            transition: width 0.5s ease;
        }
        .limit-text {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
            z-index: 2;
        }
        .limit-text span {
            color: #000; font-weight: 500;
        }
        .amount {
            color: #007BFF; font-weight: 600;
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
    <div class="limit-container">
        <?php if (isset($_SESSION['msg'])): ?>
            <script>alert('<?php echo $_SESSION['msg']; ?>');</script>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>
        <h2>Set Limit for Expense Categories</h2>
        <form method="POST">
            <input type="text" name="category" placeholder="Category (e.g., Food)" required>
            <input type="number" step="0.01" name="limit_amount" placeholder="Limit Amount (₹)" required>
            <button type="submit" name="set_limit">Set Limit</button>
        </form>

        <?php foreach ($limits as $limit):
            $category = $limit['category'];
            $limit_amount = $limit['limit_amount'];
            $spent = getSpent($conn, $user_id, $category);
            $remaining = $limit_amount - $spent;
            $percent = $limit_amount > 0 ? ($spent / $limit_amount) * 100 : 0;
            $color = $percent < 60 ? '#28a745' : ($percent < 90 ? '#ffc107' : '#dc3545');
        ?>
        <div class="limit-item">
            <div class="progress-fill" style="width: <?php echo min($percent, 100); ?>%; background: <?php echo $color; ?>;"></div>
            <div class="limit-text">
                <span><?php echo htmlspecialchars($category); ?></span>
                <span class="amount">Limit: <?php echo formatIndianCurrency($limit_amount); ?></span>
                <span class="amount">Spent: <?php echo formatIndianCurrency($spent); ?></span>
                <span class="amount">Remaining: <?php echo formatIndianCurrency($remaining); ?></span>
                <span><?php echo round($percent); ?>%</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Krishna
</footer>
</body>
</html>
