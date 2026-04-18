<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us | Personal Budget Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #a18cd1, #fbc2eb);
    overflow-x: hidden;
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    background: rgba(123, 44, 191, 0.7);
    backdrop-filter: blur(10px);
    color: white;
    position: relative;
    z-index: 10;
}

.header h1 {
    margin: 0;
    font-size: 22px;
}

.header .buttons a {
    color: white;
    background: #c77dff;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 8px;
    margin-left: 12px;
    transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
}

.header .buttons a:hover {
    background: #9d4edd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.main-content-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
    gap: 60px;
}

.about-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 50px;
    max-width: 1100px;
    flex-wrap: wrap;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 50px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    opacity: 0;
    transform: translateY(30px);
    animation: fadeInSlideUp 1s ease forwards;
}

.about-section:nth-of-type(2) { /* This applies to the second .about-section, which is our target */
    animation-delay: 0.3s;
    /* Styles for the new layout of the second section */
    flex-direction: column; /* Stack children vertically */
    text-align: center; /* Center align text inside this section */
    gap: 30px; /* Adjust gap for vertical stacking */
}


.about-text {
    flex: 1;
    min-width: 300px;
    text-align: left;
}

.about-text h2 {
    font-size: 38px;
    color: #fff;
    margin-bottom: 20px;
    text-shadow: 2px 2px 5px rgba(0,0,0,0.2);
}
/* Specific style for the "Key Features" heading to center it */
.about-section.features-layout h2 {
    text-align: center;
    margin-bottom: 30px; /* Space between heading and image */
}


.about-text p {
    font-size: 18px;
    color: #f0f0f0;
    line-height: 1.8;
    margin-bottom: 25px;
}

.image-container { /* General styling for image containers */
    display: flex; /* Make image container a flex item to center its image */
    justify-content: center; /* Center the image horizontally */
    align-items: center; /* Center the image vertically */
}

.image-container img {
    max-width: 450px; /* Larger image */
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}

.image-container img:hover {
    transform: scale(1.02);
}

/* Specific styling for image in the features section */
.about-section.features-layout .image-container img {
    max-width: 500px; /* Adjust as needed for the graph image */
    margin-bottom: 30px; /* Space between image and feature cards */
}


.features-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    max-width: 1100px;
    margin-top: 0; /* Remove top margin as the image provides spacing now */
}

.feature-card {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    padding: 30px;
    flex: 1 1 280px;
    max-width: 320px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInSlideUp 0.8s ease forwards;
}

.feature-card:nth-of-type(1) { animation-delay: 0.4s; }
.feature-card:nth-of-type(2) { animation-delay: 0.6s; }
.feature-card:nth-of-type(3) { animation-delay: 0.8s; }


.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.feature-card .icon {
    font-size: 3em;
    color: #c77dff;
    margin-bottom: 15px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    padding: 15px;
    display: inline-block;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.feature-card h3 {
    font-size: 22px;
    color: #fff;
    margin-bottom: 10px;
}

.feature-card p {
    font-size: 15px;
    color: #f0f0f0;
}

.get-started {
    text-align: center;
    margin-top: 50px;
    opacity: 0;
    animation: fadeIn 1s ease forwards 1s;
}

.get-started a {
    text-decoration: none;
    background: #8e6dfd;
    color: white;
    padding: 15px 30px;
    font-size: 18px;
    border-radius: 10px;
    transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.get-started a:hover {
    background: #7b2cbf;
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.footer {
    text-align: center;
    padding: 15px;
    background: rgba(123, 44, 191, 0.7);
    backdrop-filter: blur(10px);
    color: white;
    font-size: 14px;
    position: relative;
    z-index: 10;
    margin-top: auto;
}

/* Animations */
@keyframes fadeInSlideUp {
    from { opacity: 0; transform: translateY(50px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .header {
        padding: 15px 20px;
    }
    .header h1 {
        font-size: 20px;
    }
    .header .buttons a {
        padding: 8px 15px;
        font-size: 14px;
    }
    .about-section {
        flex-direction: column;
        text-align: center;
        padding: 30px;
        gap: 30px;
    }
    .about-text {
        text-align: center;
    }
    .about-text h2 {
        font-size: 30px;
    }
    .about-text p {
        font-size: 16px;
    }
    .image-container img {
        max-width: 300px;
    }
    .feature-card {
        flex: 1 1 90%;
        max-width: 400px;
    }
    .main-content-area {
        padding: 20px 10px;
        gap: 40px;
    }
    /* Adjust specific elements for the new features layout on small screens */
    .about-section.features-layout .image-container img {
        margin-bottom: 20px; /* Smaller margin on mobile */
    }
}

@media (max-width: 500px) {
    .header {
        flex-direction: column;
        gap: 10px;
    }
    .header .buttons {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    .header .buttons a {
        margin: 5px;
    }
    .about-section {
        padding: 20px;
    }
    .about-text h2 {
        font-size: 24px;
    }
    .about-text p {
        font-size: 14px;
    }
    .get-started a {
        padding: 12px 25px;
        font-size: 16px;
    }
}
</style>
</head>
<body>
<header class="header">
    <h1><i class="fas fa-wallet"></i> Personal Budget Tracker</h1>
    <div class="buttons">
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a>
    </div>
</header>

<main class="main-content-area">
    <section class="about-section">
        <div class="about-text">
            <h2>Your Path to Financial Clarity</h2>
            <p>Welcome to Personal Budget Tracker, your intuitive partner in mastering your finances. We believe that managing money should be simple, insightful, and stress-free. Our platform empowers you to track income, categorize expenses, set limits, and visualize your financial health with ease.</p>
            <p>Designed for individuals who want to take control of their spending habits and achieve their financial goals, our tracker provides the tools you need without the complexity.</p>
        </div>
        <div class="image-container">
            <img src="images/fin.jpg" alt="Financial Tracking">
        </div>
    </section>

    <section class="about-section features-layout"> <h2>Key Features at a Glance</h2>
        <div class="image-container">
            <img src="images/budget.jpg" alt="Data Visualization">
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <h3>Insightful Graphs</h3>
                <p>See your income and expenses evolve over time with interactive charts.</p>
            </div>
            <div class="feature-card">
                <div class="icon"><i class="fas fa-bullseye"></i></div>
                <h3>Expense Limits</h3>
                <p>Set spending limits for categories and stay within your budget.</p>
            </div>
            <div class="feature-card">
                <div class="icon"><i class="fas fa-list-alt"></i></div>
                <h3>Smart Categorization</h3>
                <p>Organize your transactions into custom categories for clear overview.</p>
            </div>
        </div>
    </section>


    <div class="get-started">
        <a href="signup.php"><i class="fas fa-arrow-circle-right"></i> Start Your Financial Journey Today!</a>
    </div>
</main>

<footer class="footer">
    © 2025 Personal Budget Tracker | Made with ❤️ by Isha
</footer>
</body>
</html>
