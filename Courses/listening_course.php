<?php
$conn = new mysqli("localhost", "root", "", "aspireielts");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$sql = "SELECT video_number, video_path FROM sectionvideos 
        WHERE section = 'listening' 
        ORDER BY video_number ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Listening Course</title>

  <!-- Keep same “outside theme” behavior (no toggle here) -->
  <script>
    (function () {
      try {
        if (localStorage.getItem('darkMode') === 'enabled') {
          document.documentElement.classList.add('dark-mode');
        }
      } catch (e) {}
    })();
  </script>

  <!-- Fonts + Icons (Cognisense style) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --ana-surface: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
      --ana-card: rgba(255,255,255,.72);
      --ana-border: rgba(148,163,184,.45);
      --ana-shadow: 0 26px 70px rgba(15,23,42,.14);
      --ana-text: #0b1120;
      --ana-muted: rgba(15,23,42,.62);

      --cyan: #38bdf8;
      --indigo: #4f46e5;
      --gold: #facc15;

      --radius-xl: 26px;
      --radius-lg: 22px;
      --radius-md: 18px;
    }

    html.dark-mode{
      --ana-surface: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
      --ana-card: rgba(15,23,42,.62);
      --ana-border: rgba(37,99,235,.55);
      --ana-shadow: 0 30px 90px rgba(0,0,0,.60);
      --ana-text: #e5e7eb;
      --ana-muted: rgba(229,231,235,.68);
    }

    *{ box-sizing:border-box; }

    body{
      margin:0;
      min-height:100vh;
      font-family:'Poppins',sans-serif;
      color: var(--ana-text);
      background: linear-gradient(145deg, #e0eaff, #f0f4ff);
      padding: 22px 18px 70px;
      overflow-x:hidden;
      transition: background .35s ease, color .35s ease;
    }

    html.dark-mode body{
      background: linear-gradient(145deg, #0c0c0c, #050505);
    }

    /* Aura background */
    body::before{
      content:"";
      position: fixed;
      inset:0;
      pointer-events:none;
      background:
        radial-gradient(900px 520px at 20% 15%, rgba(56,189,248,.14), transparent 60%),
        radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.12), transparent 62%);
      mix-blend-mode: soft-light;
      opacity: .95;
      z-index:-1;
    }
    html.dark-mode body::before{ mix-blend-mode: screen; opacity: .7; }

    .shell{
      max-width: 1180px;
      margin: 0 auto;
    }

    /* Topbar */
    .topbar{
      display:flex;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      margin-bottom: 14px;
    }

    .brand{
      display:flex;
      align-items:center;
      gap: 12px;
      min-width: 0;
    }

    .brand-badge{
      width: 46px;
      height: 46px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      background: radial-gradient(circle at 30% 10%,
        rgba(254,249,195,.92),
        rgba(56,189,248,.55),
        rgba(79,70,229,.45));
      box-shadow: 0 14px 26px rgba(37,99,235,.16);
      flex: 0 0 auto;
    }

    .brand-title{
      margin:0;
      font-family:'Orbitron','Poppins',sans-serif;
      letter-spacing: .12em;
      text-transform: uppercase;
      font-size: 1.05rem;
      line-height: 1.2;
      white-space: nowrap;
    }
    .brand-sub{
      margin-top:4px;
      font-size: .88rem;
      color: var(--ana-muted);
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 60vw;
    }

    .dash-btn{
      display:inline-flex;
      align-items:center;
      gap: 10px;
      padding: 12px 16px;
      border-radius: 18px;
      text-decoration:none;
      color:#fff;
      font-weight: 900;
      letter-spacing: .08em;
      text-transform: uppercase;
      background: linear-gradient(135deg, #4a00e0, #06b6d4);
      box-shadow: 0 18px 44px rgba(37,99,235,.22);
      transition: transform .18s ease, filter .2s ease, box-shadow .2s ease;
      flex: 0 0 auto;
    }
    .dash-btn:hover{
      transform: translateY(-2px);
      filter: brightness(1.05);
      box-shadow: 0 26px 70px rgba(37,99,235,.26);
    }
    .dash-btn:active{ transform: translateY(0) scale(.99); }

    /* Hero */
    .hero{
      border-radius: var(--radius-xl);
      padding: 18px 18px;
      background: var(--ana-surface);
      border: 1px solid var(--ana-border);
      box-shadow: var(--ana-shadow);
      position: relative;
      overflow:hidden;
      isolation:isolate;
      margin-bottom: 16px;
    }
    .hero::before{
      content:"";
      position:absolute;
      inset:-2px;
      border-radius: inherit;
      background: conic-gradient(from 190deg,
        rgba(56,189,248,.40),
        rgba(79,70,229,.34),
        rgba(250,204,21,.18),
        rgba(56,189,248,.40)
      );
      opacity: .22;
      filter: blur(12px);
      pointer-events:none;
      z-index:0;
    }
    .hero::after{
      content:"";
      position:absolute;
      inset:0;
      border-radius: inherit;
      background:
        radial-gradient(860px 360px at 18% 8%, rgba(56,189,248,.16), transparent 62%),
        radial-gradient(860px 420px at 86% 16%, rgba(79,70,229,.12), transparent 65%);
      opacity: .86;
      pointer-events:none;
      z-index:0;
    }
    .hero > *{ position:relative; z-index:1; }

    .hero-title{
      margin: 0;
      font-family:'Orbitron','Poppins',sans-serif;
      letter-spacing:.14em;
      text-transform: uppercase;
      font-size: clamp(1.4rem, 2.3vw, 2.1rem);
      background: linear-gradient(90deg, var(--cyan), var(--indigo), var(--gold));
      -webkit-background-clip:text;
      background-clip:text;
      color: transparent;
    }
    .hero-sub{
      margin: 10px 0 0;
      color: var(--ana-muted);
      line-height: 1.65;
      max-width: 75ch;
    }

    /* Grid */
    .video-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 16px;
      margin-top: 16px;
    }

    .video-card{
      background: var(--ana-card);
      border: 1px solid var(--ana-border);
      border-radius: var(--radius-xl);
      box-shadow: 0 22px 55px rgba(15,23,42,.10);
      overflow:hidden;
      position: relative;
      isolation:isolate;
      transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
      opacity: 0;
      transform: translateY(18px);
      will-change: transform;
    }
    .video-card::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(820px 260px at 10% 0%, rgba(56,189,248,.12), transparent 60%),
        radial-gradient(820px 260px at 90% 10%, rgba(79,70,229,.10), transparent 62%);
      opacity: .95;
      pointer-events:none;
      z-index:0;
    }
    .video-card > *{ position:relative; z-index:1; }

    .video-card:hover{
      transform: translateY(-4px);
      border-color: rgba(56,189,248,.45);
      box-shadow: 0 30px 80px rgba(15,23,42,.16);
    }

    /* Entrance (uses your existing JS that sets animationDelay) */
    .video-card{
      animation: cardIn .8s ease forwards;
    }
    @keyframes cardIn{
      to { opacity: 1; transform: translateY(0); }
    }

    .video-wrap{
      padding: 12px;
    }

    video{
      width: 100%;
      max-width: 100%;
      border-radius: var(--radius-lg);
      display:block;
      background: rgba(0,0,0,.18);
      outline:none;
    }

    .label{
      margin: 10px 12px 14px;
      padding: 10px 12px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,.30);
      background: rgba(255,255,255,.40);
      color: var(--ana-text);
      font-weight: 900;
      letter-spacing: .10em;
      text-transform: uppercase;
      font-size: .80rem;
      display:flex;
      align-items:center;
      justify-content:center;
      gap: 10px;
      user-select:none;
    }
    html.dark-mode .label{
      background: rgba(2,6,23,.28);
      border-color: rgba(129,140,248,.18);
    }

    .empty{
      margin-top: 18px;
      padding: 18px;
      border-radius: var(--radius-xl);
      background: var(--ana-card);
      border: 1px solid var(--ana-border);
      box-shadow: 0 22px 55px rgba(15,23,42,.10);
      color: var(--ana-muted);
      text-align:center;
    }
    .empty strong{
      color: var(--ana-text);
      letter-spacing:.08em;
      text-transform: uppercase;
      font-family:'Orbitron','Poppins',sans-serif;
    }

    @media (max-width: 520px){
      .dash-btn{ padding: 10px 12px; border-radius: 16px; }
      .brand-sub{ max-width: 52vw; }
      .video-grid{ grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>
  <div class="shell">

    <header class="topbar">
      <div class="brand">
        <div class="brand-badge" aria-hidden="true"><i class="fa-solid fa-headphones"></i></div>
        <div>
          <div class="brand-title">Listening Course</div>
          <div class="brand-sub">Lecture videos playlist • AspireIELTS</div>
        </div>
      </div>

      <a href="../dashboard.php" class="dash-btn" title="Back to Dashboard">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>
    </header>

    <section class="hero" aria-label="Listening course header">
      <h1 class="hero-title">🎧 Listening Course Playlist</h1>
      <p class="hero-sub">Watch the videos below to improve your listening skills. Use fullscreen if needed—your layout will stay stable.</p>
    </section>

    <div class="video-grid">
      <?php
      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $num = htmlspecialchars($row['video_number']);
              $path = htmlspecialchars($row['video_path']);
              $adjusted_path = "../" . $path;

              echo "
              <div class='video-card'>
                <div class='video-wrap'>
                  <video controls controlsList='nodownload'>
                    <source src='$adjusted_path' type='video/mp4'>
                    Your browser does not support the video tag.
                  </video>
                </div>
                <div class='label'><i class='fa-solid fa-play'></i> Listening Video $num</div>
              </div>";
          }
      } else {
          echo "<div class='empty'><strong>No videos found</strong><br/>No videos found for Listening section.</div>";
      }
      $conn->close();
      ?>
    </div>

  </div>

  <script>
    // Fix video card size when exiting fullscreen (LOGIC UNCHANGED)
    document.querySelectorAll('video').forEach(video => {
      function fixCardSizeOnExit() {
        const isFullscreen = document.fullscreenElement || document.webkitFullscreenElement;
        if (!isFullscreen) {
          const card = video.closest('.video-card');
          if (!card) return;

          const originalWidth = card.offsetWidth + 'px';
          const originalHeight = card.offsetHeight + 'px';

          card.style.transition = 'none';
          card.style.width = originalWidth;
          card.style.height = originalHeight;

          void card.offsetWidth;

          setTimeout(() => {
            card.style.transition = '';
            card.style.width = '';
            card.style.height = '';
          }, 150);
        }
      }

      video.addEventListener('fullscreenchange', fixCardSizeOnExit);
      video.addEventListener('webkitfullscreenchange', fixCardSizeOnExit);
    });

    // Trigger 3D entrance effect on load (LOGIC UNCHANGED)
    window.addEventListener("load", () => {
      document.querySelectorAll(".video-card").forEach((card, index) => {
        card.style.animationDelay = (index * 0.15) + "s";
        card.classList.add("animate-in");
      });
    });
  </script>
</body>
</html>
