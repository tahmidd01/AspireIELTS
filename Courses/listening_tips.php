<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Listening Tips</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
  margin: 0;
  padding: 0;
  background-color: #001f3f; /* Deep Blue */
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
      margin-top: 30px;
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

      .dashboard-button {
        align-self: center;
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

  <div class="dashboard-button">
    <a href="../dashboard.php">← Back to Dashboard</a>
  </div>

  <h1>🎧 Listening Tips for Success</h1>

  <div class="tip-container">
    <div class="tip-card">
      <div class="tip-number">Tip 1</div>
      <div class="tip-text">Stay focused and avoid distractions during the audio. Practice in a quiet environment to simulate real test conditions.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 2</div>
      <div class="tip-text">Read the questions before the audio begins. This helps you anticipate the answers while listening.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 3</div>
      <div class="tip-text">Use shorthand for quick note-taking. Keywords and abbreviations help you keep up with fast conversations.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 4</div>
      <div class="tip-text">Double-check spelling and grammar before submitting. Incorrect spellings will lose marks even if the answer is right.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 5</div>
      <div class="tip-text">Practice with a timer. Train your mind to process audio and answer under time constraints.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 6</div>
      <div class="tip-text">Don’t panic if you miss an answer. Stay calm and refocus on the next question to avoid missing more.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 7</div>
      <div class="tip-text">Listen for signpost words like “however,” “but,” and “finally” to track the flow of conversation.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 8</div>
      <div class="tip-text">Be aware of different accents. Practice listening to British, American, and Australian speakers.</div>
    </div>
  </div>

  <form action="listening_course.php" method="get">
    <button class="start-button" type="submit" aria-label="Start Listening Course">Start Listening Course</button>
  </form>

</body>
</html>
