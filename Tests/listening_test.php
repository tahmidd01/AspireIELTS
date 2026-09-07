<?php
session_start();
require_once "../db_connection.php";

$user_id = $_SESSION['UserID'];
$current_set = null;
$test_type = 'Listening'; // Proper case to match database

// STEP 1: Check which sets the user has already completed
$sql = "SELECT DISTINCT SetNumber FROM testresponses WHERE UserID = ? AND TestType = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $test_type);
$stmt->execute();
$result = $stmt->get_result();

$completed_sets = [];
while ($row = $result->fetch_assoc()) {
    $completed_sets[] = (int)$row['SetNumber'];
}

// STEP 2: Determine which set the user should take next
for ($i = 1; $i <= 3; $i++) {
    if (!in_array($i, $completed_sets)) {
        $current_set = $i;
        break;
    }
}

// STEP 3: If all sets completed
if ($current_set === null) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <script>
(function () {
  try {
    if (localStorage.getItem('darkMode') === 'enabled') {
      document.documentElement.classList.add('dark-mode');
    }
  } catch (e) {}
})();
</script>

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Listening Test - Completed</title>
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
            }
            body{
                margin:0; min-height:100vh;
                font-family:'Poppins',sans-serif;
                display:grid; place-items:center;
                background: linear-gradient(145deg, #e0eaff, #f0f4ff);
                color: var(--ana-text);
                padding: 24px;
            }
            body::before{
                content:""; position:fixed; inset:0; pointer-events:none;
                background:
                    radial-gradient(900px 520px at 20% 15%, rgba(56,189,248,.16), transparent 60%),
                    radial-gradient(900px 520px at 85% 20%, rgba(79,70,229,.12), transparent 62%);
                mix-blend-mode: soft-light;
            }
            .card{
                max-width: 820px;
                width: 100%;
                padding: 26px;
                border-radius: 26px;
                background: var(--ana-surface);
                border: 1px solid var(--ana-border);
                box-shadow: var(--ana-shadow);
                position: relative;
                overflow:hidden;
                isolation:isolate;
            }
            .card::before{
                content:""; position:absolute; inset:-2px; border-radius: inherit;
                background: conic-gradient(from 180deg,
                    rgba(56,189,248,.55),
                    rgba(79,70,229,.45),
                    rgba(250,204,21,.22),
                    rgba(56,189,248,.55)
                );
                opacity:.22; filter: blur(12px); pointer-events:none;
                z-index:0;
            }
            .card > *{ position:relative; z-index:1; }
            h1{
                margin:0;
                font-family:'Orbitron','Poppins',sans-serif;
                letter-spacing:.12em;
                text-transform: uppercase;
                font-size: 1.6rem;
                background: linear-gradient(90deg, #38bdf8, #4f46e5, #facc15);
                -webkit-background-clip:text;
                background-clip:text;
                color:transparent;
            }
            p{ margin: 12px 0 18px; color: var(--ana-muted); line-height:1.6; }
            .btn{
                display:inline-flex;
                align-items:center;
                gap:10px;
                padding: 12px 16px;
                border-radius: 16px;
                border: none;
                cursor:pointer;
                text-decoration:none;
                color:#fff;
                font-weight:800;
                background: linear-gradient(135deg, #4a00e0, #06b6d4);
                box-shadow: 0 14px 30px rgba(37,99,235,.22);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Listening Sets Completed</h1>
            <p>You have already completed all Listening Test Sets. Great work!</p>
            <a class="btn" href="../dashboard.php">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// STEP 4: Fetch questions for the current set
$sql_questions = "SELECT * FROM questions WHERE TestType = ? AND SetNumber = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param("si", $test_type, $current_set);
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

// STEP 5: Fetch audio path for the current set
$sql_audio = "SELECT AudioFilePath FROM listeningaudio WHERE SetNumber = ?";
$stmt_audio = $conn->prepare($sql_audio);
$stmt_audio->bind_param("i", $current_set);
$stmt_audio->execute();
$result_audio = $stmt_audio->get_result();
$audio_path = "";

if ($audio_row = $result_audio->fetch_assoc()) {
    $audio_path = $audio_row['AudioFilePath']; // e.g., "Audio/listening_set1.mp3"
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Listening Test - Audio Missing</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            :root{
                --ana-surface: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
                --ana-border: rgba(148,163,184,.45);
                --ana-shadow: 0 26px 70px rgba(15,23,42,.14);
                --ana-text: #0b1120;
                --ana-muted: rgba(15,23,42,.62);
            }
            body{ margin:0; min-height:100vh; display:grid; place-items:center; padding:24px;
                font-family:'Poppins',sans-serif;
                background: linear-gradient(145deg, #e0eaff, #f0f4ff);
                color: var(--ana-text);
            }
            .card{ max-width:820px; width:100%; padding:26px; border-radius:26px;
                background: var(--ana-surface);
                border:1px solid var(--ana-border);
                box-shadow: var(--ana-shadow);
            }
            h1{ margin:0; font-family:'Orbitron','Poppins',sans-serif; letter-spacing:.12em; text-transform:uppercase; font-size:1.4rem; }
            p{ margin:12px 0 18px; color: var(--ana-muted); }
            .btn{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:16px;
                background: linear-gradient(135deg, #ef4444, #b91c1c); color:#fff; text-decoration:none; font-weight:800;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Audio not found</h1>
            <p>Audio not found for Set <?= (int)$current_set ?></p>
            <a class="btn" href="../dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Listening Test - Set <?= $current_set ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            --danger: #ef4444;

            --radius-xl: 26px;
            --radius-lg: 22px;
            --radius-md: 18px;

            --grid: rgba(15,23,42,.12);
        }

        body.dark-mode{
            --ana-surface: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
            --ana-card: rgba(15,23,42,.62);
            --ana-border: rgba(37,99,235,.55);
            --ana-shadow: 0 30px 90px rgba(0,0,0,.60);
            --ana-text: #e5e7eb;
            --ana-muted: rgba(229,231,235,.68);
            --grid: rgba(129,140,248,.18);
        }

        *{ box-sizing: border-box; }

        body{
            margin:0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(145deg, #e0eaff, #f0f4ff);
            color: var(--ana-text);
            padding: 26px 18px 80px;
            overflow-x: hidden;
            transition: background .35s ease, color .35s ease;
        }
        body.dark-mode{
            background: linear-gradient(145deg, #0c0c0c, #050505);
        }

        /* Background aura */
        body::before{
            content:"";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(900px 520px at 20% 15%, rgba(56,189,248,.14), transparent 60%),
                radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.12), transparent 62%);
            mix-blend-mode: soft-light;
            opacity: .95;
            z-index: -1;
        }
        body.dark-mode::before{ mix-blend-mode: screen; opacity: .7; }

        .shell{
            max-width: 1180px;
            margin: 0 auto;
        }

        /* Top bar */
        .topbar{
            display:flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
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
            letter-spacing: .14em;
            text-transform: uppercase;
            font-size: 1.1rem;
            line-height: 1.2;
        }
        .brand .sub{
            font-size: .88rem;
            color: var(--ana-muted);
            margin-top: 4px;
        }

        .actions{
            display:flex;
            align-items:center;
            gap: 12px;
        }

        /* Cognisense-like theme toggle (uses your same id darkModeToggle & localStorage key) */
        .cs-theme-toggle{
            position: relative;
            display: inline-flex;
            align-items:center;
            cursor:pointer;
            user-select:none;
        }
        .cs-theme-toggle input{ display:none; }

        .cs-track{
            width: 148px;
            height: 60px;
            border-radius: 999px;
            padding: 8px 14px;
            display:flex;
            align-items:center;
            justify-content: space-between;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(148,163,184,.55);
            box-shadow: 0 10px 26px rgba(15,23,42,.18);
            position: relative;
            overflow:hidden;
            transition: border-color .25s ease, box-shadow .25s ease, background .25s ease;
        }
        .cs-track i{
            font-size: 1.25rem;
            position: relative;
            z-index: 2;
            transition: transform .25s ease, opacity .25s ease, filter .25s ease;
        }
        .cs-thumb{
            position:absolute;
            top: 7px;
            left: 8px;
            width: 46px;
            height: 46px;
            border-radius: 999px;
            box-shadow: 0 0 0 2px rgba(148,163,184,.8), 0 10px 24px rgba(15,23,42,.35);
            transition: transform .32s cubic-bezier(.4,0,.2,1), box-shadow .32s ease;
            z-index: 1;
        }

        .cs-theme-toggle input:not(:checked) + .cs-track .sun{
            opacity: 1;
            transform: scale(1.12);
            filter: drop-shadow(0 0 10px rgba(250,204,21,.75));
            color: #f59e0b;
        }
        .cs-theme-toggle input:not(:checked) + .cs-track .moon{
            opacity: .35;
            transform: scale(.92);
            color: rgba(15,23,42,.65);
        }

        .cs-theme-toggle input:checked + .cs-track{
            background: rgba(15,23,42,0.10);
            border-color: rgba(37,99,235,.70);
            box-shadow: 0 14px 34px rgba(15,23,42,.55);
        }
        .cs-theme-toggle input:checked + .cs-track .cs-thumb{
            transform: translateX(84px);
            box-shadow: 0 0 0 2px rgba(129,140,248,.9),
                        0 14px 34px rgba(15,23,42,.75),
                        0 0 24px rgba(56,189,248,.55);
        }
        .cs-theme-toggle input:checked + .cs-track .sun{
            opacity: .25;
            transform: scale(.92);
            filter:none;
            color: rgba(229,231,235,.55);
        }
        .cs-theme-toggle input:checked + .cs-track .moon{
            opacity: 1;
            transform: scale(1.12);
            filter: drop-shadow(0 0 12px rgba(129,140,248,.85));
            color: #c7d2fe;
        }

        /* Quit button (same id / same JS logic) */
        #quitBtn{
            border:none;
            cursor:pointer;
            padding: 14px 18px;
            border-radius: 18px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #fff;
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

        /* Hero header */
        .hero{
            border-radius: var(--radius-xl);
            padding: 20px 20px;
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
            opacity: .26;
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
        .hero > *{ position: relative; z-index: 1; }

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
            font-size: clamp(1.6rem, 2.2vw, 2.2rem);
            background: linear-gradient(90deg, #38bdf8, #4f46e5, #facc15);
            -webkit-background-clip:text;
            background-clip:text;
            color: transparent;
        }
        .hero-sub{
            margin: 10px 0 0;
            color: var(--ana-muted);
            line-height: 1.6;
            max-width: 70ch;
        }

        .set-pill{
            display:inline-flex;
            align-items:center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.46);
            border: 1px solid var(--ana-border);
            color: var(--ana-text);
            box-shadow: 0 14px 30px rgba(15,23,42,.10);
            font-size: .82rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-weight: 900;
            white-space: nowrap;
        }
        body.dark-mode .set-pill{ background: rgba(2,6,23,.35); }

        .hero-badges{
            margin-top: 14px;
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
        }
        body.dark-mode .badge{ background: rgba(2,6,23,.28); }

        /* Audio + timer widget (keeps your wrapper logic) */
        .audio-timer-wrapper{ position: relative; }

        .audio-timer-container{
            position: sticky;
            top: 16px;
            z-index: 999;
            width: 100%;
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 14px;
            border-radius: var(--radius-xl);
            background: var(--ana-card);
            border: 1px solid var(--ana-border);
            box-shadow: 0 22px 55px rgba(15,23,42,.12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: transform .22s ease, box-shadow .22s ease, width .3s ease;
            overflow:hidden;
            isolation:isolate;
            flex-wrap: wrap;
        }
        .audio-timer-container::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(820px 260px at 10% 0%, rgba(56,189,248,.12), transparent 60%),
                radial-gradient(820px 260px at 90% 10%, rgba(79,70,229,.10), transparent 62%);
            opacity: .9;
            pointer-events:none;
            z-index:0;
        }
        .audio-timer-container > *{ position:relative; z-index:1; }

        /* Floating compact mode after scroll (your same .scrolled logic) */
        .scrolled .audio-timer-container{
            position: fixed;
            top: 18px;
            right: 18px;
            width: min(420px, calc(100vw - 36px));
        }

        audio{
            width: 100%;
            max-width: 720px;
            border-radius: 14px;
            outline: none;
        }
        /* ✅ Audio slot must be allowed to shrink inside flex */
.audio-slot{
  flex: 1 1 320px;
  min-width: 0;                 /* ✅ KEY FIX: prevents overflow in flex */
}

/* keep audio responsive */
.audio-slot audio{
  width: 100%;
  max-width: 100%;
}

/* ✅ When floating on the side, stack neatly */
.scrolled .audio-timer-container{
  width: min(420px, calc(100vw - 36px)); /* your current width is fine */
  flex-direction: column;               /* ✅ stack vertically */
  align-items: stretch;                 /* ✅ full width */
}

/* audio on top in scrolled mode */
.scrolled .audio-slot{
  width: 100%;
}

/* timer becomes full-width below audio in scrolled mode */
.scrolled .timer-box{
  width: 100%;
  min-width: 100%;
}
.scrolled .timer-inner{
  width: 100%;
}


        /* ✅ FIX: Timer pill sizing so text never clips */
.timer-box{
  flex: 0 0 auto;
  min-width: 260px;              /* was 170px */
  display: grid;
  place-items: center;
  padding: 10px;
  border-radius: 999px;
  border: 1px solid rgba(148,163,184,.35);
  background:
    conic-gradient(from -90deg,
      rgba(56,189,248,.95) 0 var(--tPct, 100%),
      rgba(148,163,184,.18) var(--tPct, 100%) 100%
    );
  box-shadow: 0 18px 44px rgba(15,23,42,.12);
}

.timer-inner{
  width: 230px;                  /* was 140px */
  height: 64px;                  /* a bit taller */
  border-radius: 999px;
  display: grid;
  place-items: center;
  background: var(--ana-surface);
  border: 1px solid rgba(148,163,184,.25);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);
  padding: 8px 14px;             /* more horizontal padding */
}

#timer{
  font-family:'Orbitron','Poppins',sans-serif;
  font-weight: 900;
  letter-spacing: .06em;         /* was .10em */
  font-size: 0.88rem;            /* slightly smaller */
  color: var(--ana-text);
  text-transform: uppercase;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
  line-height: 1;
}

        .timer-hint{
            margin-top: 4px;
            font-size: .72rem;
            color: var(--ana-muted);
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        /* Question blocks */
        .question-block{
            margin-top: 16px;
            padding: 18px;
            border-radius: var(--radius-xl);
            background: var(--ana-card);
            border: 1px solid var(--ana-border);
            box-shadow: 0 22px 55px rgba(15,23,42,.10);
            position: relative;
            overflow:hidden;
            isolation:isolate;
        }
        .question-block::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(860px 300px at 14% 0%, rgba(56,189,248,.10), transparent 62%),
                radial-gradient(860px 300px at 90% 10%, rgba(79,70,229,.08), transparent 64%);
            opacity: .95;
            pointer-events:none;
        }
        .question-block > *{ position: relative; z-index: 1; }

        .section-title{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .section-title h2{
            margin:0;
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.12em;
            text-transform: uppercase;
            font-size: 1rem;
            color: var(--ana-text);
            display:flex;
            align-items:center;
            gap: 10px;
        }
        .mini-dot{
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: rgba(56,189,248,.95);
            box-shadow: 0 0 12px rgba(56,189,248,.45);
        }
        .section-note{
            color: var(--ana-muted);
            font-size: .86rem;
        }

        .q{
            padding: 14px 14px;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(148,163,184,.22);
            background: rgba(255,255,255,.38);
            margin-top: 12px;
            transition: transform .16s ease, border-color .2s ease, box-shadow .2s ease;
        }
        body.dark-mode .q{ background: rgba(2,6,23,.28); border-color: rgba(129,140,248,.18); }
        .q:hover{
            transform: translateY(-2px);
            border-color: rgba(56,189,248,.35);
            box-shadow: 0 18px 44px rgba(15,23,42,.10);
        }

        .q-title{
            margin:0 0 10px;
            font-size: 1rem;
            line-height: 1.55;
            color: var(--ana-text);
        }
        .q-title strong{
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.10em;
        }

        /* Inputs */
        select,
        input[type="text"]{
            width: min(560px, 100%);
            padding: 12px 12px;
            border-radius: 14px;
            border: 1px solid rgba(148,163,184,.38);
            background: rgba(255,255,255,.65);
            color: var(--ana-text);
            outline: none;
            box-shadow: 0 10px 22px rgba(15,23,42,.06);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        body.dark-mode select,
        body.dark-mode input[type="text"]{
            background: rgba(2,6,23,.30);
            border-color: rgba(129,140,248,.22);
            color: var(--ana-text);
            box-shadow: 0 10px 22px rgba(0,0,0,.35);
        }
        select:focus,
        input[type="text"]:focus{
            border-color: rgba(56,189,248,.65);
            box-shadow: 0 0 0 4px rgba(56,189,248,.18);
        }

        /* Radio options */
        .options{
            margin-top: 10px;
            display:grid;
            gap: 10px;
        }
        .opt{
            display:flex;
            align-items:center;
            gap: 10px;
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px solid rgba(148,163,184,.28);
            background: rgba(255,255,255,.42);
            cursor:pointer;
            transition: transform .16s ease, border-color .2s ease, box-shadow .2s ease;
        }
        body.dark-mode .opt{
            background: rgba(2,6,23,.28);
            border-color: rgba(129,140,248,.18);
        }
        .opt:hover{
            transform: translateY(-1px);
            border-color: rgba(79,70,229,.35);
            box-shadow: 0 16px 34px rgba(15,23,42,.10);
        }
        .opt input[type="radio"]{
            width: 18px;
            height: 18px;
            accent-color: var(--indigo);
            cursor:pointer;
        }

        /* Divider */
        hr{
            border: 0;
            height: 1px;
            background: rgba(148,163,184,.35);
            margin: 18px 0 0;
        }
        body.dark-mode hr{ background: rgba(129,140,248,.18); }

        /* Submit */
        .submit-wrap{
            margin-top: 18px;
            display:flex;
            justify-content: center;
        }
        .submit-btn{
            border:none;
            cursor:pointer;
            width: min(520px, 100%);
            padding: 16px 18px;
            border-radius: 22px;
            font-weight: 900;
            letter-spacing: .10em;
            text-transform: uppercase;
            color:#fff;
            background: linear-gradient(135deg, #4a00e0, #06b6d4);
            box-shadow: 0 18px 44px rgba(37,99,235,.22);
            transition: transform .18s ease, box-shadow .2s ease, filter .2s ease;
        }
        .submit-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 26px 70px rgba(37,99,235,.26);
            filter: brightness(1.04);
        }
        .submit-btn:active{ transform: translateY(0) scale(.99); }

        /* Quit modal (same ids, upgraded look) */
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
            opacity:.18;
            filter: blur(12px);
            pointer-events:none;
            z-index:0;
        }
        #quitModalContent > *{ position:relative; z-index:1; }

        #quitModalContent p{
            margin: 0 0 16px;
            font-weight: 800;
            letter-spacing: .06em;
            color: var(--ana-text);
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

        @media (max-width: 920px){
            .audio-timer-container{ flex-direction: column; align-items: stretch; }
            .timer-box{ width: 100%; }
            .timer-inner{ width: 100%; height: 62px; }
        }

        /* ✅ SCROLLED MODE: pack items tightly (no huge gap) */
.scrolled .audio-timer-container{
  flex-direction: column;
  align-items: stretch;
  justify-content: flex-start;   /* ✅ IMPORTANT */
  gap: 12px;                    /* ✅ small consistent spacing */
}

/* ✅ Stop audio section from expanding vertically */
.scrolled .audio-slot{
  flex: 0 0 auto !important;     /* ✅ IMPORTANT */
}

/* Optional: ensure timer stays tight too */
.scrolled .timer-box{
  margin-top: 0 !important;
}

    </style>

    <script>
        // --- Keep your timer logic exactly (30 min auto submit) ---
        let timerSeconds = 1800; // 30 minutes

        function startTimer() {
            const timerDisplay = document.getElementById("timer");
            const timerBox = document.querySelector(".timer-box");

            const interval = setInterval(() => {
                const mins = Math.floor(timerSeconds / 60);
                const secs = timerSeconds % 60;

                if (timerDisplay) {
                    timerDisplay.textContent = `Time Left: ${mins}:${secs < 10 ? '0' : ''}${secs}`;
                }

                // Visual ring progress (design only)
                if (timerBox) {
                    const pct = Math.max(0, Math.min(100, (timerSeconds / 1800) * 100));
                    timerBox.style.setProperty("--tPct", pct.toFixed(1) + "%");
                }

                if (timerSeconds <= 0) {
                    clearInterval(interval);
                    alert("Time's up! Submitting your test.");
                    const f = document.getElementById("testForm");
                    if (f) f.submit();
                }

                timerSeconds--;
            }, 1000);
        }

        // --- Quit modal logic (same IDs, same redirect) ---
        function showQuitModal() {
            const m = document.getElementById('quitModal');
            if (m) m.style.display = 'flex';
        }

        function hideQuitModal() {
            const m = document.getElementById('quitModal');
            if (m) m.style.display = 'none';
        }

        function confirmQuit() {
            window.location.href = "../dashboard.php"; // same as your code
        }

        // --- Dark mode logic (same localStorage key & same checkbox id) ---
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }

        function restoreDarkMode() {
            const toggle = document.getElementById('darkModeToggle');
            const enabled = localStorage.getItem('darkMode') === 'enabled';
            if (enabled) document.body.classList.add('dark-mode');
            if (toggle) toggle.checked = enabled;
        }

        // Load events (fixes the old window.onload override without changing behavior)
        window.addEventListener('load', () => {
            startTimer();

            const quitBtn = document.getElementById('quitBtn');
            const cancelBtn = document.getElementById('cancelQuitBtn');
            const confirmBtn = document.getElementById('confirmQuitBtn');

            if (quitBtn) quitBtn.addEventListener('click', showQuitModal);
            if (cancelBtn) cancelBtn.addEventListener('click', hideQuitModal);
            if (confirmBtn) confirmBtn.addEventListener('click', confirmQuit);
        });

        window.addEventListener('load', () => {
            restoreDarkMode();
        });

        // Keep your scroll-to-float behavior
        window.addEventListener("scroll", function () {
            const wrapper = document.querySelector(".audio-timer-wrapper");
            if (!wrapper) return;
            if (window.scrollY > 200) wrapper.classList.add("scrolled");
            else wrapper.classList.remove("scrolled");
        });

        // Optional: click outside modal closes (design only, logic safe)
        window.addEventListener('click', (e) => {
            const m = document.getElementById('quitModal');
            const c = document.getElementById('quitModalContent');
            if (!m || !c) return;
            if (m.style.display === 'flex' && e.target === m) hideQuitModal();
        });

        // ESC closes modal (design only, safe)
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideQuitModal();
        });
    </script>
</head>

<body>
<div class="shell">

    <!-- TOP BAR -->
    <div class="topbar">
        <div class="brand">
            <div class="brand-badge" aria-hidden="true"><i class="fa-solid fa-headphones"></i></div>
            <div>
                <h1>Listening Test</h1>
                <div class="sub">AspireIELTS • Exam-style practice</div>
            </div>
        </div>

        <div class="actions">


            <!-- keep same id and modal logic -->
            <button id="quitBtn" type="button" title="Quit Test">Quit</button>
        </div>
    </div>

    <!-- HERO -->
    <section class="hero" aria-label="Listening test header">
        <div class="hero-top">
            <div>
                <h2 class="hero-title">LISTENING TEST</h2>
                <p class="hero-sub">
                    Listen carefully and answer each question. Your answers are required and will be submitted automatically when time ends.
                </p>
            </div>

            <div class="set-pill" title="Current set">
                <i class="fa-solid fa-layer-group"></i>
                Set <?= (int)$current_set ?> of 3
            </div>
        </div>

        <div class="hero-badges" aria-hidden="true">
            <span class="badge"><i class="fa-solid fa-stopwatch"></i> 30 Minutes</span>
            <span class="badge"><i class="fa-solid fa-headphones-simple"></i> Audio-based</span>
            <span class="badge"><i class="fa-solid fa-circle-check"></i> Required answers</span>
        </div>
    </section>

    <!-- AUDIO + TIMER (keeps wrapper + class scrolled behavior) -->
    <div class="audio-timer-wrapper">
        <div class="audio-timer-container" aria-label="Audio and timer">
            <div class="audio-slot">

                <audio controls autoplay>
                    <source src="../<?= $audio_path ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>

            <div class="timer-box" style="--tPct: 100%;">
                <div class="timer-inner">
                    <div>
                        <div id="timer">Time Left: 30:00</div>
                        <div class="timer-hint">Auto-submit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM (logic unchanged) -->
    <form id="testForm" method="POST" action="submit_listening.php">
        <input type="hidden" name="SetNumber" value="<?= $current_set ?>">
        <input type="hidden" name="TestType" value="listening">

        <?php
        $sections = [];
        while ($row = $questions_result->fetch_assoc()) {
            $sections[$row['Section']][] = $row;
        }

        foreach ($sections as $section_number => $questions) {
            echo "<div class='question-block'>";
            echo "  <div class='section-title'>";
            echo "    <h2><span class='mini-dot'></span> Section " . htmlspecialchars($section_number) . "</h2>";
            echo "    <div class='section-note'>Answer all questions</div>";
            echo "  </div>";

            $counter = 1; // Initialize counter
            foreach ($questions as $q) {
                $question_id = $q['QuestionID'];
                $question_text = $q['QuestionText'];
                $answer_options = $q['AnswerOptions'];

                echo "<div class='q'>";
                echo "  <p class='q-title'><strong>Q{$counter}:</strong> {$question_text}</p>";

                if (!empty($answer_options)) {
                    $options = array_map('trim', explode(',', $answer_options));
                    $lower = array_map('strtolower', $options);
                    $is_tfng = in_array('true', $lower) && in_array('false', $lower) && in_array('not given', $lower);

                    if ($is_tfng) {
                        echo "<select name='answers[$question_id]' required>";
                        echo "<option value=''>Select</option>";
                        foreach ($options as $opt) {
                            echo "<option value='" . htmlspecialchars($opt) . "'>$opt</option>";
                        }
                        echo "</select>";
                    } else {
                        echo "<div class='options'>";
                        foreach ($options as $opt) {
                            $opt_clean = htmlspecialchars($opt);
                            echo "<label class='opt'><input type='radio' name='answers[$question_id]' value='$opt_clean' required> $opt_clean</label>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<input type='text' name='answers[$question_id]' placeholder='Your answer' required>";
                }

                echo "</div>";
                $counter++;
            }

            echo "</div><hr>";
        }
        ?>

        <div class="submit-wrap">
            <button type="submit" class="submit-btn">Submit Answers</button>
        </div>
    </form>

</div>

<!-- QUIT MODAL (same ids, same logic) -->
<div id="quitModal" aria-hidden="true">
    <div id="quitModalContent" role="dialog" aria-modal="true" aria-label="Quit confirmation">
        <p>Are you sure you want to quit the test?</p>
        <div class="modal-actions">
            <button id="confirmQuitBtn" class="modal-btn" type="button">Yes, Quit</button>
            <button id="cancelQuitBtn" class="modal-btn" type="button">No, Stay</button>
        </div>
    </div>
</div>

</body>
</html>
