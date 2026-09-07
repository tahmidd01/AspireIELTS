<?php
session_start();
include '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT AdminID FROM admin WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $errorMessage = "An admin account with this email already exists.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admin (Username, email, PasswordHash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $passwordHash);

        if ($stmt->execute()) {
            $successMessage = "Admin account created successfully. Redirecting to login...";
            header("Location: admin_login.php");
            exit();
 // Change if login page name differs
        } else {
            $errorMessage = "Error: " . $stmt->error;
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
  <title>Admin Sign Up</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #7ccfd0 0%, #f5aebc 100%);

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
      0%, 100% {
        transform: rotateY(0deg) rotateX(0deg);
      }
      50% {
        transform: rotateY(15deg) rotateX(12deg);
      }
    }

    @keyframes glowPulse {
      0% {
        box-shadow: 0 0 20px rgba(0, 123, 255, 0.6), 0 0 50px rgba(0, 123, 255, 0.5);
      }
      100% {
        box-shadow: 0 0 40px rgba(0, 123, 255, 0.8), 0 0 100px rgba(0, 123, 255, 0.7);
      }
    }

   
 
/* Logo Style */
.logo {
  display: block;
  margin: 0 auto 20px;
  width: 250px; /* Adjusted width to make it bigger */
  height: ; /* Maintain the aspect ratio */
  animation: fadeIn 2s ease-in-out;
}



    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    /* Text Animation */
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
      from {
        opacity: 0;
        transform: translateY(-50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Form Input Styles */
    label {
      display: block;
      margin-bottom: 8px;
      color: #2d3b73;
      font-weight: 600;
    }

    input {
      width: 100%;
      padding: 16px;
      margin-bottom: 20px;
      border: none;
      border-radius: 12px;
      outline: none;
      background: #e0f2ff;
      box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    input:focus {
      box-shadow: 0 0 12px rgba(0, 123, 255, 0.7);
      transform: scale(1.05);
    }

    /* Button Styling */
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

    /* Footer and Links */
.footer {
  text-align: center;
  margin-top: 25px;
  font-size: 1em;
  color: #2d3b73;
  animation: fadeIn 2s ease-in-out;
}

.footer a {
  color: #0062cc;
  text-decoration: none;
  font-weight: bold;
  position: relative;
  display: inline-block;
  transition: color 0.3s ease, transform 0.3s ease;
}

.footer a:hover {
  color: #004095;
  transform: translateY(-3px); /* Subtle hover effect */
}

/* Footer Background Animation */
.footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, #0062cc, #004095);
  animation: footerGlow 3s ease-in-out infinite alternate;
}

@keyframes footerGlow {
  0% {
    transform: scaleX(0);
  }
  50% {
    transform: scaleX(1);
  }
  100% {
    transform: scaleX(0);
  }
}


    /* Error Message */
    .error-message {
      color: #e74c3c;
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
      animation: fadeIn 1s ease-in-out;
    }

    /* Text Animation */
.animated-heading {
  font-size: 2.5em; /* Slightly bigger but not too large */
  text-align: center;
  color: #2d3b73;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-weight: bold;
  position: relative;
  animation: animateHeading 2s ease-in-out infinite;
}

@keyframes animateHeading {
  0%, 100% {
    transform: rotateY(0deg) rotateX(0deg);
    text-shadow: 0 0 5px rgba(0, 123, 255, 0.5), 0 0 15px rgba(0, 123, 255, 0.3);
  }
  25% {
    transform: rotateY(5deg) rotateX(5deg);
    text-shadow: 0 0 10px rgba(0, 123, 255, 0.7), 0 0 20px rgba(0, 123, 255, 0.5);
  }
  50% {
    transform: rotateY(-5deg) rotateX(-5deg);
    text-shadow: 0 0 15px rgba(0, 123, 255, 1), 0 0 30px rgba(0, 123, 255, 0.7);
  }
  75% {
    transform: rotateY(3deg) rotateX(3deg);
    text-shadow: 0 0 10px rgba(0, 123, 255, 0.6), 0 0 25px rgba(0, 123, 255, 0.4);
  }
}

  </style>
</head>
<body>
  <div class="container">
    <img src="../images/transparent-logo.png" alt="AspireIELTS Logo" class="logo">
    <h2 class="animated-heading">Admin Sign Up</h2>

    <form method="POST" action="">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <?php
        if (isset($errorMessage)) echo "<div class='error-message'>$errorMessage</div>";
        if (isset($successMessage)) echo "<div class='success-message'>$successMessage</div>";
      ?>

      <button type="submit">Sign Up</button>
    </form>

    <div class="footer">
      <p>Already have an admin account? <a href="admin_login.php">Login here</a></p>
    </div>
  </div>
</body>
</html>
