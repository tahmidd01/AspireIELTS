<?php
require_once 'db_connection.php';
require_once('fpdf/fpdf.php'); // Ensure the path is correct

$bandScore = isset($_GET['score']) ? floatval($_GET['score']) : 0.0;

if ($bandScore < 6) {
    header("Location: certificate_restriction.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $dob = $_POST['date_of_birth'];
    $testDate = $_POST['test_date'];
    $submittedAt = date('Y-m-d H:i:s');

    // Save to DB
    $query = "INSERT INTO certificate_info 
        (full_name, email, phone_number, date_of_birth, test_date, overall_band_score, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssd", $fullName, $email, $phone, $dob, $testDate, $bandScore, $submittedAt);

    
if ($stmt->execute()) {
    $fileName = 'certificate_' . time() . '_' . preg_replace('/\s+/', '_', strtolower($fullName)) . '.pdf';
    $filePath = 'certificate/' . $fileName;

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

// Outer Border (Thicker & darker)
$pdf->SetLineWidth(1.5);
$pdf->SetDrawColor(50, 50, 100); // Deep rich blue-gray tone
$pdf->Rect(10, 10, 190, 277);

// Inner Border (Thin, spaced inward for depth effect)
$pdf->SetLineWidth(0.4);
$pdf->SetDrawColor(180, 180, 180); // Light grey for contrast
$pdf->Rect(13, 13, 184, 271);


// Header background
$pdf->SetFillColor(230, 240, 255);
$pdf->Rect(10, 10, 190, 30, 'F');

// Title - Modern & Professional Like IDP
$pdf->SetFont('Helvetica', 'B', 22); // Use Helvetica for a modern sans-serif look
$pdf->SetTextColor(0, 51, 102); // Dark professional blue (IDP-like)
$pdf->SetXY(10, 15);
$pdf->Cell(190, 12, 'EDACADEMIX - IELTS CERTIFICATE', 0, 1, 'C');

// Optional: Thin line under the title (IDP style divider)
$pdf->SetDrawColor(0, 51, 102);
$pdf->SetLineWidth(0.3);
$pdf->Line(55, 29, 155, 29); // Underline from X=55 to X=155


// Logo (tripled size, centered under title)
$pdf->Image('images/edlo.jpg', 70, 40, 70); // Height = 70 → ends at Y ≈ 110

// Decorative line (tight gap after logo)
$pdf->SetDrawColor(90, 90, 150);
$pdf->SetLineWidth(0.8);
$pdf->Line(50, 95, 160, 95); // Just 2px below the logo's end

// Certification Line (consistent spacing)
$pdf->SetFont('Times', 'BI', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(10, 105); // 8px below the decorative line
$pdf->Cell(190, 10, "This is to certify that", 0, 1, 'C');

// Candidate Name
$pdf->SetFont('Times', 'BI', 26);
$pdf->SetTextColor(20, 20, 60);
$pdf->SetXY(10, 117); // 14px gap for emphasis
$pdf->Cell(190, 16, $fullName, 0, 1, 'C');

// Description line
$pdf->SetFont('Times', 'BI', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(10, 135); // 16px gap below name (balanced)
$pdf->Cell(190, 10, "has successfully completed the IELTS mock test with an overall band score of", 0, 1, 'C');

// Band Score
$pdf->SetFont('Times', 'BI', 24);
$pdf->SetTextColor(200, 50, 50);
$pdf->SetXY(10, 145); // 14px below description
$pdf->Cell(190, 16, number_format($bandScore, 1), 0, 1, 'C');


// Details Box (light gradient-like tone & border for elegance)
$detailsY = 164;
$detailsHeight = 44;
$pdf->SetFillColor(250, 250, 250);        // Softer, brighter fill
$pdf->SetDrawColor(100, 100, 120);        // Subtle elegant border
$pdf->SetLineWidth(0.6);                  // Slightly thicker for premium feel
$pdf->Rect(45, $detailsY, 120, $detailsHeight, 'FD');

// Text inside box
$padding = 6;
$pdf->SetFont('Times', 'I', 14);           // Light Italic for label
$pdf->SetTextColor(50, 50, 80);            // Muted blue-gray for luxury look

// Date of Birth
$pdf->SetXY(45 + $padding, $detailsY + $padding);
$pdf->Cell(45, 10, "Date of Birth:", 0, 0, 'L');

$pdf->SetFont('Times', 'BI', 14);          // Bold Italic for value
$pdf->SetTextColor(20, 20, 50);
$pdf->Cell(50, 10, $dob, 0, 1, 'L');

// Test Date
$pdf->SetFont('Times', 'I', 14);
$pdf->SetTextColor(50, 50, 80);
$pdf->SetXY(45 + $padding, $detailsY + $padding + 16);
$pdf->Cell(45, 10, "Test Date:", 0, 0, 'L');

$pdf->SetFont('Times', 'BI', 14);
$pdf->SetTextColor(20, 20, 50);
$pdf->Cell(50, 10, $testDate, 0, 1, 'L');

// Signature Placeholder
$pdf->SetFont('Times', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(120, $detailsY + $detailsHeight + 20); // Y=208
$pdf->Cell(70, 10, "__________________________", 0, 1, 'R');

$pdf->SetXY(120, $detailsY + $detailsHeight + 28); // Y=216
$pdf->Cell(70, 10, "Authorized Signature", 0, 1, 'R');

// Issued by text
$pdf->SetFont('Times', 'I', 14);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(10, $detailsY + $detailsHeight + 50); // Y=232
$pdf->Cell(190, 12, "Issued by EdAcademix Team", 0, 1, 'C');


    // Optional Watermark (uncomment if needed)
    // $pdf->SetFont('Arial', 'B', 50);
    // $pdf->SetTextColor(230, 230, 230);
    // $pdf->SetXY(20, 160);
    // $pdf->Cell(170, 30, 'AspireIELTS', 0, 0, 'C');

    // Save PDF
    $pdf->Output('F', $filePath);



        // Then redirect
        header("Location: submit_cert.php");
        exit();
    } else {
        echo "<script>alert('Submission failed. Please try again.'); window.location.href='cert.php?score=$bandScore';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Certificate Application</title>
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    perspective: 1500px;
    overflow: hidden;
  }

  .form-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 45px 40px;
    border-radius: 25px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
    width: 480px;
    animation: slideIn 0.8s ease-out, floatCard 5s ease-in-out infinite;
    transform-style: preserve-3d;
    transform: rotateX(0deg) rotateY(0deg);
    transition: transform 0.3s ease;
  }

  @keyframes slideIn {
    from {
      transform: translateY(80px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  @keyframes floatCard {
    0%, 100% {
      transform: translateY(0) rotateX(0deg) rotateY(0deg);
    }
    50% {
      transform: translateY(-10px) rotateX(1deg) rotateY(1deg);
    }
  }

  h2 {
    text-align: center;
    font-size: 26px;
    font-weight: 700;
    color: #333;
    margin-bottom: 30px;
    text-shadow: 1px 1px 1px #e0e0e0;
  }

  label {
    display: block;
    margin-top: 18px;
    font-weight: 600;
    color: #444;
    font-size: 14px;
    letter-spacing: 0.3px;
  }

  input[type="text"],
  input[type="email"],
  input[type="date"],
  input[type="tel"] {
    width: 100%;
    padding: 12px 14px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 12px;
    background-color: #fdfdfd;
    font-size: 15px;
    transition: all 0.3s ease;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  }

  input[type="text"]:focus,
  input[type="email"]:focus,
  input[type="date"]:focus,
  input[type="tel"]:focus {
    border-color: #4facfe;
    box-shadow: 0 0 10px rgba(0, 242, 254, 0.4);
    outline: none;
    background-color: #fff;
  }

  .readonly {
    background-color: #efefef;
  }

  button {
    width: 100%;
    margin-top: 35px;
    padding: 14px 0;
    background: linear-gradient(to right, #4facfe, #00f2fe);
    border: none;
    color: white;
    font-size: 17px;
    font-weight: 600;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0, 242, 254, 0.3);
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
  }

  button:hover {
    background: linear-gradient(to right, #0072ff, #00c6ff);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 24px rgba(0, 114, 255, 0.4);
  }

  button:active {
    transform: scale(0.98);
    box-shadow: 0 5px 15px rgba(0, 114, 255, 0.2);
  }
  .typing-banner {
  font-size: 20px;
  font-weight: bold;
  color: black;
  text-align: center;
  margin-bottom: 30px;
  white-space: nowrap;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}

#typing-text {
  display: inline-block;
  max-width: 100%;
}

.cursor {
  display: inline-block;
  animation: blink 0.7s infinite;
  font-weight: 900;
  color: #fff;
  margin-left: 4px;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}
#typing-text {
  font-family: 'Courier New', Courier, monospace; /* Unique typewriter style font */
  font-size: 28px; /* Bigger size */
  font-weight: 600;
  color:rgb(11, 11, 11);
  letter-spacing: 1.5px;
  white-space: pre-line; /* to respect line breaks */
}

</style>

</head>
<body>
<!-- Quit Button (fixed top right) -->
<button id="quit-btn" 
  style="
    position: fixed; 
    top: 20px; 
    right: 20px; 
    background: #ff4d4d; 
    border: none; 
    color: white; 
    padding: 10px 18px; 
    font-weight: bold; 
    border-radius: 8px; 
    cursor: pointer; 
    z-index: 1000;
    width: auto;
    max-width: 120px;
    white-space: nowrap;
    display: inline-block;
  ">
  Quit
</button>


<!-- Confirmation Modal -->
<div id="quit-modal" 
  style="
    display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100vw; 
    height: 100vh; 
    background: rgba(0,0,0,0.6); 
    backdrop-filter: blur(3px); 
    z-index: 1100; 
    justify-content: center; 
    align-items: center;
  ">
  <div 
    style="
      background: white; 
      padding: 30px 40px; 
      border-radius: 12px; 
      max-width: 400px; 
      text-align: center; 
      box-shadow: 0 10px 30px rgba(0,0,0,0.25); 
      font-family: 'Segoe UI', sans-serif;
    ">
    <h3 style="margin-bottom: 20px; color: #333;">Are you sure you want to quit?</h3>
    <div style="display: flex; justify-content: center; gap: 15px;">
      <button id="confirm-quit" 
        style="
          background: #ff4d4d; 
          color: white; 
          border: none; 
          padding: 10px 20px; 
          font-weight: bold; 
          border-radius: 8px; 
          cursor: pointer;
        ">
        Yes
      </button>
      <button id="cancel-quit" 
        style="
          background: #ccc; 
          color: #333; 
          border: none; 
          padding: 10px 20px; 
          font-weight: bold; 
          border-radius: 8px; 
          cursor: pointer;
        ">
        No
      </button>
    </div>
  </div>
</div>

<script>
  const quitBtn = document.getElementById('quit-btn');
  const quitModal = document.getElementById('quit-modal');
  const confirmQuit = document.getElementById('confirm-quit');
  const cancelQuit = document.getElementById('cancel-quit');

  quitBtn.addEventListener('click', () => {
    quitModal.style.display = 'flex';
  });

  cancelQuit.addEventListener('click', () => {
    quitModal.style.display = 'none';
  });

  confirmQuit.addEventListener('click', () => {
    window.location.href = 'dashboard.php';
  });

  quitModal.addEventListener('click', (e) => {
    if (e.target === quitModal) {
      quitModal.style.display = 'none';
    }
  });
</script>


  <div class="form-container">
    <!-- Typing Banner -->
  <div class="typing-banner">
    <span id="typing-text"></span>
    <span class="cursor">|</span>
  </div>
    <form method="POST" action="">
      <label for="full_name">Full Name</label>
      <input type="text" name="full_name" id="full_name" required>

      <label for="email">Email</label>
      <input type="email" name="email" id="email" required>

      <label for="phone_number">Phone Number</label>
      <input type="tel" name="phone_number" id="phone_number" required>

      <label for="date_of_birth">Date of Birth</label>
      <input type="date" name="date_of_birth" id="date_of_birth" required>

      <label for="test_date">Test Date</label>
      <input type="date" name="test_date" id="test_date" required>

      <label>Overall Band Score</label>
      <input type="text" value="<?php echo htmlspecialchars($bandScore); ?>" readonly class="readonly">

      <button type="submit">Submit Application</button>


    </form>
  </div>
<script>
const text = "Do you want our official certificate?\nThen fill up the form"; 
let i = 0; 
let forward = true; 
let speed = 75; 
const typingText = document.getElementById("typing-text"); 

function typeEffect() { 
  if (forward) { 
    typingText.innerText = text.slice(0, i++); 
    if (i > text.length) { 
      forward = false; 
      setTimeout(typeEffect, 1500); // wait before deleting 
      return; 
    } 
  } else { 
    typingText.innerText = text.slice(0, i--); 
    if (i < 0) { 
      typingText.innerText = ""; // clear text
      setTimeout(() => {
        forward = true;
        i = 0;
        typeEffect();
      }, 500); // pause before typing again
      return;
    } 
  } 
  setTimeout(typeEffect, forward ? speed : speed / 2); 
} 

document.addEventListener("DOMContentLoaded", typeEffect);</script>


</body>
</html>
