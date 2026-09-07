<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connection.php';

$signupSuccess = false;
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkStmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $errorMessage = "This email is already registered. Please log in or use a different email.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Users (FullName, Email, PasswordHash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $password);
        if ($stmt->execute()) {
            $signupSuccess = true;
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            </script>";
        } else {
            $errorMessage = "Signup failed. Try again.";
        }
        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AspireIELTS Signup</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
        body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
      height: 100vh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      perspective: 1500px;
    }

    .container {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      box-shadow: 0 25px 75px rgba(31, 38, 135, 0.37);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.18);
      padding: 50px;
      width: 380px;
      transform-style: preserve-3d;
      animation: floatCard 6s ease-in-out infinite, glowPulse 2s ease-in-out infinite alternate;
      transform-origin: center;
    }

    @keyframes floatCard {
      0%, 100% { transform: rotateY(0deg) rotateX(0deg); }
      50% { transform: rotateY(15deg) rotateX(12deg); }
    }

    @keyframes glowPulse {
      0% { box-shadow: 0 0 20px rgba(0, 123, 255, 0.6), 0 0 50px rgba(0, 123, 255, 0.5); }
      100% { box-shadow: 0 0 40px rgba(0, 123, 255, 0.8), 0 0 100px rgba(0, 123, 255, 0.7); }
    }

    .logo {
      display: block;
      margin: 0 auto 20px;
      width: 280px;
      height: auto;
      animation: fadeIn 2s ease-in-out;
    }

    h2 {
      text-align: center;
      color: #2d3b73;
      margin-bottom: 30px;
      font-size: 2em;
      text-transform: uppercase;
      letter-spacing: 2px;
      animation: slideIn 1s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-50px); }
      to { opacity: 1; transform: translateY(0); }
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #2d3b73;
      font-weight: 600;
    }

    input {
      width: 92%;
      padding: 16px;
      margin-bottom: 20px;
      border: none;
      border-radius: 12px;
      background: #e0f2ff;
      box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    input:focus {
      box-shadow: 0 0 12px rgba(0, 123, 255, 0.7);
      transform: scale(1.05);
    }

    button {
      width: 100%;
      padding: 16px;
      background: linear-gradient(90deg, #0062cc, #004095);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1.4em;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
    }

    button:hover {
      background: linear-gradient(90deg, #004095, #0062cc);
      transform: scale(1.1);
      box-shadow: 0 0 12px rgba(0, 123, 255, 0.8);
    }

    .footer {
      text-align: center;
      margin-top: 25px;
      font-size: 1em;
      color: #2d3b73;
    }

    .footer a {
      color: #0062cc;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s ease, transform 0.3s ease;
    }

    .footer a:hover {
      color: #004095;
      transform: translateY(-3px);
    }

    .error-message {
      color: #e74c3c;
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
    }

    .modal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(0.8);
      background: white;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 0 60px rgba(0, 123, 255, 0.4);
      z-index: 999;
      animation: fadeIn 0.4s forwards;
    }

    .modal.show {
      display: block;
      transform: translate(-50%, -50%) scale(1);
      animation: popup 0.4s ease forwards;
    }

    @keyframes popup {
      from { transform: translate(-50%, -50%) scale(0.5); opacity: 0; }
      to { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }

    .modal h3 {
      margin: 0 0 15px;
      font-size: 1.5em;
      color: #2d3b73;
    }

    .modal p {
      font-size: 1em;
      color: #333;
    }

    @media (max-width: 480px) {
      .container {
        width: 90%;
        padding: 30px 20px;
      }

      .logo {
        width: 200px;
      }

      h2 {
        font-size: 1.5em;
      }

      button {
        font-size: 1.2em;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <img src="images/AI.png" alt="AspireIELTS Logo" class="logo">
  <h2>Sign Up</h2>

  <?php if ($signupSuccess): ?>
    <div class="modal show">
      <h3>Registration Successful!</h3>
      <p>Redirecting to login page...</p>
    </div>
  <?php else: ?>
<form method="POST" action="" autocomplete="off">
  <label for="fullName">Full Name:</label>
  <input type="text" id="fullName" name="fullName" required autocomplete="off">

  <label for="email">Email:</label>
  <input type="email" id="email" name="email" required autocomplete="off">

  <label for="password">Password:</label>
  <input type="password" id="password" name="password" required autocomplete="new-password">

  <?php if (!empty($errorMessage)): ?>
    <div class='error-message'><?= $errorMessage ?></div>
  <?php endif; ?>

  <button type="submit">Sign Up</button>
</form>

  <?php endif; ?>

  <div class="footer">
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</div>

</body>
</html>
