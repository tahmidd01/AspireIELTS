<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Certificate Access Denied</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(to right, #e96443, #904e95);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }

    .card {
      background: white;
      border-radius: 20px;
      padding: 40px;
      text-align: center;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      animation: popUp 0.6s ease-out;
    }

    @keyframes popUp {
      0% { transform: scale(0.6); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }

    .card i {
      font-size: 60px;
      color: #e74c3c;
      animation: pulse 1.2s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }

    .card h2 {
      margin-top: 20px;
      font-size: 28px;
      color: #333;
    }

    .card p {
      font-size: 18px;
      margin: 10px 0;
      color: #555;
    }

    .back-btn {
      margin-top: 20px;
      background: #e74c3c;
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s ease;
    }

    .back-btn:hover {
      background: #c0392b;
    }
  </style>
</head>
<body>
  <div class="card">
    <i class="fas fa-exclamation-triangle"></i>
    <h2>Oops! Not Eligible Yet</h2>
    <p>Sorry, your overall band score is less than <strong>6</strong>.</p>
    <p>Try again once you improve, bud 💪</p>
    <button class="back-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
  </div>
</body>
</html>
