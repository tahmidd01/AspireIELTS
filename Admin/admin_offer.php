<?php
session_start();
require_once '../db_connection.php';

date_default_timezone_set('Asia/Dhaka');

if (!isset($_SESSION['AdminID'])) {
    header("Location: admin_login.php");
    exit();
}
// Sanitize and get ID from URL if set
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

function safeOutput($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
$adminId = $_SESSION['AdminID'];
$adminName = "Admin";
$stmt = $conn->prepare("SELECT Username FROM admin WHERE AdminID = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $adminName = $row['Username'];
}
$stmt->close();

$usersQuery = "SELECT * FROM users ORDER BY UserID ASC";
$usersResult = mysqli_query($conn, $usersQuery);

$selectedUser = isset($_GET['user']) ? (int)$_GET['user'] : null;
$selectedUserFullName = "";
if ($selectedUser) {
    $userInfoResult = mysqli_query($conn, "SELECT FullName FROM users WHERE UserID = $selectedUser");
    if ($userInfoResult && mysqli_num_rows($userInfoResult) > 0) {
        $userInfo = mysqli_fetch_assoc($userInfoResult);
        $selectedUserFullName = $userInfo['FullName'];
    }
}


$userResponses = $userResults = $userTests = [];
if ($selectedUser) {
    $userResponses = mysqli_query($conn, "SELECT * FROM testresponses WHERE UserID = $selectedUser");
    $userResults = mysqli_query($conn, "SELECT * FROM testresults WHERE UserID = $selectedUser");
    $userTests = mysqli_query($conn, "SELECT * FROM tests WHERE UserID = $selectedUser");
}

$bdTime = date("l, j F Y - h:i:s A");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - AspireIELTS</title>
   <style>
:root {
    --bg-light: #f1f3f7;
    --bg-dark: #121212;
    --text-light: #2c3e50;
    --text-dark: #f1f1f1;
    --card-light: #ffffff;
    --card-dark: #1e1e1e;
    --accent: #3498db;
    --sidebar-width: 220px;
    --sidebar-collapsed-width: 60px;
}
* {
    box-sizing: border-box;
}
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background-color: var(--bg-light);
    color: var(--text-light);
    transition: background 0.3s ease, color 0.3s ease;
    display: flex;
    height: 100vh;
    overflow: hidden;
}
.dark-mode {
    background-color: var(--bg-dark);
    color: var(--text-dark);
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100%;
    background: var(--card-light);
    box-shadow: 2px 0 12px rgba(0,0,0,0.1);
    padding-top: 20px;
    transition: width 0.3s ease;
    overflow: hidden;
    z-index: 1000;
}
.dark-mode .sidebar {
    background: var(--card-dark);
    box-shadow: 2px 0 12px rgba(255,255,255,0.1);
}
.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}
.logo {
  padding: 20px;
  margin-bottom: 20px;
  text-align: center;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.logo img {
  height: 80px; /* Set explicit height */
  width: auto;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
@media (max-width: 768px) {
  .logo img {
    height: 60px;
  }
}

.sidebar.collapsed .logo {
  opacity: 0;
  transform: translateX(-20px);
  pointer-events: none;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar ul li {
    padding: 15px 25px;
    cursor: pointer;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 15px;
    font-weight: 600;
    color: var(--text-light);
    transition: background 0.2s ease;
}
.dark-mode .sidebar ul li {
    color: var(--text-dark);
}
.sidebar ul li:hover {
    background: linear-gradient(135deg, #4a00e0, #8e2de2);
    color: white;
    box-shadow: inset 2px 2px 8px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
}
.sidebar ul li a {
    color: inherit;
    text-decoration: none;
    flex-grow: 1;
}
.sidebar.collapsed ul li span.text {
    display: none;
}
.sidebar ul li .icon {
    width: 28px;
    font-size: 1.3em;
    color: var(--accent);
}
.sidebar ul li:hover .icon {
    color: white;
}
.sidebar.collapsed ul li {
    justify-content: center;
}

/* MAIN CONTENT */
.main-content {
    margin-left: var(--sidebar-width);
    padding: 40px;
    width: calc(100% - var(--sidebar-width));
    overflow-y: auto;
    transition: margin-left 0.3s ease, width 0.3s ease;
    height: 100vh;
}
.sidebar.collapsed + .main-content {
    margin-left: var(--sidebar-collapsed-width);
    width: calc(100% - var(--sidebar-collapsed-width));
}

/* TOP BAR */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.top-bar-left {
    display: flex;
    align-items: center;
    gap: 15px;
}
.sidebar-toggle-btn {
    background: linear-gradient(135deg, #4a00e0, #8e2de2);
    border: none;
    color: white;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1.1em;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px auto;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.sidebar-toggle-btn:hover {
    background: linear-gradient(135deg, #5f00e5, #9a30f5);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    transform: scale(1.05);
}

.top-bar button, .top-bar a button {
    padding: 10px 15px;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease;
}
.top-bar button:hover, .top-bar a button:hover {
    background: #2980b9;
}
body {
    margin: 0;
    height: 100vh;
    background: linear-gradient(145deg, #e0eaff, #f0f4ff);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    perspective: 1000px;
    color: #333;
    transition: background 0.5s, color 0.5s;
}

.admin-container {
    text-align: center;
    background: #ffffff;
    padding: 40px 60px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transform-style: preserve-3d;
    animation: floatCard 5s ease-in-out infinite;
    transition: transform 0.3s, background 0.5s, box-shadow 0.5s;
    color: #333;
}

.dashboard-title {
    font-size: 2.5rem;
    color: #1e3a8a;
    text-shadow: 2px 4px 8px rgba(30, 58, 138, 0.4);
    margin-bottom: 15px;
    transition: color 0.5s, text-shadow 0.5s;
}

.welcome {
    font-size: 1.3rem;
    color: #333;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    transition: color 0.5s, text-shadow 0.5s;
}

@keyframes floatCard {
    0%, 100% {
        transform: rotateX(0deg) rotateY(0deg) translateY(0px);
    }
    50% {
        transform: rotateX(5deg) rotateY(5deg) translateY(-10px);
    }
}

/* Dark Mode Styles */
body.dark-mode {
    background: linear-gradient(145deg,rgb(15, 15, 15),rgb(8, 8, 8));
    color: #ccc;
}

body.dark-mode .admin-container {
    background: #121627;
    box-shadow: 0 10px 30px rgba(255,255,255,0.1);
    color: #ccc;
}

body.dark-mode .dashboard-title {
    color: #66aaff;
    text-shadow: 2px 4px 8px rgba(102, 170, 255, 0.7);
}

body.dark-mode .welcome {
    color: #bbb;
    text-shadow: 1px 1px 2px rgba(255,255,255,0.1);
}

.time-display {
    font-family: 'Orbitron', sans-serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: #00bfff;
    padding: 18px 36px;
    border-radius: 20px;
    min-width: 280px;
    text-align: center;

    /* 3D Glass + toned-down glow */
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow:
        inset 0 0 6px rgba(0, 191, 255, 0.25),
        0 0 6px rgba(0, 191, 255, 0.3),
        0 0 14px rgba(0, 191, 255, 0.25),
        2px 2px 14px rgba(0, 0, 0, 0.2);

    text-shadow:
        0 0 2px #00bfff,
        0 0 4px #00bfff;

    animation: floatTimer 4s ease-in-out infinite, glowShift 3s ease-in-out infinite alternate;
    transition: all 0.4s ease-in-out;
}

/* Float up and down */
@keyframes floatTimer {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
    100% { transform: translateY(0px); }
}

/* Subtle glow pulsing */
@keyframes glowShift {
    0% {
        box-shadow:
            inset 0 0 4px rgba(0, 191, 255, 0.25),
            0 0 5px rgba(0, 191, 255, 0.3),
            0 0 12px rgba(0, 191, 255, 0.25),
            1px 1px 8px rgba(0, 0, 0, 0.1);
    }
    100% {
        box-shadow:
            inset 0 0 6px rgba(0, 191, 255, 0.35),
            0 0 8px rgba(0, 191, 255, 0.4),
            0 0 18px rgba(0, 191, 255, 0.35),
            2px 2px 12px rgba(0, 0, 0, 0.2);
    }
}

/* CARDS */
.card {
    background: var(--card-light);
    border-radius: 15px;
    box-shadow: 6px 6px 15px rgba(0,0,0,0.1);
    margin-bottom: 40px;
    padding: 25px;
    transition: all 0.3s ease-in-out;
}
.dark-mode .card {
    background: var(--card-dark);
    box-shadow: 0 0 20px rgba(255,255,255,0.05);
}
.card:hover {
    transform: scale(1.01);
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 12px;
    text-align: center;
    border: 1px solid #ccc;
}
th {
    background: var(--accent);
    color: white;
}
.user-list a {
    color: var(--accent);
    text-decoration: none;
}
.user-list a:hover {
    text-decoration: underline;
}
input[type="text"] {
    padding: 10px;
    width: 300px;
    max-width: 100%;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        width: var(--sidebar-collapsed-width);
    }
    .sidebar.collapsed {
        width: 0;
    }
    .main-content {
        margin-left: var(--sidebar-collapsed-width);
        width: calc(100% - var(--sidebar-collapsed-width));
    }
    .sidebar.collapsed + .main-content {
        margin-left: 0;
        width: 100%;
    }
}

/* USER DISPLAY CARD */
.user-display-card {
    background: linear-gradient(135deg, #e0f7fa, #e3f2fd);
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform-style: preserve-3d;
    animation: userCardFloat 4s ease-in-out infinite;
    transition: transform 0.3s, box-shadow 0.3s;
    text-align: center;
    margin: 20px auto;
    max-width: 500px;
}

.user-display-card h2 {
    font-size: 2rem;
    color: #1565c0;
    text-shadow: 1px 2px 6px rgba(21, 101, 192, 0.3);
}

.user-display-card p {
    font-size: 1.2rem;
    color: #333;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

@keyframes userCardFloat {
    0%, 100% {
        transform: rotateX(0deg) rotateY(0deg) translateY(0px);
    }
    50% {
        transform: rotateX(3deg) rotateY(3deg) translateY(-8px);
    }
}
.sidebar.collapsed .logo {
    display: none;
}
.switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    margin-right: 10px;
    vertical-align: middle;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 28px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #2196F3;
}
input:checked + .slider:before {
    transform: translateX(24px);
}

#speaking-notifications {
    background-color: #fff5e6;
    padding: 10px 20px;
    border-left: 5px solid #ff9900;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
#sidebarLogo {
  padding: 20px;
  text-align: center;
  transition: all 0.3s ease;
}

#sidebarLogo img {
  height: 180px;
  width: auto;
  object-fit: contain;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

/* Adjust when sidebar is collapsed */
.sidebar.collapsed #sidebarLogo {
  padding: 10px;
}

.sidebar.collapsed #sidebarLogo img {
  height: 40px;
  margin: auto;
}
.welcome {
  font-size: 1.2rem;
  font-weight: 500;
  color: #333;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Poppins', sans-serif;
  gap: 6px;
  /* Adjust font family or other styles as needed */
}

.welcome strong {
  border-right: 2px solid #00bfff;
  white-space: nowrap;
  overflow: hidden;
  display: inline-block;
  min-width: max-content;  /* prevents shrinking */
  animation: blinkCursor 0.8s infinite;
}

@keyframes blinkCursor {
  0%, 100% { border-color: transparent; }
  50% { border-color: #00bfff; }
}

.dashboard-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 3.5rem;   /* Larger size */
    font-weight: 700;
    color: #00bfff;      /* Sky blue color for style */
    letter-spacing: 2px; /* Spacing for uniqueness */
    text-transform: uppercase;
    margin-bottom: 20px;
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f9;
    color: #333;
    margin: 0;
    padding: 2rem;
}

.section {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    padding: 1.8rem 2rem;
    margin-bottom: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.section:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.section h3 {
    font-size: 1.5rem;
    color: #1d3557;
    border-left: 4px solid #457b9d;
    padding-left: 0.75rem;
    margin-bottom: 1rem;
}

.label {
    display: inline-block;
    width: 180px;
    font-weight: 600;
    color: #2b2d42;
}

.value {
    color: #333;
    font-weight: 400;
    background: #f1f3f5;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    display: inline-block;
    max-width: calc(100% - 200px);
    word-wrap: break-word;
}

.section div {
    margin-bottom: 0.8rem;
}

.img-preview {
    max-height: 100px;
    max-width: 140px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-right: 0.5rem;
    vertical-align: middle;
}

.file-link {
    text-decoration: none;
    color: #1d3557;
    font-weight: 500;
    padding: 0.2rem 0.4rem;
    background-color: #e9f5ff;
    border: 1px solid #a8dadc;
    border-radius: 6px;
    margin-left: 0.5rem;
    transition: background-color 0.2s ease;
}

.file-link:hover {
    background-color: #d0f0ff;
}

small {
    font-size: 0.9rem;
    color: #555;
    display: block;
    margin-top: 1rem;
    text-align: right;
}
/* LIGHT MODE (default styles) */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f9;
    color: #333;
    margin: 0;
    padding: 2rem;
}

.section {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    padding: 1.8rem 2rem;
    margin-bottom: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.section:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
}

.section h3 {
    font-size: 1.5rem;
    color: #1d3557;
    border-left: 4px solid #457b9d;
    padding-left: 0.75rem;
    margin-bottom: 1rem;
}

.label {
    display: inline-block;
    width: 180px;
    font-weight: 600;
    color: #2b2d42;
}

.value {
    color: #333;
    font-weight: 400;
    background: #f1f3f5;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    display: inline-block;
    max-width: calc(100% - 200px);
    word-wrap: break-word;
}

.section div {
    margin-bottom: 0.8rem;
}

.img-preview {
    max-height: 100px;
    max-width: 140px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-right: 0.5rem;
    vertical-align: middle;
}

.file-link {
    text-decoration: none;
    color: #1d3557;
    font-weight: 500;
    padding: 0.2rem 0.4rem;
    background-color: #e9f5ff;
    border: 1px solid #a8dadc;
    border-radius: 6px;
    margin-left: 0.5rem;
    transition: background-color 0.2s ease;
}

.file-link:hover {
    background-color: #d0f0ff;
}

small {
    font-size: 0.9rem;
    color: #555;
    display: block;
    margin-top: 1rem;
    text-align: right;
}


/* DARK MODE */


.dark-mode .section {
    background: #2a2d3a;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
}

.dark-mode .section h3 {
    color: #9ecfff;
    border-left-color: #5fa8d3;
}

.dark-mode .label {
    color: #bfc7d5;
}

.dark-mode .value {
    background: #373b4f;
    color: #eaeaea;
}

.dark-mode .file-link {
    color: #a5c9ff;
    background-color: #33415c;
    border-color: #5fa8d3;
}

.dark-mode .file-link:hover {
    background-color: #425474;
}

.dark-mode small {
    color: #aaa;
}

.dark-mode .img-preview {
    box-shadow: 0 2px 12px rgba(255, 255, 255, 0.08);
}
.back-link {
    display: inline-block;
    padding: 0.6rem 1.2rem;
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #457b9d, #1d3557);
    text-decoration: none;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    margin-top: -75px;
    float: right;
}

.back-link:hover {
    background: linear-gradient(135deg, #1d3557, #457b9d);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
}

@media (max-width: 600px) {
    .back-link {
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
        float: none; /* stack full width on small screens */
        display: block;
        width: fit-content;
        margin-left: auto; /* center horizontally on small */
    }
}
h2 {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 2rem;
    font-weight: 700;
    color:rgb(0, 0, 0);
    border-bottom: 3px solid #457b9d;
    padding-bottom: 0.4rem;
    margin-bottom: 1.5rem;
    text-align: center; /* Center align for emphasis */
    letter-spacing: 0.05em;
    user-select: none;
    transition: color 0.3s ease;
}

h2:hover {
    color: #2a6496;
    border-color: #1d3557;
    cursor: default;
}

.dark-mode h2 {
    color: #a8dadc;
    border-bottom-color: #63cdda;
}

.dark-mode h2:hover {
    color: #f1faee;
    border-bottom-color: #a8dadc;
}

</style>

</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const strongTag = document.querySelector(".welcome strong");
    const fullName = strongTag.textContent.trim();
    let i = 0;

    function type() {
      if (i <= fullName.length) {
        strongTag.textContent = fullName.substring(0, i);
        i++;
        setTimeout(type, 100);
      } else {
        // After typing completes, wait 1.5s then erase and start again
        setTimeout(erase, 1500);
      }
    }

    function erase() {
      if (i >= 0) {
        strongTag.textContent = fullName.substring(0, i);
        i--;
        setTimeout(erase, 50);
      } else {
        // Restart typing
        setTimeout(type, 500);
      }
    }

    // Start typing on page load
    type();
  });
  
</script>

<body>

   <nav class="sidebar" id="sidebar">
    <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
  <i class="fas fa-bars"></i>
</button>

       <div class="logo" id="sidebarLogo">
  <img src="../images/transparent-logo.png" alt="AspireIELTS Logo" />
</div>
    <ul>
        <li><a href="admin_dashboard.php"><i class="fas fa-home icon"></i><span class="text">Dashboard</span></a></li>
        <li><a href="admin_users.php"><i class="fas fa-users icon"></i><span class="text">Users</span></a></li>
        <li><a href="admin_speaking.php"><i class="fas fa-microphone icon"></i><span class="text">Speaking Test</span></a></li>
        <li><a href="admin_report.php"><i class="fas fa-chart-line icon"></i><span class="text">Reports</span></a></li>
       <li><a href="admin_offer.php"><i class="fas fa-chart-line icon"></i><span class="text">Offer Letter Request</span></a></li>
        <li><a href="../index.php"><i class="fas fa-sign-out-alt icon"></i><span class="text">Logout</span></a></li>
    </ul>
</nav>



    <div class="main-content" id="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <div class="time-display" id="time"></div>
                

            </div>
            <div>
    <label class="switch">
        <input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()">
        <span class="slider round"></span>
    </label>
    <a href="admin_logout.php"><button>Logout</button></a>
</div>

        </div>

        <div class="admin-container">
  <h1 class="dashboard-title">Admin Dashboard</h1>
  <div class="welcome">
    Welcome, <strong><?= htmlspecialchars($adminName) ?></strong> 👋
  </div>
</div>

<script>
        function toggleCardContent(id) {
            const content = document.getElementById(id);
            if (content.style.display === "none") {
                content.style.display = "block";
            } else {
                content.style.display = "none";
            }
        }
    </script>

     <script>
    function updateBDTime() {
        const timeElement = document.getElementById("time");

        const now = new Date();

        const optionsDate = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: '2-digit',
            timeZone: 'Asia/Dhaka'
        };

        const optionsTime = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'Asia/Dhaka'
        };

        const datePart = now.toLocaleDateString('en-GB', optionsDate);
        const timePart = now.toLocaleTimeString('en-US', optionsTime);

        timeElement.innerHTML = `${datePart} - ${timePart} (BD)`;
    }

    updateBDTime(); // initial call
    setInterval(updateBDTime, 1000); // update every second

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });

    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('userTable');
        const trs = table.getElementsByTagName('tr');

        for (let i = 1; i < trs.length; i++) {
            const tds = trs[i].getElementsByTagName('td');
            const name = tds[1].textContent.toLowerCase();
            const email = tds[2].textContent.toLowerCase();

            if (name.indexOf(filter) > -1 || email.indexOf(filter) > -1) {
                trs[i].style.display = "";
            } else {
                trs[i].style.display = "none";
            }
        }
    }

    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
        } else {
            localStorage.setItem('darkMode', 'disabled');
        }
    }

    function toggleCardContent(id, btn) {
        const content = document.getElementById(id);
        const isVisible = content.style.display !== "none";

        content.style.display = isVisible ? "none" : "block";
        btn.innerHTML = isVisible ? "➕" : "➖";

        localStorage.setItem(id + "_visible", !isVisible);
    }

    // ✅ Merged all window.onload logic
    window.onload = function () {
        document.getElementById('darkModeToggle').checked = localStorage.getItem('darkMode') === 'enabled';

        // Restore dark mode
       if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeToggle').checked = true; // ✅ this line
    }


        // Restore toggle states
        const sections = ['responses-content', 'results-content', 'tests-content'];
        sections.forEach(id => {
            const savedState = localStorage.getItem(id + "_visible");
            const isVisible = savedState === null ? true : savedState === "true";
            const content = document.getElementById(id);
            const btn = document.querySelector(`button[onclick*="${id}"]`);

            if (content && btn) {
                content.style.display = isVisible ? "block" : "none";
                btn.innerHTML = isVisible ? "➖" : "➕";
            }
        });
    };
