<?php
declare(strict_types=1);
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AspireIELTS — Practice Smarter. Score Higher.</title>
  <meta name="description" content="AspireIELTS is a modern IELTS mock testing platform for Listening, Reading, Writing, and Speaking with analytics, AI feedback, and study tools." />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Prevent theme flash -->
<script>
  (function () {
    // Cognisense-style storage:
    // localStorage.darkMode = "enabled" | "disabled"
    const dm = localStorage.getItem('darkMode');

    // fallback to your old key: localStorage.theme = "dark" | "light"
    const old = localStorage.getItem('theme');

    let isDark;
    if (dm === 'enabled') isDark = true;
    else if (dm === 'disabled') isDark = false;
    else if (old) isDark = (old === 'dark');
    else isDark = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
  })();
</script>


  <style>
    :root{
      /* DARK (default) */
      --bg: #070a14;
      --bg2:#050712;

      --text:#eef3ff;
      --muted:#c3cbe6;

      --card: rgba(255,255,255,.075);
      --card2: rgba(255,255,255,.10);
      --stroke: rgba(255,255,255,.16);

      --shadow: 0 18px 50px rgba(0,0,0,.55);
      --shadow2: 0 12px 30px rgba(0,0,0,.40);

      --p1:#6C63FF;
      --p2:#22D3EE;
      --p3:#A78BFA;

      --radius: 22px;
      --radius2: 28px;

      /* ✅ wider */
      --container: 1560px;

      --glass-blur: blur(14px);
      --t: 220ms cubic-bezier(.2,.8,.2,1);
    }

    /* LIGHT theme */
    [data-theme="light"]{
      --bg:#f4f7ff;
      --bg2:#edf2ff;

      --text:#0b1120;
      --muted:#3b4a63;

      --card: rgba(255,255,255,.82);
      --card2: rgba(255,255,255,.92);
      --stroke: rgba(15,23,42,.12);

      --shadow: 0 18px 45px rgba(2,6,23,.14);
      --shadow2: 0 12px 28px rgba(2,6,23,.10);
    }

    *{ box-sizing:border-box; margin:0; padding:0; font-family:"Poppins",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; }
    html,body{ height:100%; }

    body{
      color:var(--text);
      background:
        radial-gradient(1200px 700px at 10% 10%, rgba(108,99,255,.22), transparent 55%),
        radial-gradient(900px 650px at 90% 15%, rgba(34,211,238,.18), transparent 55%),
        radial-gradient(900px 700px at 55% 100%, rgba(167,139,250,.20), transparent 60%),
        linear-gradient(180deg, var(--bg), var(--bg2));
      overflow-x:hidden;
    }

    /* Subtle noise overlay (kept BEHIND modals) */
    body::before{
      content:"";
      position:fixed; inset:0;
      pointer-events:none;
      z-index: 0;
      background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23n)' opacity='.18'/%3E%3C/svg%3E");
      mix-blend-mode:overlay;
      opacity:.12;
    }

    a{ color:inherit; text-decoration:none; }

    /* ✅ wider container, less “middle cramped” */
    .container{
      width:min(var(--container), calc(100% - 56px));
      margin:0 auto;
    }

    /* wrap for blur-on-modal */
    #pageWrap{
      position: relative;
      z-index: 1;
    }

    /* Header */
    header{
      position:sticky; top:0; z-index:50;
      backdrop-filter: var(--glass-blur);
      background: linear-gradient(180deg, rgba(0,0,0,.18), rgba(0,0,0,0));
      border-bottom: 1px solid var(--stroke);
    }
    [data-theme="light"] header{
      background: linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,0));
    }

    .nav{
      display:flex; align-items:center; justify-content:space-between;
      padding: 16px 0;
      gap: 16px;
    }

    .brand{
      display:flex; align-items:center; gap:12px;
      min-width: 240px;
    }

    /* ✅ YOUR LOGO (images/AI.png) */
    .brand-logo{
      width: 52px;
      height: 52px;
      object-fit: contain;
      border-radius: 16px;
      background: rgba(255,255,255,.06);
      border: 1px solid var(--stroke);
      box-shadow: 0 12px 30px rgba(108,99,255,.22);
      padding: 6px;
    }
    [data-theme="light"] .brand-logo{
      background: rgba(255,255,255,.85);
      box-shadow: 0 12px 26px rgba(2,6,23,.12);
    }

    .brand h1{
      font-size: 1.08rem;
      line-height:1.1;
      letter-spacing:.2px;
      font-weight:800;
    }
    .brand small{
      display:block;
      font-size:.78rem;
      color:var(--muted);
      font-weight:600;
      margin-top:2px;
    }

    .nav-right{
      display:flex; align-items:center; gap:10px; flex-wrap:wrap;
      justify-content:flex-end;
    }

    .pill{
      display:inline-flex; align-items:center; justify-content:center;
      padding: 10px 14px;
      border-radius: 999px;
      border: 1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      backdrop-filter: var(--glass-blur);
      box-shadow: 0 10px 25px rgba(0,0,0,.12);
      transition: transform var(--t), box-shadow var(--t), background var(--t), border-color var(--t);
      font-weight:700;
      font-size:.92rem;
      gap:8px;
      cursor:pointer;
      user-select:none;
      -webkit-tap-highlight-color: transparent;
    }
    [data-theme="light"] .pill{ background: rgba(255,255,255,.75); }

    .pill:hover{
      transform: translateY(-2px);
      box-shadow: var(--shadow2);
      border-color: rgba(34,211,238,.22);
    }

    .pill.primary{
      border: 1px solid rgba(255,255,255,.18);
      background: linear-gradient(135deg, rgba(108,99,255,.95), rgba(34,211,238,.85));
      color: #071023;
    }
    [data-theme="light"] .pill.primary{ color:#061022; }

    .pill.primary:hover{
      box-shadow: 0 18px 45px rgba(34,211,238,.18), 0 18px 45px rgba(108,99,255,.18);
    }

    .icon{ width:18px; height:18px; display:inline-block; }

    /* Theme toggle */
    .theme{
      position:relative;
      width: 56px; height: 34px;
      border-radius: 999px;
      border: 1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      box-shadow: inset 0 0 12px rgba(0,0,0,.25);
      transition: background var(--t), transform var(--t), box-shadow var(--t);
      cursor: pointer;
    }
    [data-theme="light"] .theme{ background: rgba(255,255,255,.85); }
    .theme:hover{ box-shadow: inset 0 0 12px rgba(0,0,0,.18), 0 14px 28px rgba(0,0,0,.18); }
    .theme::after{
      content:"";
      position:absolute; top:4px; left:4px;
      width: 24px; height: 24px;
      border-radius: 999px;
      background: linear-gradient(135deg, rgba(255,255,255,.95), rgba(255,255,255,.6));
      box-shadow: 0 10px 18px rgba(0,0,0,.25);
      transition: transform var(--t), background var(--t);
    }
    [data-theme="dark"] .theme::after{
      transform: translateX(22px);
      background: linear-gradient(135deg, rgba(34,211,238,.95), rgba(108,99,255,.9));
    }

    /* Hero */
    .hero{ padding: 44px 0 22px; }

    .hero-grid{
      display:grid;
      grid-template-columns: 1.25fr .75fr;
      gap: 22px;
      align-items: stretch;
    }
    @media (max-width: 980px){ .hero-grid{ grid-template-columns:1fr; } }

    .hero-card{
      border:1px solid var(--stroke);
      background: linear-gradient(180deg, var(--card), rgba(255,255,255,.02));
      backdrop-filter: var(--glass-blur);
      border-radius: var(--radius2);
      box-shadow: var(--shadow);
      padding: 28px;
      position:relative;
      overflow:hidden;
    }
    .hero-card::before{
      content:"";
      position:absolute; inset:-1px;
      background:
        radial-gradient(640px 280px at 10% 10%, rgba(34,211,238,.22), transparent 55%),
        radial-gradient(560px 280px at 78% 0%, rgba(108,99,255,.22), transparent 60%);
      opacity:.95;
      pointer-events:none;
    }
    .hero-card > *{ position:relative; z-index:1; }

    .kicker{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 12px;
      border-radius: 999px;
      border: 1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      font-weight:700;
      color: var(--muted);
      width: fit-content;
      margin-bottom: 14px;
    }
    [data-theme="light"] .kicker{ background: rgba(255,255,255,.82); }

    .kicker .dot{
      width:10px; height:10px; border-radius:999px;
      background: radial-gradient(circle at 30% 30%, #fef9c3, var(--p2) 55%, var(--p1));
      box-shadow: 0 0 0 6px rgba(34,211,238,.10);
    }

    .hero h2{
      font-size: clamp(1.9rem, 3.3vw, 3.1rem);
      line-height: 1.10;
      letter-spacing: -.7px;
      margin-bottom: 12px;
    }
    .gradient-text{
      background: linear-gradient(90deg, var(--p2), var(--p1), var(--p3));
      -webkit-background-clip: text;
      background-clip:text;
      color: transparent;
    }

    .hero p{
      font-size: 1.05rem;
      color: var(--muted);
      line-height: 1.75;
      max-width: 70ch;
      margin-bottom: 18px;
    }

    .cta-row{
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    /* ✅ FIX: typing should NOT change layout width */
    .typed-wrap{
      display:inline-block;
      min-width: 30ch;       /* fallback */
      white-space: nowrap;
      vertical-align: bottom;
    }
    .typed{
      display:inline-block;
      padding-right: 6px;
      border-right: 2px solid rgba(255,255,255,.55);
      animation: caret 1.05s steps(1) infinite;
    }
    [data-theme="light"] .typed{ border-right-color: rgba(15,23,42,.45); }
    @keyframes caret{ 50%{ border-right-color: transparent; } }

    /* Side panel */
    .side{ display:grid; gap: 12px; }

    .mini{
      border:1px solid var(--stroke);
      background: linear-gradient(180deg, var(--card), rgba(255,255,255,.02));
      backdrop-filter: var(--glass-blur);
      border-radius: var(--radius);
      box-shadow: var(--shadow2);
      padding: 16px;
      display:flex;
      gap: 12px;
      align-items:flex-start;
      transition: transform var(--t), box-shadow var(--t);
      opacity: 0;
      transform: translateY(12px);
    }
    .mini.reveal{ opacity: 1; transform: translateY(0); }
    .mini:hover{ transform: translateY(-2px); box-shadow: var(--shadow); }

    .mini b{ display:block; font-size: .98rem; margin-bottom: 4px; }
    .mini span{ color: var(--muted); font-size: .92rem; line-height: 1.55; }

    .badge{
      width: 38px; height: 38px; border-radius: 14px;
      background: rgba(255,255,255,.10);
      border: 1px solid var(--stroke);
      display:grid; place-items:center;
      flex: 0 0 auto;
    }
    [data-theme="light"] .badge{ background: rgba(255,255,255,.92); }

    /* Sections */
    .section{ padding: 26px 0 10px; }

    .section-head{
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap: 18px;
      margin-bottom: 16px;
    }
    .section-head h3{ font-size: 1.35rem; letter-spacing: -.2px; }
    .section-head p{ color: var(--muted); max-width: 80ch; line-height: 1.7; }

    .grid{
      display:grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 14px;
    }

    .feature{
      grid-column: span 4;
      border:1px solid var(--stroke);
      background: linear-gradient(180deg, var(--card2), rgba(255,255,255,.03));
      backdrop-filter: var(--glass-blur);
      border-radius: var(--radius);
      box-shadow: var(--shadow2);
      padding: 16px;
      transition: transform var(--t), box-shadow var(--t), border-color var(--t);
      position:relative;
      overflow:hidden;

      opacity: 0;
      transform: translateY(14px);
    }
    .feature.reveal{ opacity: 1; transform: translateY(0); }
    .feature:hover{
      transform: translateY(-4px);
      box-shadow: var(--shadow);
      border-color: rgba(34,211,238,.25);
    }

    .feature .top{ display:flex; gap: 10px; align-items:center; margin-bottom: 10px; }
    .feature .top .ficon{
      width:40px; height:40px; border-radius: 16px;
      background: radial-gradient(circle at 30% 30%, rgba(34,211,238,.26), rgba(108,99,255,.18));
      border: 1px solid var(--stroke);
      display:grid; place-items:center;
      flex: 0 0 auto;
    }
    .feature h4{ font-size: 1.02rem; letter-spacing: -.2px; line-height: 1.25; }
    .feature p{ color: var(--muted); font-size: .92rem; line-height: 1.65; }

    @media (max-width: 980px){ .feature{ grid-column: span 6; } }
    @media (max-width: 680px){
      .container{ width: calc(100% - 34px); }
      .feature{ grid-column: span 12; }
      .nav{ flex-wrap: wrap; }
      .brand{ min-width: auto; }
      .nav-right{ gap:8px; }
      .pill{ padding: 10px 12px; }
    }

    /* ============================================================
       ✅ FOOTER — copied style from Cognisense dashboard (same UX)
       ============================================================ */
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

    .footer-socials { display: flex; gap: 40px; flex-wrap: wrap; justify-content:center; }

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
      transition:
        transform 0.35s ease,
        box-shadow 0.35s ease,
        background 0.35s ease;
      -webkit-tap-highlight-color: transparent;
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
      transition:
        opacity 0.35s ease,
        transform 0.35s ease;
    }

    .footer-social-link img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      filter: drop-shadow(0 0 4px rgba(15,23,42,0.35));
      transition:
        transform 0.35s ease,
        opacity 0.35s ease;
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
      transition:
        opacity 0.35s ease,
        transform 0.35s ease;
      color: #4b5563;
      text-align: center;
      padding: 0 8px;
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

    .footer-copy {
      font-size: 1rem;
      opacity: 0.85;
      gap: 2px;
      color: inherit;
    }

    /* Dark mode variant (same selectors as Cognisense) */
    body.dark-mode .footer {
      border-top-color: rgba(31,41,55,0.9);
      color: #9ca3af;
    }
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

    /* ============================================================
       ✅ MODALS — copied open/anim feel from Cognisense about overlay
       (used for BOTH About + Contact)
       ============================================================ */

    body.modal-open { overflow: hidden; }

    body.modal-open #pageWrap{
      filter: blur(8px);
      transform: scale(0.995);
      pointer-events: none;
      transition: filter .18s ease, transform .18s ease;
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
      margin: 14px 0 6px;
      font-size: 2.0rem;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #0b1120;
      text-shadow: 2px 6px 18px rgba(30,58,138,.18);
      font-weight: 800;
    }

    .about-subtitle{
      margin: 0;
      font-size: 1.02rem;
      opacity: .88;
      color: #1f2937;
      max-width: 75ch;
      line-height: 1.7;
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
      font-weight: 700;
    }

    .about-body{
      padding: 6px 8px 14px;
      overflow: auto;
    }

    .about-grid{
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
      margin-top: 14px;
    }
    @media (max-width: 820px){ .about-grid{ grid-template-columns: 1fr; } }

    .about-card{
      background: rgba(255,255,255,.65);
      border: 1px solid rgba(148,163,184,.40);
      border-radius: 18px;
      padding: 14px 14px 12px;
      box-shadow:
        0 16px 34px rgba(15,23,42,.12),
        inset 0 0 0 1px rgba(255,255,255,.55);
      transition: transform .18s ease, box-shadow .18s ease;
    }

    .about-card:hover{
      transform: translateY(-2px) rotateX(5deg) rotateY(-6deg);
      box-shadow: 0 22px 46px rgba(15,23,42,.16);
    }

    .about-card h3{ margin: 0 0 6px; font-size: 1.05rem; color: #0b1120; }
    .about-card p{ margin: 0; color: #334155; opacity: .92; line-height: 1.55; font-size: .95rem; }

    .about-footer{
      padding: 10px 10px 0;
      border-top: 1px solid rgba(148,163,184,.25);
      margin-top: 8px;
    }
    .about-mini{ font-size: .92rem; opacity: .82; color: #334155; line-height: 1.6; }

    /* Dark mode modal variants (same as Cognisense) */
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

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce){
      *{ scroll-behavior:auto !important; animation: none !important; transition: none !important; }
      .typed{ border-right:none; }
    }

    /* =========================
   Cognisense Theme Swap Pulse
   (uses body::after so it doesn't conflict with your noise body::before)
   ========================= */
body::after{
  content:"";
  position: fixed;
  inset: 0;
  pointer-events: none;
  opacity: 0;
  transition: opacity .28s ease;
  z-index: 9999;
  background:
    radial-gradient(900px 480px at 20% 15%, rgba(56,189,248,.14), transparent 60%),
    radial-gradient(900px 520px at 85% 20%, rgba(79,70,229,.12), transparent 62%);
  mix-blend-mode: soft-light;
}
body.theme-swap::after{ opacity: 1; }
body.dark-mode::after{ mix-blend-mode: screen; opacity: 0; }
body.dark-mode.theme-swap::after{ opacity: .95; }

/* =========================
   Cognisense Theme Toggle (Sun/Moon PNG)
   ========================= */
.theme-toggle {
  position: relative;
  display: inline-block;
  cursor: pointer;
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

/* Light mode (unchecked) */
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

/* Dark mode (checked) */
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

  </style>
</head>

<body>
<div id="pageWrap">
  <header>
    <div class="container">
      <div class="nav" role="navigation" aria-label="Primary">
        <a class="brand" href="index.php" aria-label="AspireIELTS Home">
          <img src="images/AI.png" class="brand-logo" alt="AspireIELTS Logo">
          <div>
            <h1>AspireIELTS</h1>
            <small>Mock tests • Analytics • AI feedback</small>
          </div>
        </a>

        <div class="nav-right">
          <?php if ($isLoggedIn): ?>
            <span class="pill" style="cursor:default" title="Signed in">
              <svg class="icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M20 21a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2"/>
              </svg>
              <?= htmlspecialchars($userName ?: 'Account', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a class="pill primary" href="dashboard.php">Dashboard</a>
            <a class="pill" href="logout.php">Logout</a>
          <?php else: ?>
            <a class="pill" href="login.php">Login</a>
            <a class="pill primary" href="signup.php">Create account</a>
          <?php endif; ?>

          <button class="pill" type="button" id="aboutOpenBtn" aria-label="About" aria-haspopup="dialog" aria-controls="aboutOverlay">
            About
          </button>

          <button class="pill" type="button" id="contactOpenBtn" aria-label="Contact" aria-haspopup="dialog" aria-controls="contactOverlay">
            Contact
          </button>

          <label class="theme-toggle" aria-label="Toggle theme">
  <input type="checkbox" id="darkModeToggle">
  <div class="toggle-track">
    <img src="images/sun.png" alt="Light Mode" class="icon-sun">
    <img src="images/moon.png" alt="Dark Mode" class="icon-moon">
    <div class="toggle-thumb"></div>
  </div>
</label>

        </div>
      </div>
    </div>
  </header>

  <main class="container">
    <!-- HERO -->
    <section class="hero" aria-label="Hero">
      <div class="hero-grid">
        <div class="hero-card">
          <div class="kicker">
            <span class="dot" aria-hidden="true"></span>
            Built for real IELTS pressure — timed & tracked
          </div>

          <h2>
            <span class="gradient-text">Practice. Improve.</span><br/>
            <span class="typed-wrap">
              <span id="typed" class="typed">Achieve Band 9.</span>
            </span>
          </h2>

          <p>
            AspireIELTS gives you full-length mocks for Listening, Reading, Writing, and Speaking — plus analytics and smart feedback
            so you know exactly what to fix next.
          </p>

          <div class="cta-row">
            <a class="pill primary" href="<?= $isLoggedIn ? 'dashboard.php' : 'signup.php' ?>">
              <?= $isLoggedIn ? 'Go to Dashboard' : 'Start for free' ?>
            </a>

            <a class="pill" href="<?= $isLoggedIn ? 'mocktests.php' : 'login.php' ?>">
              Take a mock test
            </a>

            <button class="pill" type="button" onclick="openOverlay('aboutOverlay')">
              How it works
            </button>
          </div>
        </div>

        <aside class="side" aria-label="Highlights">
          <div class="mini">
            <div class="badge" aria-hidden="true">🎧</div>
            <div>
              <b>Full IELTS modules</b>
              <span>Listening, Reading, Writing, and Speaking with realistic exam flow.</span>
            </div>
          </div>

          <div class="mini">
            <div class="badge" aria-hidden="true">🧠</div>
            <div>
              <b>Smart feedback</b>
              <span>Actionable tips + scoring insights so your next attempt improves.</span>
            </div>
          </div>

          <div class="mini">
            <div class="badge" aria-hidden="true">📊</div>
            <div>
              <b>Progress analytics</b>
              <span>Band estimates, performance trends, and a clean attempt history.</span>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="section" aria-label="Features">
      <div class="section-head">
        <div>
          <h3>Everything you need — in one focused platform</h3>
          <p>Designed to feel premium, fast, and exam-realistic. Clear UI, smooth motion, and dark/light modes tuned properly.</p>
        </div>
      </div>

      <div class="grid" id="featuresGrid">
        <?php
        $features = [
          ["🎧", "Full IELTS test suite", "Listening, Reading, Writing, and Speaking mocks — structured like the real exam."],
          ["🧠", "AI-powered feedback", "Score hints, improvement points, and evidence-driven suggestions."],
          ["📊", "Real-time band insights", "See estimated band ranges and performance trends at a glance."],
          ["💬", "Lectures & practice materials", "Video lessons and targeted practice to strengthen weak areas."],
          ["📞", "Live speaking sessions", "Optional real-time speaking tests with a certified examiner (via video call)."],
          ["🤖", "Personal IELTS chatbot", "Quick guidance, strategies, and reminders anytime you need."],
          ["⏱️", "Timers + word count", "Realistic time pressure and writing tools to build exam discipline."],
          ["📂", "Secure attempt history", "All tests, feedback, and analytics stored for easy review."],
          ["🌐", "User dashboard", "Clean overview of your journey — modules, scores, and growth."],
          ["🔒", "Secure & user-friendly", "Private accounts with a polished, modern interface + theme mode."],
          ["🧩", "Adaptive improvement path", "Recommended next steps based on recent performance patterns."],
          ["✨", "Premium feel, lightweight tech", "Fast loading, smooth animations, and mobile-first layout."],
        ];
        foreach ($features as $f):
        ?>
          <article class="feature">
            <div class="top">
              <div class="ficon" aria-hidden="true"><?= $f[0] ?></div>
              <h4><?= htmlspecialchars($f[1], ENT_QUOTES, 'UTF-8') ?></h4>
            </div>
            <p><?= htmlspecialchars($f[2], ENT_QUOTES, 'UTF-8') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ✅ FOOTER like Cognisense (NO about/login links) -->
    <footer class="footer" aria-label="Footer">
      <div class="footer-socials">
        <a class="footer-social-link" href="https://www.facebook.com/midzz.01" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
          <img src="images/facebook.png" alt="Facebook">
          <span class="footer-social-label">Facebook</span>
        </a>

        <a class="footer-social-link" href="https://www.instagram.com/tahmid__1?igsh=MWtkNWdjbWUyOWdpeA==" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
          <img src="images/instagram.png" alt="Instagram">
          <span class="footer-social-label">Instagram</span>
        </a>

        <a class="footer-social-link" href="#" aria-label="Twitter">
          <img src="images/twitter.png" alt="Twitter">
          <span class="footer-social-label">Twitter</span>
        </a>

        <a class="footer-social-link" href="https://github.com/" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
          <img src="images/github.png" alt="GitHub">
          <span class="footer-social-label">GitHub</span>
        </a>
      </div>

      <div class="footer-copy">
        © <?= date('Y'); ?> AspireIELTS. All rights reserved.
      </div>
    </footer>
  </main>
</div>

<!-- =========================
     ABOUT OVERLAY (Cognisense-style)
     ========================= -->
<div class="about-overlay" id="aboutOverlay" aria-hidden="true">
  <div class="about-modal" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" tabindex="-1">
    <button class="about-close" type="button" data-close="aboutOverlay" aria-label="Close About">✕</button>

    <div class="about-hero">
      <div class="about-hero-badge">
        <span class="about-dot"></span>
        <span>ASPIREIELTS • PROJECT OVERVIEW</span>
      </div>

      <h2 class="about-title" id="aboutTitle">AspireIELTS</h2>

      <p class="about-subtitle">
        AspireIELTS is a comprehensive IELTS mock testing platform offering realistic tests for Listening, Reading, Writing, and Speaking.
        Get feedback, performance analytics, and AI-based writing evaluation to prepare effectively for the real IELTS exam.
      </p>

      <div class="about-tags">
        <span class="about-tag">🎧 Listening</span>
        <span class="about-tag">📖 Reading</span>
        <span class="about-tag">📝 Writing</span>
        <span class="about-tag">🗣️ Speaking</span>
        <span class="about-tag">📊 Analytics</span>
        <span class="about-tag">🤖 AI Feedback</span>
      </div>
    </div>

    <div class="about-body">
      <div class="about-grid">
        <div class="about-card">
          <h3>Realistic exam flow</h3>
          <p>Timers, module structure, and attempt tracking so practice feels like the real thing.</p>
        </div>
        <div class="about-card">
          <h3>Actionable improvement</h3>
          <p>Clear feedback and insights that tell you what to fix next — not just a score.</p>
        </div>
        <div class="about-card">
          <h3>Progress history</h3>
          <p>See your trends over time and revisit past attempts with stored feedback.</p>
        </div>
        <div class="about-card">
          <h3>Study support</h3>
          <p>Practice materials and guidance to strengthen weak areas fast.</p>
        </div>
      </div>
    </div>

    <div class="about-footer">
      <div class="about-mini">
        Built for speed, clarity, and consistency — practice smarter and move toward Band 9 with confidence.
      </div>
    </div>
  </div>
</div>

<!-- =========================
     CONTACT OVERLAY (same open/anim)
     ========================= -->
<div class="about-overlay" id="contactOverlay" aria-hidden="true">
  <div class="about-modal" role="dialog" aria-modal="true" aria-labelledby="contactTitle" tabindex="-1">
    <button class="about-close" type="button" data-close="contactOverlay" aria-label="Close Contact">✕</button>

    <div class="about-hero">
      <div class="about-hero-badge">
        <span class="about-dot"></span>
        <span>CONTACT • DEVELOPER</span>
      </div>

      <h2 class="about-title" id="contactTitle">Tahmid Ahmed Talukder</h2>

      <p class="about-subtitle">
        Developer of AspireIELTS • Dhaka, Bangladesh
      </p>

      <div class="about-tags">
        <span class="about-tag">📧 tahmidahmed789456@gmail.com</span>
        <span class="about-tag">📞 +880-1608902939</span>
      </div>
    </div>

    <div class="about-body">
      <div class="about-grid">
        <div class="about-card">
          <h3>Email</h3>
          <p><a href="mailto:tahmidahmed789456@gmail.com">tahmidahmed789456@gmail.com</a></p>
        </div>
        <div class="about-card">
          <h3>LinkedIn</h3>
          <p><a href="https://www.linkedin.com/in/tahmid-ahmed-417712285" target="_blank" rel="noopener noreferrer">
            linkedin.com/in/tahmid-ahmed-417712285
          </a></p>
        </div>
        <div class="about-card">
          <h3>Facebook</h3>
          <p><a href="https://www.facebook.com/midzz.01" target="_blank" rel="noopener noreferrer">
            facebook.com/midzz.01
          </a></p>
        </div>
        <div class="about-card">
          <h3>Instagram</h3>
          <p><a href="https://www.instagram.com/tahmid__1?igsh=MWtkNWdjbWUyOWdpeA==" target="_blank" rel="noopener noreferrer">
            instagram.com/tahmid__1
          </a></p>
        </div>
      </div>
    </div>

    <div class="about-footer">
      <div class="about-mini">
        For issues, features, or collaboration — reach out anytime.
      </div>
    </div>
  </div>
</div>

<script>
  // =========================
  // Theme toggle (keeps your data-theme, AND adds body.dark-mode for Cognisense footer/modal CSS)
  // =========================
  function pulseTheme() {
    document.body.classList.add('theme-swap');
    clearTimeout(window.__themeSwapT);
    window.__themeSwapT = setTimeout(() => {
      document.body.classList.remove('theme-swap');
    }, 260);
  }

  function applyTheme(pulse = false) {
    const stored = localStorage.getItem('darkMode');

    // fallback to your old storage key if exists
    const old = localStorage.getItem('theme');

    let enabled;
    if (stored === 'enabled') enabled = true;
    else if (stored === 'disabled') enabled = false;
    else if (old) enabled = (old === 'dark');
    else enabled = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

    // IMPORTANT: your page theme is controlled by data-theme
    document.documentElement.setAttribute('data-theme', enabled ? 'dark' : 'light');

    // Keep this because your footer/modal CSS uses body.dark-mode
    document.body.classList.toggle('dark-mode', enabled);

    // Sync checkbox UI
    const toggle = document.getElementById('darkModeToggle');
    if (toggle) toggle.checked = enabled;

    if (pulse) pulseTheme();
  }

  function setTheme(enabled) {
    localStorage.setItem('darkMode', enabled ? 'enabled' : 'disabled');

    // optional compatibility for your older pages
    localStorage.setItem('theme', enabled ? 'dark' : 'light');

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
    if (e.key === 'darkMode' || e.key === 'theme') applyTheme(true);
  });

  // =========================
  // Typed headline (fixed width to prevent layout jitter)
  // =========================
  (function () {
    const el = document.getElementById('typed');
    const wrap = el?.closest('.typed-wrap');
    if(!el || !wrap) return;

    const phrases = [
      "Achieve Band 9.",
      "Master IELTS faster.",
      "Train with real exam flow.",
      "Get feedback that helps."
    ];

    // ✅ set min width based on longest phrase (prevents container resize)
    const maxLen = phrases.reduce((m,s)=>Math.max(m, s.length), 0);
    wrap.style.minWidth = (maxLen + 2) + "ch";

    let p = 0, i = 0, deleting = false;

    function tick(){
      const text = phrases[p];

      if(!deleting){
        i++;
        el.textContent = text.slice(0, i);
        if(i >= text.length){
          deleting = true;
          setTimeout(tick, 1100);
          return;
        }
      } else {
        i--;
        el.textContent = text.slice(0, i);
        if(i <= 0){
          deleting = false;
          p = (p + 1) % phrases.length;
        }
      }
      setTimeout(tick, deleting ? 40 : 70);
    }
    tick();
  })();

  // =========================
  // Reveal on scroll (no external libs)
  // =========================
  (function () {
    const items = [...document.querySelectorAll('.feature, .mini')];
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('reveal');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.15 });

    items.forEach(el => io.observe(el));
  })();

  // =========================
  // Cognisense-style overlay controls (About + Contact)
  // =========================
  function openOverlay(id){
    const overlay = document.getElementById(id);
    if(!overlay) return;
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden','false');
    document.body.classList.add('modal-open');

    // focus close
    setTimeout(() => {
      overlay.querySelector('[data-close="'+id+'"]')?.focus();
    }, 10);
  }

  function closeOverlay(id){
    const overlay = document.getElementById(id);
    if(!overlay) return;
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden','true');
    document.body.classList.remove('modal-open');
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('aboutOpenBtn')?.addEventListener('click', () => openOverlay('aboutOverlay'));
    document.getElementById('contactOpenBtn')?.addEventListener('click', () => openOverlay('contactOverlay'));

    document.querySelectorAll('[data-close]').forEach(btn => {
      btn.addEventListener('click', () => closeOverlay(btn.getAttribute('data-close')));
    });

    // outside click closes
    document.querySelectorAll('.about-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if(e.target === overlay) closeOverlay(overlay.id);
      });
    });

    // ESC closes any open overlay
    document.addEventListener('keydown', (e) => {
      if(e.key !== 'Escape') return;
      document.querySelectorAll('.about-overlay.open').forEach(o => closeOverlay(o.id));
    });
  });
</script>
</body>
</html>
