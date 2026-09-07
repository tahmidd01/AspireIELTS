<?php
session_start();
require_once "../db_connection.php";

$user_id = $_SESSION['UserID'];
$current_set = null;

// STEP 1: Check which reading sets the user has already completed
$sql = "SELECT DISTINCT SetNumber FROM testresponses WHERE UserID = ? AND TestType = 'reading'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$completed_sets = [];
while ($row = $result->fetch_assoc()) {
    $completed_sets[] = $row['SetNumber'];
}

// STEP 2: Determine which reading set to offer next
for ($i = 1; $i <= 3; $i++) {
    if (!in_array($i, $completed_sets)) {
        $current_set = $i;
        break;
    }
}

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
        <title>Reading Test - Completed</title>
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
            .dark-mode{
                --ana-surface: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
                --ana-card: rgba(15,23,42,.62);
                --ana-border: rgba(37,99,235,.55);
                --ana-shadow: 0 30px 90px rgba(0,0,0,.60);
                --ana-text: #e5e7eb;
                --ana-muted: rgba(229,231,235,.68);
            }
            *{ box-sizing:border-box; }
            body{
                margin:0; min-height:100vh;
                font-family:'Poppins',sans-serif;
                display:grid; place-items:center;
                background: linear-gradient(145deg, #e0eaff, #f0f4ff);
                color: var(--ana-text);
                padding: 24px;
            }
            .dark-mode body{ background: linear-gradient(145deg, #0c0c0c, #050505); }

            body::before{
                content:""; position:fixed; inset:0; pointer-events:none;
                background:
                    radial-gradient(900px 520px at 20% 15%, rgba(56,189,248,.16), transparent 60%),
                    radial-gradient(900px 520px at 85% 20%, rgba(79,70,229,.12), transparent 62%);
                mix-blend-mode: soft-light;
                opacity:.95;
            }
            .dark-mode body::before{ mix-blend-mode: screen; opacity:.7; }

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
                opacity:.18; filter: blur(12px); pointer-events:none;
                z-index:0;
            }
            .card > *{ position:relative; z-index:1; }
            h1{
                margin:0;
                font-family:'Orbitron','Poppins',sans-serif;
                letter-spacing:.12em;
                text-transform: uppercase;
                font-size: 1.55rem;
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
            <h1>Reading Sets Completed</h1>
            <p>You have already completed all Reading Test Sets.</p>
            <a class="btn" href="../dashboard.php">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// STEP 3: Fetch passages
$sql_passages = "SELECT * FROM reading_passages WHERE SetNumber = ? ORDER BY PassageID ASC";
$stmt_passages = $conn->prepare($sql_passages);
$stmt_passages->bind_param("i", $current_set);
$stmt_passages->execute();
$passages_result = $stmt_passages->get_result();

$passages = [];
while ($row = $passages_result->fetch_assoc()) {
    $passages[$row['PassageID']] = $row;
}

// STEP 4: Fetch questions for this set
$sql_questions = "SELECT * FROM questions WHERE TestType = 'Reading' AND SetNumber = ? ORDER BY Section ASC, QuestionID ASC";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param("i", $current_set);
$stmt_questions->execute();
$questions_result = $stmt_questions->get_result();

$sections = [];
while ($row = $questions_result->fetch_assoc()) {
    $sections[$row['Section']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        (function () {
            try {
                if (localStorage.getItem('darkMode') === 'enabled') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>
    <title>Reading Test - Set <?= $current_set ?></title>
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
        .dark-mode body{
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
        .dark-mode body::before{ mix-blend-mode: screen; opacity: .7; }

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
            font-size: 1.05rem;
            line-height: 1.2;
        }
        .brand .sub{
            font-size: .88rem;
            color: var(--ana-muted);
            margin-top: 4px;
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
            opacity: .24;
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
            max-width: 75ch;
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
        .dark-mode .set-pill{ background: rgba(2,6,23,.35); }

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
        .dark-mode .badge{ background: rgba(2,6,23,.28); }

        /* TIMER (sticky -> floating on scroll) */
        .timer-wrapper{ position: relative; }

        .timer-container{
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
        .timer-container::before{
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
        .timer-container > *{ position:relative; z-index:1; }

        .timer-meta{
            display:flex;
            align-items:center;
            gap: 12px;
            min-width: 240px;
            flex: 1 1 260px;
        }
        .timer-meta .meta-ico{
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display:grid;
            place-items:center;
            background: rgba(255,255,255,.50);
            border: 1px solid rgba(148,163,184,.35);
            box-shadow: 0 14px 30px rgba(15,23,42,.08);
        }
        .dark-mode .timer-meta .meta-ico{ background: rgba(2,6,23,.35); border-color: rgba(129,140,248,.18); }

        .timer-meta .meta-title{
            margin:0;
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.10em;
            text-transform: uppercase;
            font-size: .92rem;
        }
        .timer-meta .meta-sub{
            margin-top: 4px;
            color: var(--ana-muted);
            font-size: .84rem;
        }

        /* Floating compact mode after scroll */
        .timer-wrapper.scrolled .timer-container{
            position: fixed;
            top: 18px;
            right: 18px;
            width: min(420px, calc(100vw - 36px));
        }

        /* Timer ring pill */
        .timer-box{
            flex: 0 0 auto;
            min-width: 260px;
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
            width: 230px;
            height: 64px;
            border-radius: 999px;
            display:grid;
            place-items:center;
            background: var(--ana-surface);
            border: 1px solid rgba(148,163,184,.25);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);
            padding: 8px 14px;
        }
        #timer{
            font-family:'Orbitron','Poppins',sans-serif;
            font-weight: 900;
            letter-spacing: .06em;
            font-size: 0.88rem;
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
            text-align:center;
        }

        /* Section layout (passage + questions) */
        .reading-section{
            margin-top: 16px;
            display:grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 14px;
            align-items: start;
        }
        @media (max-width: 980px){
            .reading-section{ grid-template-columns: 1fr; }
        }

        /* Passage card */
        .passage{
            border-radius: var(--radius-xl);
            padding: 18px;
            background: var(--ana-card);
            border: 1px solid var(--ana-border);
            box-shadow: 0 22px 55px rgba(15,23,42,.10);
            position: relative;
            overflow:hidden;
            isolation:isolate;
        }
        .passage::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(860px 300px at 14% 0%, rgba(56,189,248,.10), transparent 62%),
                radial-gradient(860px 300px at 90% 10%, rgba(79,70,229,.08), transparent 64%);
            opacity: .95;
            pointer-events:none;
        }
        .passage > *{ position:relative; z-index:1; }

        .passage h2{
            margin:0 0 10px;
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.10em;
            text-transform: uppercase;
            font-size: 1rem;
            display:flex;
            align-items:center;
            gap: 10px;
        }
        .passage .p-badge{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,.40);
            border: 1px solid rgba(148,163,184,.25);
            box-shadow: 0 14px 30px rgba(15,23,42,.06);
            color: var(--ana-text);
            font-size: .78rem;
            letter-spacing: .06em;
        }
        .dark-mode .passage .p-badge{ background: rgba(2,6,23,.28); border-color: rgba(129,140,248,.18); }

        .passage p{
            margin: 12px 0 0;
            font-size: 1rem;
            line-height: 1.85;
            color: var(--ana-text);
            white-space: pre-line;
        }
        .passage .muted{
            color: var(--ana-muted);
            font-size: .88rem;
            margin-top: 10px;
        }

        /* Questions block */
        .question-block{
            border-radius: var(--radius-xl);
            padding: 18px;
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
        .question-block > *{ position:relative; z-index:1; }

        .section-title{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .section-title h3{
            margin:0;
            font-family:'Orbitron','Poppins',sans-serif;
            letter-spacing:.12em;
            text-transform: uppercase;
            font-size: 1rem;
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
        .dark-mode .q{ background: rgba(2,6,23,.28); border-color: rgba(129,140,248,.18); }
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
        .dark-mode select,
        .dark-mode input[type="text"]{
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

        /* Radio options as cards */
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
        .dark-mode .opt{
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
        .dark-mode hr{ background: rgba(129,140,248,.18); }

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

        /* Quit modal */
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
            .timer-container{ flex-direction: column; align-items: stretch; }
            .timer-box{ width: 100%; min-width: 100%; }
            .timer-inner{ width: 100%; height: 62px; }
        }
    </style>

    <script>
        // ✅ Keep your timer logic (60 minutes, auto submit)
        let timerSeconds = 3600; // 60 minutes

        function startTimer() {
            const timerDisplay = document.getElementById("timer");
            const timerBox = document.querySelector(".timer-box");

            const interval = setInterval(() => {
                const mins = Math.floor(timerSeconds / 60);
                const secs = timerSeconds % 60;

                if (timerDisplay) {
                    timerDisplay.textContent = `Time Left: ${mins}:${secs < 10 ? '0' : ''}${secs}`;
                }

                // Visual progress ring (design only)
                if (timerBox) {
                    const pct = Math.max(0, Math.min(100, (timerSeconds / 3600) * 100));
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

        function showQuitModal() {
            const m = document.getElementById('quitModal');
            if (m) m.style.display = 'flex';
        }

        function hideQuitModal() {
            const m = document.getElementById('quitModal');
            if (m) m.style.display = 'none';
        }

        function confirmQuit() {
            window.location.href = "../dashboard.php";
        }

        // ✅ Use addEventListener so nothing overrides anything
        window.addEventListener('load', () => {
            startTimer();

            const quitBtn = document.getElementById('quitBtn');
            const cancelBtn = document.getElementById('cancelQuitBtn');
            const confirmBtn = document.getElementById('confirmQuitBtn');

            if (quitBtn) quitBtn.addEventListener('click', showQuitModal);
            if (cancelBtn) cancelBtn.addEventListener('click', hideQuitModal);
            if (confirmBtn) confirmBtn.addEventListener('click', confirmQuit);
        });

        // Keep your scroll-to-float behavior (same threshold, same class name)
        window.addEventListener("scroll", function () {
            const wrapper = document.querySelector(".timer-wrapper");
            if (!wrapper) return;
            if (window.scrollY > 200) wrapper.classList.add("scrolled");
            else wrapper.classList.remove("scrolled");
        });

        // Optional: click outside modal closes (safe)
        window.addEventListener('click', (e) => {
            const m = document.getElementById('quitModal');
            const c = document.getElementById('quitModalContent');
            if (!m || !c) return;
            if (m.style.display === 'flex' && e.target === m) hideQuitModal();
        });

        // ESC closes modal (safe)
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
            <div class="brand-badge" aria-hidden="true"><i class="fa-solid fa-book-open"></i></div>
            <div>
                <h1>Reading Test</h1>
                <div class="sub">AspireIELTS • Passage-based practice</div>
            </div>
        </div>

        <button id="quitBtn" type="button" title="Quit Test">Quit</button>
    </div>

    <!-- HERO -->
    <section class="hero" aria-label="Reading test header">
        <div class="hero-top">
            <div>
                <h2 class="hero-title">READING TEST</h2>
                <p class="hero-sub">
                    Read each passage carefully and answer all questions. Your answers are required and will be submitted automatically when time ends.
                </p>
            </div>

            <div class="set-pill" title="Current set">
                <i class="fa-solid fa-layer-group"></i>
                Set <?= (int)$current_set ?> of 3
            </div>
        </div>

        <div class="hero-badges" aria-hidden="true">
            <span class="badge"><i class="fa-solid fa-stopwatch"></i> 60 Minutes</span>
            <span class="badge"><i class="fa-solid fa-book"></i> Passages</span>
            <span class="badge"><i class="fa-solid fa-circle-check"></i> Required answers</span>
        </div>
    </section>

    <!-- TIMER -->
    <div class="timer-wrapper">
        <div class="timer-container" aria-label="Timer">
            <div class="timer-meta">
                <div class="meta-ico" aria-hidden="true"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <div class="meta-title">Exam Timer</div>
                    <div class="meta-sub">Stay focused — auto-submit when time ends</div>
                </div>
            </div>

            <div class="timer-box" style="--tPct: 100%;">
                <div class="timer-inner">
                    <div>
                        <div id="timer">Time Left: 60:00</div>
                        <div class="timer-hint">Auto-submit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM (logic unchanged) -->
    <form id="testForm" method="POST" action="submit_reading.php">
        <input type="hidden" name="SetNumber" value="<?= $current_set ?>">
        <input type="hidden" name="TestType" value="reading">

        <?php
        $section_to_passage = [];

        foreach ($sections as $section_number => $questions) {
            // Get the first question in this section
            if (!empty($questions)) {
                $first_question = $questions[0];

                if (isset($first_question['PassageID'])) {
                    $section_to_passage[$section_number] = $first_question['PassageID'];
                }
            }

            echo "<div class='reading-section'>";

            // Passage card
            echo "<div class='passage'>";
            if (isset($section_to_passage[$section_number]) && isset($passages[$section_to_passage[$section_number]])) {
                $passage = $passages[$section_to_passage[$section_number]];
                $passage_title = htmlspecialchars($passage['PassageTitle']);
                echo "<h2><span class='mini-dot'></span> Section " . htmlspecialchars($section_number) . " — Passage</h2>";
                echo "<div class='p-badge'><i class='fa-solid fa-scroll'></i> " . $passage_title . "</div>";
                echo "<p>" . nl2br(htmlspecialchars($passage['PassageContent'])) . "</p>";
            } else {
                echo "<h2><span class='mini-dot'></span> Section " . htmlspecialchars($section_number) . " — Passage</h2>";
                echo "<p class='muted'>Passage not found.</p>";
            }
            echo "</div>";

            // Questions card
            echo "<div class='question-block'>";
            echo "  <div class='section-title'>";
            echo "    <h3><span class='mini-dot'></span> Section " . htmlspecialchars($section_number) . " — Questions</h3>";
            echo "    <div class='section-note'>Answer all questions</div>";
            echo "  </div>";

            $display_number = 1;
            foreach ($questions as $q) {
                $question_id = $q['QuestionID'];
                $question_text = $q['QuestionText'];
                $answer_options = $q['AnswerOptions'];

                echo "<div class='q'>";
                echo "<p class='q-title'><strong>Q{$display_number}:</strong> $question_text</p>";

                if (!empty($answer_options)) {
                    $options = array_map('trim', explode(',', $answer_options));
                    $lower = array_map('strtolower', $options);

                    // Check if it's a T/F/NG question
                    $is_tfng = count($options) === 3 && in_array('true', $lower) && in_array('false', $lower) && in_array('not given', $lower);

                    if ($is_tfng) {
                        // Render a dropdown
                        echo "<select name='answers[$question_id]' required>";
                        echo "<option value=''>Select</option>";
                        foreach ($options as $opt) {
                            $opt_clean = htmlspecialchars($opt);
                            echo "<option value='$opt_clean'>$opt_clean</option>";
                        }
                        echo "</select>";
                    } else {
                        // Render MCQ as radio buttons
                        echo "<div class='options'>";
                        foreach ($options as $opt) {
                            $opt_clean = htmlspecialchars($opt);
                            echo "<label class='opt'><input type='radio' name='answers[$question_id]' value='$opt_clean' required> $opt_clean</label>";
                        }
                        echo "</div>";
                    }
                } else {
                    // Short answer text input
                    echo "<input type='text' name='answers[$question_id]' placeholder='Your answer' required>";
                }

                echo "</div>";
                $display_number++;
            }

            echo "</div>";
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
