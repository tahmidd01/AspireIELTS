<?php
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Get Offer Letter</title>
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

* {
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

  body {
    background: linear-gradient(135deg,rgb(123, 125, 165), #7f87f7);
    margin: 0;
    padding: 50px 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    perspective: 1400px;
    overflow-x: hidden;
  }

  .form-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: saturate(180%) blur(16px);
    border-radius: 30px;
    padding: 50px 60px;
    max-width: 900px;
    width: 100%;
    box-shadow:
      0 20px 50px rgba(0, 0, 0, 0.25),
      inset 0 0 20px rgba(108, 99, 255, 0.25);
    transform-style: preserve-3d;
    animation: cardEntrance 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    transform-origin: center bottom;
    border: 1.5px solid rgba(108, 99, 255, 0.4);
  }

  h2 {
    text-align: center;
    margin-bottom: 50px;
    color: #1b1b3a;
    font-weight: 800;
    font-size: 3rem;
    letter-spacing: 2px;
    text-shadow:
      0 2px 8px rgba(108, 99, 255, 0.55),
      0 0 12px rgba(108, 99, 255, 0.4);
  }

  label {
    font-weight: 700;
    display: block;
    margin-top: 25px;
    color: #2a2a50;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    user-select: none;
    transition: color 0.3s ease;
  }

  label:hover {
    color: #6c63ff;
  }

  input, select, textarea {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #cfd7ff;
    border-radius: 18px;
    margin-top: 10px;
    font-size: 1.1rem;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow:
      inset 0 4px 10px rgba(0, 0, 0, 0.05),
      0 2px 8px rgba(108, 99, 255, 0.1);
    background: #fefeff;
    font-weight: 500;
    transform-style: preserve-3d;
    color: #2a2a50;
    letter-spacing: 0.02em;
  }

  input::placeholder,
  textarea::placeholder {
    color: #a2a6c6;
    font-weight: 400;
  }

  input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #6c63ff;
    box-shadow:
      0 0 25px rgba(108, 99, 255, 0.6),
      inset 0 0 12px rgba(108, 99, 255, 0.3);
    background: #fff;
    color: #1b1b3a;
    transform: translateZ(40px);
  }

  .file-input {
    padding: 8px 0;
  }

  .submit-btn {
    background: linear-gradient(135deg, #6c63ff, #5549d5);
    color: #fff;
    border: none;
    padding: 20px;
    width: 100%;
    border-radius: 20px;
    margin-top: 50px;
    font-size: 1.4rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow:
      0 12px 30px rgba(108, 99, 255, 0.75),
      0 8px 10px rgba(0, 0, 0, 0.25);
    transition: all 0.35s ease;
    transform-style: preserve-3d;
    letter-spacing: 0.08em;
    user-select: none;
    text-transform: uppercase;
  }

  .submit-btn:hover {
    background: linear-gradient(135deg, #5549d5, #3f3999);
    box-shadow:
      0 18px 40px rgba(85, 73, 213, 0.95),
      0 12px 15px rgba(0, 0, 0, 0.35);
    transform: translateZ(14px) scale(1.02);
  }

  .submit-btn:active {
    transform: translateZ(8px) scale(0.98);
    box-shadow:
      0 10px 22px rgba(85, 73, 213, 0.7),
      0 6px 8px rgba(0, 0, 0, 0.2);
  }

  /* Subtle floating effect on input focus */
  input:focus, select:focus, textarea:focus {
    animation: floatUp 0.6s forwards;
  }

  @keyframes floatUp {
    0% {
      transform: translateZ(0);
    }
    100% {
      transform: translateZ(40px);
    }
  }

  @keyframes cardEntrance {
    0% {
      opacity: 0;
      transform: rotateX(18deg) translateY(60px) scale(0.94);
      filter: blur(10px);
    }
    100% {
      opacity: 1;
      transform: rotateX(0deg) translateY(0) scale(1);
      filter: blur(0);
    }
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .form-card {
      padding: 35px 30px;
      border-radius: 20px;
    }
    h2 {
      font-size: 2.25rem;
      margin-bottom: 35px;
    }
    label {
      font-size: 1rem;
    }
    input, select, textarea {
      font-size: 1rem;
      padding: 14px 16px;
      border-radius: 14px;
    }
    .submit-btn {
      font-size: 1.15rem;
      padding: 16px;
      border-radius: 16px;
      margin-top: 35px;
    }
  }
  .file-input {
  margin-top: 20px;
}

input[type="file"] {
  padding: 14px 18px;
  font-size: 1.05rem;
  font-weight: 600;
  border: 2px solid #ccc;
  border-radius: 14px;
  background: #fafafa;
  color: #333;
  width: 100%;
  cursor: pointer;
  transition: all 0.3s ease;
}

input[type="file"]::file-selector-button {
  font-size: 1.05rem;
  font-weight: 600;
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #6c63ff, #5549d5);
  color: white;
  cursor: pointer;
  transition: background 0.3s ease, transform 0.2s ease;
  box-shadow: 0 4px 10px rgba(108, 99, 255, 0.4);
}

input[type="file"]::file-selector-button:hover {
  background: linear-gradient(135deg, #5549d5, #3f3999);
  transform: scale(1.05);
}
.typing-text {
  text-align: center;
  font-weight: 700;
  font-size: 2rem;
  color: #222;
  margin-bottom: 40px;
}

.typing-text span::after {
  content: "|";
  animation: blink 0.8s infinite;
  color: #6c63ff;
  font-weight: bold;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}


</style>

</head>
<body>
  <form class="form-card" action="submit_offer.php" method="POST" enctype="multipart/form-data">
    <h2 class="typing-text">
  <span id="line1"></span><br>
  <span id="line2"></span>
</h2>



    <!-- Personal Info -->
    <label>Full Name (as per passport/NID):</label>
    <input type="text" name="full_name" required>

    <label>Father’s Name:</label>
    <input type="text" name="father_name" required>

    <label>Mother’s Name:</label>
    <input type="text" name="mother_name" required>

    <label>Date of Birth:</label>
    <input type="date" name="dob" required>

    <label>Nationality:</label>
    <input type="text" name="nationality" required>

    <label>Religion:</label>
    <input type="text" name="religion" required>

    <label>Marital Status:</label>
    <input type="text" name="marital_status" required>

    <!-- Contact Info -->
    <label>Phone Number:</label>
    <input type="text" name="phone" required>

    <label>Email Address:</label>
    <input type="email" name="email" required>

    <label>Present Address:</label>
    <textarea name="present_address" required></textarea>

    <!-- Academic -->
    <label>SSC/O Levels Passing Year:</label>
    <input type="text" name="ssc_year" required>

    <label>HSC/A Levels Passing Year:</label>
    <input type="text" name="hsc_year" required>


    <label>Medium of Study:</label>
    <select name="medium" required>
      <option value="English">English</option>
      <option value="Bangla">Bangla</option>
    </select>

    <!-- File Uploads -->
    <label>National ID Card (NID):</label>
    <input class="file-input" type="file" name="nid_card" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>SSC Certificate or Marksheet:</label>
    <input class="file-input" type="file" name="ssc_certificate" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>HSC Certificate or Marksheet:</label>
    <input class="file-input" type="file" name="hsc_certificate" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>Valid Passport (if available):</label>
    <input class="file-input" type="file" name="passport_scan" accept=".jpg,.jpeg,.png,.pdf">

    <label>English Language Proficiency Certificate:</label>
    <input class="file-input" type="file" name="english_cert" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>Father’s NID Card:</label>
    <input class="file-input" type="file" name="father_nid" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>Mother’s NID Card:</label>
    <input class="file-input" type="file" name="mother_nid" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>Letter of Recommendation (LOR):</label>
    <input class="file-input" type="file" name="lor" accept=".jpg,.jpeg,.png,.pdf" required>

    <label>Statement of Purpose (optional):</label>
    <input class="file-input" type="file" name="sop" accept=".jpg,.jpeg,.png,.pdf">

    <label>CV/Resume (optional):</label>
    <input class="file-input" type="file" name="cv" accept=".jpg,.jpeg,.png,.pdf">

    <label>Passport Size Photograph:</label>
    <input class="file-input" type="file" name="passport_photo" accept=".jpg,.jpeg,.png,.pdf" required>

        <label>Country Preference:</label>
    <input type="text" name="country_of_choice" required>

            <label>University Preference:</label>
    <input type="text" name="preferred_university" required>

                <label>Which program do you wanna apply for:</label>
    <select name="program_type" required>
  <option value="Undergraduate">Undergraduate</option>
  <option value="Graduate">Graduate</option>
  <option value="PhD">PhD</option>
</select>
                <label>What is your preferred subject :</label>
    <input type="text" name="preferred_subject" required>

    <label>Intake Sessson:</label>
<select name="intake_season" required>
  <option value="Spring">Spring</option>
  <option value="Summer">Summer</option>
  <option value="Fall">Fall</option>
  <option value="Winter">Winter</option>
</select>

<label>Intake Year:</label>
<select name="intake_year" required>
  <option value="2023">2023</option>
  <option value="2024">2024</option>
  <option value="2025">2025</option>
  <option value="2026">2026</option>
  <option value="2027">2027</option>
  <option value="2028">2028</option>
  <option value="2029">2029</option>
  <option value="2030">2030</option>
</select>
<label>Have you already applied to any universities?</label>
<select name="already_applied" required>
  <option value="Yes">Yes</option>
  <option value="No">No</option>
</select>
<!-- Declaration Confirmation -->
<label for="declaration_confirmation">I confirm that all information provided is accurate:</label>
<select name="declaration_confirmation" id="declaration_confirmation" required>
  <option value="" disabled selected>Select</option>
  <option value="Yes">Yes</option>
  <option value="No">No</option>
</select>

<!-- Data Usage Agreement Checkbox (Google Form Style) -->
<div style="display: flex; align-items: center; justify-content: space-between; margin: 15px 0; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background-color: #f9f9f9;">
  <label for="agree_data_use" style="font-size: 16px; color: #333; flex: 1;">
    I agree to allow EdAcademix to use my data for university application processing.
  </label>
  <input type="checkbox" id="agree_data_use" name="agree_data_use" required style="width: 28px; height: 28px; cursor: pointer; accent-color: #1a73e8;">
</div>

    <button class="submit-btn" type="submit">Submit Request</button>
  </form>

<script>
  const line1Text = "Do you want an Offer Letter?";
  const line2Text = "Then fill up the credentials";

  const line1El = document.getElementById("line1");
  const line2El = document.getElementById("line2");

  let i = 0, j = 0, isErasing = false;

  function typeLine1() {
    if (!isErasing) {
      if (i <= line1Text.length) {
        line1El.textContent = line1Text.substring(0, i);
        i++;
        setTimeout(typeLine1, 80);
      } else {
        setTimeout(typeLine2, 500);
      }
    } else {
      if (i >= 0) {
        line1El.textContent = line1Text.substring(0, i);
        i--;
        setTimeout(typeLine1, 40);
      } else {
        isErasing = false;
        typeLine1();
      }
    }
  }

  function typeLine2() {
    if (!isErasing) {
      if (j <= line2Text.length) {
        line2El.textContent = line2Text.substring(0, j);
        j++;
        setTimeout(typeLine2, 80);
      } else {
        setTimeout(eraseLines, 2000);
      }
    } else {
      if (j >= 0) {
        line2El.textContent = line2Text.substring(0, j);
        j--;
        setTimeout(typeLine2, 40);
      } else {
        isErasing = false;
        typeLine1();
      }
    }
  }

  function eraseLines() {
    isErasing = true;
    typeLine2();
  }

  window.onload = typeLine1;
</script>


</body>
</html>