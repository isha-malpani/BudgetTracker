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

// Prepare monthly income & expenses
$months = [];
$income_data = [];
$expense_data = [];
for ($i = 1; $i <= 12; $i++) {
    $months[] = date('M', mktime(0, 0, 0, $i, 1));

    $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id=? AND type='income' AND MONTH(date)=?");
    $stmt->bind_param("ii", $user_id, $i);
    $stmt->execute();
    $result = $stmt->get_result();
    $income_data[] = ($row = $result->fetch_assoc()) ? $row['total'] ?? 0 : 0;

    $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id=? AND type='expense' AND MONTH(date)=?");
    $stmt->bind_param("ii", $user_id, $i);
    $stmt->execute();
    $result = $stmt->get_result();
    $expense_data[] = ($row = $result->fetch_assoc()) ? $row['total'] ?? 0 : 0;
}

// Top 5 categories
$top_categories = [];
$cat_query = mysqli_query($conn, "SELECT category FROM transactions WHERE user_id=$user_id AND type='expense' GROUP BY category ORDER BY SUM(amount) DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($cat_query)) {
    $top_categories[] = $row['category'];
}

// Category-wise monthly data
$category_monthly_data = [];
foreach ($top_categories as $category) {
    $data = [];
    for ($i = 1; $i <= 12; $i++) {
        $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id=? AND type='expense' AND category=? AND MONTH(date)=?");
        $stmt->bind_param("isi", $user_id, $category, $i);
        $stmt->execute();
        $result = $stmt->get_result();
        $data[] = ($row = $result->fetch_assoc()) ? $row['total'] ?? 0 : 0;
    }
    $category_monthly_data[$category] = $data;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Graph | Personal Budget Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 15px;
        }
        .graph-container {
        height: auto;
        max-width: 1200px;
        margin: 20px auto;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
        }
        canvas {
            width: 100%;
            max-width: 1200px; /* adjust to your desired size */
            height: auto;
            margin: 20px auto;
            display: block;
        }
        h2 {
            color: #000;
            margin-bottom: 10px;
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
    <div class="graph-container">
        <h2>Income vs Expense (Monthly)</h2>
        <canvas id="incomeExpenseChart"></canvas>
    </div>
    <div class="graph-container">
        <h2>Top 5 Expense Categories (Monthly)</h2>
        <canvas id="categoryChart"></canvas>
    </div>
</main>

<script>
const months = <?php echo json_encode($months); ?>;
const incomeData = <?php echo json_encode($income_data); ?>;
const expenseData = <?php echo json_encode($expense_data); ?>;
const categoryData = <?php echo json_encode($category_monthly_data); ?>;
const topCategories = <?php echo json_encode($top_categories); ?>;

new Chart(document.getElementById('incomeExpenseChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Income',
                data: incomeData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.3
            },
            {
                label: 'Expense',
                data: expenseData,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2})}`
                }
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const colors = ['#007BFF', '#FFC107', '#28A745', '#17A2B8', '#FD7E14'];
const categoryDatasets = topCategories.map((cat, idx) => ({
    label: cat,
    data: categoryData[cat],
    borderColor: colors[idx % colors.length],
    backgroundColor: colors[idx % colors.length] + '33',
    fill: true,
    tension: 0.3
}));

new Chart(document.getElementById('categoryChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: months,
        datasets: categoryDatasets
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits: 2})}`
                }
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Krishna
</footer>
</body>
</html>
