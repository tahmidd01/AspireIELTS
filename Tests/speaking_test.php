<?php
session_start();
require_once "../db_connection.php";

$userID = $_SESSION['UserID'];
$roomName = "AspireIELTS_SpeakingTest_User" . $userID;

// Insert or update the speaking session
$sql = "INSERT INTO speaking_sessions (user_id, room_name, status, started_at)
        VALUES (?, ?, 'ongoing', NOW())
        ON DUPLICATE KEY UPDATE status='ongoing', started_at=NOW(), ended_at=NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userID, $roomName);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- ✅ Inherit mode from your site (no toggle here) -->
    <script>
        (function () {
            try {
                if (localStorage.getItem('darkMode') === 'enabled') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speaking Test - AspireIELTS</title>

    <!-- Cognisense-ish fonts + icons -->
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
            --danger: #ef4444;

            --radius-xl: 26px;
            --radius-lg: 22px;
            --radius-md: 18px;
        }

        /* Dark mode variables (class is applied on <html>) */
        .dark-mode{
            --ana-surface: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
            --ana-card: rgba(15,23,42,.62);
            --ana-border: rgba(37,99,235,.55);
            --ana-shadow: 0 30px 90px rgba(0,0,0,.60);
            --ana-text: #e5e7eb;
            --ana-muted: rgba(229,231,235,.68);
        }

        *{ box-sizing: border-box; margin:0; padding:0; }

        body{
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(145deg, #e0eaff, #f0f4ff);
            color: var(--ana-text);
            padding: 22px 16px 26px;
            overflow-x: hidden;
        }
        .dark-mode body{
            background: linear-gradient(145deg, #0c0c0c, #050505);
        }

        /* Background aura + subtle grain */
        body::before{
            content:"";
            position: fixed;
            inset: 0;
            pointer-events:none;
            background:
                radial-gradient(900px 520px at 20% 15%, rgba(56,189,248,.14), transparent 60%),
                radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.12), transparent 62%),
                radial-gradient(700px 500px at 50% 95%, rgba(250,204,21,.08), transparent 65%);
            mix-blend-mode: soft-light;
            opacity: .95;
            z-index: -2;
        }
        .dark-mode body::before{ mix-blend-mode: screen; opacity: .7; }

        body::after{
            content:"";
            position: fixed;
            inset: 0;
            pointer-events:none;
            background:
                repeating-linear-gradient(
                    90deg,
                    rgba(15,23,42,.05) 0px,
                    rgba(15,23,42,.05) 1px,
                    transparent 1px,
                    transparent 52px
                );
            opacity: .10;
            z-index: -1;
        }
        .dark-mode body::after{
            opacity: .12;
            background:
                repeating-linear-gradient(
                    90deg,
                    rgba(129,140,248,.08) 0px,
                    rgba(129,140,248,.08) 1px,
                    transparent 1px,
                    transparent 54px
                );
        }

        .shell{
            max-width: 1180px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* Topbar */
        .topbar{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap: 12px;
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
        }
        .brand h1{
            margin:0;
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.14em;
            text-transform: uppercase;
            font-size: 1.05rem;
            line-height: 1.2;
        }
        .brand .sub{
            font-size: .88rem;
            color: var(--ana-muted);
            margin-top: 4px;
        }

        /* Quit button (same id) */
        #quitBtn{
            border:none;
            cursor:pointer;
            padding: 12px 16px;
            border-radius: 18px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color:#fff;
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            box-shadow: 0 18px 44px rgba(239,68,68,.18);
            transition: transform .18s ease, filter .2s ease, box-shadow .2s ease;
        }
        #quitBtn:hover{
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 22px 55px rgba(239,68,68,.22);
        }
        #quitBtn:active{ transform: translateY(0) scale(.98); }

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
            opacity: .88;
            pointer-events:none;
            z-index:0;
        }
        .hero > *{ position:relative; z-index:1; }

        .hero-top{
            display:flex;
            align-items:flex-start;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .hero-title{
            margin:0;
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.14em;
            text-transform: uppercase;
            font-size: clamp(1.55rem, 2.2vw, 2.2rem);
            background: linear-gradient(90deg, #38bdf8, #4f46e5, #facc15);
            -webkit-background-clip:text;
            background-clip:text;
            color: transparent;
        }
        .hero-sub{
            margin-top: 10px;
            color: var(--ana-muted);
            line-height: 1.6;
            max-width: 78ch;
        }

        .hero-badges{
            margin-top: 12px;
            display:flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .badge{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.40);
            border: 1px solid var(--ana-border);
            color: var(--ana-text);
            box-shadow: 0 14px 30px rgba(15,23,42,.08);
            font-size: .80rem;
            letter-spacing: .08em;
            white-space: nowrap;
        }
        .dark-mode .badge{ background: rgba(2,6,23,.28); }

        /* Stage (Jitsi card) */
        .stage{
            border-radius: var(--radius-xl);
            background: var(--ana-card);
            border: 1px solid var(--ana-border);
            box-shadow: 0 22px 55px rgba(15,23,42,.12);
            position: relative;
            overflow:hidden;
            isolation:isolate;
            transform-style: preserve-3d;
        }
        .stage::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: conic-gradient(from 180deg,
                rgba(56,189,248,.55),
                rgba(79,70,229,.45),
                rgba(250,204,21,.18),
                rgba(56,189,248,.55)
            );
            opacity: .16;
            filter: blur(14px);
            pointer-events:none;
            z-index:0;
        }
        .stage::after{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(900px 380px at 12% 0%, rgba(56,189,248,.10), transparent 62%),
                radial-gradient(900px 380px at 90% 10%, rgba(79,70,229,.08), transparent 64%);
            opacity: .95;
            pointer-events:none;
            z-index:0;
        }
        .stage > *{ position: relative; z-index: 1; }

        /* Keep your required id */
        #jitsi-container{
            width: 100%;
            height: 600px;              /* same height as your Jitsi options */
            border-radius: var(--radius-xl);
            overflow: hidden;
            transform-style: preserve-3d;
            animation: float3D 12s ease-in-out infinite;
        }

        @keyframes float3D {
            0% { transform: rotateX(3deg) rotateY(3deg); }
            25% { transform: rotateX(4deg) rotateY(-3deg); }
            50% { transform: rotateX(-3deg) rotateY(2deg); }
            75% { transform: rotateX(2deg) rotateY(-4deg); }
            100% { transform: rotateX(3deg) rotateY(3deg); }
        }

        /* Icons (subtle, decorative) */
        .floating-icons{
            position: fixed;
            left: 18px;
            bottom: 18px;
            display:flex;
            gap: 10px;
            opacity: .10;
            z-index: 2;
            pointer-events: none;
            filter: drop-shadow(0 0 12px rgba(56,189,248,.35));
            animation: floatIcons 6s ease-in-out infinite;
        }
        .floating-icons .icon{
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display:grid;
            place-items:center;
            background: rgba(255,255,255,.20);
            border: 1px solid rgba(148,163,184,.25);
            font-size: 26px;
        }
        .dark-mode .floating-icons .icon{ background: rgba(2,6,23,.22); border-color: rgba(129,140,248,.18); }

        @keyframes floatIcons {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        /* Quit modal (same ids) */
        #quitModal{
            display:none;
            position:fixed;
            inset:0;
            z-index: 5000;
            justify-content:center;
            align-items:center;
            padding: 24px;
            background:
                radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.22), transparent 60%),
                radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.20), transparent 62%),
                rgba(15, 23, 42, 0.32);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            color: var(--ana-text);
        }
        #quitModalContent{
            width: min(520px, 96vw);
            padding: 22px 20px;
            border-radius: 26px;
            background: var(--ana-surface);
            border: 1px solid var(--ana-border);
            box-shadow: 0 30px 80px rgba(15,23,42,.34);
            text-align:center;
            position: relative;
            overflow:hidden;
            isolation:isolate;
        }
        #quitModalContent::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: conic-gradient(from 180deg,
                rgba(56,189,248,.55),
                rgba(79,70,229,.45),
                rgba(250,204,21,.22),
                rgba(56,189,248,.55)
            );
            opacity:.16;
            filter: blur(12px);
            pointer-events:none;
            z-index:0;
        }
        #quitModalContent > *{ position:relative; z-index:1; }

        #quitModalContent h2{
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.12em;
            text-transform: uppercase;
            font-size: 1.05rem;
            margin-bottom: 10px;
        }
        #quitModalContent p{
            margin: 0 0 16px;
            color: var(--ana-muted);
            line-height: 1.6;
            font-weight: 600;
        }

        .modal-actions{
            display:flex;
            justify-content:center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .modal-btn{
            border:none;
            cursor:pointer;
            padding: 12px 16px;
            border-radius: 18px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            min-width: 160px;
            transition: transform .18s ease, filter .2s ease;
        }
        #confirmQuitBtn{
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color:#fff;
            box-shadow: 0 18px 44px rgba(239,68,68,.18);
        }
        #cancelQuitBtn{
            background: linear-gradient(135deg, #4a00e0, #06b6d4);
            color:#fff;
            box-shadow: 0 18px 44px rgba(37,99,235,.22);
        }
        .modal-btn:hover{ transform: translateY(-2px); filter: brightness(1.05); }
        .modal-btn:active{ transform: translateY(0) scale(.99); }

        @media (max-width: 980px){
            #jitsi-container{ height: 560px; }
        }
        @media (max-width: 720px){
            body{ padding: 18px 12px 22px; }
            #jitsi-container{ height: 520px; }
            .floating-icons{ display:none; }
        }

        @media (prefers-reduced-motion: reduce){
            #jitsi-container{ animation: none; }
            .floating-icons{ animation:none; }
        }
    </style>
