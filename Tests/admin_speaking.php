<?php
session_start();
require_once "db_connection.php"; // Ensure this connects via $conn

// Optional: Admin auth check can go here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Speaking Test - AspireIELTS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            flex-direction: column;
        }
        form {
            background: rgba(0, 0, 0, 0.5);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0,255,255,0.3);
            text-align: center;
            width: 320px;
        }
        input[type=text] {
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            border: none;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        button {
            padding: 10px 25px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            background-color: #00f0ff;
            color: #000;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #00b8cc;
        }
        #jitsi-container {
            width: 90%;
            max-width: 1000px;
            height: 600px;
            margin: 40px auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.7);
        }
    </style>
</head>
<body>
    <form method="get" action="">
        <h2>Join Speaking Test as Examiner</h2>
        <label for="uid">Enter User ID:</label><br />
        <input type="text" id="uid" name="uid" required placeholder="User ID" />
        <br />
        <button type="submit">Join Call</button>
    </form>

<?php if (isset($_GET['uid'])): 
    $uid = htmlspecialchars($_GET['uid']);

    if (is_numeric($uid)) {
        // Update session as completed
        $stmt = $conn->prepare("UPDATE speaking_sessions SET status='completed', ended_at=NOW() WHERE user_id=?");
        $stmt->bind_param("i", $uid);
        if ($stmt->execute()) {
            echo "<p style='color: #90ee90; margin-top: 10px;'>Session marked as completed for User ID: $uid</p>";
        } else {
            echo "<p style='color: #ffcccb; margin-top: 10px;'>Error updating session: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: yellow;'>Invalid User ID</p>";
    }

    $roomName = "AspireIELTS_SpeakingTest_User" . $uid;
?>
    <div id="jitsi-container"></div>

    <script src='https://meet.jit.si/external_api.js'></script>
    <script>
        const domain = "meet.jit.si";
        const options = {
            roomName: "<?php echo $roomName; ?>",
            width: "100%",
            height: 600,
            parentNode: document.querySelector('#jitsi-container'),
            configOverwrite: {
                startWithVideoMuted: false,
                startWithAudioMuted: false,
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                DEFAULT_REMOTE_DISPLAY_NAME: 'Candidate',
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
<?php endif; ?>
</body>
</html>
