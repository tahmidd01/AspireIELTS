<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Writing Tips</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
    }

     body {
  margin: 0;
  padding: 0;
  background-color: black;
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

  <h1>✍️ Writing Tips for Success</h1>

  <div class="tip-container">
    <div class="tip-card">
      <div class="tip-number">Tip 1</div>
      <div class="tip-text">Plan your essay before writing. Outline your main ideas and supporting points clearly.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 2</div>
      <div class="tip-text">Write a clear introduction and conclusion to frame your argument effectively.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 3</div>
      <div class="tip-text">Use a range of vocabulary and sentence structures to demonstrate language ability.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 4</div>
      <div class="tip-text">Keep your writing focused on the task. Avoid going off-topic or adding irrelevant details.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 5</div>
      <div class="tip-text">Check your grammar, punctuation, and spelling carefully before submitting.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 6</div>
      <div class="tip-text">Practice writing under timed conditions to build speed and confidence.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 7</div>
      <div class="tip-text">Use linking words and phrases to improve coherence and flow between sentences and paragraphs.</div>
    </div>
    <div class="tip-card">
      <div class="tip-number">Tip 8</div>
      <div class="tip-text">Read sample essays and model answers to learn effective writing techniques.</div>
    </div>
  </div>

  <form action="writing_course.php" method="get">
    <button class="start-button" type="submit" aria-label="Start Writing Course">Start Writing Course</button>
  </form>

</body>
</html>