</head>

<body>
    <div class="shell">
        <!-- TOP BAR -->
        <div class="topbar">
            <div class="brand">
                <div class="brand-badge" aria-hidden="true"><i class="fa-solid fa-microphone-lines"></i></div>
                <div>
                    <h1>Speaking Test</h1>
                    <div class="sub">AspireIELTS • Live speaking room</div>
                </div>
            </div>

            <!-- same id as your original -->
            <button id="quitBtn" type="button" title="Quit Test">Quit</button>
        </div>

        <!-- HERO -->
        <section class="hero" aria-label="Speaking test header">
            <div class="hero-top">
                <div>
                    <h2 class="hero-title">LIVE SPEAKING TEST</h2>
                    <p class="hero-sub">Please make sure your microphone and camera are enabled before you begin.</p>
                </div>
            </div>

            <div class="hero-badges" aria-hidden="true">
                <span class="badge"><i class="fa-solid fa-video"></i> Live session</span>
                <span class="badge"><i class="fa-solid fa-microphone"></i> Mic required</span>
                <span class="badge"><i class="fa-solid fa-user-tie"></i> Examiner room</span>
            </div>
        </section>

        <!-- JITSI STAGE -->
        <div class="stage">
            <div id="jitsi-container"></div>
        </div>
    </div>

    <!-- Decorative icons -->
    <div class="floating-icons" aria-hidden="true">
        <div class="icon">🎤</div>
        <div class="icon">📹</div>
    </div>

    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        const domain = "meet.jit.si";
        const roomName = "AspireIELTS_SpeakingTest_User<?= $userID ?>";
        const options = {
            roomName: roomName,
            width: "100%",
            height: 600,
            parentNode: document.querySelector('#jitsi-container'),
            configOverwrite: {
                startWithVideoMuted: false,
                startWithAudioMuted: false,
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                DEFAULT_REMOTE_DISPLAY_NAME: 'Examiner',
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);

        // 3D tilt effect based on mouse position (kept as in your file; no logic added here)
        const tiltContainer = document.getElementById("tilt-container");
        const jitsiContainer = document.getElementById("jitsi-container");
    </script>

    <!-- Quit Confirmation Modal (same ids, same logic) -->
    <div id="quitModal">
        <div id="quitModalContent">
            <h2>Quit Test?</h2>
            <p>Are you sure you want to quit the speaking test?</p>
            <div class="modal-actions">
                <button id="confirmQuitBtn" class="modal-btn" type="button">Yes</button>
                <button id="cancelQuitBtn" class="modal-btn" type="button">No</button>
            </div>
        </div>
    </div>

    <script>
        function showQuitModal() {
            const modal = document.getElementById('quitModal');
            modal.style.display = 'flex';
        }

        function hideQuitModal() {
            document.getElementById('quitModal').style.display = 'none';
        }

        function confirmQuit() {
            window.location.href = "../dashboard.php"; // Adjust path if needed
        }

        // Same behavior, but avoids overwriting other onload logic
        window.addEventListener('load', () => {
            document.getElementById('quitBtn').addEventListener('click', showQuitModal);
            document.getElementById('cancelQuitBtn').addEventListener('click', hideQuitModal);
            document.getElementById('confirmQuitBtn').addEventListener('click', confirmQuit);
        });

        // Optional: click outside closes (safe)
        window.addEventListener('click', (e) => {
            const m = document.getElementById('quitModal');
            if (m && m.style.display === 'flex' && e.target === m) hideQuitModal();
        });

        // Optional: ESC closes (safe)
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideQuitModal();
        });
    </script>
</body>
</html>
