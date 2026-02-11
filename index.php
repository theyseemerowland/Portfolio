<?php
if (isset($_GET['telemetry'])) {
    header('Content-Type: application/json');
    $stats = ['used' => '0.00', 'total' => '0.00', 'cpu' => '0'];
    if (file_exists("/proc/loadavg")) {
        $load = sys_getloadavg();
        $stats['cpu'] = max(3, min(100, round($load[0] * 10, 1))); 
    } else { $stats['cpu'] = rand(5, 12); }
    if (file_exists("/proc/meminfo")) {
        $data = file_get_contents("/proc/meminfo");
        $meminfo = [];
        foreach (explode("\n", $data) as $line) {
            if (strpos($line, ':')) {
                list($key, $val) = explode(":", $line);
                $meminfo[trim($key)] = (int) trim(str_replace('kB', '', $val)); } }
        $total = $meminfo['MemTotal'];
        $available = $meminfo['MemAvailable'] ?? ($meminfo['MemFree'] + ($meminfo['Buffers'] ?? 0) + ($meminfo['Cached'] ?? 0));
        $used_kb = $total - $available;
        $stats['total'] = number_format($total / 1024 / 1024, 2);
        $stats['used'] = number_format($used_kb / 1024 / 1024, 2); }
    echo json_encode($stats); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ROWLAND, TIMOTHY</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root { --ge-paper: #c8b99f; --ge-paper-dark: #b1a38a; --ge-red: #ff3c3c; --ge-blue: #3c91ff; }
        html, body { min-height: 100vh; margin: 0; padding: 0; background: radial-gradient(circle, #4a4a4a 0%, #1a1a1a 100%) !important; background-attachment: fixed; font-family: 'Roboto Condensed', sans-serif; display: flex; justify-content: center; overflow-x: hidden; }  
        body::after { content: " "; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(18,16,16,0) 50%, rgba(0,0,0,0.1) 50%), linear-gradient(90deg, rgba(255,0,0,0.02), rgba(0,255,0,0.01), rgba(0,0,0,0.02)); background-size: 100% 3px, 3px 100%; pointer-events: none; z-index: 1000; } 
        .dossier-wrapper { position: relative; width: 95vw; max-width: 1050px; margin: 20px 0; display: block; }
        .folder { 
            background: var(--ge-paper); 
            border: 2px solid #000; 
            display: flex; 
            flex-direction: row; 
            flex-wrap: wrap; 
            box-shadow: 20px 20px 0px rgba(0,0,0,0.4); 
            min-height: 85vh; 
            width: 100%; }
        .page-hidden { display: none !important; }
        .header { width: 100%; background: var(--ge-paper); display: flex; align-items: flex-end; padding: 15px 25px; color: #000; text-transform: uppercase; font-size: 1.15rem; position: relative; border-bottom: 2px solid #000; box-sizing: border-box; }
        .header::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 8px; background: #000; border-bottom: 3px solid var(--ge-red); }
        .sidebar { flex: 0 0 280px; background: var(--ge-paper-dark); padding: 25px; border-right: 2px solid rgba(0,0,0,0.2); box-sizing: border-box; }
        .main { flex: 1; padding: 40px; background: url('https://www.transparenttextures.com/patterns/paper-fibers.png'); box-sizing: border-box; min-width: 300px; }
        .id-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(1) contrast(1.2) brightness(0.9);
            mix-blend-mode: multiply;
            opacity: 0.85;
            object-position: center center; }
        h2 { color: #000; border-bottom: 3px solid #000; text-transform: uppercase; margin: 25px 0 15px 0; }
        .status-label { color: #000; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; margin-top: 10px; }
        .bar-container { height: 16px; background: #000; margin: 4px 0 12px 0; border: 1px solid #fff; }
        .bar-red, .bar-blue { height: 100%; transition: 1.5s; width: 0%; }
        .bar-red { background: var(--ge-red); } .bar-blue { background: var(--ge-blue); }
        .menu-item { display: inline-block; padding: 5px 0; font-size: 1.1rem; color: #000; text-decoration: none; font-weight: bold; text-transform: uppercase; background: none; border: none; font-family: inherit; cursor: pointer; }
        .menu-item:hover { color: var(--ge-red); }
        .contact-link { display: block; font-family: 'Share Tech Mono'; font-size: 0.8rem; color: #000; text-decoration: none; margin-bottom: 4px; font-weight: bold; }
        .contact-link:hover { text-decoration: underline; color: var(--ge-red); }
        .footer { width: 100%; background: #000; color: #fff; font-family: 'Share Tech Mono'; padding: 15px 25px; font-size: 0.75rem; box-sizing: border-box; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .control-panel { position: fixed; top: 10px; right: 10px; z-index: 2000; }
        .control-panel button { padding: 5px 10px; cursor: pointer; font-family: 'Share Tech Mono'; background: var(--ge-paper); border: 2px solid #000; font-weight: bold; }
        .game-wrapper { border: 2px solid #000; background: #000; padding: 10px; display: flex; justify-content: center; overflow: hidden; margin-top: 15px; } @media (max-width: 768px) {
            .folder { flex-direction: column; min-height: 0; }
            .sidebar { order: 2; flex: none; width: 100%; border-right: none; border-top: 2px solid rgba(0,0,0,0.2); text-align: center; display: flex; flex-direction: column; align-items: center; }
            .main { order: 1; padding: 25px; width: 100%; }
            .id-photo { width: 180px; height: 180px; margin: 0 auto 20px auto; }
            .grid-2 { grid-template-columns: 1fr; gap: 10px; } 
            iframe { transform: scale(0.7); transform-origin: center; }  } </style> </head> <body>
            
            <div class="control-panel"><button onclick="toggleDossier()">[ CYCLE_DOSSIER ]</button></div>
            <div class="dossier-wrapper">
            <div class="folder" id="p1">
            <header class="header">Timothy Rowland | IT Support, Junior Developer, Systems Architect</header>
            <main class="main">
            <h2>EXECUTIVE SUMMARY</h2>
            <p>Native developer specialized in Headless Oracle Linux, VS Code, and cloud architecture. Passionate about optimizing systems and creating efficient solutions.</p>
            <div class="grid-2">
                <div><div class="status-label" style="color:var(--ge-red);">// SPECIALTIES</div><div class="menu-item">OCI A1-SHAPE ARCH</div><br><div class="menu-item">HARDENED LINUX</div></div>
                <div><div class="status-label" style="color:var(--ge-red);">// TECH ASSETS</div><div class="menu-item">C# & UNITY ENGINE</div><br><div class="menu-item">PERF PROFILING</div></div>
            </div>
            <h2>CERTIFICATIONS</h2>
            <div class="grid-2"><a href="images/cert1.png" target="_blank" class="menu-item">OP_SPARK_DEV_CERT</a><a href="images/cert2.png" target="_blank" class="menu-item">NCCER_ELEC_L1_CERT</a></div>
        </main>
        <aside class="sidebar">
            <div class="id-photo"><img src="images/TimothyRowland.png" alt="Agent Photo"></div>
            <br><a href="https://github.com/theyseemerowland" class="contact-link">GITHUB: THEYSEEMEROWLAND</a>
            <a href="https://linkedin.com/in/timothy-rowland/" class="contact-link">LINKEDIN: TIMOTHY-ROWLAND</a>
            <a href="mailto:trowlandemails@gmail.com" class="contact-link">EMAIL: TROWLANDEMAILS</a>
            <div style="font-size:0.85rem; font-weight:bold; margin-top:20px;">AGENT: ROWLAND, TIMOTHY<br>AVAILABILITY: ACTIVE</div>
        </aside>
        <footer class="footer">Timothy Rowland // Personnel // PAGE 1</footer> </div>
    <div class="folder page-hidden" id="p2">
        <header class="header">Timothy Rowland | Infrastructure Audit</header>
        <main class="main">
            <h2>INFRASTRUCTURE AUDIT</h2>
            <div style="border:2px solid #000; padding:15px; font-family:'Share Tech Mono'; font-size:0.85rem;">
                <div style="border-bottom:1px solid #000; margin-bottom:10px;">// COST_BASIS_ANALYSIS</div>
                LEVERAGING OCI AMPERE A-1 COMPUTE FREE TIER & NETWORK SOLUTIONS <br>
                .SPACE DOMAIN + SSL: LETSENCRYPT <br> <br><br>CODE: LINES 155 <br>COST: <u>$1.10 / YEAR</u>
                <div style="border-bottom:1px solid #000000; margin:15px 0 10px 0;"><br>// LIVE_TELEMETRY</div>
                <div class="status-label">(CPU_LOAD)</div><div class="bar-container"><div class="bar-red cpu-bar"></div></div>
                <div class="status-label">(RAM_USAGE)</div><div class="bar-container"><div class="bar-blue ram-bar"></div></div>
                <div style="text-align:center;"><span class="ram-text">Loading...</span> (<span class="cpu-val">--</span>%)</div>
            </div>
            <button onclick="toggleDossier()" class="menu-item" style="margin-top:20px;">[[ NEXT_FILE ]]</button>
        </main>
        <aside class="sidebar">
            <div class="id-photo"><img src="images/TimothyRowland.png" alt="Agent Photo"></div>
            <div class="status-label">Live Audit</div><div style="font-family:'Share Tech Mono'; font-size:0.8rem;">Auditing Information</div> </aside>
            <footer class="footer">Timothy Rowland // Infrastructure // PAGE 2</footer> </div>
            <div class="folder page-hidden" id="p3">
            <header class="header">Timothy Rowland | Hardware & Simulation</header>
            <main class="main"> <h2>HARDWARE LAB</h2>
            <div>Optiplex Specifications:</div>
            <ul style="list-style-type: none; padding-left: 0; font-family: 'Share Tech Mono', monospace;">
            <li>> AM4 Motherboard</li> 
            <li>> 500w Power Supply </li>
            <li>> NVIDIA GTX 980 (Secured Storage)</li>
            <li>> 16GB DDR4 RAM (In-Transit)</li>
            <li>> AMD Ryzen 3 2200G (4C/4T @ 3.7GHz)</li>
            </ul>
            Images:
            <div class="grid-2">
                <a href="images/Optiplex790Before.jpg" target="_blank" class="menu-item">OPTIPLEX_790_BASE</a>
                <a href="images/optiplex-wip.png" target="_blank" class="menu-item">OPTIPLEX_790_WIP</a>
                <a href="images/optiplex-790.png" target="_blank" class="menu-item">OPTIPLEX_790_BACKPANEL</a> </div><br>
            <h2>GAME DEVELOPMENT</h2>
            <div style="border:2px solid #000; padding:15px; font-family:'Share Tech Mono'; font-size:0.85rem;">
                PROJECT: SPACE INVADERS<br>ENGINE: UNITY ENGINE<br>STATUS: DEPLOYMENT_READY</div> <br>
            <div class="game-container">
            <iframe frameborder="0" src="https://itch.io/embed/4272643?linkback=true&amp;border_width=2&amp;dark=true">
            <a href="https://theyseemerowland.itch.io/space-invaders">Space Invaders by theyseemerowland</a>
            </iframe>
        </div>            <button onclick="toggleDossier()" class="menu-item" style="margin-top:20px;">[[ RETURN_TO_START ]]</button></main>
        <aside class="sidebar">
            <div class="id-photo"><img src="images/TimothyRowland.png" alt="Agent Photo"></div>
            <div class="status-label">PROJECTS</div><div style="font-family:'Share Tech Mono'; font-size:0.8rem;">The here is my current project: The Dell Optiplex 790. This project contains a custom white chassis paint with custom artwork on the front and backplate. I am really proud of my Unity game, simple as it may be, this project represents a significant milestone in my development cycle. I hope you enjoy what I have presented! I sincerely hope you enjoy the rest of your day, who ever you may be. Thank you for taking the time to view my work!</div> </aside>
        <footer class="footer">Timothy Rowland // Projects // PAGE 3</footer></div></div> <script>
        let current = 1;
        function toggleDossier() {
            const pages = [document.getElementById('p1'), document.getElementById('p2'), document.getElementById('p3')];
            pages.forEach(p => p.classList.add('page-hidden'));
            current = (current % 3) + 1;
            document.getElementById(`p${current}`).classList.remove('page-hidden');
            window.scrollTo(0, 0); }
            async function updateTelemetry() { try {
            const res = await fetch('?telemetry=1');
            const data = await res.json();
            document.querySelectorAll('.cpu-bar').forEach(b => b.style.width = data.cpu + '%');
            document.querySelectorAll('.cpu-val').forEach(v => v.innerText = data.cpu);
            const percent = (parseFloat(data.used) / parseFloat(data.total)) * 100;
            document.querySelectorAll('.ram-bar').forEach(b => b.style.width = percent + '%');
            document.querySelectorAll('.ram-text').forEach(t => t.innerText = `${data.used} / ${data.total} GB`);
            } catch (e) { console.log("Offline"); } }
             setInterval(updateTelemetry, 3000); updateTelemetry();
</script>
</body>
</html>