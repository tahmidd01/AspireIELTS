<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Certificate Request Submitted</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #d4fc79, #96e6a1);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .confirmation-box {
      background: white;
      padding: 40px;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      animation: scaleUp 0.5s ease-out;
      max-width: 600px;
    }

    .confirmation-box h2 {
      color: #28a745;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .confirmation-box p {
      font-size: 16px;
      color: #333;
      line-height: 1.6;
    }

    .confirmation-box button {
      margin-top: 30px;
      padding: 12px 25px;
      font-size: 16px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .confirmation-box button:hover {
      background-color: #218838;
    }

    @keyframes scaleUp {
      0% {
        transform: scale(0.85);
        opacity: 0;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
  </style>
</head>
<body>
  <div class="confirmation-box">
    <h2>🎉 Application Submitted Successfully!</h2>
    <p>Your request has been sent successfully to the Admins.</p>
    <p>Within 48 hours you will receive the certificate in your registered email.</p>
    <p><strong>Congratulations for your achievement!</strong></p>
    <form action="dashboard.php">
      <button type="submit">Back to Dashboard</button>
    </form>
  </div>
</body>
</html>
