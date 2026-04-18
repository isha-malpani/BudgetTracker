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

// Get totals
$income_result = mysqli_query($conn, "SELECT SUM(amount) AS total_income FROM transactions WHERE user_id=$user_id AND type='income'");
$income_row = mysqli_fetch_assoc($income_result);
$total_income = $income_row['total_income'] ?? 0;

$expense_result = mysqli_query($conn, "SELECT SUM(amount) AS total_expense FROM transactions WHERE user_id=$user_id AND type='expense'");
$expense_row = mysqli_fetch_assoc($expense_result);
$total_expense = $expense_row['total_expense'] ?? 0;

$remaining_budget = $total_income - $total_expense;

// INR format
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
    <title>Dashboard | Personal Budget Tracker</title>
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
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            width: 220px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .card h2 {
            margin: 10px 0;
            font-size: 18px;
            color: #000;
        }
        .card p {
            font-size: 20px;
            font-weight: bold;
            color: #007BFF;
        }
        .add-transaction {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .add-transaction h2 {
            color: #fff;
            margin-bottom: 15px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        form select, form input {
            padding: 10px;
            border-radius: 6px;
            border: none;
            width: 80%;
            max-width: 300px;
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
    <section class="cards">
        <div class="card">
            <h2>Total Income</h2>
            <p><?php echo formatIndianCurrency($total_income); ?></p>
        </div>
        <div class="card">
            <h2>Total Expenses</h2>
            <p><?php echo formatIndianCurrency($total_expense); ?></p>
        </div>
        <div class="card">
            <h2>Remaining Budget</h2>
            <p><?php echo formatIndianCurrency($remaining_budget); ?></p>
        </div>
    </section>

    <section class="add-transaction">
        <h2>Add Transaction</h2>
        <form action="add_transaction.php" method="POST">
            <select name="type" required>
                <option value="">Select Type</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
            <input type="number" name="amount" placeholder="Amount" step="0.01" required>
            <input type="text" name="category" placeholder="Category (e.g., Salary, Food)" required>
            <input type="text" name="note" placeholder="Note (optional)">
            <button type="submit">Add Transaction</button>
        </form>
    </section>
</main>

<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Krishna
</footer>
</body>
</html>
