
<?php
session_start();
include 'db_connection.php';

date_default_timezone_set('Asia/Dhaka');

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userID = (int)$_SESSION['UserID'];

// ============================
// Fetch user
// ============================
$stmt = $conn->prepare("SELECT FullName, Email FROM Users WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$userRes = $stmt->get_result();

if ($userRes->num_rows <= 0) {
    echo "User not found.";
    exit();
}

$userRow  = $userRes->fetch_assoc();
$fullName = htmlspecialchars($userRow['FullName'] ?? 'AspireIELTS User', ENT_QUOTES, 'UTF-8');
$email    = htmlspecialchars($userRow['Email'] ?? '', ENT_QUOTES, 'UTF-8');
$stmt->close();

// ============================
// Fetch test results (for analytics + charts)
// ============================
// NOTE: Keep your backend logic (UserID-based TestResults). Using ASC for clean trend chart.
$stmt = $conn->prepare("SELECT TestType, Score, TestDate FROM TestResults WHERE UserID = ? ORDER BY TestDate ASC");
$stmt->bind_param("i", $userID);
$stmt->execute();
$testResults = $stmt->get_result();

$totalScore = 0.0;
$totalTests = 0;

$bestScore  = null;
$worstScore = null;

// IELTS sections
$sections = [
    'Listening' => [],
    'Reading'   => [],
    'Writing'   => [],
    'Speaking'  => [],
];

// Trend arrays
$testDates  = [];
$testScores = [];
$testTypes  = []; // for donut counts

// Distribution bands (IELTS-ish)
$dist = [
    'b1' => 0, // <4.0
    'b2' => 0, // 4.0–4.9
    'b3' => 0, // 5.0–5.9
    'b4' => 0, // 6.0–6.9
    'b5' => 0, // 7.0+
];

while ($r = $testResults->fetch_assoc()) {
    $score    = (float)($r['Score'] ?? 0);
    $testType = (string)($r['TestType'] ?? '');
    $testDate = (string)($r['TestDate'] ?? '');

    $totalScore += $score;
    $totalTests++;

    if ($bestScore === null || $score > $bestScore) $bestScore = $score;
    if ($worstScore === null || $score < $worstScore) $worstScore = $score;

    if (isset($sections[$testType])) {
        $sections[$testType][] = $score;
    }

    // Trend
    $testDates[]  = $testDate;
    $testScores[] = $score;

    // Types count (includes sections + any other types you might have)
    if (!isset($testTypes[$testType])) $testTypes[$testType] = 0;
    $testTypes[$testType]++;

    // Distribution
    if ($score < 4.0) $dist['b1']++;
    elseif ($score < 5.0) $dist['b2']++;
    elseif ($score < 6.0) $dist['b3']++;
    elseif ($score < 7.0) $dist['b4']++;
    else $dist['b5']++;
}

$averageScore = $totalTests > 0 ? round($totalScore / $totalTests, 2) : 0.0;
$bestScore    = $bestScore !== null ? round($bestScore, 2) : 0.0;
$worstScore   = $worstScore !== null ? round($worstScore, 2) : 0.0;

// Section-wise performance analysis
$sectionPerformance = [];
foreach ($sections as $section => $scores) {
    $sectionPerformance[$section] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : null;
}

// Tier label (AspireIELTS flavor)
$tier = 'Building';
if ($averageScore >= 7.5) $tier = 'Excellent';
elseif ($averageScore >= 6.5) $tier = 'Strong';
elseif ($averageScore >= 5.5) $tier = 'Growing';

// Ring % (IELTS is /9)
$ringPct = $averageScore > 0 ? (int)round(min(100, max(0, ($averageScore / 9.0) * 100))) : 0;

// Recent tests
$stmtRecent = $conn->prepare("SELECT TestType, Score, TestDate FROM TestResults WHERE UserID = ? ORDER BY TestDate DESC LIMIT 10");
$stmtRecent->bind_param("i", $userID);
$stmtRecent->execute();
$recentResults = $stmtRecent->get_result();

// Build analytics payload for Chart.js
$payload = [
    'totalTests'   => $totalTests,
    'avgScore'     => $averageScore,
    'bestScore'    => $bestScore,
    'worstScore'   => $worstScore,
    'tier'         => $tier,
    'ringPct'      => $ringPct,
    'trend' => [
        'labels' => $testDates,
        'scores' => $testScores,
    ],
    'sections' => [
        'labels' => array_keys($sectionPerformance),
        'avg'    => array_map(function($k) use ($sectionPerformance){
            return $sectionPerformance[$k] === null ? 0 : $sectionPerformance[$k];
        }, array_keys($sectionPerformance)),
    ],
    'dist' => $dist,
    'types' => [
        'labels' => array_keys($testTypes),
        'counts' => array_values($testTypes),
    ]
];

$stmt->close();
$stmtRecent->close();
$conn->close();

// Active helper for sidebar
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page, $currentPage){
    return $page === $currentPage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AspireIELTS</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts + Icons (same as Cognisense dashboard.blade) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-light: #f1f3f7;
            --bg-dark: #121212;
            --text-light: #2c3e50;
            --text-dark: #f1f1f1;
            --card-light: #ffffff;
            --card-dark: #1e1e1e;
            --accent: #3498db;
            --sidebar-width: 220px;
            --sidebar-collapsed-width: 60px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins','Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(145deg, #e0eaff, #f0f4ff);
            color: var(--text-light);
            transition: background 0.5s, color 0.5s;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Smooth theme swap pulse */
        body::before{
            content:"";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0;
            transition: opacity .28s ease;
            z-index: 9000;
            background:
                radial-gradient(900px 480px at 20% 15%, rgba(56,189,248,.14), transparent 60%),
                radial-gradient(900px 520px at 85% 20%, rgba(79,70,229,.12), transparent 62%);
            mix-blend-mode: soft-light;
        }
        body.theme-swap::before{ opacity: 1; }
        body.dark-mode::before{ mix-blend-mode: screen; opacity: 0; }
        body.dark-mode.theme-swap::before{ opacity: .95; }

        body.dark-mode {
            background: linear-gradient(145deg, #0c0c0c, #050505);
            color: var(--text-dark);
        }

        /* =========================
           SIDEBAR (identical behavior)
           ========================= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100%;
            background: var(--card-light);
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.1);
            padding-top: 20px;
            transition: width 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
        }

        body.dark-mode .sidebar {
            background: var(--card-dark);
            box-shadow: 2px 0 12px rgba(255, 255, 255, 0.1);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .logo {
            padding: 1px;
            margin-bottom: 1px;
            margin-top: auto;
            text-align: center;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .logo img {
            height: 250px;
            width: 190px;
            object-fit: fill;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .logo img { height: 60px; }
        }

        .sidebar.collapsed .logo {
            opacity: 0;
            transform: translateX(-20px);
            pointer-events: none;
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: none;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar.collapsed ul {
            margin-top: 55px;
        }

        .sidebar ul li {
            padding: 15px 25px;
            cursor: pointer;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            color: var(--text-light);
            transition: background 0.2s ease, transform 0.15s ease;
        }

        body.dark-mode .sidebar ul li {
            color: var(--text-dark);
        }

        .sidebar ul li:hover {
            background: linear-gradient(135deg, #d9d4e4ff, #8e2de2);
            color: white;
            box-shadow: inset 2px 2px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            transform: translateX(2px);
        }

        .sidebar ul li.active {
            background: linear-gradient(135deg, #752dfbff, #00c6ff);
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 18px rgba(74, 0, 224, 0.45);
        }

        .sidebar ul li a {
            color: inherit;
            text-decoration: none;
            flex-grow: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar.collapsed ul li span.text { display: none; }

        .sidebar ul li .icon {
            width: 30px;
            height: 30px;
            object-fit: contain;
            transform-origin: center;
            display: block;
            transition: transform 0.25s ease, filter 0.25s ease;
            filter: drop-shadow(0 0 2px rgba(0,0,0,0.2));
        }

        .sidebar ul li:hover .icon {
            filter:
                drop-shadow(0 0 6px rgba(0, 198, 255, 0.75))
                drop-shadow(0 0 10px rgba(74, 0, 224, 0.8));
            animation: navIconWiggle 0.45s ease-out;
        }

        .sidebar ul li.active .icon {
            filter:
                drop-shadow(0 0 8px rgba(0, 198, 255, 0.95))
                drop-shadow(0 0 12px rgba(74, 0, 224, 0.9));
            animation: navIconPulse 1.4s ease-in-out infinite;
        }

        @keyframes navIconWiggle {
            0%   { transform: translateX(0) rotate(0deg) scale(1); }
            25%  { transform: translateX(2px) rotate(8deg)  scale(1.1); }
            50%  { transform: translateX(-1px) rotate(-6deg) scale(1.05); }
            75%  { transform: translateX(1px) rotate(3deg)  scale(1.08); }
            100% { transform: translateX(0) rotate(0deg) scale(1); }
        }

        @keyframes navIconPulse {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.13); }
            100% { transform: scale(1); }
        }

        .sidebar.collapsed ul li { justify-content: center; }

        /* SIDEBAR TOGGLE BUTTON - small, top-right */
        .sidebar-toggle-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            z-index: 1100;
        }

        .sidebar-toggle-btn:hover {
            background: linear-gradient(135deg, rgb(5, 3, 9), rgb(37, 7, 62));
            box-shadow: 0 3px 8px rgba(19, 1, 1, 0.35);
            transform: scale(1.05);
        }

        /* =========================
           MAIN CONTENT
           ========================= */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            overflow-y: auto;
            transition: margin-left 0.3s ease, width 0.3s ease;
            height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* =========================
           TOP BAR
           ========================= */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .top-bar-right{
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* =========================
           THEME TOGGLE (same)
           ========================= */
        .theme-toggle {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin-right: 0;
            user-select: none;
        }

        .theme-toggle input { display: none; }

        .toggle-track {
            width: 150px;
            height: 64px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            box-shadow:
                0 4px 14px rgba(15, 23, 42, 0.35),
                inset 0 0 3px rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(148, 163, 184, 0.55);
            transition: background 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .toggle-track img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            position: relative;
            z-index: 2;
            transition: opacity 0.25s ease, transform 0.25s ease, filter 0.25s ease;
        }

        .toggle-thumb {
            position: absolute;
            top: 8px;
            left: 9px;
            width: 48px;
            height: 48px;
            border-radius: 999px;
            background: transparent;
            box-shadow:
                0 0 0 2px rgba(148, 163, 184, 0.8),
                0 6px 16px rgba(15, 23, 42, 0.55);
            transition: transform 0.32s cubic-bezier(.4,0,.2,1), box-shadow 0.32s ease;
            z-index: 1;
        }

        .theme-toggle input:not(:checked) + .toggle-track .icon-sun {
            opacity: 1;
            filter:
                drop-shadow(0 0 8px rgba(250, 204, 21, 0.95))
                drop-shadow(0 0 18px rgba(245, 158, 11, 0.75));
            transform: scale(1.12);
        }
        .theme-toggle input:not(:checked) + .toggle-track .icon-moon {
            opacity: 0.35;
            transform: scale(0.9);
            filter: none;
        }

        .theme-toggle input:checked + .toggle-track {
            background: rgba(15, 23, 42, 0.12);
            box-shadow:
                0 5px 18px rgba(15, 23, 42, 0.85),
                inset 0 0 5px rgba(15, 23, 42, 0.7);
            border-color: rgba(30, 64, 175, 0.7);
        }
        .theme-toggle input:checked + .toggle-track .toggle-thumb {
            transform: translateX(83px);
            background: transparent;
            box-shadow:
                0 0 0 2px rgba(129, 140, 248, 0.9),
                0 10px 24px rgba(15, 23, 42, 0.95),
                0 0 22px rgba(56, 189, 248, 0.85);
        }
        .theme-toggle input:checked + .toggle-track .icon-sun {
            opacity: 0.25;
            transform: scale(0.9);
            filter: none;
        }
        .theme-toggle input:checked + .toggle-track .icon-moon {
            opacity: 1;
            transform: scale(1.14);
            filter:
                drop-shadow(0 0 10px rgba(129, 140, 248, 1))
                drop-shadow(0 0 24px rgba(56, 189, 248, 0.95));
        }

        /* =========================
           TOP RIGHT ICON BUTTONS (About)
           ========================= */
        .about-btn.icon-only{
            width: 78px;
            height: 72px;
            padding: 10px;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;

            border: 1px solid rgba(148,163,184,.55);
            background: linear-gradient(135deg, rgba(255,255,255,.88), rgba(226,232,240,.88));
            box-shadow:
                0 16px 40px rgba(15,23,42,.18),
                inset 0 0 0 1px rgba(255,255,255,.72);

            position: relative;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
            cursor: pointer;

            transition: transform .18s ease, box-shadow .22s ease, border-color .22s ease;
        }
        .about-btn.icon-only::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(560px 260px at 20% 10%, rgba(56,189,248,.18), transparent 55%),
                radial-gradient(520px 240px at 85% 15%, rgba(250,204,21,.10), transparent 60%);
            opacity: .9;
            pointer-events:none;
        }
        .about-btn.icon-only::after{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            padding: 2px;
            pointer-events:none;
            opacity: .35;
            transition: opacity .22s ease;
            background: linear-gradient(135deg,
                rgba(56,189,248,.70),
                rgba(79,70,229,.60),
                rgba(250,204,21,.45)
            );
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        .about-btn.icon-only:hover{
            transform: translateY(-2px);
            box-shadow:
                0 20px 52px rgba(15,23,42,.22),
                inset 0 0 0 1px rgba(255,255,255,.80);
            border-color: rgba(191,219,254,.95);
        }
        .about-btn.icon-only:hover::after{ opacity: .80; }
        .about-btn.icon-only:active{ transform: translateY(0px) scale(.98); }

        .about-icon-circle{
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 30% 0%,
                #fef9c3 0,
                #facc15 28%,
                #38bdf8 65%,
                #4f46e5 100%);
            box-shadow:
                0 14px 28px rgba(37,99,235,.16),
                0 0 18px rgba(56,189,248,.40);
            overflow: hidden;
        }
        .about-icon{
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        body.dark-mode .about-btn.icon-only{
            background: linear-gradient(135deg, rgba(15,23,42,.78), rgba(2,6,23,.84));
            border-color: rgba(37,99,235,.55);
            box-shadow:
                0 22px 60px rgba(0,0,0,.70),
                inset 0 0 0 1px rgba(30,64,175,.32);
        }
        body.dark-mode .about-btn.icon-only::after{ opacity: .55; }

        /* =========================
           TIME DISPLAY (same premium clock)
           ========================= */
        .time-display {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 14px;

            font-family: 'Orbitron', 'Poppins', monospace;
            font-size: 1.4rem;
            letter-spacing: 0.14em;
            color: #0b1120;

            padding: 18px 32px;
            border-radius: 999px;

            background: linear-gradient(135deg,
                rgba(255, 255, 255, 0.97),
                rgba(241, 245, 249, 0.99)
            );
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.7);
            box-shadow:
                0 12px 30px rgba(15, 23, 42, 0.26),
                0 0 0 1px rgba(255, 255, 255, 0.8);

            animation: floatTimer 10s ease-in-out infinite;
            transition:
                background 0.3s ease,
                color 0.3s ease,
                border-color 0.3s ease,
                box-shadow 0.3s ease;
            overflow: hidden;

            isolation: isolate;
            transform-style: preserve-3d;
            perspective: 900px;
        }

        .time-text{
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap: 6px;
            line-height: 1.05;
            position: relative;
            z-index: 3;
        }

        .time-main{
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum" 1, "lnum" 1;
            min-width: 9ch;
            text-align: left;
            white-space: nowrap;
        }
        .time-sub{
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.8;
            margin-left: 0 !important;
            white-space: nowrap;
            position: relative;
            padding-top: 6px;
        }
        .time-sub::before{
            content:"";
            position:absolute;
            left:0;
            top:0;
            width: 82%;
            height: 1px;
            background: linear-gradient(90deg, rgba(56,189,248,.55), rgba(79,70,229,.35), transparent);
            opacity: .55;
        }

        @keyframes floatTimer {
            0%, 100% { transform: translateY(0px); }
            50%      { transform: translateY(-4px); }
        }

        /* turbine ring */
        .time-display::before{
            content:"";
            width: 32px;
            height: 32px;
            border-radius: 999px;
            position: relative;
            z-index: 4;

            background:
                conic-gradient(
                    from 0deg,
                    rgba(56,189,248,.0) 0 18deg,
                    rgba(56,189,248,.95) 18deg 26deg,
                    rgba(79,70,229,.0) 26deg 52deg,
                    rgba(79,70,229,.9) 52deg 60deg,
                    rgba(168,85,247,.0) 60deg 88deg,
                    rgba(168,85,247,.85) 88deg 96deg,
                    rgba(250,204,21,.0) 96deg 126deg,
                    rgba(250,204,21,.75) 126deg 134deg,
                    rgba(56,189,248,.0) 134deg 360deg
                );

            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 4px));
            mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 4px));

            box-shadow:
                0 0 14px rgba(56,189,248,.55),
                0 0 26px rgba(79,70,229,.35);

            animation:
                timeTurbine 2.6s linear infinite,
                timeGlow 2.2s ease-in-out infinite;
        }
        @keyframes timeTurbine{ to{ transform: rotate(360deg); } }
        @keyframes timeGlow{
            0%,100%{ filter: drop-shadow(0 0 0 rgba(56,189,248,0)); opacity: .92; }
            50%{ filter: drop-shadow(0 0 12px rgba(56,189,248,.55)); opacity: 1; }
        }

        .time-display::after{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            pointer-events:none;
            z-index: 1;
            background:
                conic-gradient(from -90deg,
                    rgba(56,189,248,.00) 0deg,
                    rgba(56,189,248,.00) calc(var(--pdeg, 0deg) - 14deg),
                    rgba(56,189,248,.80) calc(var(--pdeg, 0deg) - 6deg),
                    rgba(79,70,229,.65) var(--pdeg, 0deg),
                    rgba(56,189,248,.00) calc(var(--pdeg, 0deg) + 10deg),
                    rgba(56,189,248,.00) 360deg
                ),
                conic-gradient(from 180deg,
                    rgba(56,189,248,.65),
                    rgba(79,70,229,.55),
                    rgba(168,85,247,.42),
                    rgba(250,204,21,.28),
                    rgba(56,189,248,.65)
                ),
                linear-gradient(120deg,
                    transparent 0%,
                    rgba(255,255,255,.22) 18%,
                    transparent 36%
                ),
                radial-gradient(220px 140px at var(--mx, 35%) var(--my, 30%),
                    rgba(255,255,255,.22), transparent 62%
                ),
                radial-gradient(160px 120px at 78% 22%,
                    rgba(56,189,248,.16), transparent 64%
                ),
                repeating-linear-gradient(90deg,
                    rgba(255,255,255,.045) 0 1px,
                    transparent 1px 12px
                );
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            padding: 2px;

            opacity: .62;
            mix-blend-mode: soft-light;

            background-size:
                auto,
                auto,
                220% 100%,
                auto,
                auto,
                auto;

            animation:
                timePrismSpin 7.8s linear infinite,
                timeSheenMove 6.2s ease-in-out infinite,
                timeHueShift 10s ease-in-out infinite;
        }

        @keyframes timePrismSpin{ to{ transform: rotate(360deg); } }
        @keyframes timeSheenMove{
            0%,55%{ background-position: 0 0, 0 0, -140% 0, 0 0, 0 0, 0 0; }
            75%,100%{ background-position: 0 0, 0 0, 140% 0, 0 0, 0 0, 0 0; }
        }
        @keyframes timeHueShift{
            0%,100%{ filter: hue-rotate(0deg); }
            50%{ filter: hue-rotate(18deg); }
        }

        .time-main .t-sep{
            display:inline-block;
            opacity: .62;
            transform: translateY(-1px);
            text-shadow: 0 0 12px rgba(56,189,248,.18);
            animation: sepBlink 1s steps(2, end) infinite;
        }
        @keyframes sepBlink{
            0%,100%{ opacity:.25; }
            50%{ opacity:.95; }
        }

        body.dark-mode .time-display{
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.99));
            color: #e5e7eb;
            border-color: rgba(37, 99, 235, 0.9);
            box-shadow:
                0 14px 32px rgba(15, 23, 42, 0.98),
                0 0 22px rgba(30, 64, 175, 0.7);
        }
        body.dark-mode .time-display::after{
            opacity: .55;
            mix-blend-mode: screen;
        }
        body.dark-mode .time-sub::before{
            background: linear-gradient(90deg, rgba(129,140,248,.55), rgba(56,189,248,.30), transparent);
            opacity: .45;
        }

        body:not(.dark-mode) .time-display::after{
            opacity: .92;
            mix-blend-mode: normal;
            filter: saturate(1.12) contrast(1.06);
        }
        body:not(.dark-mode) .time-display::before{
            box-shadow:
                0 0 18px rgba(56,189,248,.75),
                0 0 34px rgba(79,70,229,.45);
        }

        /* =========================
           DHAKA WEATHER (same)
           ========================= */
        .weather-display{
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 14px;

            padding: 14px 22px;
            border-radius: 999px;

            background: linear-gradient(135deg,
                rgba(255, 255, 255, 0.92),
                rgba(226, 232, 240, 0.95)
            );
            border: 1px solid rgba(148, 163, 184, 0.65);
            box-shadow:
                0 16px 34px rgba(15, 23, 42, 0.20),
                0 0 0 1px rgba(255, 255, 255, 0.75);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            transform-style: preserve-3d;
            perspective: 900px;
            overflow: hidden;

            animation: wxFloat 8.5s ease-in-out infinite;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
        }

        .weather-display:hover{
            transform: translateY(-3px) rotateX(7deg) rotateY(-9deg);
            box-shadow:
                0 22px 46px rgba(15, 23, 42, 0.28),
                0 0 0 1px rgba(191, 219, 254, 0.95);
        }

        .weather-display::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: conic-gradient(from 160deg,
                rgba(56,189,248,0),
                rgba(56,189,248,.32),
                rgba(79,70,229,.22),
                rgba(34,197,94,.14),
                rgba(56,189,248,0)
            );
            filter: blur(10px);
            opacity: .55;
            pointer-events:none;
        }
        .weather-display::after{
            content:"";
            position:absolute;
            inset:0;
            border-radius: inherit;
            background: linear-gradient(120deg,
                transparent 0%,
                rgba(255,255,255,.24) 18%,
                transparent 36%
            );
            transform: translateX(-140%);
            opacity: .35;
            pointer-events:none;
            animation: wxShine 4.8s ease-in-out infinite;
        }

        @keyframes wxShine{
            0%,55%{ transform: translateX(-140%); }
            75%,100%{ transform: translateX(140%); }
        }
        @keyframes wxFloat{
            0%,100%{ transform: translateY(0px); }
            50%{ transform: translateY(-4px); }
        }

        .wx-icon{
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            position: relative;

            background: radial-gradient(circle at 30% 10%,
                rgba(254, 249, 195, 0.95),
                rgba(56, 189, 248, 0.70),
                rgba(79, 70, 229, 0.60)
            );

            box-shadow:
                0 12px 24px rgba(37,99,235,.22),
                inset 0 0 0 1px rgba(255,255,255,.55);

            transform: translateZ(14px);
            overflow: hidden;
        }
        .wx-icon::before{
            content:"";
            position:absolute;
            inset:-40%;
            background: conic-gradient(
                from 0deg,
                rgba(255,255,255,.70),
                rgba(255,255,255,0),
                rgba(255,255,255,.45),
                rgba(255,255,255,0)
            );
            opacity:.28;
            transform: scale(1.02);
            animation: wxBreathe 3.8s ease-in-out infinite;
        }
        @keyframes wxBreathe{
            0%,100% { opacity: .18; transform: scale(1.00); }
            50%     { opacity: .38; transform: scale(1.06); }
        }

        .wx-emoji{
            position: relative;
            z-index: 1;
            font-size: 1.2rem;
            filter: drop-shadow(0 6px 12px rgba(15,23,42,.25));
        }

        .wx-text{ transform: translateZ(10px); }
        .wx-top{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom: 4px;
        }
        .wx-city{
            font-family: 'Orbitron', 'Poppins', sans-serif;
            letter-spacing: .08em;
            text-transform: uppercase;
            font-size: .85rem;
            opacity: .9;
        }
        .wx-chip{
            font-size: .68rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(56,189,248,.16);
            border: 1px solid rgba(56,189,248,.55);
            color: #0b2a4a;
        }
        .wx-temp{
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: .06em;
            line-height: 1.1;
        }
        .wx-meta{
            font-size: .82rem;
            opacity: .82;
            white-space: nowrap;
        }

        body.dark-mode .weather-display{
            background: linear-gradient(135deg,
                rgba(15, 23, 42, 0.96),
                rgba(2, 6, 23, 0.98)
            );
            border-color: rgba(37, 99, 235, 0.90);
            box-shadow:
                0 18px 40px rgba(0,0,0,.92),
                0 0 22px rgba(30,64,175,.55);
        }
        body.dark-mode .weather-display::after{
            background: linear-gradient(120deg,
                transparent 0%,
                rgba(255,255,255,.10) 18%,
                transparent 36%
            );
            opacity: .26;
        }
        body.dark-mode .wx-chip{
            background: rgba(129,140,248,.14);
            border-color: rgba(129,140,248,.55);
            color: #e5e7eb;
        }

        /* =========================
           HERO (Dashboard title + Welcome) — same patch
           ========================= */
        .container{
            max-width: 1600px;
            width: 100%;
            margin: 18px auto 0;
            padding: clamp(18px, 3vw, 34px);

            text-align: left;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;

            background: linear-gradient(135deg, rgba(255,255,255,.90), rgba(226,232,240,.86));
            border: 1px solid rgba(148,163,184,.55);
            border-radius: 26px;
            box-shadow: 0 26px 70px rgba(15,23,42,.14);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            position: relative;
            overflow: hidden;
            isolation: isolate;
        }
        .container::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: conic-gradient(
                from 180deg,
                rgba(56,189,248,.55),
                rgba(79,70,229,.45),
                rgba(250,204,21,.22),
                rgba(56,189,248,.55)
            );
            opacity: .22;
            filter: blur(12px);
            pointer-events:none;
            z-index: 0;
        }
        .container::after{
            content:"";
            position:absolute;
            inset:0;
            border-radius: inherit;
            background:
                radial-gradient(900px 380px at 12% 10%, rgba(56,189,248,.16), transparent 60%),
                radial-gradient(900px 420px at 86% 18%, rgba(79,70,229,.12), transparent 62%);
            opacity: .9;
            pointer-events:none;
            z-index: 0;
        }
        .container > *{ position: relative; z-index: 1; }

        .dashboard-title{
            margin: 0;
            font-family: 'Orbitron','Poppins',sans-serif;
            font-size: clamp(2.1rem, 2.6vw, 3.1rem);
            letter-spacing: .14em;
            text-transform: uppercase;

            background: linear-gradient(90deg, #38bdf8, #4f46e5, #facc15);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .welcome{
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: rgba(15,23,42,.78);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .welcome strong{
            font-weight: 800;
            color: #0b1120;
            border-right: 2px solid rgba(56,189,248,.85);
            padding-right: 8px;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            min-width: max-content;
            animation: blinkCursor 0.8s infinite;
        }
        @keyframes blinkCursor {
            0%, 100% { border-color: transparent; }
            50% { border-color: rgba(56,189,248,.85); }
        }

        body.dark-mode .container{
            background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
            border-color: rgba(37,99,235,.60);
            box-shadow: 0 34px 90px rgba(0,0,0,.70);
        }
        body.dark-mode .welcome{ color: rgba(229,231,235,.75); }
        body.dark-mode .welcome strong{ color: #e5e7eb; }

        /* =========================
           ANALYTICS (reuse Cognisense style, mapped to AspireIELTS data)
           ========================= */
        :root{
            --ana-surface: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
            --ana-card: rgba(255,255,255,.70);
            --ana-border: rgba(148,163,184,.45);
            --ana-shadow: 0 24px 60px rgba(15,23,42,.12);
            --ana-text: #0b1120;
            --ana-muted: rgba(15,23,42,.62);

            --ana-chart-grid: rgba(15,23,42,.12);
            --ana-chart-tick: rgba(15,23,42,.55);
        }
        body.dark-mode{
            --ana-surface: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
            --ana-card: rgba(15,23,42,.62);
            --ana-border: rgba(37,99,235,.55);
            --ana-shadow: 0 30px 90px rgba(0,0,0,.60);
            --ana-text: #e5e7eb;
            --ana-muted: rgba(229,231,235,.68);

            --ana-chart-grid: rgba(129,140,248,.18);
            --ana-chart-tick: rgba(229,231,235,.68);
        }

        .ana-shell{
            max-width: 1600px;
            width: 100%;
            margin: 24px auto 0;
        }

        .ana-empty{
            margin-top: 16px;
            padding: 18px;
            border-radius: 22px;
            border: 1px dashed rgba(148,163,184,.55);
            background: rgba(255,255,255,.35);
            color: var(--ana-text);
        }
        body.dark-mode .ana-empty{ background: rgba(2,6,23,.24); border-color: rgba(129,140,248,.35); }
        .ana-empty h3{
            margin: 0 0 8px;
            font-family: 'Orbitron','Poppins',sans-serif;
            letter-spacing: .10em;
            text-transform: uppercase;
            font-size: .95rem;
        }
        .ana-empty p{
            margin: 0 0 12px;
            color: var(--ana-muted);
        }
        .ana-cta{
            display:inline-flex;
            align-items:center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, #4a00e0, #06b6d4);
            box-shadow: 0 14px 30px rgba(37,99,235,.22);
            text-decoration: none;
        }
        .ana-cta:hover{ filter: brightness(1.05); }

        .ana-hero{
            position: relative;
            display: grid;
            grid-template-columns: 1.45fr .55fr;
            gap: 16px;
            padding: 18px 18px;
            border-radius: 24px;
            background: var(--ana-surface);
            border: 1px solid var(--ana-border);
            box-shadow: var(--ana-shadow);
            overflow: hidden;
        }

        .ana-hero::before{
            content:"";
            position:absolute;
            inset:-2px;
            border-radius: inherit;
            background: conic-gradient(
                from 190deg,
                rgba(56,189,248,.40),
                rgba(79,70,229,.34),
                rgba(250,204,21,.18),
                rgba(56,189,248,.40)
            );
            opacity: .28;
            pointer-events:none;
            filter: blur(10px);
        }

        .ana-hero::after{
            content:"";
            position:absolute;
            inset:0;
            border-radius: inherit;
            background:
                radial-gradient(860px 360px at 18% 8%, rgba(56,189,248,.18), transparent 62%),
                radial-gradient(860px 420px at 86% 16%, rgba(79,70,229,.12), transparent 65%);
            pointer-events:none;
            opacity: .85;
        }
        .ana-hero > *{ position: relative; z-index: 1; }

        .ana-title{
            display:flex;
            align-items:center;
            gap: 12px;
            margin: 0;
            font-family: 'Orbitron','Poppins',sans-serif;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: 1.15rem;
            color: var(--ana-text);
        }
        .ana-title .dot{
            width: 10px; height: 10px; border-radius: 999px;
            background: radial-gradient(circle at 30% 30%, #fef9c3, #38bdf8 55%, #4f46e5);
            box-shadow: 0 0 12px rgba(56,189,248,.55);
        }
        .ana-sub{
            margin: 8px 0 0;
            color: var(--ana-muted);
            line-height: 1.55;
            font-size: .98rem;
            max-width: 68ch;
        }

        .ana-badges{
            margin-top: 12px;
            display:flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .ana-badge{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,.46);
            border: 1px solid var(--ana-border);
            color: var(--ana-text);
            box-shadow: 0 14px 30px rgba(15,23,42,.10);
            font-size: .82rem;
            letter-spacing: .06em;
        }
        body.dark-mode .ana-badge{ background: rgba(2,6,23,.35); }

        .ana-ring{
            width: 170px;
            height: 170px;
            border-radius: 999px;
            display:grid;
            place-items:center;
            margin-left:auto;

            background:
                conic-gradient(from -90deg,
                    rgba(56,189,248,.95) 0 var(--p, 0%),
                    rgba(148,163,184,.18) var(--p, 0%) 100%
                );

            box-shadow:
                0 22px 55px rgba(15,23,42,.18),
                0 0 18px rgba(56,189,248,.20);
            border: 1px solid var(--ana-border);
            position: relative;
            overflow: hidden;
        }
        .ana-ring::before{
            content:"";
            position:absolute;
            inset: 10px;
            border-radius: inherit;
            background: var(--ana-surface);
            border: 1px solid rgba(148,163,184,.25);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);
        }
        .ana-ring-inner{ position: relative; z-index: 1; text-align:center; padding: 10px; }
        .ana-ring-big{
            font-family: 'Orbitron','Poppins',sans-serif;
            font-weight: 800;
            letter-spacing: .10em;
            color: var(--ana-text);
            font-size: 1.35rem;
        }
        .ana-ring-small{
            margin-top: 6px;
            font-size: .82rem;
            color: var(--ana-muted);
            letter-spacing: .10em;
            text-transform: uppercase;
        }

        .ana-kpis{
            margin-top: 16px;
            display:grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }
        .ana-kpi{
            position: relative;
            border-radius: 20px;
            padding: 14px 14px 12px;
            background: var(--ana-card);
            border: 1px solid var(--ana-border);
            box-shadow: 0 18px 44px rgba(15,23,42,.10);
            transform-style: preserve-3d;
            transition: transform .18s ease, box-shadow .22s ease, border-color .22s ease;
            overflow:hidden;
        }
        .ana-kpi::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(520px 220px at 10% 0%, rgba(56,189,248,.16), transparent 60%),
                radial-gradient(520px 220px at 90% 20%, rgba(79,70,229,.12), transparent 60%);
            opacity: .9;
            pointer-events:none;
        }
        .ana-kpi:hover{
            transform: translateY(-2px);
            box-shadow: 0 24px 60px rgba(15,23,42,.14);
            border-color: rgba(56,189,248,.45);
        }
        .ana-kpi-top{
            position: relative;
            z-index: 1;
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
        }
        .ana-kpi-label{
            font-size: .78rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--ana-muted);
            font-weight: 700;
        }
        .ana-kpi-ic{
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display:grid;
            place-items:center;
            background: radial-gradient(circle at 30% 10%,
                rgba(254,249,195,.92),
                rgba(56,189,248,.55),
                rgba(79,70,229,.45));
            box-shadow: 0 14px 26px rgba(37,99,235,.16);
        }
        .ana-kpi-val{
            position: relative;
            z-index: 1;
            margin-top: 10px;
            font-family: 'Orbitron','Poppins',sans-serif;
            font-weight: 900;
            letter-spacing: .10em;
            color: var(--ana-text);
            font-size: 1.35rem;
        }
        .ana-kpi-sub{
            position: relative;
            z-index: 1;
            margin-top: 6px;
            font-size: .88rem;
            color: var(--ana-muted);
        }

        .ana-grids{
            margin-top: 16px;
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .ana-card{
            border-radius: 22px;
            padding: 14px;
            background: var(--ana-card);
            border: 1px solid var(--ana-border);
            box-shadow: 0 22px 55px rgba(15,23,42,.10);
            overflow: hidden;
            position: relative;
        }
        .ana-card::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(760px 280px at 16% 0%, rgba(56,189,248,.12), transparent 60%),
                radial-gradient(760px 300px at 92% 12%, rgba(79,70,229,.10), transparent 62%);
            opacity: .9;
            pointer-events:none;
        }
        .ana-card > *{ position: relative; z-index: 1; }

        .ana-card-head{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }
        .ana-card-title{
            display:flex;
            align-items:center;
            gap: 10px;
            font-weight: 900;
            color: var(--ana-text);
            letter-spacing: .08em;
            text-transform: uppercase;
            font-size: .80rem;
            margin: 0;
        }
        .ana-card-title .mini-dot{
            width: 8px; height: 8px; border-radius: 999px;
            background: rgba(56,189,248,.95);
            box-shadow: 0 0 10px rgba(56,189,248,.45);
        }
        .ana-card-note{
            font-size: .86rem;
            color: var(--ana-muted);
            opacity: .95;
        }

        .ana-chart-wrap{ height: 300px; position: relative; }
        .ana-chart-wrap canvas{
            display:block;
            width: 100% !important;
            height: 100% !important;
        }

        .recent-table{
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        .recent-table th{
            text-align:left;
            font-size: .72rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--ana-muted);
            padding: 0 10px 6px;
        }
        .recent-table td{
            padding: 10px 10px;
            background: rgba(255,255,255,.40);
            border: 1px solid rgba(148,163,184,.28);
            color: var(--ana-text);
        }
        body.dark-mode .recent-table td{
            background: rgba(2,6,23,.28);
            border-color: rgba(129,140,248,.20);
        }
        .recent-table tr td:first-child{
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
        }
        .recent-table tr td:last-child{
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }
        .mode-pill{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(56,189,248,.26);
            background: rgba(56,189,248,.10);
            font-size: .74rem;
            letter-spacing: .10em;
            text-transform: uppercase;
        }

        @media (max-width: 1100px){
            .ana-hero{ grid-template-columns: 1fr; }
            .ana-ring{ margin-left: 0; }
            .ana-kpis{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ana-grids{ grid-template-columns: 1fr; }
        }
        @media (max-width: 560px){
            .ana-kpis{ grid-template-columns: 1fr; }
            .ana-chart-wrap{ height: 240px; }
        }

        /* =========================
           FOOTER (same)
           ========================= */
        .footer {
            margin-top: 32px;
            padding: 16px 0 12px;
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            font-size: 0.9rem;
            color: #6b7280;
        }
        .footer-socials { display: flex; gap: 40px; }
        .footer-social-link {
            position: relative;
            width: 77px;
            height: 77px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            box-shadow:
                0 6px 16px rgba(15,23,42,0.18),
                0 0 0 1px rgba(148,163,184,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transform: translateY(0) scale(1);
            transition: transform 0.35s ease, box-shadow 0.35s ease, background 0.35s ease;
        }
        .footer-social-link::before {
            content: "";
            position: absolute;
            inset: -30%;
            border-radius: inherit;
            background: radial-gradient(circle at 20% 0%,
                        rgba(96,165,250,0.0) 0,
                        rgba(129,140,248,0.45) 35%,
                        rgba(56,189,248,0.0) 75%);
            opacity: 0;
            transform: scale(0.7);
            pointer-events: none;
            transition: opacity 0.35s ease, transform 0.35s ease;
        }
        .footer-social-link img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 0 4px rgba(15,23,42,0.35));
            transition: transform 0.35s ease, opacity 0.35s ease;
        }
        .footer-social-label {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 0.35s ease, transform 0.35s ease;
            color: #4b5563;
            text-align: center;
        }
        .footer-social-link:hover {
            transform: translateY(-6px) scale(1.06);
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            box-shadow:
                0 14px 32px rgba(15,23,42,0.75),
                0 0 0 1px rgba(129,140,248,0.95);
        }
        .footer-social-link:hover::before { opacity: 1; transform: scale(1); }
        .footer-social-link:hover img { transform: scale(0.7) rotate(-6deg); opacity: 0.15; }
        .footer-social-link:hover .footer-social-label { opacity: 1; transform: translateY(0); color: #e5e7eb; }
        .footer-copy { font-size: 1rem; opacity: 0.85; }

        body.dark-mode .footer { border-top-color: rgba(31,41,55,0.9); color: #9ca3af; }
        body.dark-mode .footer-social-link {
            background: rgba(15,23,42,0.95);
            box-shadow:
                0 8px 20px rgba(0,0,0,0.85),
                0 0 0 1px rgba(30,64,175,0.7);
        }
        body.dark-mode .footer-social-link::before {
            background: radial-gradient(circle at 20% 0%,
                        rgba(56,189,248,0.05) 0,
                        rgba(129,140,248,0.6) 35%,
                        rgba(15,23,42,0.0) 80%);
        }
        body.dark-mode .footer-copy { color: #9ca3af; }

        /* =========================
           ABOUT OVERLAY (same structure, Aspire content)
           ========================= */
        body.modal-open .sidebar,
        body.modal-open .main-content{
            filter: blur(8px);
            transform: scale(0.995);
            pointer-events: none;
        }
        .about-overlay{
            position: fixed;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            z-index: 10050;

            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .22s ease, visibility .22s ease;

            background:
                radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.22), transparent 60%),
                radial-gradient(900px 560px at 85% 22%, rgba(79,70,229,.20), transparent 62%),
                rgba(15, 23, 42, 0.28);

            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .about-overlay.open{
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        body.modal-open { overflow: hidden; }

        .about-modal{
            position: relative;
            width: min(1120px, 96vw);
            height: min(86vh, 780px);
            overflow: hidden;
            border-radius: 26px;
            padding: 26px 26px 18px;

            display: grid;
            grid-template-rows: auto 1fr auto;

            transform: translateY(14px) scale(.985);
            opacity: 0;
            transition: transform .26s ease, opacity .26s ease;

            transform-style: preserve-3d;
            background: linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92));
            border: 1px solid rgba(148,163,184,.55);
            box-shadow:
                0 30px 80px rgba(15,23,42,.34),
                0 0 0 1px rgba(255,255,255,.75);
        }
        .about-overlay.open .about-modal{
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .about-close{
            position: absolute;
            top: 18px;
            right: 18px;
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1px solid rgba(148,163,184,.55);
            background: rgba(255,255,255,.55);
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(15,23,42,.18);
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
            z-index: 5;
        }
        .about-close:hover{
            transform: translateY(-1px) scale(1.03);
            box-shadow: 0 16px 30px rgba(15,23,42,.22);
            background: rgba(255,255,255,.75);
        }
        .about-hero{ position: relative; padding: 10px 8px 18px; }
        .about-hero-badge{
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(56,189,248,.12);
            border: 1px solid rgba(56,189,248,.40);
            color: #0b2a4a;
            font-size: .72rem;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .about-dot{
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 30%, #fef9c3, #38bdf8 55%, #4f46e5);
            box-shadow: 0 0 12px rgba(56,189,248,.75);
        }
        .about-title{
            font-family: 'Orbitron', 'Poppins', sans-serif;
            margin: 14px 0 6px;
            font-size: 2.2rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #0b1120;
            text-shadow: 2px 6px 18px rgba(30,58,138,.18);
        }
        .about-subtitle{
            margin: 0;
            font-size: 1.02rem;
            opacity: .85;
            color: #1f2937;
            max-width: 70ch;
        }
        .about-tags{
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .about-tag{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .82rem;
            background: rgba(255,255,255,.55);
            border: 1px solid rgba(148,163,184,.45);
            color: #0b1120;
            box-shadow: 0 10px 20px rgba(15,23,42,.10);
        }
        .about-grid{
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 14px;
            padding: 6px 6px 14px;
        }
        .about-card{
            background: rgba(255,255,255,.65);
            border: 1px solid rgba(148,163,184,.40);
            border-radius: 18px;
            padding: 14px 14px 12px;
            box-shadow:
                0 16px 34px rgba(15,23,42,.12),
                inset 0 0 0 1px rgba(255,255,255,.55);
            transform-style: preserve-3d;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .about-card:hover{
            transform: translateY(-2px) rotateX(5deg) rotateY(-6deg);
            box-shadow: 0 22px 46px rgba(15,23,42,.16);
        }
        .about-card-head{
            display:flex;
            align-items:center;
            gap: 10px;
            margin-bottom: 6px;
        }
        .about-card h3{
            margin: 0;
            font-size: 1.05rem;
            color: #0b1120;
        }
        .about-card p{
            margin: 0;
            color: #334155;
            opacity: .92;
            line-height: 1.5;
            font-size: .95rem;
        }
        .about-ic{
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display:grid;
            place-items:center;
            color: #0b1120;
            background: radial-gradient(circle at 30% 10%, rgba(254,249,195,.95), rgba(56,189,248,.55), rgba(79,70,229,.45));
            box-shadow: 0 14px 26px rgba(37,99,235,.16);
            transform: translateZ(12px);
        }
        .about-footer{
            padding: 10px 10px 0;
            border-top: 1px solid rgba(148,163,184,.25);
            margin-top: 8px;
        }
        .about-mini{
            font-size: .9rem;
            opacity: .82;
            color: #334155;
        }

        body.dark-mode .about-overlay{
            background:
                radial-gradient(900px 520px at 15% 18%, rgba(56,189,248,.18), transparent 60%),
                radial-gradient(900px 560px at 85% 22%, rgba(129,140,248,.16), transparent 62%),
                rgba(2, 6, 23, 0.62);
        }
        body.dark-mode .about-modal{
            background: linear-gradient(135deg, rgba(15,23,42,.96), rgba(2,6,23,.98));
            border-color: rgba(37,99,235,.70);
            box-shadow:
                0 34px 90px rgba(0,0,0,.70),
                0 0 22px rgba(30,64,175,.40);
        }
        body.dark-mode .about-close{
            background: rgba(15,23,42,.55);
            border-color: rgba(129,140,248,.55);
            color: #e5e7eb;
        }
        body.dark-mode .about-hero-badge{
            background: rgba(129,140,248,.12);
            border-color: rgba(129,140,248,.35);
            color: #e5e7eb;
        }
        body.dark-mode .about-title{ color: #e5e7eb; }
        body.dark-mode .about-subtitle{ color: #cbd5e1; }
        body.dark-mode .about-tag{
            background: rgba(15,23,42,.55);
            border-color: rgba(129,140,248,.35);
            color: #e5e7eb;
        }
        body.dark-mode .about-card{
            background: rgba(15,23,42,.62);
            border-color: rgba(129,140,248,.30);
        }
        body.dark-mode .about-card h3{ color: #e5e7eb; }
        body.dark-mode .about-card p{ color: #cbd5e1; }
        body.dark-mode .about-mini{ color: #cbd5e1; }

        @media (max-width: 820px){
            .about-grid{ grid-template-columns: 1fr; }
        }

        /* =========================
           RESPONSIVE (same pattern)
           ========================= */
        @media (max-width: 768px) {
            .sidebar { position: fixed; width: var(--sidebar-collapsed-width); }
            .sidebar.collapsed { width: 0; }
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                width: calc(100% - var(--sidebar-collapsed-width));
                padding: 20px;
            }
            .sidebar.collapsed + .main-content {
                margin-left: 0;
                width: 100%;
            }
            .time-display { font-size: 1.4rem; padding: 10px 16px; }
        }

        /* =========================
           ✅ AspireIELTS Chatbot CSS (kept as-is from your dashboard.php)
           (You asked to leave chatbot like it is)
           ========================= */

        /* Floating Chatbot Icon */
        #chatbot-icon {
          position: fixed;
          bottom: 20px;
          right: 25px;
          z-index: 999;
          cursor: pointer;
          width: 60px;
          height: 60px;
          background: #0077cc;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        #chatbot-icon img {
          width: 35px;
          height: 35px;
        }

        @keyframes fadeInUp {
          from { opacity: 0; transform: translateY(30px) scale(0.98); }
          to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes pulseShadow {
          0%, 100% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
          50%      { box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35); }
        }

        #chatbot-container {
          position: fixed;
          bottom: 90px;
          right: 25px;
          width: 320px;
          max-height: 450px;
          background: #fff;
          border-radius: 15px;
          box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
          overflow: hidden;
          display: flex;
          flex-direction: column;
          z-index: 1000;
          animation: fadeInUp 0.5s ease forwards, pulseShadow 6s ease-in-out infinite;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        #chatbot-container:hover {
          transform: translateY(-4px) scale(1.02);
          box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
        }

        .chatbot-header {
          background: #1a73e8;
          color: white;
          padding: 2px 8px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          font-weight: 600;
          letter-spacing: 0.4px;
          font-size: 10px;
          line-height: 1;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
          transition: transform 0.3s ease, box-shadow 0.3s ease;
          animation: slideDown 0.4s ease-out;
        }

        .chatbot-header:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 12px rgba(0, 115, 230, 0.3);
        }

        .chatbot-header .close-btn {
          font-size: 20px;
          cursor: pointer;
          transition: transform 0.3s, color 0.3s;
        }

        .chatbot-header .close-btn:hover {
          transform: rotate(90deg) scale(1.2);
          color: #ffdede;
        }

        @keyframes slideDown {
          from { opacity: 0; transform: translateY(-20px); }
          to   { opacity: 1; transform: translateY(0); }
        }

        .messages {
          flex-grow: 1;
          padding: 12px;
          overflow-y: auto;
          display: flex;
          flex-direction: column;
          gap: 8px;
          scroll-behavior: smooth;
        }

        @keyframes messagePop {
          from { opacity: 0; transform: scale(0.95) translateY(10px); }
          to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .message {
          margin-bottom: 4px;
          padding: 10px 14px;
          border-radius: 12px;
          max-width: 80%;
          animation: messagePop 0.3s ease;
          transition: background 0.3s, transform 0.3s;
        }

        .message:hover { transform: scale(1.02); }

        .message.user {
          background: linear-gradient(135deg, #0077cc, #005fa3);
          color: white;
          align-self: flex-end;
          text-align: right;
        }

        .message.bot {
          background: #f1f1f1;
          color: #333;
          align-self: flex-start;
          text-align: left;
        }

        .input-container {
          border-top: 1px solid #ddd;
          padding: 10px;
          background: #f9f9f9;
          transition: background 0.3s;
        }

        .input-container input {
          width: 100%;
          padding: 10px 12px;
          border-radius: 10px;
          border: 1px solid #ccc;
          outline: none;
          transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        .input-container input:focus {
          border-color: #0077cc;
          box-shadow: 0 0 6px rgba(0, 123, 255, 0.2);
        }

        .hidden { display: none; }

        /* Dark Mode Styles for chatbot */
        .dark-mode #chatbot-icon {
          background: #1a73e8;
          box-shadow: 0 6px 15px rgba(255, 255, 255, 0.1);
        }

        .dark-mode #chatbot-container {
          background: #1e1e1e;
          box-shadow: 0 10px 30px rgba(255, 255, 255, 0.15);
          border: 1px solid #333;
        }

        .dark-mode .chatbot-header {
          background: linear-gradient(135deg, #0077cc, #005fa3);
          color: #fff;
        }

        .dark-mode .chatbot-header .close-btn { color: #fff; }

        .dark-mode .messages {
          background-color: #1e1e1e;
          color: #f1f1f1;
        }

        .dark-mode .input-container {
          background: #2a2a2a;
          border-top: 1px solid #444;
        }

        .dark-mode .input-container input {
          background: #333;
          color: #fff;
          border: 1px solid #555;
        }

        .dark-mode .message.user {
          background: #1a73e8;
          color: #fff;
        }

        .dark-mode .message.bot {
          background: #333;
          color: #ddd;
        }

/* =========================
   Cognisense XL Test Actions
   ========================= */
.cs-test-actions--xl{
  margin-top: 22px;
  padding: 22px;
  border-radius: 28px;
  background: var(--ana-surface, linear-gradient(135deg, rgba(255,255,255,.92), rgba(226,232,240,.92)));
  border: 1px solid var(--ana-border, rgba(148,163,184,.45));
  box-shadow: var(--ana-shadow, 0 24px 60px rgba(15,23,42,.12));
  position: relative;
  overflow: hidden;
  isolation: isolate;
}

.cs-test-actions--xl::before{
  content:"";
  position:absolute;
  inset:-3px;
  border-radius: inherit;
  background: conic-gradient(
    from 200deg,
    rgba(56,189,248,.55),
    rgba(79,70,229,.42),
    rgba(250,204,21,.22),
    rgba(56,189,248,.55)
  );
  opacity: .24;
  filter: blur(14px);
  pointer-events:none;
  z-index: 0;
}
.cs-test-actions--xl::after{
  content:"";
  position:absolute;
  inset:0;
  border-radius: inherit;
  background:
    radial-gradient(900px 420px at 14% 10%, rgba(56,189,248,.18), transparent 62%),
    radial-gradient(900px 460px at 86% 18%, rgba(79,70,229,.14), transparent 65%);
  opacity: .9;
  pointer-events:none;
  z-index: 0;
}
.cs-test-actions--xl > *{ position: relative; z-index: 1; }

/* HERO ROW */
.cs-test-hero{
  display:flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 18px;
  padding: 6px 6px 14px;
  margin-bottom: 10px;
}

.cs-test-hero-left{ flex: 1; min-width: 0; }
.cs-test-title{
  margin: 0;
  display:flex;
  align-items:center;
  gap: 12px;
  font-family: 'Orbitron','Poppins',sans-serif;
  letter-spacing: .14em;
  text-transform: uppercase;
  font-size: 1.18rem;
  color: var(--ana-text, #0b1120);
}
.cs-dot{
  width: 11px; height: 11px; border-radius: 999px;
  background: radial-gradient(circle at 30% 30%, #fef9c3, #38bdf8 55%, #4f46e5);
  box-shadow: 0 0 16px rgba(56,189,248,.65);
}
.cs-test-sub{
  margin: 10px 0 0;
  color: var(--ana-muted, rgba(15,23,42,.62));
  font-size: 1rem;
  line-height: 1.55;
  max-width: 72ch;
}

/* Chips */
.cs-test-chips{
  margin-top: 12px;
  display:flex;
  flex-wrap: wrap;
  gap: 10px;
}
.cs-chip{
  display:inline-flex;
  align-items:center;
  gap: 8px;
  padding: 9px 12px;
  border-radius: 999px;
  background: rgba(255,255,255,.46);
  border: 1px solid var(--ana-border, rgba(148,163,184,.45));
  color: var(--ana-text, #0b1120);
  box-shadow: 0 14px 30px rgba(15,23,42,.10);
  font-size: .82rem;
  letter-spacing: .08em;
}
body.dark-mode .cs-chip{ background: rgba(2,6,23,.35); }

/* Hero right visuals */
.cs-test-hero-right{
  width: min(340px, 42%);
  display:flex;
  justify-content:flex-end;
  position: relative;
  padding-top: 4px;
}
.cs-orb{
  position:absolute;
  right: 12px;
  top: 10px;
  width: 150px;
  height: 150px;
  border-radius: 999px;
  background: radial-gradient(circle at 30% 20%,
    rgba(250,204,21,.35),
    rgba(56,189,248,.28),
    rgba(79,70,229,.22),
    transparent 62%
  );
  filter: blur(0px);
  opacity: .85;
  animation: csOrbFloat 8.8s ease-in-out infinite;
}
.cs-orb2{
  right: 120px;
  top: 50px;
  width: 110px;
  height: 110px;
  opacity: .65;
  animation-duration: 10.4s;
}
@keyframes csOrbFloat{
  0%,100%{ transform: translateY(0px) translateX(0px) scale(1); }
  50%{ transform: translateY(-6px) translateX(-4px) scale(1.04); }
}
.cs-hero-badge{
  position: relative;
  margin-left:auto;
  padding: 14px 16px;
  border-radius: 20px;
  background: rgba(255,255,255,.58);
  border: 1px solid rgba(148,163,184,.35);
  box-shadow: 0 18px 44px rgba(15,23,42,.12);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  overflow:hidden;
}
.cs-hero-badge::after{
  content:"";
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  background: linear-gradient(135deg,
    rgba(56,189,248,.55),
    rgba(79,70,229,.50),
    rgba(250,204,21,.26)
  );
  opacity:.35;
  filter: blur(10px);
  pointer-events:none;
}
.cs-hero-badge-top{
  font-size: .72rem;
  letter-spacing: .20em;
  text-transform: uppercase;
  color: var(--ana-muted, rgba(15,23,42,.62));
  font-weight: 800;
}
.cs-hero-badge-main{
  margin-top: 8px;
  font-family: 'Orbitron','Poppins',sans-serif;
  font-weight: 900;
  letter-spacing: .14em;
  color: var(--ana-text, #0b1120);
  font-size: 1.05rem;
  text-transform: uppercase;
}
.cs-hero-badge-sub{
  margin-top: 6px;
  font-size: .9rem;
  color: var(--ana-muted, rgba(15,23,42,.62));
}

body.dark-mode .cs-hero-badge{
  background: rgba(2,6,23,.35);
  border-color: rgba(129,140,248,.35);
}

/* GRID */
.cs-test-grid--xl{
  display:grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

/* CARD (taller) */
.cs-test-card{
  position: relative;
  display:flex;
  flex-direction: column;
  justify-content: space-between;

  min-height: 220px;           /* ✅ bigger height */
  padding: 18px 16px;
  border-radius: 24px;
  text-decoration: none;

  background: var(--ana-card, rgba(255,255,255,.70));
  border: 1px solid var(--ana-border, rgba(148,163,184,.45));
  box-shadow: 0 18px 44px rgba(15,23,42,.10);

  transform-style: preserve-3d;
  transition: transform .18s ease, box-shadow .22s ease, border-color .22s ease, filter .22s ease;
  overflow:hidden;
  -webkit-tap-highlight-color: transparent;
}

.cs-test-card::before{
  content:"";
  position:absolute;
  inset:0;
  background:
    radial-gradient(620px 260px at 12% 0%, rgba(56,189,248,.16), transparent 60%),
    radial-gradient(620px 260px at 92% 14%, rgba(79,70,229,.12), transparent 62%);
  opacity: .9;
  pointer-events:none;
}

.cs-card-top{
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 12px;
  position: relative;
  z-index: 2;
}

.cs-ic{
  width: 64px;                 /* ✅ bigger icon block */
  height: 64px;
  border-radius: 22px;
  display:grid;
  place-items:center;
  font-size: 1.6rem;
  background: radial-gradient(circle at 30% 10%,
    rgba(254,249,195,.92),
    rgba(56,189,248,.55),
    rgba(79,70,229,.45)
  );
  box-shadow: 0 14px 26px rgba(37,99,235,.16);
  transform: translateZ(16px);
}

.cs-pill{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding: 9px 12px;
  border-radius: 999px;
  font-size: .76rem;
  letter-spacing: .18em;
  text-transform: uppercase;
  font-weight: 900;
  color: #0b1120;
  background: rgba(250,204,21,.30);
  border: 1px solid rgba(250,204,21,.45);
  box-shadow: 0 14px 28px rgba(15,23,42,.10);
  transform: translateZ(10px);
}

.cs-meta{
  position: relative;
  z-index: 2;
  margin-top: 14px;
}

.cs-name{
  font-weight: 900;
  letter-spacing: .10em;
  color: var(--ana-text, #0b1120);
  text-transform: uppercase;
  font-size: 1rem;             /* ✅ bigger title */
  font-family: 'Orbitron','Poppins',sans-serif;
}
.cs-desc{
  margin-top: 10px;
  color: var(--ana-muted, rgba(15,23,42,.62));
  font-size: .98rem;           /* ✅ bigger desc */
  line-height: 1.45;
}

.cs-card-bottom{
  margin-top: 16px;
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 12px;
  position: relative;
  z-index: 2;
}

.cs-mini{
  display:inline-flex;
  align-items:center;
  gap: 8px;
  font-size: .84rem;
  color: var(--ana-muted, rgba(15,23,42,.62));
  letter-spacing: .06em;
}

.cs-go{
  width: 52px;                 /* ✅ bigger arrow */
  height: 52px;
  border-radius: 18px;
  display:grid;
  place-items:center;
  color: var(--ana-text, #0b1120);
  background: rgba(56,189,248,.10);
  border: 1px solid rgba(56,189,248,.26);
  transform: translateZ(12px);
  transition: transform .18s ease, background .22s ease;
}

/* Premium sheen */
.cs-sheen{
  position:absolute;
  inset:0;
  background: linear-gradient(120deg,
    transparent 0%,
    rgba(255,255,255,.22) 18%,
    transparent 36%
  );
  transform: translateX(-140%);
  opacity: .55;
  pointer-events:none;
  animation: csSheen 6.2s ease-in-out infinite;
}
@keyframes csSheen{
  0%,55%{ transform: translateX(-140%); }
  75%,100%{ transform: translateX(140%); }
}

.cs-glow{
  position:absolute;
  inset:-2px;
  border-radius: inherit;
  background: linear-gradient(135deg,
    rgba(56,189,248,.55),
    rgba(79,70,229,.50),
    rgba(250,204,21,.30)
  );
  opacity: 0;
  filter: blur(14px);
  transition: opacity .22s ease;
  pointer-events:none;
}

/* Color accents per card */
.cs-sky{    --accentA: rgba(56,189,248,.55); --accentB: rgba(14,165,233,.35); }
.cs-indigo{ --accentA: rgba(79,70,229,.55); --accentB: rgba(129,140,248,.35); }
.cs-gold{   --accentA: rgba(250,204,21,.55); --accentB: rgba(245,158,11,.35); }
.cs-green{  --accentA: rgba(34,197,94,.55); --accentB: rgba(16,185,129,.35); }

.cs-test-card.cs-sky::before{
  background:
    radial-gradient(620px 260px at 12% 0%, var(--accentA), transparent 62%),
    radial-gradient(620px 260px at 92% 14%, var(--accentB), transparent 64%);
  opacity: .22;
}
.cs-test-card.cs-indigo::before{
  background:
    radial-gradient(620px 260px at 12% 0%, var(--accentA), transparent 62%),
    radial-gradient(620px 260px at 92% 14%, var(--accentB), transparent 64%);
  opacity: .22;
}
.cs-test-card.cs-gold::before{
  background:
    radial-gradient(620px 260px at 12% 0%, var(--accentA), transparent 62%),
    radial-gradient(620px 260px at 92% 14%, var(--accentB), transparent 64%);
  opacity: .22;
}
.cs-test-card.cs-green::before{
  background:
    radial-gradient(620px 260px at 12% 0%, var(--accentA), transparent 62%),
    radial-gradient(620px 260px at 92% 14%, var(--accentB), transparent 64%);
  opacity: .22;
}

/* Hover / Active / Focus */
.cs-test-card:hover{
  transform: translateY(-4px) rotateX(7deg) rotateY(-9deg);
  box-shadow: 0 28px 70px rgba(15,23,42,.18);
  border-color: rgba(56,189,248,.50);
}
.cs-test-card:hover .cs-go{
  background: rgba(79,70,229,.12);
  transform: translateZ(12px) translateX(3px);
}
.cs-test-card:hover .cs-glow{ opacity: .58; }
.cs-test-card:active{ transform: translateY(-2px) scale(.99); }

.cs-test-card:focus-visible{
  outline: 3px solid rgba(56,189,248,.55);
  outline-offset: 4px;
}

/* Dark mode tuning */
body.dark-mode .cs-test-card{
  box-shadow: 0 22px 60px rgba(0,0,0,.60);
}
body.dark-mode .cs-pill{
  color: #e5e7eb;
  background: rgba(129,140,248,.16);
  border-color: rgba(129,140,248,.45);
}
body.dark-mode .cs-go{
  color: #e5e7eb;
  background: rgba(129,140,248,.12);
  border-color: rgba(129,140,248,.28);
}

/* Responsive */
@media (max-width: 1200px){
  .cs-test-hero-right{ display:none; }
  .cs-test-grid--xl{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 560px){
  .cs-test-grid--xl{ grid-template-columns: 1fr; }
  .cs-test-actions--xl{ padding: 16px; }
  .cs-test-card{ min-height: 210px; }
}

    </style>
</head>

<body>

<!-- =========================
     SIDEBAR (AspireIELTS)
     ========================= -->
<nav class="sidebar" id="sidebar">
    <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
        <i class="fas fa-angles-left"></i>
    </button>

    <div class="logo" id="sidebarLogo">
        <!-- If you have a different AspireIELTS logo, replace this file.
             You said your images/ folder has the same names as Cognisense. -->
        <img src="images/AI.png" alt="AspireIELTS Logo">
    </div>

    <ul>
        <!-- Keep links as AspireIELTS PHP routes (backend logic unchanged) -->
        <li class="<?= isActive('dashboard.php', $currentPage) ?>">
            <a href="dashboard.php">
                <img src="images/dashboard.png" class="icon" alt="Dashboard">
                <span class="text">Dashboard</span>
            </a>
        </li>

        <!-- You can map these to your real AspireIELTS pages.
             Icons exist in images/ with Cognisense names. -->
        <li class="<?= isActive('tests.php', $currentPage) ?>">
            <a href="tests.php">
                <img src="images/ielts.png" class="icon" alt="Listening">
                <span class="text">Tests</span>
            </a>
        </li>

        <li class="<?= isActive('videos.php', $currentPage) ?>">
            <a href="videos.php">
                <img src="images/learning.png" class="icon" alt="Reading">
                <span class="text">Videos</span>
            </a>
        </li>
      <?php if ($averageScore >= 6): ?>

        <li>
    <a href="cert.php?score=<?php echo urlencode($averageScore); ?>">
      <img src="images/certificate.png" class="icon" alt="Certificate">
      <span class="text">Certificate</span>
    </a>
  </li>
  <?php else: ?>
  <li>
    <a href="cert_restrict.php">
      <img src="images/certificate.png" class="icon" alt="Certificate">
      <span class="text">Certificate</span>
    </a>
  </li>
  <?php endif; ?>

<li>
    <a href="http://localhost/Cognisense/public/dashboard">
        <img src="images/Cognix.png" class="icon" alt="Cognisense">
        <span class="text">Cognisense</span>
    </a>
</li>
    </ul>
</nav>

<!-- =========================
     MAIN CONTENT
     ========================= -->
<div class="main-content" id="main-content">
    <div class="top-bar">
        <div class="top-bar-left">
            <div class="time-display" id="time"></div>

            <div class="weather-display" id="weather" aria-live="polite">
                <div class="wx-icon" aria-hidden="true">
                    <span class="wx-emoji">⛅</span>
                </div>
                <div class="wx-text">
                    <div class="wx-top">
                        <span class="wx-city">Dhaka</span>
                        <span class="wx-chip" id="wxStatus">Loading</span>
                    </div>
                    <div class="wx-temp" id="wxTemp">--°C</div>
                    <div class="wx-meta" id="wxMeta">Fetching weather…</div>
                </div>
            </div>
        </div>

        <div class="top-bar-right">
            <!-- Theme toggle -->
            <label class="theme-toggle" aria-label="Theme toggle">
                <input type="checkbox" id="darkModeToggle">
                <div class="toggle-track">
                    <img src="images/sun.png" alt="Light Mode" class="icon-sun">
                    <img src="images/moon.png" alt="Dark Mode" class="icon-moon">
                    <div class="toggle-thumb"></div>
                </div>
            </label>

            <!-- About (icon only) — notifications + user panel intentionally removed -->
            <button type="button"
                    class="about-btn icon-only"
                    id="aboutOpenBtn"
                    aria-label="About">
                <span class="about-icon-circle">
                    <img src="images/about.png" class="about-icon" alt="">
                </span>
            </button>
        </div>
    </div>

    
        <div class="container">
        <h1 class="dashboard-title">Dashboard</h1>
        <div class="welcome">
            Welcome,
            <strong><?= $fullName ?></strong> 👋
        </div>
    </div>
    <section class="cs-test-actions cs-test-actions--xl" aria-label="Choose a test section">
  <div class="cs-test-hero">
    <div class="cs-test-hero-left">
      <h3 class="cs-test-title">
        <span class="cs-dot"></span>
        Choose a Test Section
      </h3>
      <p class="cs-test-sub">
        Select a section to begin. Each test is timed and designed for real IELTS pacing.
      </p>

      <div class="cs-test-chips" aria-hidden="true">
        <span class="cs-chip"><i class="fa-solid fa-stopwatch"></i> Timed</span>
        <span class="cs-chip"><i class="fa-solid fa-bullseye"></i> Exam-style</span>
        <span class="cs-chip"><i class="fa-solid fa-chart-line"></i> Tracked</span>
      </div>
    </div>

    <div class="cs-test-hero-right" aria-hidden="true">
      <div class="cs-orb"></div>
      <div class="cs-orb cs-orb2"></div>
      <div class="cs-hero-badge">
        <div class="cs-hero-badge-top">Mode</div>
        <div class="cs-hero-badge-main">Practice</div>
        <div class="cs-hero-badge-sub">Band-focused</div>
      </div>
    </div>
  </div>

  <div class="cs-test-grid cs-test-grid--xl">
    <a class="cs-test-card cs-sky" href="tests/listening_confirmation.php" aria-label="Start Listening Test">
      <div class="cs-card-top">
        <div class="cs-ic">🎧</div>
        <span class="cs-pill">Start</span>
      </div>

      <div class="cs-meta">
        <div class="cs-name">Listening</div>
        <div class="cs-desc">Audio accuracy • timing • focus</div>
      </div>

      <div class="cs-card-bottom">
        <span class="cs-mini"><i class="fa-solid fa-wave-square"></i> Part-style prompts</span>
        <span class="cs-go"><i class="fa-solid fa-arrow-right"></i></span>
      </div>

      <div class="cs-sheen" aria-hidden="true"></div>
      <div class="cs-glow" aria-hidden="true"></div>
    </a>

    <a class="cs-test-card cs-indigo" href="tests/reading_confirmation.php" aria-label="Start Reading Test">
      <div class="cs-card-top">
        <div class="cs-ic">📘</div>
        <span class="cs-pill">Start</span>
      </div>

      <div class="cs-meta">
        <div class="cs-name">Reading</div>
        <div class="cs-desc">Skim • scan • manage pace</div>
      </div>

      <div class="cs-card-bottom">
        <span class="cs-mini"><i class="fa-solid fa-book"></i> Passage sets</span>
        <span class="cs-go"><i class="fa-solid fa-arrow-right"></i></span>
      </div>

      <div class="cs-sheen" aria-hidden="true"></div>
      <div class="cs-glow" aria-hidden="true"></div>
    </a>

    <a class="cs-test-card cs-gold" href="tests/writing_confirmation.php" aria-label="Start Writing Test">
      <div class="cs-card-top">
        <div class="cs-ic">✍️</div>
        <span class="cs-pill">Start</span>
      </div>

      <div class="cs-meta">
        <div class="cs-name">Writing</div>
        <div class="cs-desc">Coherence • structure • vocab</div>
      </div>

      <div class="cs-card-bottom">
        <span class="cs-mini"><i class="fa-solid fa-pen-nib"></i> Task-style</span>
        <span class="cs-go"><i class="fa-solid fa-arrow-right"></i></span>
      </div>

      <div class="cs-sheen" aria-hidden="true"></div>
      <div class="cs-glow" aria-hidden="true"></div>
    </a>

    <a class="cs-test-card cs-green" href="tests/speaking_confirmation.php" aria-label="Start Speaking Test">
      <div class="cs-card-top">
        <div class="cs-ic">🗣️</div>
        <span class="cs-pill">Start</span>
      </div>

      <div class="cs-meta">
        <div class="cs-name">Speaking</div>
        <div class="cs-desc">Fluency • confidence • clarity</div>
      </div>

      <div class="cs-card-bottom">
        <span class="cs-mini"><i class="fa-solid fa-microphone-lines"></i> Guided prompts</span>
        <span class="cs-go"><i class="fa-solid fa-arrow-right"></i></span>
      </div>

      <div class="cs-sheen" aria-hidden="true"></div>
      <div class="cs-glow" aria-hidden="true"></div>
    </a>
  </div>
</section>



    <!-- FOOTER (same style) -->
    <footer class="footer">
        <div class="footer-socials">
            <div class="footer-social-link">
                <img src="images/facebook.png" alt="Facebook">
                <span class="footer-social-label">Facebook</span>
            </div>
            <div class="footer-social-link">
                <img src="images/instagram.png" alt="Instagram">
                <span class="footer-social-label">Instagram</span>
            </div>
            <div class="footer-social-link">
                <img src="images/twitter.png" alt="Twitter">
                <span class="footer-social-label">Twitter</span>
            </div>
            <div class="footer-social-link">
                <img src="images/github.png" alt="GitHub">
                <span class="footer-social-label">GitHub</span>
            </div>
        </div>

        <div class="footer-copy">
            © <?= date('Y') ?> AspireIELTS. All rights reserved.
        </div>
    </footer>
</div>

<!-- =========================
     ABOUT OVERLAY (AspireIELTS) — same UI
     ========================= -->
<div class="about-overlay" id="aboutOverlay" aria-hidden="true">
    <div class="about-modal" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" tabindex="-1">

        <button class="about-close" id="aboutCloseBtn" type="button" aria-label="Close About">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="about-hero">
            <div class="about-hero-left">
                <div class="about-hero-badge">
                    <span class="about-dot"></span>
                    <span class="about-badge-text">ASPIREIELTS • PROJECT OVERVIEW</span>
                </div>

                <h2 class="about-title" id="aboutTitle">AspireIELTS</h2>

                <p class="about-subtitle">
                    A focused IELTS practice dashboard: track your band scores, monitor trends, and improve section-by-section
                    with a clean exam-style workflow.
                </p>

                <div class="about-tags">
                    <span class="about-tag"><i class="fa-solid fa-headphones"></i> Listening</span>
                    <span class="about-tag"><i class="fa-solid fa-book-open"></i> Reading</span>
                    <span class="about-tag"><i class="fa-solid fa-pen-nib"></i> Writing</span>
                    <span class="about-tag"><i class="fa-solid fa-microphone"></i> Speaking</span>
                    <span class="about-tag"><i class="fa-solid fa-chart-line"></i> Analytics</span>
                    <span class="about-tag"><i class="fa-solid fa-certificate"></i> Results</span>
                </div>
            </div>
        </div>

        <div class="about-grid">
            <div class="about-card">
                <div class="about-card-head">
                    <span class="about-ic"><i class="fa-solid fa-headphones"></i></span>
                    <h3>Listening</h3>
                </div>
                <p>Practice timed listening tasks and build accuracy with structured reviews.</p>
            </div>

            <div class="about-card">
                <div class="about-card-head">
                    <span class="about-ic"><i class="fa-solid fa-book-open"></i></span>
                    <h3>Reading</h3>
                </div>
                <p>Improve comprehension and skimming/scanning speed with exam-like passages.</p>
            </div>

            <div class="about-card">
                <div class="about-card-head">
                    <span class="about-ic"><i class="fa-solid fa-pen-nib"></i></span>
                    <h3>Writing</h3>
                </div>
                <p>Track progress across tasks and focus on structure, coherence, and vocabulary.</p>
            </div>

            <div class="about-card">
                <div class="about-card-head">
                    <span class="about-ic"><i class="fa-solid fa-microphone"></i></span>
                    <h3>Speaking</h3>
                </div>
                <p>Build confidence and fluency through guided prompts and consistent practice.</p>
            </div>

            <div class="about-card">
                <div class="about-card-head">
                    <span class="about-ic"><i class="fa-solid fa-chart-line"></i></span>
                    <h3>Analytics</h3>
                </div>
                <p>See trends, section averages, and distributions to target your weak spots.</p>
            </div>

            <div class="about-card">
                <div class="about-card-head">
                    <span class="about-ic"><i class="fa-solid fa-certificate"></i></span>
                    <h3>Results</h3>
                </div>
                <p>Keep all test outcomes organized and track improvement over time.</p>
            </div>
        </div>

        <div class="about-footer">
            <span class="about-mini">
                Built for speed, clarity, and consistent IELTS improvement — practice smart, track progress, boost band.
            </span>
        </div>

    </div>
</div>

<!-- =========================
     ✅ AspireIELTS Chatbot (kept in-place)
     You asked to leave it as it currently is.
     (If your existing dashboard.php already has different chatbot HTML/JS,
      paste yours here instead — the rest of the page is now Cognisense-identical.)
     ========================= -->
<!-- Floating Chat Icon -->
<div id="chatbot-toggle" onclick="openChatbot()">
  <img src="images/cb.png" alt="Chatbot Icon" class="chatbot-icon" />
</div>


<!-- Chatbot Interface -->
<div id="chatbot-container" class="hidden">
  <div class="chatbot-header">
    <h1 class="title">IELTS Chat Bot</h1>
    <span onclick="closeChatbot()" class="close-btn">&times;</span>
  </div>
  <div class="messages" id="messages"></div>
  <div class="input-container">
    <input type="text" id="userMessage" placeholder="Type your message..." onkeyup="autoReply(event)">
  </div>
</div>

<!-- Minimal CSS for floating layout -->
<style>
#chatbot-toggle {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #007bff;
  color: #fff;
  font-size: 24px;
  padding: 14px 16px;
  border-radius: 50%;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  cursor: pointer;
  z-index: 9999;

  display: inline-flex;         /* added */
  align-items: center;          /* added */
  gap: 6px;                    /* spacing between emoji and image */
}
.dark-mode #chatbot-toggle {
  background-color: #0056b3; /* Darker blue for dark mode */
  color: #e0e0e0;            /* Light gray text for contrast */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.7); /* Stronger shadow for dark bg */
}

#chatbot-toggle .chatbot-icon {
  height: 50px;                 /* image size, matches emoji height */
  width: auto;
  object-fit: contain;
  border-radius: 50%;           /* keep it round to match button */
}


  #chatbot-container {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 320px;
    height: 420px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    display: none; /* Start hidden */
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
  }

  .chatbot-header {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .messages {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    
  }

  .input-container {
    padding: 10px;
  }

  .input-container input {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
  }

  .message.user {
    text-align: right;
    margin-bottom: 10px;
    color: #333;
  }

  .message.bot {
    text-align: left;
    margin-bottom: 10px;
    color: #007bff;
  }

  .close-btn {
    font-size: 20px;
    cursor: pointer;
  }

  .hidden {
    display: none !important;
  }

</style>

<!-- JS -->
<script>
  const chatbot = document.getElementById("chatbot-container");

  function openChatbot() {
    chatbot.classList.remove("hidden");
    chatbot.style.display = "flex";
  }

  function closeChatbot() {
    chatbot.classList.add("hidden");
    chatbot.style.display = "none";
  }

  function getChatbotResponse(message) {
    const responses = {
"hello": "Hi there! How can I assist you with your IELTS preparation today?",
    "hi": "Hello! Ready to improve your IELTS score?",
    "hey": "Hey! Need help with IELTS reading, writing, speaking or listening?",

    // Thanks and Farewells
    "thank you": "You're welcome! I'm always here to help you succeed.",
    "thanks": "No problem! Let me know if you have any other IELTS questions.",
    "bye": "Goodbye! Best of luck with your IELTS journey!",
    "see you": "See you! Don’t forget to keep practicing.",

    // Writing
    "writing tips": "Focus on task achievement, coherence, vocabulary, and grammar. Practice both Task 1 and Task 2 regularly.",
    "how to improve writing": "Practice structured essay writing, use linking words, and check grammar. Review band 9 sample answers to learn formatting.",
    "writing task 1": "For Task 1, describe the visual (chart, graph, etc.) clearly. Summarize key trends and avoid personal opinions.",
    "writing task 2": "For Task 2, present your opinion, support it with examples, and structure your essay with clear introduction, body, and conclusion.",
    "common writing mistakes": "Avoid contractions, informal language, and vague arguments. Always revise your writing for grammar and coherence.",

    // Speaking
    "speaking tips": "Practice speaking fluently without too many pauses. Use a range of vocabulary and correct grammar.",
    "how to improve speaking": "Speak English daily, record yourself, and practice with IELTS sample questions. Focus on pronunciation and fluency.",
    "part 1 speaking": "Part 1 is about familiar topics like hobbies or family. Keep answers short but clear.",
    "part 2 speaking": "Part 2 is a long turn. Prepare using cue cards. Speak for 1–2 minutes, and stay on topic.",
    "part 3 speaking": "Part 3 involves deeper discussion. Give detailed answers, reasons, and examples.",

    // Listening
    "listening tips": "Practice with official IELTS recordings. Focus on keywords and note-taking.",
    "how to improve listening": "Listen to English podcasts, news, and IELTS materials. Practice identifying synonyms and paraphrasing.",
    "common listening problems": "Many students miss answers due to distractions or unfamiliar accents. Stay focused and read questions ahead.",

    // Reading
    "reading tips": "Skim for general meaning, scan for specific info, and don’t spend too much time on one question.",
    "how to improve reading": "Read newspapers, articles, and practice IELTS reading tests daily. Learn to identify main ideas quickly.",
    "true false not given tips": "Carefully compare the passage and statement. Focus on what is said, not what you know.",
    "time management in reading": "Spend about 20 minutes per passage. Don’t get stuck. Mark difficult ones and return later if needed.",

    // IELTS General
    "what is ielts": "IELTS stands for International English Language Testing System. It tests your English proficiency in Listening, Reading, Writing, and Speaking.",
    "ielts full form": "IELTS stands for International English Language Testing System.",
    "how to prepare for ielts": "Set a study schedule, take mock tests, and improve vocabulary. Use reliable practice materials and track your progress.",
    "ielts band score": "IELTS is scored on a 0–9 band scale for each skill. An average is taken for your overall band score.",
    "how many sections in ielts": "There are 4 sections: Listening, Reading, Writing, and Speaking.",
    "difference between academic and general ielts": "Academic IELTS is for higher education, while General Training is for work or immigration purposes.",
    "minimum score for uk": "It depends on the institution, but generally 6.5 or above is required for most UK universities.",
    "minimum score for canada": "For Canada, most institutions require at least 6.0 in each band, but requirements vary.",
    "minimum score for australia": "Australian institutions usually ask for 6.5 overall, with no band less than 6.0.",

    // Vocabulary and Grammar
    "how to improve vocabulary": "Read widely, keep a vocabulary journal, and use new words in context. Use apps like Quizlet for revision.",
    "how to improve grammar": "Practice grammar exercises daily. Focus on common topics like tenses, articles, and sentence structure.",
    "band 9 vocabulary": "Band 9 vocabulary includes advanced and topic-specific terms. Read sample band 9 essays to learn them.",

    // Mock Tests and Practice
    "where to take mock tests": "You can take free mock tests online on IELTS.org, British Council, and EdAcademix (if available).",
    "best books for ielts": "Try Cambridge IELTS books, The Official Cambridge Guide to IELTS, and Barron’s IELTS Superpack.",
    "how many practice tests to take": "Aim to complete 10–15 full-length mock tests before your exam date.",

    // Motivation
    "i am nervous": "It’s normal to feel nervous. Stick to your study plan, practice daily, and you’ll gain confidence.",
    "how long to prepare for ielts": "Most students take 1–3 months to prepare effectively, depending on their current level.",
    "can i get band 8": "Yes! With daily focused practice and the right strategy, you can achieve band 8 or higher.",
    "i failed ielts": "Don’t give up. Review your weaknesses, take feedback seriously, and practice smarter this time.",

    "listening": "IELTS Listening has 4 sections. Make sure to practice with headphones in a quiet space.",
"reading": "IELTS Reading tests your ability to locate, understand, and analyze information in a text. Focus on time management!",
"writing": "Writing is divided into Task 1 and Task 2. Practice regularly and analyze high-band samples.",
"speaking": "Speaking has 3 parts. Practice fluency, pronunciation, and structured responses. You can book a mock speaking test with us!",

"mock test": "You can attempt Listening, Reading, and Writing mock tests on our platform. Speaking tests can be scheduled live.",
"mock tests": "Mock tests simulate the real exam. Take one regularly to track your progress.",
"test result": "Your latest results and performance graph can be viewed on your dashboard.",
"dashboard": "Visit your dashboard to track scores, review feedback, and continue your preparation.",

"speaking questions": "You can practice with common IELTS Part 1, 2, and 3 questions. Try answering with a timer!",
"reading passage tips": "Skim the passage first, then scan for answers. Time is your biggest challenge here.",
"listening audio source": "We use authentic-style recordings. Make sure your environment is quiet when practicing.",
"writing feedback": "After you submit a writing task, you'll receive automated feedback and a band estimate.",
"score analysis": "Your test score analysis includes band scores, feedback, and suggestions for improvement.",

"tips": "Sure! Just tell me which section you're interested in: Listening, Reading, Writing, or Speaking.",
"start test": "Go to your dashboard and select the section you want to begin with.",
"start speaking test": "Please schedule your speaking test through the dashboard. An examiner will call you via Jitsi.",
"video lectures": "You can access video lectures for each test section from the preparation menu on your dashboard.",
"preparation tips": "Consistency is key. Use mock tests, video lectures, and daily practice to improve steadily.",
"reset progress": "You can reset your test attempts from your profile settings. Be sure before you do this.",

"motivate me": "You're doing great! Every bit of practice brings you closer to your goal. Band 8 is within reach!",
"band 9 goal": "With focused practice, top resources, and guidance — yes, band 9 is possible!",
"feeling stuck": "Hit a wall? Take a short break, revisit your weak areas, and come back stronger.",
"how are you": "I’m great and ready to help you ace IELTS. How can I assist today?"
};

    const lowerMessage = message.toLowerCase();
    for (const key in responses) {
      if (lowerMessage.includes(key)) {
        return responses[key];
      }
    }
    return "I am sorry, I did not understand that. Can you ask something else about IELTS?";
  }

  function sendMessage(userMessage) {
    if (!userMessage.trim()) return;

    const messagesDiv = document.getElementById("messages");
    const userDiv = document.createElement("div");
    userDiv.className = "message user";
    userDiv.textContent = userMessage;
    messagesDiv.appendChild(userDiv);

    const botResponse = getChatbotResponse(userMessage);
    const botDiv = document.createElement("div");
    botDiv.className = "message bot";
    botDiv.textContent = botResponse;
    messagesDiv.appendChild(botDiv);

    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  function autoReply(event) {
    if (event.key === "Enter") {
      const userMessage = document.getElementById("userMessage").value;
      document.getElementById("userMessage").value = "";
      sendMessage(userMessage);
    }
  }
</script>

<!-- =========================
     JS (time, theme, sidebar, weather, about)
     ========================= -->
<script>
    // Typing animation for welcome name (same behavior)
    document.addEventListener("DOMContentLoaded", () => {
        const strongTag = document.querySelector(".welcome strong");
        if (!strongTag) return;

        const fullName = strongTag.textContent.trim();
        let i = 0;

        function type() {
            if (i <= fullName.length) {
                strongTag.textContent = fullName.substring(0, i);
                i++;
                setTimeout(type, 100);
            } else {
                setTimeout(erase, 1500);
            }
        }

        function erase() {
            if (i >= 0) {
                strongTag.textContent = fullName.substring(0, i);
                i--;
                setTimeout(erase, 50);
            } else {
                setTimeout(type, 500);
	                   }
            }

            type();
        });

/* =========================
   Dhaka Time (premium clock)
   ========================= */
function updateBDTime() {
  const timeElement = document.getElementById("time");
  if (!timeElement) return;

  // Create nodes once
  let mainEl = timeElement.querySelector(".time-main");
  let subEl  = timeElement.querySelector(".time-sub");

  if (!mainEl || !subEl) {
    timeElement.innerHTML = `
      <div class="time-text">
        <span class="time-main" aria-label="Bangladesh time"></span>
        <span class="time-sub" aria-label="Bangladesh date"></span>
      </div>
    `;
    mainEl = timeElement.querySelector(".time-main");
    subEl  = timeElement.querySelector(".time-sub");
  }

  const now = new Date();

  const optionsDate = {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    timeZone: 'Asia/Dhaka'
  };

  const optionsTime = {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
    timeZone: 'Asia/Dhaka'
  };

  const datePart = now.toLocaleDateString('en-GB', optionsDate);

  // HH:MM:SS (in Dhaka)
  const rawTime = now.toLocaleTimeString('en-GB', optionsTime);
  const [hh, mm, ss] = rawTime.split(':');

  // separators that animate
  mainEl.innerHTML = `${hh}<span class="t-sep"> : </span>${mm}<span class="t-sep"> : </span>${ss}`;
  subEl.textContent  = `${datePart} · BD`;

  // seconds progress ring (0..360deg)
  const s = Number(ss) || 0;
  const ms = now.getMilliseconds();
  const pdeg = (s + ms / 1000) * 6; // 60s => 360deg
  timeElement.style.setProperty('--pdeg', `${pdeg.toFixed(1)}deg`);
}

/* Optional: mouse sparkle tracking (super premium, zero size change) */
(function attachClockSparkle(){
  const el = document.getElementById("time");
  if (!el) return;

  el.addEventListener("mousemove", (e) => {
    const r = el.getBoundingClientRect();
    const px = ((e.clientX - r.left) / r.width) * 100;
    const py = ((e.clientY - r.top) / r.height) * 100;
    el.style.setProperty("--mx", px.toFixed(1) + "%");
    el.style.setProperty("--my", py.toFixed(1) + "%");
  });

  el.addEventListener("mouseleave", () => {
    el.style.setProperty("--mx", "35%");
    el.style.setProperty("--my", "30%");
  });
})();

updateBDTime();
setInterval(updateBDTime, 1000);

/* =========================
   Sidebar toggle (+ persist)
   ========================= */
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
const toggleIcon = toggleBtn ? toggleBtn.querySelector('i') : null;

function updateSidebarToggleIcon() {
  if (!toggleIcon || !sidebar) return;

  if (sidebar.classList.contains('collapsed')) {
    // collapsed -> show » (angles-right) indicating expand
    toggleIcon.classList.remove('fa-angles-left');
    toggleIcon.classList.add('fa-angles-right');
  } else {
    // expanded -> show « (angles-left) indicating collapse
    toggleIcon.classList.remove('fa-angles-right');
    toggleIcon.classList.add('fa-angles-left');
  }
}

// restore
try {
  const saved = localStorage.getItem('sidebarCollapsed');
  if (saved === '1') sidebar?.classList.add('collapsed');
} catch(e){}

toggleBtn?.addEventListener('click', () => {
  sidebar?.classList.toggle('collapsed');
  updateSidebarToggleIcon();

  // persist
  try {
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
  } catch(e){}
});

// Initialize correct icon on load
updateSidebarToggleIcon();

/* =========================
   Dark mode (smooth + synced)
   ========================= */
function pulseTheme() {
  document.body.classList.add('theme-swap');
  clearTimeout(window.__themeSwapT);
  window.__themeSwapT = setTimeout(() => {
    document.body.classList.remove('theme-swap');
  }, 260);
}

function applyTheme(pulse = false) {
  const stored = localStorage.getItem('darkMode');
  const enabled = stored === 'enabled';

  document.body.classList.toggle('dark-mode', enabled);

  const toggle = document.getElementById('darkModeToggle');
  if (toggle) toggle.checked = enabled;

  if (pulse) pulseTheme();
}

function setTheme(enabled) {
  localStorage.setItem('darkMode', enabled ? 'enabled' : 'disabled');
  applyTheme(true);
}

document.addEventListener('DOMContentLoaded', () => {
  applyTheme(false);

  const darkToggle = document.getElementById('darkModeToggle');
  if (darkToggle) {
    darkToggle.addEventListener('change', (e) => setTheme(e.target.checked));
  }
});

// sync across tabs/windows
window.addEventListener('storage', (e) => {
  if (e.key === 'darkMode') applyTheme(true);
});
</script>

<script>
/* =========================
   Dhaka Weather (no API key) — Open-Meteo
   ========================= */
const wxStatus = document.getElementById('wxStatus');
const wxTemp   = document.getElementById('wxTemp');
const wxMeta   = document.getElementById('wxMeta');
const wxEmoji  = document.querySelector('#weather .wx-emoji');

function wxFromCode(code, isDay){
  const map = {
    0:  ["Clear",        isDay ? "☀️" : "🌙"],
    1:  ["Mostly clear", isDay ? "🌤️" : "🌙"],
    2:  ["Partly cloudy","⛅"],
    3:  ["Cloudy",       "☁️"],
    45: ["Fog",          "🌫️"],
    48: ["Fog",          "🌫️"],
    51: ["Drizzle",      "🌦️"],
    53: ["Drizzle",      "🌦️"],
    55: ["Drizzle",      "🌦️"],
    61: ["Rain",         "🌧️"],
    63: ["Rain",         "🌧️"],
    65: ["Heavy rain",   "⛈️"],
    71: ["Snow",         "🌨️"],
    73: ["Snow",         "🌨️"],
    75: ["Snow",         "🌨️"],
    80: ["Showers",      "🌦️"],
    81: ["Showers",      "🌦️"],
    82: ["Heavy showers","⛈️"],
    95: ["Thunder",      "⛈️"],
    96: ["Thunder",      "⛈️"],
    99: ["Thunder",      "⛈️"],
  };
  return map[code] || ["Weather", "🌡️"];
}

async function updateDhakaWeather(){
  try {
    if (wxStatus) wxStatus.textContent = "LIVE";

    const url =
      "https://api.open-meteo.com/v1/forecast" +
      "?latitude=23.8103&longitude=90.4125" +
      "&current=temperature_2m,apparent_temperature,relative_humidity_2m,is_day,weather_code,wind_speed_10m" +
      "&timezone=Asia%2FDhaka&temperature_unit=celsius&wind_speed_unit=kmh";

    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error("Weather fetch failed");

    const data = await res.json();
    const c = data.current;

    const temp  = Math.round(c.temperature_2m);
    const feels = Math.round(c.apparent_temperature);
    const hum   = Math.round(c.relative_humidity_2m);
    const wind  = Math.round(c.wind_speed_10m);
    const isDay = !!c.is_day;

    const [label, emoji] = wxFromCode(c.weather_code, isDay);

    if (wxEmoji)  wxEmoji.textContent = emoji;
    if (wxTemp)   wxTemp.textContent  = `${temp}°C`;
    if (wxMeta)   wxMeta.textContent  = `${label} · Feels ${feels}° · Hum ${hum}% · Wind ${wind} km/h`;
    if (wxStatus) wxStatus.textContent = "LIVE";
  } catch (e) {
    if (wxStatus) wxStatus.textContent = "OFFLINE";
    if (wxMeta)   wxMeta.textContent   = "Weather unavailable";
  }
}

updateDhakaWeather();
// refresh every 12 minutes
setInterval(updateDhakaWeather, 12 * 60 * 1000);
</script>

<!-- Chart.js (required for the analytics charts) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
/* =========================
   Analytics Charts (AspireIELTS payload)
   ========================= */
(function(){
  // Payload from PHP
  const A = <?= json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  if (!window.Chart) return;

  const fontFamily = "'Poppins','Segoe UI',sans-serif";
  const cssVar = (name, fallback) => {
    const v = getComputedStyle(document.body).getPropertyValue(name).trim();
    return v || fallback;
  };

  const theme = () => ({
    grid: cssVar('--ana-chart-grid', 'rgba(15,23,42,.12)'),
    tick: cssVar('--ana-chart-tick', 'rgba(15,23,42,.55)'),
    cyan: '#38bdf8',
    indigo: '#4f46e5',
    gold: '#facc15',
    red: '#ef4444',
    green: '#22c55e'
  });

  let charts = {};

  function destroyAll(){
    Object.values(charts).forEach(c => c && c.destroy());
    charts = {};
  }

  function basePlugins(t){
    return {
      legend: {
        labels: { color: t.tick, font: { family: fontFamily, weight: "600" } }
      },
      tooltip: {
        backgroundColor: "rgba(2,6,23,.92)",
        titleColor: "#fff",
        bodyColor: "#e5e7eb",
        borderColor: "rgba(148,163,184,.25)",
        borderWidth: 1
      }
    };
  }

  function baseScales(t){
    return {
      x: { grid: { color: t.grid }, ticks: { color: t.tick, font: { family: fontFamily } } },
      y: { beginAtZero: true, grid: { color: t.grid }, ticks: { color: t.tick, font: { family: fontFamily } } }
    };
  }

  function buildCharts(){
    destroyAll();
    const t = theme();

    // 1) Trend (line)
    const trendEl = document.getElementById("trendChart");
    if (trendEl) {
      charts.trend = new Chart(trendEl, {
        type: "line",
        data: {
          labels: (A.trend?.labels || []).map(x => String(x)),
          datasets: [{
            label: "Band score",
            data: (A.trend?.scores || []).map(n => Number(n)),
            borderColor: t.cyan,
            backgroundColor: "rgba(56,189,248,.18)",
            pointRadius: 3,
            pointHoverRadius: 4,
            tension: 0.35,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: basePlugins(t),
          scales: {
            x: baseScales(t).x,
            y: {
              ...baseScales(t).y,
              min: 0,
              max: 9,
              title: { display: true, text: "Band (0–9)", color: t.tick, font: { family: fontFamily, weight: "700" } }
            }
          }
        }
      });
    }

    // 2) Section Averages (bar)
    const sectionEl = document.getElementById("sectionChart");
    if (sectionEl) {
      charts.section = new Chart(sectionEl, {
        type: "bar",
        data: {
          labels: (A.sections?.labels || []).map(String),
          datasets: [{
            label: "Avg band",
            data: (A.sections?.avg || []).map(Number),
            backgroundColor: "rgba(79,70,229,.22)",
            borderColor: "rgba(79,70,229,.55)",
            borderWidth: 1,
            borderRadius: 12
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: basePlugins(t),
          scales: {
            x: baseScales(t).x,
            y: { ...baseScales(t).y, min: 0, max: 9 }
          }
        }
      });
    }

    // 3) Distribution (bar)
    const distEl = document.getElementById("distChart");
    if (distEl) {
      const d = A.dist || {};
      charts.dist = new Chart(distEl, {
        type: "bar",
        data: {
          labels: ["<4.0", "4.0–4.9", "5.0–5.9", "6.0–6.9", "7.0+"],
          datasets: [{
            label: "Attempts",
            data: [d.b1||0, d.b2||0, d.b3||0, d.b4||0, d.b5||0],
            backgroundColor: [
              "rgba(239,68,68,.22)",
              "rgba(250,204,21,.22)",
              "rgba(56,189,248,.22)",
              "rgba(79,70,229,.22)",
              "rgba(34,197,94,.22)"
            ],
            borderColor: [
              "rgba(239,68,68,.55)",
              "rgba(250,204,21,.55)",
              "rgba(56,189,248,.55)",
              "rgba(79,70,229,.55)",
              "rgba(34,197,94,.55)"
            ],
            borderWidth: 1,
            borderRadius: 12
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { ...basePlugins(t), legend: { display: false } },
          scales: baseScales(t)
        }
      });
    }

    // 4) Tests by Type (doughnut)
    const typeEl = document.getElementById("typeChart");
    if (typeEl) {
      const labels = (A.types?.labels || []).map(String);
      const counts = (A.types?.counts || []).map(Number);

      charts.type = new Chart(typeEl, {
        type: "doughnut",
        data: {
          labels,
          datasets: [{
            data: counts,
            backgroundColor: [
              "rgba(56,189,248,.55)",
              "rgba(79,70,229,.55)",
              "rgba(250,204,21,.55)",
              "rgba(34,197,94,.55)",
              "rgba(239,68,68,.55)",
              "rgba(148,163,184,.55)"
            ],
            borderColor: "rgba(255,255,255,.10)",
            borderWidth: 1,
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "68%",
          plugins: {
            legend: { position: "bottom", labels: { color: t.tick, font: { family: fontFamily, weight: "600" } } },
            tooltip: basePlugins(t).tooltip
          }
        }
      });
    }

    queueResize();
  }

  function queueResize(){
    const r = () => Object.values(charts).forEach(c => c && c.resize());
    r();
    setTimeout(r, 60);
    setTimeout(r, 360); // after sidebar transition
  }

  window.__resizeCharts = queueResize;

  buildCharts();

  // When sidebar opens/closes, force resize so charts stay visible
  document.getElementById("sidebarToggle")?.addEventListener("click", () => setTimeout(queueResize, 340));
  window.addEventListener("resize", queueResize);

  // When theme changes, rebuild charts to match CSS vars
  const prevApplyTheme = window.applyTheme;
  window.applyTheme = function(pulse){
    if (typeof prevApplyTheme === "function") prevApplyTheme(pulse);
    buildCharts();
  };
})();
</script>

<script>
/* =========================
   About Overlay Controls
   ========================= */
const aboutOpenBtn  = document.getElementById('aboutOpenBtn');
const aboutOverlay  = document.getElementById('aboutOverlay');
const aboutCloseBtn = document.getElementById('aboutCloseBtn');
const aboutModal    = aboutOverlay?.querySelector('.about-modal');

function openAbout(){
  if (!aboutOverlay) return;
  aboutOverlay.classList.add('open');
  aboutOverlay.setAttribute('aria-hidden', 'false');
  document.body.classList.add('modal-open');

  setTimeout(() => {
    aboutCloseBtn?.focus();
    aboutModal?.focus?.();
  }, 30);
}

function closeAbout(){
  if (!aboutOverlay) return;
  aboutOverlay.classList.remove('open');
  aboutOverlay.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('modal-open');
}

aboutOpenBtn?.addEventListener('click', (e) => {
  e.preventDefault();
  openAbout();
});

aboutCloseBtn?.addEventListener('click', (e) => {
  e.preventDefault();
  closeAbout();
});

// click outside modal to close
aboutOverlay?.addEventListener('click', (e) => {
  if (e.target === aboutOverlay) closeAbout();
});

// ESC closes
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && aboutOverlay?.classList.contains('open')) closeAbout();
});
</script>

<script>
/* =========================
   ✅ AspireIELTS Chatbot JS (for your #chatbot-icon version)
   ========================= */
(function(){
  const icon = document.getElementById('chatbot-icon');
  const box  = document.getElementById('chatbot-container');
  const close= document.getElementById('chatbot-close');
  const input= document.getElementById('chatbot-input');
  const msgs = document.getElementById('chatbot-messages');

  if(!icon || !box || !close || !input || !msgs) return;

  const open = () => {
    box.classList.remove('hidden');
    box.setAttribute('aria-hidden', 'false');
    setTimeout(() => input.focus(), 40);
  };

  const hide = () => {
    box.classList.add('hidden');
    box.setAttribute('aria-hidden', 'true');
  };

  const toggle = () => box.classList.contains('hidden') ? open() : hide();

  icon.addEventListener('click', toggle);
  close.addEventListener('click', hide);

  function addMessage(text, who){
    const div = document.createElement('div');
    div.className = `message ${who}`;
    div.textContent = text;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function botReply(userText){
    const t = userText.toLowerCase();

    if (t.includes('listening')) return "For Listening: practice daily + review wrong answers. Want a mini plan (30 mins/day)?";
    if (t.includes('reading'))   return "For Reading: time-box passages and train skimming/scanning. Want tips for T/F/NG?";
    if (t.includes('writing'))   return "For Writing: focus on Task response + coherence. Tell me Task 1 or Task 2?";
    if (t.includes('speaking'))  return "For Speaking: record answers + improve fluency and vocabulary. Want common Part 1 questions?";
    if (t.includes('band'))      return "Band improvement comes from accuracy + consistency. Which section is weakest right now?";
    if (t.includes('hello') || t.includes('hi')) return "Hi! 👋 Ask me about Listening/Reading/Writing/Speaking, or improving your band.";
    return "I can help with IELTS strategies. Type: listening / reading / writing / speaking / band.";
  }

  input.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    const text = input.value.trim();
    if (!text) return;

    addMessage(text, 'user');
    input.value = '';

    setTimeout(() => addMessage(botReply(text), 'bot'), 450);
  });

  // close chatbot on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !box.classList.contains('hidden')) hide();
  });
})();
</script>

</body>
</html>

