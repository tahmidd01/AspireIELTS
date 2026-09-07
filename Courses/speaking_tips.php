<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Speaking Tips</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
  margin: 0;
  padding: 0;
  background-color:rgb(193, 113, 125);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  overflow-x: hidden;
  perspective: 1200px;
}

     .dashboard-button {
      margin-top: 20px;
      align-self: flex-start;
      margin-left: 20px;
    }

    .dashboard-button a {
  text-decoration: none;
  background: linear-gradient(135deg, #00ccff, #00ffcc);
  color: #000;
  padding: 15px 35px;
  border-radius: 40px;
  font-weight: bold;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  box-shadow: 0 10px 25px rgba(0, 255, 255, 0.3);
  display: inline-block;
}

.dashboard-button a:hover {
  background: linear-gradient(135deg, #00ffcc, #00ccff);
  transform: scale(1.05);
  box-shadow: 0 15px 35px rgba(0, 255, 255, 0.5);
}

    h1 {
      margin-top: 60px;
      font-size: 3rem;
      color: #00ffff;
      text-shadow: 0 0 15px #00ffff, 0 0 30px #00ccff;
      animation: floatText 4s ease-in-out infinite;
      text-align: center;
    }

    @keyframes floatText {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .tip-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      padding: 50px;
      width: 90%;
      transform-style: preserve-3d;
      animation: fadeIn 1.5s ease;
    }

    .tip-card {
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
      transform: rotateY(15deg) rotateX(2deg) translateZ(20px);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      position: relative;
      overflow: hidden;
    }

    .tip-card:hover {
      transform: rotateY(0deg) rotateX(0deg) scale(1.05);
      box-shadow: 0 20px 50px rgba(0, 255, 255, 0.3);
    }

    .tip-number {
      font-size: 1.2rem;
      font-weight: bold;
      color: #00ffff;
      margin-bottom: 12px;
    }

    .tip-text {
      font-size: 1rem;
      line-height: 1.6;
      color: #e0f7ff;
    }

    .start-button {
      margin: 30px auto 60px auto;
      padding: 15px 35px;
      font-size: 1.1rem;
      background: linear-gradient(135deg, #00ccff, #00ffcc);
      color: #000;
      font-weight: bold;
      border: none;
      border-radius: 40px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 10px 25px rgba(0, 255, 255, 0.3);
      display: block;
    }

    .start-button:hover {
      background: linear-gradient(135deg, #00ffcc, #00ccff);
      transform: scale(1.05);
      box-shadow: 0 15px 35px rgba(0, 255, 255, 0.5);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.95) translateZ(-100px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateZ(0);
      }
    }

    @media (max-width: 600px) {
      h1 {
        font-size: 2rem;
      }

      .tip-container {
        padding: 20px;
      }

      .start-button {
        width: 90%;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>

<div class="dashboard-button">
    <a href="../dashboard.php">← Back to Dashboard</a>
  </div>

  <h1>🗣️ Speaking Tips for Success</h1>

  <div class="tip-container">
    <div class="tip-card">
      <div class="tip-number">Tip 1</div>
      <div class="tip-text">Practice speaking regularly with a partner or in front of a mirror to build confidence and fluency.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 2</div>
      <div class="tip-text">Listen to native speakers and imitate their pronunciation, intonation, and rhythm.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 3</div>
      <div class="tip-text">Expand your vocabulary by learning phrases and expressions related to common IELTS topics.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 4</div>
      <div class="tip-text">Don’t rush. Speak clearly and at a natural pace to improve comprehension and reduce mistakes.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 5</div>
      <div class="tip-text">Use a range of grammatical structures and link your ideas logically using connectors like “however” and “therefore.”</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 6</div>
      <div class="tip-text">Practice answering common IELTS speaking questions and try to develop your answers fully with examples.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 7</div>
      <div class="tip-text">Record yourself speaking and listen back to identify areas for improvement in pronunciation and grammar.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 8</div>
      <div class="tip-text">Stay calm during the test. Take a deep breath if you get stuck, and try to keep speaking without long pauses.</div>
    </div>
  </div>

  <form action="speaking_course.php" method="get">
    <button class="start-button" type="submit" aria-label="Start Speaking Course">Start Speaking Course</button>
  </form>

</body>
</html>
