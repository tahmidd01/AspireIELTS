<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start_test'])) {
        if ($_POST['start_test'] === 'yes') {
            header("Location: speaking_test.php");
            exit();
        } else {
            header("Location: ../dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Speaking Test Confirmation</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1000px;
        }

        .card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            padding: 50px 40px;
            text-align: center;
            color: #fff;
            width: 90%;
            max-width: 480px;
            transform-style: preserve-3d;
            transform: rotateY(0deg);
            animation: floatIn 0.8s ease-out;
            transition: transform 0.6s ease;
        }

        .card:hover {
            transform: rotateY(8deg) rotateX(4deg);
        }

        @keyframes floatIn {
            from {
                transform: translateY(100px) rotateY(-10deg);
                opacity: 0;
            }
            to {
                transform: translateY(0) rotateY(0deg);
                opacity: 1;
            }
        }

        .card h2 {
            font-size: 26px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .btn {
            padding: 14px 28px;
            font-size: 16px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(145deg, #1e90ff, #00bfff);
            box-shadow: 0 6px 15px rgba(0, 191, 255, 0.3);
            transition: all 0.3s ease;
            transform: translateZ(20px);
        }

        .btn:hover {
            transform: scale(1.05) translateZ(30px);
            box-shadow: 0 10px 20px rgba(0, 191, 255, 0.5);
        }

        .btn.no {
            background: linear-gradient(145deg, #ff4e50, #f36a6a);
            box-shadow: 0 6px 15px rgba(243, 106, 106, 0.3);
        }

        .btn.no:hover {
            box-shadow: 0 10px 20px rgba(243, 106, 106, 0.5);
        }

        @media (max-width: 480px) {
            .btn-group {
                flex-direction: column;
                gap: 15px;
            }

            .card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>You're about to begin the<br><strong>Speaking Test</strong>.<br>Do you want to proceed?</h2>
        <form method="POST" class="btn-group">
            <button class="btn yes" name="start_test" value="yes">✅ Yes, Start Now</button>
            <button class="btn no" name="start_test" value="no">❌ No, Go Back</button>
        </form>
    </div>
</body>
</html>