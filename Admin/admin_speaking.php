<?php
session_start();
require_once "../db_connection.php";

// Save speaking test data if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submitSpeakingResult'])) {
    $uid = $_POST['uid'];
    $bandScore = $_POST['bandscore'];
    $feedback = $_POST['feedback'];
    $testType = "Speaking";
    $testDate = date('Y-m-d H:i:s');
    
    // Insert into 'tests' table
    $stmt1 = $conn->prepare("INSERT INTO tests (UserID, TestType, TestDate, BandScore, Feedback) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("issss", $uid, $testType, $testDate, $bandScore, $feedback);
    $stmt1->execute();
    $testID = $conn->insert_id;

    // Insert into 'testresults' table
    $stmt2 = $conn->prepare("INSERT INTO testresults (TestID, UserID, TestType, Score, DateTaken, TestDate) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("iissss", $testID, $uid, $testType, $bandScore, $testDate, $testDate);
    $stmt2->execute();

    // Redirect to admin dashboard after saving
    header("Location: admin_dashboard.php");
    exit();
}
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
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        #jitsi-container {
            width: 95%;
            max-width: 1000px;
            height: 600px;
            margin: 700px auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.7);
        }



        form.eval-form {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(0, 255, 255, 0.2);
    color: #ffffff;
    text-align: center;
    max-width: 450px;
    margin: 50px auto;
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.eval-form input,
.eval-form textarea {
    width: 100%;
    padding: 14px;
    margin-bottom: 20px;
    font-size: 1rem;
    border: none;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    box-shadow: inset 0 0 5px rgba(0, 255, 255, 0.1);
    transition: all 0.3s ease-in-out;
}

.eval-form input:focus,
.eval-form textarea:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 10px rgba(0, 255, 255, 0.4);
}

.eval-form button {
    padding: 12px 30px;
    font-size: 1.05rem;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #00f0ff, #00b8cc);
    color: #000;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 5px 15px rgba(0, 255, 255, 0.3);
}

.eval-form button:hover {
    background: linear-gradient(135deg, #00c2d3, #00a2b0);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 255, 255, 0.4);
}
h2 {
    text-align: center;
    font-size: 2rem;
    color: #00f0ff;
    margin-bottom: 20px;
    text-shadow: 0 0 10px rgba(0, 240, 255, 0.7);
}

label {
    display: block;
    font-size: 1.1rem;
    color: #fff;
    margin-bottom: 8px;
    text-align: center;
}

input[type="text"] {
    width: 80%;
    max-width: 400px;
    display: block;
    margin: 0 auto;
    padding: 12px 15px;
    font-size: 1rem;
    border-radius: 10px;
    border: none;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    backdrop-filter: blur(10px);
    box-shadow: 0 0 10px rgba(0, 240, 255, 0.3);
    outline: none;
    transition: 0.3s ease;
}

input[type="text"]::placeholder {
    color: #ccc;
}

input[type="text"]:focus {
    box-shadow: 0 0 20px rgba(0, 240, 255, 0.6);
    background: rgba(255, 255, 255, 0.15);
}

button[type="submit"] {
    display: block;
    margin: 20px auto 0;
    padding: 12px 30px;
    font-size: 1rem;
    border: none;
    border-radius: 12px;
    background-color: #00f0ff;
    color: #000;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 0 15px rgba(0, 240, 255, 0.4);
    transition: all 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #00b8cc;
    box-shadow: 0 0 25px rgba(0, 240, 255, 0.7);
    transform: scale(1.05);
}

    </style>
</head>
<body>

<?php if (!isset($_GET['uid'])): ?>
    <form method="get" action="" style="text-align: center; margin-top: 100px;">
        <h2>Join Speaking Test as Examiner</h2>
        <label for="uid">Enter User ID:</label><br />
        <input type="text" id="uid" name="uid" required placeholder="User ID" />
        <br /><br />
        <button type="submit">Join Call</button>
    </form>
<?php else: 
    $uid = htmlspecialchars($_GET['uid']);
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

<!-- Quit Button -->
<button id="quitBtn" style="position: fixed; top: 20px; right: 20px; padding: 12px 24px; background: linear-gradient(145deg, #ff4b4b, #c33232); color: white; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; z-index: 1000; box-shadow: 0 5px 0 #991f1f;">
Quit</button>

<!-- Quit Modal -->
<div id="quitModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center; z-index: 1001; flex-direction: column;">
    <div style="background-color: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 4px 8px rgba(0,0,0,0.2); color: #000;">
        <h2>Quit Test?</h2>
        <p>Are you sure you want to quit the speaking test?</p>
        <button id="confirmQuitBtn" style="background: linear-gradient(145deg, #f44336, #d32f2f); color: white; margin-right: 10px; padding: 10px 20px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Yes</button>
        <button id="cancelQuitBtn" style="background: linear-gradient(145deg, #e0e0e0, #bdbdbd); color: #333; padding: 10px 20px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">No</button>
    </div>
</div>

<!-- Evaluation Form (hidden initially) -->
<div id="evaluationFormContainer" style="display: none;">
    <form method="post" class="eval-form">
        <h2>Speaking Test Evaluation</h2>
        <input type="hidden" name="uid" value="<?php echo $uid; ?>" />
        <label>Band Score:</label>
        <input type="text" name="bandscore" required placeholder="e.g., 7.5" />
        <label>Feedback:</label>
        <textarea name="feedback" required placeholder="Feedback for the candidate..."></textarea>
        <button type="submit" name="submitSpeakingResult">Submit Result</button>
    </form>
</div>

<script>
    const quitBtn = document.getElementById('quitBtn');
    const quitModal = document.getElementById('quitModal');
    const cancelQuitBtn = document.getElementById('cancelQuitBtn');
    const confirmQuitBtn = document.getElementById('confirmQuitBtn');
    const evalFormContainer = document.getElementById('evaluationFormContainer');
    const jitsiContainer = document.getElementById('jitsi-container');

    // If UID is not set (form is showing), redirect on quit
    quitBtn.addEventListener('click', () => {
        <?php if (!isset($_GET['uid'])): ?>
            window.location.href = 'admin_dashboard.php';
        <?php else: ?>
            quitModal.style.display = 'flex';
        <?php endif; ?>
    });

    cancelQuitBtn?.addEventListener('click', () => {
        quitModal.style.display = 'none';
    });

    confirmQuitBtn?.addEventListener('click', () => {
        quitModal.style.display = 'none';
        if (jitsiContainer) jitsiContainer.style.display = 'none';
        quitBtn.style.display = 'none';
        if (evalFormContainer) evalFormContainer.style.display = 'block';
    });
</script>


</body>
</html>
