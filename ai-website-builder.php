<?php
/**
 * Website Draft — Intake / Landing Page
 *
 * "Get a Free Website Draft in Minutes."
 * Captures a prospect's business details and queues them in Supabase
 * (via api/site-builder-leads.php) for the generation workflow.
 */

require_once __DIR__ . '/config/env-loader.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/admin/config/database.php';
require_once __DIR__ . '/includes/SEOHelper.php';
require_once __DIR__ . '/includes/SpamProtection.php';
require_once __DIR__ . '/includes/gen-cap.php';

initSecureSession();
setSecurityHeaders();

$nonce = getCSPNonce();
$recaptchaSiteKey = getEnv('RECAPTCHA_SITE_KEY', '');
$genCap = iz_gen_read();          // ['used'=>n,'remaining'=>r]
$genRemaining = (int) $genCap['remaining'];
$genExhausted = $genRemaining <= 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

<?php
SEOHelper::outputMetaTags('ai-website-builder', [
    'page_title' => 'Get a Free Website Draft in Minutes | Izende Studio Web',
    'meta_description' => 'Describe your business and we\'ll prepare a live draft of your website in minutes — then host it for you on your own domain. No page builders, no Wix. Free draft, St. Louis web hosting.',
    'canonical_url' => 'https://izendestudioweb.com/ai-website-builder.php',
    'og_image' => 'https://www.izendestudioweb.com/assets/img/ai-builder-og-v3-1200x630.jpg'
]);
?>
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Izende — free website draft, ready in minutes">
<meta name="twitter:image:alt" content="Izende — free website draft, ready in minutes">

  <?php include './assets/includes/header-links.php'; ?>

  <style nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>">
    .vibe-card{display:block;cursor:pointer;margin:0;height:100%}
    .vibe-card input{position:absolute;opacity:0;width:0;height:0}
    .vibe-card-inner{border:2px solid #e2e8f0;border-radius:10px;padding:12px 10px;height:100%;text-align:center;transition:border-color .15s,box-shadow .15s}
    .vibe-card:hover .vibe-card-inner{border-color:#cbd5e1}
    .vibe-card input:checked + .vibe-card-inner{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
    .vibe-card input:focus-visible + .vibe-card-inner{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.25)}
    .vibe-swatches{display:flex;gap:4px;justify-content:center;margin-bottom:8px}
    .vibe-swatches span{width:22px;height:22px;border-radius:4px;border:1px solid rgba(0,0,0,.08)}
    .vibe-swatches .s1{background:var(--c1)}.vibe-swatches .s2{background:var(--c2)}
    .vibe-swatches .s3{background:var(--c3)}.vibe-swatches .s4{background:var(--c4)}
    .vibe-aa{font-size:20px;font-weight:700;line-height:1;margin-bottom:6px;color:var(--c4,#334155);font-family:var(--ff,inherit)}
    .vibe-label{font-size:13px;font-weight:600;color:#334155;display:block}
    .vibe-auto{--c1:#64748b;--c2:#94a3b8;--c3:#cbd5e1;--c4:#334155;--ff:system-ui,sans-serif}
    .vibe-clean{--c1:#2563eb;--c2:#0ea5e9;--c3:#f1f5f9;--c4:#1e293b;--ff:'Helvetica Neue',Arial,sans-serif}
    .vibe-warm{--c1:#c2683f;--c2:#e8a87c;--c3:#fdf6ee;--c4:#5b3a29;--ff:Georgia,'Times New Roman',serif}
    .vibe-bold{--c1:#7c3aed;--c2:#ec4899;--c3:#f59e0b;--c4:#111827;--ff:'Trebuchet MS',Verdana,sans-serif}
    .vibe-elegant{--c1:#111111;--c2:#c9a96a;--c3:#ffffff;--c4:#6b6b6b;--ff:Georgia,'Times New Roman',serif}
    .vibe-corporate{--c1:#1e3a5f;--c2:#2c5282;--c3:#edf2f7;--c4:#2d3748;--ff:Arial,Helvetica,sans-serif}

    .ai-bot-wrap{display:flex;align-items:center;gap:12px;margin-bottom:16px}
    .ai-bot{width:50px;height:50px;flex:0 0 50px;animation:aiBotFloat 3s ease-in-out infinite}
    .ai-bot .ai-bot-body{fill:#fff;stroke:#2563eb;stroke-width:2}
    .ai-bot .ai-bot-screen{fill:#eef4ff}
    .ai-bot .ai-bot-eye{fill:#2563eb;transform-box:fill-box;transform-origin:center;animation:aiBotBlink 4.5s infinite}
    .ai-bot .ai-bot-antenna{stroke:#2563eb;stroke-width:2;stroke-linecap:round}
    .ai-bot .ai-bot-dot{fill:#0ea5e9;animation:aiBotPulse 1.6s ease-in-out infinite}
    .ai-bot-text strong{display:block;color:#1e293b;font-size:16px;line-height:1.2}
    .ai-bot-text span{font-size:12.5px;color:#64748b}
    @keyframes aiBotFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}
    @keyframes aiBotPulse{0%,100%{opacity:.4}50%{opacity:1}}
    @keyframes aiBotBlink{0%,92%,100%{transform:scaleY(1)}96%{transform:scaleY(.1)}}
    @media (prefers-reduced-motion: reduce){.ai-bot,.ai-bot *{animation:none!important}}

    /* ===== Watch-it-build overlay ===== */
    .build-overlay{position:fixed;inset:0;z-index:1080;display:none;background:linear-gradient(160deg,#0b1220 0%,#111c33 55%,#0b1220 100%);color:#fff;overflow:auto}
    .build-overlay.show{display:block;animation:overlayIn .42s cubic-bezier(.2,.8,.25,1) both}
    @keyframes overlayIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:none}}
    @keyframes orbIgnite{0%{transform:scale(.4);box-shadow:0 0 0 0 rgba(56,189,248,.9)}55%{transform:scale(1.18);box-shadow:0 0 0 26px rgba(56,189,248,0)}100%{transform:scale(1);box-shadow:0 0 0 0 rgba(37,99,235,.45)}}
    .build-overlay.show .build-orb{animation:orbIgnite .6s cubic-bezier(.2,.85,.3,1) both, orbPulse 2s ease-in-out .6s infinite}
    /* build task checklist (checks off as the draft comes together) */
    .build-tasks{list-style:none;margin:16px 0 14px;padding:0;display:inline-block;text-align:left}
    .build-tasks .bt{display:flex;align-items:center;gap:11px;font-size:.95rem;line-height:1.25;color:#9fb0c9;padding:6px 0;opacity:.5;transition:opacity .35s,color .35s}
    .build-tasks .bt-ico{flex:0 0 20px;width:20px;height:20px;border-radius:50%;border:2px solid #3a4a63;display:grid;place-items:center;font-size:12px;font-weight:700;color:#fff;transition:background .3s,border-color .3s}
    .build-tasks .bt.active{opacity:1;color:#dbe7f8}
    .build-tasks .bt.active .bt-ico{border-color:#3a4a63;border-top-color:#38bdf8;animation:btSpin .7s linear infinite}
    .build-tasks .bt.done{opacity:1;color:#e6eefb}
    .build-tasks .bt.done .bt-ico{background:#16a34a;border-color:#16a34a;animation:checkPop .45s cubic-bezier(.2,.9,.3,1.3) both}
    .build-tasks .bt.done .bt-ico::after{content:"\2713"}
    @keyframes btSpin{to{transform:rotate(360deg)}}
    @keyframes checkPop{0%{transform:scale(.3)}60%{transform:scale(1.2)}100%{transform:scale(1)}}
    .build-close{position:fixed;top:16px;right:18px;z-index:3;background:rgba(255,255,255,.1);border:0;color:#cbd5e1;width:38px;height:38px;border-radius:50%;font-size:20px;cursor:pointer;line-height:1}
    .build-close:hover{background:rgba(255,255,255,.2);color:#fff}
    .build-stage{position:relative;min-height:100%;display:flex;align-items:center;justify-content:center;padding:40px 16px}

    /* shimmering skeleton behind the panel */
    .build-skeleton{position:absolute;inset:0;display:flex;flex-direction:column;gap:18px;padding:6vh 8vw;filter:blur(7px);opacity:.32;pointer-events:none}
    .sk{background:linear-gradient(90deg,rgba(255,255,255,.08) 25%,rgba(255,255,255,.18) 37%,rgba(255,255,255,.08) 63%);background-size:400% 100%;border-radius:12px;animation:skShimmer 1.5s ease infinite}
    .sk-row{display:flex;gap:18px;align-items:center}
    .sk-logo{width:120px;height:26px}.sk-navi{flex:1;height:14px;max-width:340px;margin-left:auto}
    .sk-h1{width:62%;height:54px}.sk-h2{width:40%;height:30px}.sk-btn{width:180px;height:46px;border-radius:999px}
    .sk-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:8px}.sk-card{height:200px}
    @keyframes skShimmer{0%{background-position:100% 0}100%{background-position:-100% 0}}

    /* progress panel */
    .build-panel{position:relative;z-index:2;width:min(560px,92vw);text-align:center;background:rgba(13,20,38,.72);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.1);border-radius:22px;padding:42px 34px;box-shadow:0 30px 70px rgba(0,0,0,.45)}
    .build-orb{width:74px;height:74px;margin:0 auto 22px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#60a5fa,#2563eb 60%,#1e3a8a);box-shadow:0 0 0 0 rgba(37,99,235,.5);animation:orbPulse 2s ease-in-out infinite;display:grid;place-items:center;font-size:30px}
    @keyframes orbPulse{0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.45);transform:scale(1)}50%{box-shadow:0 0 0 18px rgba(37,99,235,0);transform:scale(1.05)}}
    /* ----- Zeno animated build scene ----- */
    .zeno-scene{position:relative;width:170px;height:150px;margin:0 auto 18px}
    .zeno-aura{position:absolute;left:50%;top:54%;width:120px;height:120px;transform:translate(-50%,-50%);border-radius:50%;background:radial-gradient(circle,rgba(56,189,248,.35),rgba(37,99,235,0) 68%);animation:zAura 2.6s ease-in-out infinite}
    @keyframes zAura{0%,100%{transform:translate(-50%,-50%) scale(.9);opacity:.6}50%{transform:translate(-50%,-50%) scale(1.15);opacity:1}}
    .zeno{position:absolute;left:50%;top:50%;width:96px;height:108px;transform:translate(-50%,-50%);animation:zBob 3s ease-in-out infinite, zDrift 6s ease-in-out infinite}
    @keyframes zBob{0%,100%{margin-top:0}50%{margin-top:-8px}}
    @keyframes zDrift{0%,100%{margin-left:-9px}50%{margin-left:9px}}
    .zeno .z-body{fill:#fff;stroke:#2563eb;stroke-width:2.5}
    .zeno .z-screen{fill:#0b1220}
    .zeno .z-eye{fill:#38bdf8;transform-box:fill-box;transform-origin:center;animation:zBlink 4s infinite}
    .zeno .z-smile{fill:none;stroke:#38bdf8;stroke-width:2;stroke-linecap:round}
    .zeno .z-antenna{stroke:#2563eb;stroke-width:2.5;stroke-linecap:round}
    .zeno .z-dot{fill:#38bdf8;animation:zDot 1.4s ease-in-out infinite}
    .zeno .z-ear{fill:#2563eb}
    .zeno .z-arm{stroke:#2563eb;stroke-width:5;stroke-linecap:round}
    .zeno .z-brush-grp{transform-box:fill-box;transform-origin:18px 60px;animation:zPaint 1.8s ease-in-out infinite}
    .zeno .z-brush-handle{stroke:#a16207;stroke-width:3.5;stroke-linecap:round}
    .zeno .z-brush-tip{fill:#f59e0b}
    @keyframes zBlink{0%,93%,100%{transform:scaleY(1)}96%{transform:scaleY(.12)}}
    @keyframes zDot{0%,100%{opacity:.4;r:2.5}50%{opacity:1;r:3.4}}
    @keyframes zPaint{0%,100%{transform:rotate(-18deg)}50%{transform:rotate(16deg)}}
    .z-spark{position:absolute;width:8px;height:8px;color:#fbbf24;font-size:9px;line-height:1;opacity:0;animation:zSpark 2.4s ease-in-out infinite}
    .z-spark:nth-child(2){left:18%;top:30%;animation-delay:0s}
    .z-spark:nth-child(3){right:14%;top:24%;animation-delay:.8s}
    .z-spark:nth-child(4){right:22%;bottom:18%;animation-delay:1.5s}
    .z-spark:nth-child(5){left:24%;bottom:14%;animation-delay:2s}
    @keyframes zSpark{0%{opacity:0;transform:translateY(6px) scale(.6)}40%{opacity:1;transform:translateY(-2px) scale(1)}100%{opacity:0;transform:translateY(-12px) scale(.7)}}
    .build-panel h2{font-size:1.55rem;margin:0 0 6px;color:#fff;font-weight:700}
    .build-biz{color:#7dd3fc}
    .build-step{min-height:1.5em;color:#cbd5e1;font-size:1.02rem;margin:0 0 22px;transition:opacity .3s}
    .build-bar{height:8px;border-radius:999px;background:rgba(255,255,255,.12);overflow:hidden}
    .build-bar span{display:block;height:100%;width:4%;border-radius:999px;background:linear-gradient(90deg,#2563eb,#38bdf8);transition:width .9s ease}
    .build-countdown{display:inline-flex;align-items:center;justify-content:center;gap:8px;margin:14px 0 0;padding:8px 14px;border-radius:999px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.13);color:#e2e8f0;font-size:.92rem;font-weight:700}
    .build-countdown b{color:#7dd3fc;font-variant-numeric:tabular-nums}
    .build-hint{margin:18px 0 0;font-size:.86rem;color:#7c8aa3}

    /* reveal */
    .build-reveal{position:fixed;inset:0;z-index:4;display:none;flex-direction:column;background:#0b1220}
    .build-reveal.show{display:flex}
    .iz-confetti-canvas{position:fixed;inset:0;z-index:1095;pointer-events:none;width:100vw;height:100vh}
    .reveal-bar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:12px 18px;background:#fff;border-bottom:1px solid #e2e8f0;box-shadow:0 2px 14px rgba(0,0,0,.18)}
    .reveal-bar .rb-msg{font-weight:700;color:#0f172a;display:flex;align-items:center;gap:8px;font-size:1.02rem}
    .reveal-bar .rb-msg i{color:#2563eb}
    .reveal-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .reveal-frame-wrap{flex:1;position:relative;background:#f8fafc}
    .reveal-frame-wrap iframe{width:100%;height:100%;border:0;display:block;filter:blur(22px);opacity:.4;transition:filter 1.1s ease,opacity 1.1s ease}
    .reveal-frame-wrap iframe.sharp{filter:blur(0);opacity:1}
    @media (prefers-reduced-motion: reduce){.sk,.build-orb,.build-overlay.show,.build-overlay.show .build-orb,.build-bar span,.reveal-frame-wrap iframe,.zeno-aura,.zeno,.zeno *,.z-spark,.build-tasks .bt,.build-tasks .bt-ico{animation:none!important;transition:none!important;filter:none!important;opacity:1!important}.z-spark{opacity:.7!important}.iz-confetti-canvas{display:none!important}}

    /* ===== Zeno sample-sites showcase (Swiper 3D coverflow) ===== */
    .zeno-showcase{margin-top:22px;border-top:1px solid #e2e8f0;padding-top:18px}
    .zeno-showcase-head{font-weight:700;font-size:.92rem;color:#1e293b;display:flex;align-items:center;gap:7px;margin-bottom:6px}
    .zeno-showcase-head i{color:#2563eb}
    .zeno-showcase-sub{font-size:.8rem;color:#64748b;margin:0 0 14px}
    .zeno-swiper{width:286px;max-width:100%;margin:0 auto;padding:4px 0 30px;overflow:visible}
    .zeno-swiper .swiper-slide{border-radius:14px;overflow:hidden;background:#fff;border:1px solid #e2e8f0;box-shadow:0 16px 34px rgba(15,23,42,.18)}
    .zeno-card .chrome{display:flex;align-items:center;gap:5px;padding:7px 10px;background:#f1f5f9;border-bottom:1px solid #e2e8f0}
    .zeno-card .chrome b{width:8px;height:8px;border-radius:50%;background:#cbd5e1;display:inline-block}
    .zeno-card .shot{position:relative;width:100%;height:182px;overflow:hidden;background:#fff}
    .zeno-card .shot iframe{position:absolute;top:0;left:0;width:1200px;height:780px;border:0;transform:scale(.2367);transform-origin:top left;pointer-events:none}
    .zeno-card .shot a{position:absolute;inset:0;z-index:3}
    .zeno-card .cap{font-size:.78rem;color:#475569;padding:9px 10px;text-align:center;border-top:1px solid #e2e8f0;font-weight:600;line-height:1.3}
    .zeno-swiper .swiper-pagination{bottom:4px}
    .zeno-swiper .swiper-pagination-bullet{background:#94a3b8}
    .zeno-swiper .swiper-pagination-bullet-active{background:#2563eb}
    /* trust strip to balance the column */
    .zeno-trust{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:6px}
    .zeno-trust div{display:flex;gap:8px;align-items:flex-start;font-size:.82rem;color:#475569}
    .zeno-trust i{color:#16a34a;font-size:1rem;line-height:1.2}

    /* ===== Multi-step wizard ===== */
    .wiz-progress{margin-bottom:22px}
    .wiz-steps{display:flex;align-items:flex-start;justify-content:space-between;position:relative;gap:6px}
    .wiz-node{display:flex;flex-direction:column;align-items:center;gap:6px;flex:1;min-width:0;text-align:center;position:relative;z-index:1}
    .wiz-dot{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;background:#fff;color:#94a3b8;border:2px solid #e2e8f0;transition:background-color .3s,border-color .3s,color .3s,transform .3s}
    .wiz-name{font-size:.8rem;font-weight:600;color:#94a3b8;transition:color .3s;line-height:1.2}
    .wiz-node.is-active .wiz-dot{background:#2563eb;border-color:#2563eb;color:#fff;transform:scale(1.06);box-shadow:0 0 0 4px rgba(37,99,235,.15)}
    .wiz-node.is-active .wiz-name{color:#1e293b}
    .wiz-node.is-done .wiz-dot{background:#2563eb;border-color:#2563eb;color:#fff}
    .wiz-node.is-done .wiz-dot::after{content:"\2713";font-size:.95rem}
    .wiz-node.is-done .wiz-dot{font-size:0}
    .wiz-node.is-done .wiz-name{color:#475569}
    .wiz-bar{height:6px;border-radius:999px;background:#e2e8f0;margin:14px 0 6px;overflow:hidden}
    .wiz-bar span{display:block;height:100%;width:33.33%;border-radius:999px;background:linear-gradient(90deg,#2563eb,#38bdf8);transition:width .35s ease}
    .wiz-count{font-size:.82rem;color:#64748b;text-align:center;margin:0;font-weight:600}
    .wiz-step{display:none}
    .wiz-step.active{display:block;animation:wizIn .28s ease both}
    .wiz-step.active.back{animation:wizInBack .28s ease both}
    @keyframes wizIn{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:none}}
    @keyframes wizInBack{from{opacity:0;transform:translateX(-14px)}to{opacity:1;transform:none}}
    .wiz-nav{display:flex;gap:10px;align-items:center;margin-top:18px}
    .wiz-nav .wiz-next,.wiz-nav .wiz-build{margin-left:auto}
    .wiz-build.ready{animation:wizReady 1.8s ease-in-out infinite}
    @keyframes wizReady{0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.45)}50%{box-shadow:0 0 0 7px rgba(37,99,235,0)}}
    @media (max-width:420px){.wiz-name{font-size:0}.wiz-node.is-active .wiz-name{font-size:.78rem}}
    @media (prefers-reduced-motion: reduce){.wiz-step.active,.wiz-step.active.back{animation:none!important}.wiz-dot,.wiz-bar span,.wiz-name{transition:none!important}.wiz-build.ready{animation:none!important}}
    /* ===== Analyze-my-site helper (Step 1) ===== */
    .iz-analyze{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:18px}
    .iz-analyze-row{display:flex;gap:8px}
    .iz-analyze-row .form-control{flex:1}
    .iz-analyze-row .btn{white-space:nowrap}
    .iz-analyze-note{display:block;margin-top:8px;font-size:12.5px;color:#64748b}
    .iz-analyze-note.err{color:#b91c1c}
    .iz-analyze-note.ok{color:#15803d;font-weight:600}
    .iz-spin{display:inline-block;width:13px;height:13px;border:2px solid #cbd5e1;border-top-color:#2563eb;border-radius:50%;animation:izSpin .7s linear infinite;vertical-align:-2px;margin-right:6px}
    @keyframes izSpin{to{transform:rotate(360deg)}}
    @media (max-width:480px){.iz-analyze-row{flex-direction:column}}
    @media (prefers-reduced-motion: reduce){.iz-spin{animation:none}}
    .form-field-hp{display:none!important;position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important}
  </style>
</head>

<body>
  <?php include './assets/includes/header.php'; ?>

  <!-- Watch-it-build overlay -->
  <div id="buildOverlay" class="build-overlay" role="dialog" aria-modal="true" aria-label="Preparing your website draft">
    <button type="button" class="build-close" id="buildClose" aria-label="Close">&times;</button>
    <div class="build-stage">
      <div class="build-skeleton" aria-hidden="true">
        <div class="sk-row"><span class="sk sk-logo"></span><span class="sk sk-navi"></span></div>
        <span class="sk sk-h1"></span>
        <span class="sk sk-h2"></span>
        <span class="sk sk-btn"></span>
        <div class="sk-cards"><span class="sk sk-card"></span><span class="sk sk-card"></span><span class="sk sk-card"></span></div>
      </div>

      <div class="build-panel" id="buildPanel">
        <div class="zeno-scene" id="zenoScene" aria-hidden="true">
          <span class="zeno-aura"></span>
          <span class="z-spark">✦</span><span class="z-spark">✦</span><span class="z-spark">✦</span><span class="z-spark">✦</span>
          <svg class="zeno" viewBox="0 0 96 108" role="img" aria-label="Zeno the website draft assistant at work">
            <!-- antenna -->
            <line class="z-antenna" x1="48" y1="6" x2="48" y2="16"></line>
            <circle class="z-dot" cx="48" cy="5" r="3"></circle>
            <!-- head/body -->
            <rect class="z-ear" x="6" y="40" width="6" height="20" rx="3"></rect>
            <rect class="z-ear" x="84" y="40" width="6" height="20" rx="3"></rect>
            <rect class="z-body" x="14" y="16" width="68" height="62" rx="18"></rect>
            <rect class="z-screen" x="22" y="26" width="52" height="34" rx="12"></rect>
            <circle class="z-eye" cx="38" cy="42" r="4"></circle>
            <circle class="z-eye" cx="58" cy="42" r="4"></circle>
            <path class="z-smile" d="M40 50 Q48 56 56 50"></path>
            <!-- left arm holding a paintbrush that sweeps -->
            <line class="z-arm" x1="20" y1="64" x2="10" y2="74"></line>
            <g class="z-brush-grp">
              <line class="z-brush-handle" x1="10" y1="74" x2="2" y2="92"></line>
              <circle class="z-brush-tip" cx="1.5" cy="94" r="4"></circle>
            </g>
            <!-- right arm -->
            <line class="z-arm" x1="76" y1="64" x2="86" y2="74"></line>
          </svg>
        </div>
        <h2>Zeno is drafting <span class="build-biz" id="buildBiz">your website</span>…</h2>
        <ul class="build-tasks" id="buildTasks">
          <li class="bt"><span class="bt-ico"></span> Getting to know your business</li>
          <li class="bt"><span class="bt-ico"></span> Picking colors &amp; fonts that fit you</li>
          <li class="bt"><span class="bt-ico"></span> Writing your homepage copy</li>
          <li class="bt"><span class="bt-ico"></span> Designing your services &amp; contact</li>
          <li class="bt"><span class="bt-ico"></span> Making it perfect on phones</li>
        </ul>
        <p class="build-step" id="buildStep"></p>
        <div class="build-bar"><span id="buildBarFill"></span></div>
        <div class="build-countdown" id="buildCountdown" role="timer" aria-live="polite">Estimated reveal in <b>2:00</b></div>
        <p class="build-hint">Hi, I'm <strong style="color:#7dd3fc">Zeno</strong> — keep this tab open for the live reveal. We'll email your draft too.</p>
      </div>
    </div>

    <div class="build-reveal" id="buildReveal">
      <div class="reveal-bar">
        <span class="rb-msg"><i class="bi bi-stars"></i> Here's your draft! &mdash; Zeno</span>
        <span class="reveal-actions">
          <a id="claimBtn" class="btn btn-primary btn-sm" href="#">Claim this site &rarr;</a>
          <a class="btn btn-outline-secondary btn-sm" href="tel:314-886-6356"><i class="bi bi-telephone"></i> (314) 886-6356</a>
        </span>
      </div>
      <div class="reveal-frame-wrap">
        <iframe id="revealFrame" title="Your website preview" loading="lazy"></iframe>
      </div>
    </div>
  </div>

  <main id="main">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <h2>Website Drafter</h2>
          <ol>
            <li><a href="index.php">Home</a></li>
            <li>Website Drafter</li>
          </ol>
        </div>
      </div>
    </section>

    <!-- Builder Section -->
    <section class="ai-builder-section" style="padding: 60px 0;">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <div class="section-title">
              <div class="badge bg-success text-white px-3 py-2 rounded-pill mb-3" style="font-weight: 500; letter-spacing: 0.5px;">Free Preview &bull; No Credit Card Required</div>
              <h2>Start With a Free Website Draft — Live in 2 Minutes</h2>
              <p>Tell us about your business and we'll put together a real, live draft of your website — no page builders, no blank page to stare at. Like what you see? We host it for you, right here.</p>
            </div>

<?php if ($genExhausted): ?>
            <div class="card shadow-sm"><div class="card-body p-4 text-center">
              <div style="font-size:42px;line-height:1" aria-hidden="true">🎉</div>
              <h3 class="mt-2">Ready to make it yours?</h3>
              <p class="text-muted mb-4">Claim your draft and you can edit and build as much as you like — hosting, your own domain, a free professional email and more, all set up for you.</p>
              <a class="btn btn-primary btn-lg" href="claim-site.php"><i class="bi bi-magic"></i> Claim your site</a>
            </div></div>
<?php else: ?>
            <div class="card shadow-sm">
              <div class="card-body p-4">
                <form id="aiBuilderForm" novalidate>
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES); ?>">
                  <?php echo SpamProtection::generateHoneypot('site_builder'); ?>
                  <?php echo SpamProtection::generateTimestamp('site_builder'); ?>

                  <div class="wiz-progress">
                    <div class="wiz-steps">
                      <div class="wiz-node is-active" data-node="1"><span class="wiz-dot">1</span><span class="wiz-name">Business</span></div>
                      <div class="wiz-node" data-node="2"><span class="wiz-dot">2</span><span class="wiz-name">Style</span></div>
                      <div class="wiz-node" data-node="3"><span class="wiz-dot">3</span><span class="wiz-name">Send it</span></div>
                    </div>
                    <div class="wiz-bar"><span id="wizBarFill"></span></div>
                    <p class="wiz-count" id="wizCount" aria-live="polite">Step 1 of 3 — tell us who you are</p>
                  </div>

                  <div class="wiz-step active" data-step="1">
                    <div class="iz-analyze">
                      <label class="form-label mb-1">Already have a website? <span class="text-muted fw-normal">(optional)</span></label>
                      <div class="iz-analyze-row">
                        <input type="text" inputmode="url" class="form-control" id="izAnalyzeUrl" placeholder="yourcurrentsite.com" autocomplete="off">
                        <button type="button" class="btn btn-outline-primary" id="izAnalyzeBtn"><i class="bi bi-magic"></i> Analyze</button>
                      </div>
                      <small class="iz-analyze-note" id="izAnalyzeNote">Paste your current site and Site Drafter will scan key pages for services, pricing, and contact details — edit anything you like.</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Business Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="business_name" maxlength="120" required>
                      <small class="text-muted">Just the name customers know — no LLC/Inc needed.</small>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Tell us about your business <span class="text-danger">*</span></label>
                      <textarea class="form-control" name="business_description" rows="7" style="min-height:160px;resize:vertical" maxlength="8000"
                        placeholder="e.g. We're a [type of business] in [city] offering [your main services or products]. Tell us anything you want on the site — services, hours, pricing, photos, online booking, contact info — plus the look or tone you're going for." required></textarea>
                      <small class="text-muted">Describe your business and tell us exactly what to include — the more detail, the better your draft.</small>
                    </div>

                    <div class="wiz-nav">
                      <button type="button" class="btn btn-primary btn-lg wiz-next" data-target="2">Next <i class="bi bi-arrow-right"></i></button>
                    </div>
                  </div>

                  <div class="wiz-step" data-step="2">
                  <div class="mb-3">
                    <label class="form-label d-block">Style / Vibe <small class="text-muted fw-normal">(a direction — your real preview may vary)</small></label>
                    <div class="row g-2">
                      <div class="col-6 col-md-4">
                        <label class="vibe-card vibe-auto">
                          <input type="radio" name="style_vibe" value="" checked>
                          <span class="vibe-card-inner">
                            <span class="vibe-swatches"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></span>
                            <span class="vibe-aa"><i class="bi bi-magic"></i></span>
                            <span class="vibe-label">Let Site Drafter choose</span>
                          </span>
                        </label>
                      </div>
                      <div class="col-6 col-md-4">
                        <label class="vibe-card vibe-clean">
                          <input type="radio" name="style_vibe" value="Clean &amp; Modern">
                          <span class="vibe-card-inner">
                            <span class="vibe-swatches"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></span>
                            <span class="vibe-aa">Aa</span>
                            <span class="vibe-label">Clean &amp; Modern</span>
                          </span>
                        </label>
                      </div>
                      <div class="col-6 col-md-4">
                        <label class="vibe-card vibe-warm">
                          <input type="radio" name="style_vibe" value="Warm &amp; Welcoming">
                          <span class="vibe-card-inner">
                            <span class="vibe-swatches"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></span>
                            <span class="vibe-aa">Aa</span>
                            <span class="vibe-label">Warm &amp; Welcoming</span>
                          </span>
                        </label>
                      </div>
                      <div class="col-6 col-md-4">
                        <label class="vibe-card vibe-bold">
                          <input type="radio" name="style_vibe" value="Bold &amp; Colorful">
                          <span class="vibe-card-inner">
                            <span class="vibe-swatches"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></span>
                            <span class="vibe-aa">Aa</span>
                            <span class="vibe-label">Bold &amp; Colorful</span>
                          </span>
                        </label>
                      </div>
                      <div class="col-6 col-md-4">
                        <label class="vibe-card vibe-elegant">
                          <input type="radio" name="style_vibe" value="Elegant &amp; Minimal">
                          <span class="vibe-card-inner">
                            <span class="vibe-swatches"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></span>
                            <span class="vibe-aa">Aa</span>
                            <span class="vibe-label">Elegant &amp; Minimal</span>
                          </span>
                        </label>
                      </div>
                      <div class="col-6 col-md-4">
                        <label class="vibe-card vibe-corporate">
                          <input type="radio" name="style_vibe" value="Professional &amp; Corporate">
                          <span class="vibe-card-inner">
                            <span class="vibe-swatches"><span class="s1"></span><span class="s2"></span><span class="s3"></span><span class="s4"></span></span>
                            <span class="vibe-aa">Aa</span>
                            <span class="vibe-label">Professional &amp; Corporate</span>
                          </span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Have a logo? Upload it <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <input type="file" class="form-control" id="izLogoFile" accept="image/png,image/jpeg,image/webp,image/gif" style="max-width:300px">
                      <span id="izLogoSwatches" class="d-flex gap-1"></span>
                    </div>
                    <small class="text-muted d-block mt-1" id="izLogoNote">We'll feature your logo on the site — and if you let Site Drafter choose the style, we'll match your brand colors.</small>
                  </div>

                    <div class="wiz-nav">
                      <button type="button" class="btn btn-outline-secondary btn-lg wiz-back" data-target="1"><i class="bi bi-arrow-left"></i> Back</button>
                      <button type="button" class="btn btn-primary btn-lg wiz-next" data-target="3">Next <i class="bi bi-arrow-right"></i></button>
                    </div>
                  </div>

                  <div class="wiz-step" data-step="3">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" name="contact_email" required>
                      <small class="text-muted">We'll email you the preview link.</small>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Phone</label>
                      <input type="tel" class="form-control" name="contact_phone" placeholder="(314) 555-1234">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Do you already have a domain?</label>
                    <input type="text" class="form-control" name="domain" placeholder="yourbusiness.com (optional)">
                    <small class="text-muted">If you have one, we can point it at your new site when you're ready.</small>
                  </div>

                  <?php if (!empty($recaptchaSiteKey)): ?>
                  <div class="recaptcha-container mb-3" id="recaptcha-placeholder">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey, ENT_QUOTES); ?>" style="display:none;"></div>
                  </div>
                  <?php endif; ?>

                  <div class="wiz-nav">
                    <button type="button" class="btn btn-outline-secondary btn-lg wiz-back" data-target="2"><i class="bi bi-arrow-left"></i> Back</button>
                    <button type="submit" class="btn btn-primary btn-lg wiz-build" id="aiBuilderSubmit">
                      <i class="bi bi-magic"></i> Get My Free Draft
                    </button>
                  </div>
                  <div class="text-center text-muted small mt-3">
                    <i class="bi bi-shield-check text-success"></i> No credit card required. Free, fast preview.
                  </div>

                  <div id="aiBuilderMessage" class="mt-3"></div>
                  </div>
                </form>
              </div>
            </div>
<?php endif; ?>
          </div>

          <div class="col-lg-5">
            <div class="card bg-light mt-4 mt-lg-0">
              <div class="card-body p-4">
                <div class="ai-bot-wrap">
                  <svg class="ai-bot" viewBox="0 0 48 48" role="img" aria-label="Website draft assistant">
                    <line class="ai-bot-antenna" x1="24" y1="6" x2="24" y2="12"></line>
                    <circle class="ai-bot-dot" cx="24" cy="5" r="2.6"></circle>
                    <rect class="ai-bot-body" x="9" y="12" width="30" height="26" rx="7"></rect>
                    <rect class="ai-bot-screen" x="13" y="17" width="22" height="13" rx="4"></rect>
                    <circle class="ai-bot-eye" cx="19" cy="23.5" r="2.3"></circle>
                    <circle class="ai-bot-eye" cx="29" cy="23.5" r="2.3"></circle>
                    <line class="ai-bot-antenna" x1="16" y1="38" x2="16" y2="42"></line>
                    <line class="ai-bot-antenna" x1="32" y1="38" x2="32" y2="42"></line>
                  </svg>
                  <div class="ai-bot-text">
                    <strong>Hi, I'm Zeno 👋</strong>
                    <span>Your website draft assistant — tell me about your business and I'll put together your draft</span>
                  </div>
                </div>
                <ul class="list-unstyled mt-3">
                  <li class="mb-3">
                    <i class="bi bi-1-circle text-primary"></i>
                    <strong>Tell me about your business</strong> — a sentence or two is enough
                  </li>
                  <li class="mb-3">
                    <i class="bi bi-2-circle text-primary"></i>
                    <strong>I draft your site</strong> — a real, live draft in about 2 minutes
                  </li>
                  <li class="mb-3">
                    <i class="bi bi-3-circle text-primary"></i>
                    <strong>Claim &amp; go live</strong> — we host it for you on your own domain
                  </li>
                </ul>

                <div class="zeno-showcase" id="zenoShowcase">
                  <div class="zeno-showcase-head"><i class="bi bi-stars"></i> A few drafts I've put together</div>
                  <p class="zeno-showcase-sub">Real, live drafts — each one started from a single sentence.</p>
                  <div class="swiper zeno-swiper">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide"><div class="zeno-card"><div class="chrome"><b></b><b></b><b></b></div><div class="shot"><iframe src="/previews/samples/serenity-massage-therapy/" title="Serenity Massage Therapy" scrolling="no" tabindex="-1" aria-hidden="true" loading="lazy"></iframe><a href="/previews/samples/serenity-massage-therapy/" target="_blank" rel="noopener" aria-label="Open Serenity Massage Therapy"></a></div><div class="cap">Serenity Massage Therapy<br>Warm &amp; Welcoming</div></div></div>
                      <div class="swiper-slide"><div class="zeno-card"><div class="chrome"><b></b><b></b><b></b></div><div class="shot"><iframe src="/previews/samples/riverside-dental-care/" title="Riverside Dental Care" scrolling="no" tabindex="-1" aria-hidden="true" loading="lazy"></iframe><a href="/previews/samples/riverside-dental-care/" target="_blank" rel="noopener" aria-label="Open Riverside Dental Care"></a></div><div class="cap">Riverside Dental Care<br>Clean &amp; Modern</div></div></div>
                      <div class="swiper-slide"><div class="zeno-card"><div class="chrome"><b></b><b></b><b></b></div><div class="shot"><iframe src="/previews/samples/el-camino-taqueria/" title="El Camino Taqueria" scrolling="no" tabindex="-1" aria-hidden="true" loading="lazy"></iframe><a href="/previews/samples/el-camino-taqueria/" target="_blank" rel="noopener" aria-label="Open El Camino Taqueria"></a></div><div class="cap">El Camino Taqueria<br>Bold &amp; Colorful</div></div></div>
                      <div class="swiper-slide"><div class="zeno-card"><div class="chrome"><b></b><b></b><b></b></div><div class="shot"><iframe src="/previews/samples/atelier-noir-salon/" title="Atelier Noir Salon" scrolling="no" tabindex="-1" aria-hidden="true" loading="lazy"></iframe><a href="/previews/samples/atelier-noir-salon/" target="_blank" rel="noopener" aria-label="Open Atelier Noir Salon"></a></div><div class="cap">Atelier Noir Salon<br>Elegant &amp; Minimal</div></div></div>
                      <div class="swiper-slide"><div class="zeno-card"><div class="chrome"><b></b><b></b><b></b></div><div class="shot"><iframe src="/previews/samples/coastal-tax-accounting/" title="Coastal Tax &amp; Accounting" scrolling="no" tabindex="-1" aria-hidden="true" loading="lazy"></iframe><a href="/previews/samples/coastal-tax-accounting/" target="_blank" rel="noopener" aria-label="Open Coastal Tax &amp; Accounting"></a></div><div class="cap">Coastal Tax &amp; Accounting<br>Professional &amp; Corporate</div></div></div>
                    </div>
                    <div class="swiper-pagination"></div>
                  </div>
                </div>

                <hr>
                <div class="zeno-trust">
                  <div><i class="bi bi-lightning-charge-fill"></i> <span>Live preview in ~2 minutes</span></div>
                  <div><i class="bi bi-hdd-network-fill"></i> <span>Hosted right here on Izende</span></div>
                  <div><i class="bi bi-phone-fill"></i> <span>Looks great on every phone</span></div>
                  <div><i class="bi bi-geo-alt-fill"></i> <span>Built &amp; supported in St. Louis</span></div>
                </div>

                <hr>

                <h5>Questions first?</h5>
                <p class="mb-2">
                  <i class="bi bi-telephone"></i>
                  <a href="tel:314-886-6356">+1 (314) 886-6356</a>
                </p>
                <p>
                  <i class="bi bi-envelope"></i>
                  <a href="mailto:support@izendestudioweb.com">support@izendestudioweb.com</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include './assets/includes/footer.php'; ?>

  <script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>">
  /* ---- Multi-step wizard nav ---- */
  (function(){
      var form = document.getElementById('aiBuilderForm');
      if (!form) return;
      var steps  = Array.prototype.slice.call(form.querySelectorAll('.wiz-step'));
      var nodes  = Array.prototype.slice.call(form.querySelectorAll('.wiz-node'));
      var fill   = document.getElementById('wizBarFill');
      var count  = document.getElementById('wizCount');
      var labels = ['tell us who you are', 'pick a look', 'where do we send it'];
      var total  = steps.length;
      var current = 1;

      function showStep(n, isBack, doFocus){
          n = Math.max(1, Math.min(total, n));
          steps.forEach(function(s){
              var sn = parseInt(s.getAttribute('data-step'), 10);
              var on = (sn === n);
              s.classList.toggle('active', on);
              s.classList.toggle('back', on && !!isBack);
          });
          nodes.forEach(function(nd){
              var nn = parseInt(nd.getAttribute('data-node'), 10);
              nd.classList.toggle('is-active', nn === n);
              nd.classList.toggle('is-done', nn < n);
          });
          if (fill)  fill.style.width = Math.round((n / total) * 100) + '%';
          if (count) count.textContent = 'Step ' + n + ' of ' + total + ' — ' + labels[n - 1];
          var build = form.querySelector('.wiz-build');
          if (build) build.classList.toggle('ready', n === total);
          current = n;
          if (doFocus !== false){
              var first = steps[n - 1].querySelector('input,textarea,select');
              if (first) { try { first.focus({ preventScroll: true }); } catch(_){} }
          }
      }

      function validateStep(n){
          var step = steps[n - 1];
          if (!step) return true;
          var fields = step.querySelectorAll('input,textarea,select');
          for (var i = 0; i < fields.length; i++){
              if (!fields[i].checkValidity()){ fields[i].reportValidity(); return false; }
          }
          return true;
      }

      // Used by the submit handler: validate every step up to n, jumping to the first invalid one.
      form.wizValidateThrough = function(n){
          for (var s = 1; s <= n; s++){
              var fields = steps[s - 1].querySelectorAll('input,textarea,select');
              for (var i = 0; i < fields.length; i++){
                  if (!fields[i].checkValidity()){ showStep(s, false); fields[i].reportValidity(); return false; }
              }
          }
          return true;
      };

      form.addEventListener('click', function(e){
          var next = e.target.closest ? e.target.closest('.wiz-next') : null;
          var back = e.target.closest ? e.target.closest('.wiz-back') : null;
          if (next){ e.preventDefault(); if (validateStep(current)) showStep(parseInt(next.getAttribute('data-target'), 10), false); }
          else if (back){ e.preventDefault(); showStep(parseInt(back.getAttribute('data-target'), 10), true); }
      });

      // Enter on a single-line input advances instead of submitting early.
      form.addEventListener('keydown', function(e){
          if (e.key === 'Enter' && e.target.tagName === 'INPUT' && current < total){
              e.preventDefault();
              if (validateStep(current)) showStep(current + 1, false);
          }
      });

      // Drop legal suffixes from the business name (LLC, Inc., Corp., Ltd., etc.) on blur.
      var bn = form.querySelector('[name="business_name"]');
      if (bn){
          bn.addEventListener('blur', function(){
              var v = bn.value.replace(/[\s,]+(?:LLC|L\.L\.C\.?|Inc\.?|Incorporated|Corp\.?|Corporation|Co\.|Ltd\.?|LLP|PLLC)\.?\s*$/i, '').trim();
              if (v !== bn.value) bn.value = v;
          });
      }

      showStep(1, false, false);
  })();

  /* ---- Analyze an existing site -> draft the description ---- */
  (function(){
      var btn   = document.getElementById('izAnalyzeBtn');
      var urlEl = document.getElementById('izAnalyzeUrl');
      var note  = document.getElementById('izAnalyzeNote');
      var form  = document.getElementById('aiBuilderForm');
      if (!btn || !urlEl || !form) { return; }
      var descEl = form.querySelector('[name="business_description"]');
      var nameEl = form.querySelector('[name="business_name"]');
      var csrfEl = form.querySelector('[name="csrf_token"]');

      function setNote(html, cls){ note.className = 'iz-analyze-note' + (cls ? ' ' + cls : ''); note.innerHTML = html; }

      function run(){
          var url = (urlEl.value || '').trim();
          if (url.length < 4){ setNote('Enter your website address first.', 'err'); urlEl.focus(); return; }
          var lbl = btn.innerHTML;
          btn.disabled = true; btn.innerHTML = 'Analyzing…';
          setNote('<span class="iz-spin"></span>Reading your site and its pages… up to ~20 seconds.', '');
          fetch('api/analyze-site.php', {
              method: 'POST', headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ url: url, csrf_token: csrfEl ? csrfEl.value : '' })
          }).then(function(r){ return r.json(); }).then(function(j){
              btn.disabled = false; btn.innerHTML = lbl;
              if (j && j.success){
                  if (descEl){ descEl.value = j.description; }
                  if (nameEl && !nameEl.value.trim() && j.business_name){ nameEl.value = j.business_name; }
                  if (j.business_address){ window.izBusinessAddress = j.business_address; }
                  var pages = j.pages_crawled ? ' Scanned ' + j.pages_crawled + ' page' + (j.pages_crawled === 1 ? '.' : 's.') : '';
                  setNote('<i class="bi bi-check-circle-fill"></i> Done — review and edit the services/pricing brief below.' + pages, 'ok');
                  if (descEl){ try { descEl.focus({ preventScroll: true }); } catch(_){} }
              } else {
                  setNote((j && j.message) || "Couldn't read that site — just tell us about your business below.", 'err');
              }
          }).catch(function(){
              btn.disabled = false; btn.innerHTML = lbl;
              setNote("Network error — just tell us about your business below.", 'err');
          });
      }
      btn.addEventListener('click', run);
      urlEl.addEventListener('keydown', function(e){ if (e.key === 'Enter'){ e.preventDefault(); e.stopPropagation(); run(); } });
  })();

  /* ---- Logo upload -> use their logo + match colors ---- */
  (function(){
      var fileEl = document.getElementById('izLogoFile');
      var note   = document.getElementById('izLogoNote');
      var sw     = document.getElementById('izLogoSwatches');
      var form   = document.getElementById('aiBuilderForm');
      if (!fileEl || !form) { return; }
      var csrfEl = form.querySelector('[name="csrf_token"]');
      fileEl.addEventListener('change', function(){
          var file = fileEl.files && fileEl.files[0];
          if (!file) { return; }
          if (file.size > 5 * 1024 * 1024) { note.textContent = 'That image is over 5 MB — please pick a smaller one.'; note.className = 'text-danger d-block mt-1'; fileEl.value=''; return; }
          note.innerHTML = '<span class="iz-spin"></span>Uploading your logo…'; note.className = 'text-muted d-block mt-1';
          if (sw) sw.innerHTML = '';
          var fd = new FormData();
          fd.append('logo', file);
          fd.append('csrf_token', csrfEl ? csrfEl.value : '');
          fetch('api/upload-logo.php', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(j){
                if (j && j.success){
                    window.izLogoUrl = j.url;
                    window.izBrandColors = j.brand_colors || [];
                    var swatch = '';
                    (j.brand_colors || []).forEach(function(c){ swatch += '<span style="display:inline-block;width:16px;height:16px;border-radius:4px;border:1px solid #cbd5e1;background:'+c+'"></span>'; });
                    if (sw) sw.innerHTML = swatch;
                    note.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Logo added' + ((j.brand_colors && j.brand_colors.length) ? ' — we’ll match these colors if you let Site Drafter choose the style.' : '.');
                    note.className = 'text-muted d-block mt-1';
                } else {
                    window.izLogoUrl = ''; window.izBrandColors = [];
                    note.textContent = (j && j.message) || 'Could not use that image — try a PNG or JPG.';
                    note.className = 'text-danger d-block mt-1';
                    fileEl.value = '';
                }
            }).catch(function(){
                note.textContent = 'Upload failed — please try again.';
                note.className = 'text-danger d-block mt-1';
            });
      });
  })();

  const _aiForm = document.getElementById('aiBuilderForm');
  if (_aiForm) _aiForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      const form = this;
      if (typeof form.wizValidateThrough === 'function' && !form.wizValidateThrough(3)) { return; }
      const submitBtn = document.getElementById('aiBuilderSubmit');
      const messageDiv = document.getElementById('aiBuilderMessage');
      const formData = new FormData(form);

      const data = {
          business_name: formData.get('business_name'),
          business_description: formData.get('business_description'),
          style_vibe: formData.get('style_vibe'),
          contact_email: formData.get('contact_email'),
          contact_phone: formData.get('contact_phone'),
          domain: formData.get('domain'),
          csrf_token: formData.get('csrf_token'),
          form_timestamp: formData.get('form_timestamp'),
          'g-recaptcha-response': formData.get('g-recaptcha-response'),
          logo_url: window.izLogoUrl || '',
          brand_colors: window.izBrandColors || [],
          business_address: window.izBusinessAddress || ''
      };

      // Include the session-bound honeypot field without hardcoding its randomized name.
      for (const [key, value] of formData.entries()) {
          if (key.startsWith('website_url_')) {
              data[key] = value;
          }
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Preparing your draft…';
      messageDiv.textContent = '';

      try {
          const response = await fetch('api/site-builder-leads.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(data)
          });

          const rawText = await response.text();
          let result = {};
          try {
              result = JSON.parse(rawText);
          } catch (parseErr) {
              result = { success: false, message: rawText || 'Unexpected response from server.' };
          }

          if (response.ok && result.success) {
              if (typeof gtag !== 'undefined') {
                  gtag('event', 'ai_builder_submitted', { 'business_name': data.business_name });
              }
              startBuildExperience(result.lead_id, data.business_name, data.contact_email);
          } else if (result.limit) {
              const claim = result.claim_url || 'claim-site.php';
              messageDiv.innerHTML = `<div class="alert alert-info"><i class="bi bi-stars"></i> ${result.message || "Ready to make your draft yours?"} <a href="${claim}" class="alert-link fw-bold">Claim your site →</a></div>`;
              submitBtn.disabled = false;
              submitBtn.innerHTML = '<i class="bi bi-magic"></i> Get My Free Draft';
          } else {
              const msg = result.message || `Error ${response.status}: ${rawText || 'Unable to submit right now. Please call us directly.'}`;
              messageDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${msg}</div>`;
              submitBtn.disabled = false;
              submitBtn.innerHTML = '<i class="bi bi-magic"></i> Get My Free Draft';
          }
      } catch (error) {
          messageDiv.innerHTML = `<div class="alert alert-danger">An error occurred: ${error.message}. Please try again or call us directly.</div>`;
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-magic"></i> Get My Free Draft';
      }
  });

  // ===== Watch-it-build experience =====
  (function () {
    const overlay  = document.getElementById('buildOverlay');
    const panel    = document.getElementById('buildPanel');
    const reveal   = document.getElementById('buildReveal');
    const stepEl   = document.getElementById('buildStep');
    const bizEl    = document.getElementById('buildBiz');
    const barFill  = document.getElementById('buildBarFill');
    const countdownEl = document.getElementById('buildCountdown');
    const frame    = document.getElementById('revealFrame');
    const claimBtn = document.getElementById('claimBtn');
    const closeBtn = document.getElementById('buildClose');

    let pollTimer = null, stepTimer = null, progressTimer = null, countdownTimer = null, countdownStartedAt = 0, elapsed = 0, progress = 4;
    let currentLeadId = null, currentBiz = '';
    let building = false;

    // While we're actively generating, warn before leaving (they'd still get the email, but
    // closing mid-build means they miss the live reveal + one-click claim).
    window.addEventListener('beforeunload', function (e) {
      if (!building) { return; }
      e.preventDefault();
      e.returnValue = '';
      return '';
    });

    closeBtn.addEventListener('click', stopAndClose);

    claimBtn.addEventListener('click', function (e) {
      e.preventDefault();
      if (!currentLeadId) { return; }
      claimBtn.textContent = 'Claiming…';
      claimBtn.classList.add('disabled');
      fetch('api/claim-site.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lead_id: currentLeadId })
      }).then(function (r) { return r.json(); }).then(function (res) {
        const bar = document.querySelector('.reveal-bar');
        if (res && res.success) {
          if (typeof gtag !== 'undefined') { gtag('event', 'ai_builder_site_claimed'); }
          const msg = bar.querySelector('.rb-msg');
          if (msg) { msg.innerHTML = '<i class="bi bi-bag-check-fill"></i> Locking in your site — choose your plan…'; }
          // Off to the plan chooser (static / WordPress / managed); checkout creates the account and provisions automatically.
          window.location.href = '/claim-site.php';
        } else {
          claimBtn.textContent = 'Claim this site →';
          claimBtn.classList.remove('disabled');
          alert((res && res.message) || 'Could not record your claim — please call (314) 886-6356.');
        }
      }).catch(function () {
        claimBtn.textContent = 'Claim this site →';
        claimBtn.classList.remove('disabled');
        alert('Could not record your claim — please call (314) 886-6356.');
      });
    });

    function stopAndClose() {
      building = false;
      clearInterval(pollTimer); clearInterval(stepTimer); clearInterval(progressTimer); clearInterval(countdownTimer);
      overlay.classList.remove('show');
      reveal.classList.remove('show');
      document.body.style.overflow = '';
      const btn = document.getElementById('aiBuilderSubmit');
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-magic"></i> Get My Free Draft'; }
    }

    window.startBuildExperience = function (leadId, biz, email) {
      building = true;
      currentLeadId = leadId;
      currentBiz = biz || 'your website';
      elapsed = 0;
      progress = 4;
      bizEl.textContent = currentBiz;
      barFill.style.width = '4%';
      overlay.classList.add('show');
      document.body.style.overflow = 'hidden';
      overlay.focus && overlay.focus();

      // Checklist: tasks check off as the fake-but-believable progress advances;
      // the last task stays "in progress" until the real preview lands.
      const tasks = Array.prototype.slice.call(document.querySelectorAll('#buildTasks .bt'));
      const taskMarks = [15, 35, 55, 72]; // progress % at which tasks 0..3 complete (task 4 finishes on reveal)
      function renderTasks(doneCount) {
        tasks.forEach(function (li, i) {
          li.classList.toggle('done', i < doneCount);
          li.classList.toggle('active', i === doneCount);
        });
      }
      renderTasks(0);
      stepEl.textContent = '';
      startCountdown(120);

      // Fake-but-believable progress: eases toward 92%, real completion finishes it.
      progressTimer = setInterval(function () {
        if (progress < 92) { progress += Math.max(0.4, (92 - progress) * 0.04); barFill.style.width = progress.toFixed(1) + '%'; }
        var dc = 0; for (var k = 0; k < taskMarks.length; k++) { if (progress >= taskMarks[k]) dc++; }
        renderTasks(dc);
      }, 1000);

      // If we never got a lead id, fall back to the email path.
      if (!leadId) { return fallbackToEmail(); }

      pollTimer = setInterval(function () { poll(leadId); }, 3500);
      poll(leadId);
    };

    function startCountdown(seconds) {
      clearInterval(countdownTimer);
      countdownStartedAt = Date.now();
      function render() {
        if (!countdownEl) { return; }
        var left = Math.max(0, seconds - Math.floor((Date.now() - countdownStartedAt) / 1000));
        if (left > 0) {
          var m = Math.floor(left / 60);
          var s = String(left % 60).padStart(2, '0');
          countdownEl.innerHTML = 'Estimated reveal in <b>' + m + ':' + s + '</b>';
        } else {
          countdownEl.innerHTML = '<b>Finalizing your draft…</b>';
        }
      }
      render();
      countdownTimer = setInterval(render, 1000);
    }

    function poll(leadId) {
      elapsed += 3.5;
      if (elapsed > 360) { return fallbackToEmail(); } // ~6 min ceiling
      fetch('api/preview-status.php?id=' + encodeURIComponent(leadId), { cache: 'no-store' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res || !res.success) { return; }
          const recoveredUrl = res.preview_url || (res.preview_slug ? '/previews/' + encodeURIComponent(res.preview_slug) + '/' : '');
          if (recoveredUrl && ['preview_live', 'claimed', 'converted'].includes(res.status)) { revealSite(recoveredUrl); }
          else if (res.preview_url && res.status !== 'failed') { revealSite(res.preview_url); }
          else if (res.status === 'failed') { fallbackToEmail(true); }
        })
        .catch(function () { /* transient — keep polling */ });
    }

    function revealSite(url) {
      building = false;
      clearInterval(pollTimer); clearInterval(stepTimer); clearInterval(progressTimer); clearInterval(countdownTimer);
      barFill.style.width = '100%';
      Array.prototype.forEach.call(document.querySelectorAll('#buildTasks .bt'), function (li) { li.classList.remove('active'); li.classList.add('done'); });
      stepEl.textContent = 'Your draft is ready 🎉';
      if (countdownEl) { countdownEl.innerHTML = '<b>Ready now</b>'; }
      frame.src = url;
      frame.addEventListener('load', function () { frame.classList.add('sharp'); }, { once: true });
      setTimeout(function () { reveal.classList.add('show'); burstConfetti(); }, 450);
      if (typeof gtag !== 'undefined') { gtag('event', 'ai_builder_preview_revealed'); }
    }

    function burstConfetti() {
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
      if (document.querySelector('.iz-confetti-canvas')) { return; }
      const canvas = document.createElement('canvas');
      canvas.className = 'iz-confetti-canvas';
      canvas.setAttribute('aria-hidden', 'true');
      document.body.appendChild(canvas);
      const ctx = canvas.getContext('2d');
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      const palette = ['#2563eb', '#38bdf8', '#0ea5e9', '#1d4ed8', '#fbbf24', '#ffffff'];
      let w = 0, h = 0, start = performance.now();
      function size() {
        w = window.innerWidth; h = window.innerHeight;
        canvas.width = Math.floor(w * dpr); canvas.height = Math.floor(h * dpr);
        canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      }
      size();
      const pieces = Array.from({ length: 80 }, function (_, i) {
        const angle = (-Math.PI / 2) + (Math.random() - 0.5) * 1.35;
        const speed = 7 + Math.random() * 8;
        return {
          x: w * (0.5 + (Math.random() - 0.5) * 0.18),
          y: h * 0.22,
          vx: Math.cos(angle) * speed + (Math.random() - 0.5) * 2.2,
          vy: Math.sin(angle) * speed,
          r: 4 + Math.random() * 5,
          rot: Math.random() * Math.PI,
          vr: (Math.random() - 0.5) * 0.24,
          color: palette[i % palette.length],
          shape: Math.random() > 0.45 ? 'rect' : 'circle'
        };
      });
      function draw(now) {
        const age = now - start;
        ctx.clearRect(0, 0, w, h);
        pieces.forEach(function (p) {
          p.vy += 0.22; p.vx *= 0.992; p.x += p.vx; p.y += p.vy; p.rot += p.vr;
          const alpha = Math.max(0, 1 - age / 2500);
          ctx.save();
          ctx.globalAlpha = alpha;
          ctx.translate(p.x, p.y);
          ctx.rotate(p.rot);
          ctx.fillStyle = p.color;
          if (p.shape === 'rect') { ctx.fillRect(-p.r, -p.r * 0.55, p.r * 2, p.r * 1.1); }
          else { ctx.beginPath(); ctx.arc(0, 0, p.r * 0.72, 0, Math.PI * 2); ctx.fill(); }
          ctx.restore();
        });
        if (age < 2500) { requestAnimationFrame(draw); }
        else { canvas.remove(); window.removeEventListener('resize', size); }
      }
      window.addEventListener('resize', size, { passive: true });
      requestAnimationFrame(draw);
    }

    function fallbackToEmail(failed) {
      building = false;
      clearInterval(pollTimer); clearInterval(stepTimer); clearInterval(progressTimer); clearInterval(countdownTimer);
      panel.querySelector('h2').textContent = failed ? 'Almost there' : 'Still polishing…';
      stepEl.textContent = "I'll email your draft the moment it's ready — check your inbox shortly. — Zeno";
      barFill.style.width = '100%';
      if (countdownEl) { countdownEl.innerHTML = '<b>Email fallback active</b>'; }
      const hint = panel.querySelector('.build-hint');
      if (hint) { hint.innerHTML = 'You can close this window. <a href="#" id="buildDone" style="color:#7dd3fc">Back to site</a>'; }
      const done = document.getElementById('buildDone');
      if (done) { done.addEventListener('click', function (e) { e.preventDefault(); stopAndClose(); }); }
    }
  })();

  // ===== Zeno's sample-sites showcase (Swiper 3D coverflow) =====
  (function () {
    if (typeof Swiper === 'undefined' || !document.querySelector('.zeno-swiper')) { return; }
    const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    new Swiper('.zeno-swiper', {
      effect: 'cards',
      grabCursor: true,
      rewind: true,               // cards effect + loop stalls autoplay; rewind cycles cleanly
      autoplay: reduce ? false : { delay: 3200, disableOnInteraction: false, pauseOnMouseEnter: true },
      cardsEffect: { perSlideOffset: 9, perSlideRotate: 3, rotate: true, slideShadows: true },
      pagination: { el: '.zeno-swiper .swiper-pagination', clickable: true }
    });
  })();
  </script>

</body>
</html>