</script>
<h2>Offer Letter Requests - Admin Panel</h2>

<?php if ($id === 0): 
    // Show list of all requests
    $sql = "SELECT id, full_name, email, phone_number, submitted_at FROM offer_letter_requests ORDER BY submitted_at DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= safeOutput($row['id']) ?></td>
                    <td><?= safeOutput($row['full_name']) ?></td>
                    <td><?= safeOutput($row['email']) ?></td>
                    <td><?= safeOutput($row['phone_number']) ?></td>
                    <td><?= safeOutput($row['submitted_at']) ?></td>
                    <td><a href="?id=<?= (int)$row['id'] ?>">View Details</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No offer letter requests found.</p>
    <?php endif; ?>

<?php else:
    // Show details for the selected request
    $stmt = $conn->prepare("SELECT * FROM offer_letter_requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1):
        $data = $res->fetch_assoc();

        // Folder path relative to this script
        $folder = "../Offer Letter/"; // Make sure this matches your actual folder name on server

        ?>
        <a href="admin_offer.php" class="back-link">&larr; Back to all requests</a>
        <div class="section">
            <h3>Personal Information</h3>
            <div><span class="label">Full Name:</span> <span class="value"><?= safeOutput($data['full_name']) ?></span></div>
            <div><span class="label">Father's Name:</span> <span class="value"><?= safeOutput($data['father_name']) ?></span></div>
            <div><span class="label">Mother's Name:</span> <span class="value"><?= safeOutput($data['mother_name']) ?></span></div>
            <div><span class="label">Date of Birth:</span> <span class="value"><?= safeOutput($data['date_of_birth']) ?></span></div>
            <div><span class="label">Nationality:</span> <span class="value"><?= safeOutput($data['nationality']) ?></span></div>
            <div><span class="label">Religion:</span> <span class="value"><?= safeOutput($data['religion']) ?></span></div>
            <div><span class="label">Marital Status:</span> <span class="value"><?= safeOutput($data['marital_status']) ?></span></div>
           
        </div>

        <div class="section">
            <h3>Contact Information</h3>
            <div><span class="label">Phone Number:</span> <span class="value"><?= safeOutput($data['phone_number']) ?></span></div>
            <div><span class="label">Email:</span> <span class="value"><?= safeOutput($data['email']) ?></span></div>
            <div><span class="label">Present Address:</span> <span class="value"><?= nl2br(safeOutput($data['present_address'])) ?></span></div>

        </div>

        <div class="section">
            <h3>Academic Background</h3>
            <div><span class="label">SSC Year:</span> <span class="value"><?= safeOutput($data['ssc_year']) ?></span></div>
            <div><span class="label">HSC Year:</span> <span class="value"><?= safeOutput($data['hsc_year']) ?></span></div>
            <div><span class="label">Medium of Study:</span> <span class="value"><?= safeOutput($data['medium_of_study'] ?: $data['medium']) ?></span></div>
        </div>

        <div class="section">
            <h3>Study Preferences</h3>
            <div><span class="label">Country of Choice:</span> <span class="value"><?= safeOutput($data['country_of_choice']) ?></span></div>
            <div><span class="label">Preferred University:</span> <span class="value"><?= safeOutput($data['preferred_university']) ?></span></div>
            <div><span class="label">Preferred Subject:</span> <span class="value"><?= safeOutput($data['preferred_subject']) ?></span></div>
            <div><span class="label">Program Type:</span> <span class="value"><?= safeOutput($data['program_type'] ?: 'N/A') ?></span></div>
            <div><span class="label">Intake Season:</span> <span class="value"><?= safeOutput($data['intake_season']) ?></span></div>
            <div><span class="label">Intake Year:</span> <span class="value"><?= safeOutput($data['intake_year']) ?></span></div>
            <div><span class="label">Already Applied?:</span> <span class="value"><?= safeOutput($data['already_applied']) ?></span></div>
        </div>


        <div class="section">
            <h3>Uploaded Files</h3>

            <?php
            // Helper function to display files
            function displayFile($label, $path, $folder, $isImage = false) {
                if (empty($path)) {
                    echo "<div><span class='label'>$label:</span> <span class='value'>N/A</span></div>";
                    return;
                }

                $fileName = basename($path);
                $fileServerPath = __DIR__ . DIRECTORY_SEPARATOR . $folder . $fileName;
                $fileUrl = $folder . rawurlencode($fileName);

                if (!file_exists($fileServerPath)) {
                    echo "<div><span class='label'>$label:</span> <span class='value'>File missing</span></div>";
                    return;
                }

                echo "<div><span class='label'>$label:</span> <span class='value'>";
                if ($isImage) {
                    echo "<img class='img-preview' src='$fileUrl' alt='$label' />";
                }
                echo " <a class='file-link' href='$fileUrl' target='_blank' download>$fileName</a>";
                echo "</span></div>";
            }

            displayFile('NID Card', $data['nid_card_path'], $folder);
            displayFile('SSC Certificate', $data['ssc_certificate_path'], $folder);
            displayFile('HSC Certificate', $data['hsc_certificate_path'], $folder);
            displayFile('Passport Scan', $data['passport_scan_path'], $folder);
            displayFile('English Certificate', $data['english_cert_path'], $folder);
            displayFile('Father\'s NID', $data['father_nid_path'], $folder);
            displayFile('Mother\'s NID', $data['mother_nid_path'], $folder);
            displayFile('LOR', $data['lor_path'], $folder);
            displayFile('SOP', $data['sop_path'], $folder);
            displayFile('CV', $data['cv_path'], $folder);
            displayFile('Passport Photo', $data['passport_photo_path'], $folder, true);
            ?>
        </div>

        <div class="section">
            <small>Submitted at: <?= safeOutput($data['submitted_at']) ?></small>
        </div>

    <?php else: ?>
        <p>Invalid offer letter request ID or record not found.</p>
        <a href="admin_offer.php">&larr; Back to all requests</a>
    <?php
    endif;
    $stmt->close();
endif;
?>

</body>
</html>