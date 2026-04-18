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

// Totals
$income_result = mysqli_query($conn, "SELECT SUM(amount) AS total_income FROM transactions WHERE user_id=$user_id AND type='income'");
$income_row = mysqli_fetch_assoc($income_result);
$total_income = $income_row['total_income'] ?? 0;

$expense_result = mysqli_query($conn, "SELECT SUM(amount) AS total_expense FROM transactions WHERE user_id=$user_id AND type='expense'");
$expense_row = mysqli_fetch_assoc($expense_result);
$total_expense = $expense_row['total_expense'] ?? 0;

$remaining = $total_income - $total_expense;

// Expenses by Category
$category_result = mysqli_query($conn, "SELECT category, SUM(amount) AS total FROM transactions WHERE user_id=$user_id AND type='expense' GROUP BY category");

$categories = [];
$amounts = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row['category'];
    $amounts[] = $row['total'];
}
$categories[] = "Remaining";
$amounts[] = $remaining;

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
<title>Overview | Personal Budget Tracker</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
html, body {
    height: 100%;
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #a18cd1, #fbc2eb);
    color: #333;
    display: flex;
    flex-direction: column;
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
    padding: 10px 20px;
    font-size: 16px;
    line-height: 1.5;
    border-radius: 8px;
    color: white;
    background: #c77dff;
    text-decoration: none;
    transition: background 0.3s, transform 0.2s;
    min-width: 120px;
}

.navbar a i {
    margin-right: 8px;
}

.navbar a:hover {
    background: #7b2cbf;
    transform: translateY(-2px);
}

main {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.overview-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 1000px;
    width: 90%;
}

.overview-chart {
    flex: 0 0 300px;
}

.legend-list {
    flex: 0 0 300px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    padding: 8px 12px;
}

.color-box {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    flex-shrink: 0;
}

.legend-label {
    flex: 1;
    color: #000;
}

.legend-amount {
    color: #007BFF;
    font-weight: 500;
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
<main>
    <section class="overview-container">
        <div class="overview-chart">
            <canvas id="budgetPieChart" width="300" height="300"></canvas>
        </div>
        <div class="legend-list" id="legendList"></div>
    </section>
</main>
<script>
const categories = <?php echo json_encode($categories); ?>;
const amounts = <?php echo json_encode($amounts); ?>;
const colors = ['#007BFF', '#28A745', '#FFC107', '#DC3545', '#6610f2', '#20c997', '#fd7e14', '#6c757d', '#17a2b8', '#343a40', '#FF6384'];

const ctx = document.getElementById('budgetPieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: categories,
        datasets: [{
            data: amounts,
            backgroundColor: colors.slice(0, categories.length)
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        return `${label}: ₹${value.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
                    }
                }
            }
        }
    }
});

const legendList = document.getElementById('legendList');
categories.forEach((cat, index) => {
    const amount = amounts[index].toLocaleString('en-IN', {minimumFractionDigits: 2});
    const color = colors[index % colors.length];
    const div = document.createElement('div');
    div.className = 'legend-item';
    div.innerHTML = `
        <div class="color-box" style="background:${color}"></div>
        <div class="legend-label">${cat}</div>
        <div class="legend-amount">₹${amount}</div>
    `;
    legendList.appendChild(div);
});
</script>
<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Krishna
</footer>
</body>
</html>
