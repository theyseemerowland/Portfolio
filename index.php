<?php
/**
 * MI6 CLASSIFIED PERSONNEL DOSSIER - FINAL PFP, MOBILE, & LINKED CERTS
 */
if (isset($_GET['telemetry'])) {
    header('Content-Type: application/json');
    $stats = ['used' => '0.00', 'total' => '0.00', 'cpu' => '0'];
    
    if (file_exists("/proc/loadavg")) {
        $load = sys_getloadavg();
        $stats['cpu'] = max(3, min(100, round($load[0] * 10, 1))); 
    } else {
        $stats['cpu'] = rand(5, 12); 
    }

    if (file_exists("/proc/meminfo")) {
        $data = file_get_contents("/proc/meminfo");
        $meminfo = [];
        foreach (explode("\n", $data) as $line) {
            if (strpos($line, ':')) {
                list($key, $val) = explode(":", $line);
                $meminfo[$key] = (int) trim(str_replace('kB', '', $val));
            }
        }
        $stats['total'] = number_format($meminfo['MemTotal'] / 1024 / 1024, 2);
        $stats['used'] = number_format(($meminfo['MemTotal'] - ($meminfo['MemFree'] + $meminfo['Buffers'] + $meminfo['Cached'])) / 1024 / 1024, 2);
    }
    echo json_encode($stats);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROWLAND, TIMOTHY</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --ge-paper: #c8b99f;
            --ge-paper-dark: #b1a38a;
            --ge-cyan: #00f0f0;
            --ge-red: #ff3c3c;
            --ge-blue: #3c91ff;
        }

        html, body { 
            min-height: 100%; margin: 0; padding: 0; 
            background: radial-gradient(circle, #4a4a4a 0%, #1a1a1a 100%) !important;
            background-attachment: fixed;
            font-family: 'Roboto Condensed', sans-serif;
            display: flex; justify-content: center; align-items: flex-start;
        }

        /* Scanline Overlay */
        body::after {
            content: " "; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), 
                        linear-gradient(90deg, rgba(255, 0, 0, 0.02), rgba(0, 255, 0, 0.01), rgba(0, 0, 0, 0.02));
            background-size: 100% 3px, 3px 100%; pointer-events: none; z-index: 1000;
        }

        .folder {
            width: 90vw; max-width: 1050px; min-height: 85vh;
            margin: 40px 0; background: var(--ge-paper);
            border: 2px solid #000; display: grid;
            grid-template-columns: 280px 1fr; grid-template-rows: 80px 1fr 50px;
            box-shadow: 35px 35px 0px rgba(0,0,0,0.5); position: relative;
        }

        .header {
            grid-column: 1 / 3; 
            background: var(--ge-paper); 
            display: flex; align-items: flex-end; padding: 0 25px 12px 25px;
            color: #000; text-transform: uppercase; letter-spacing: 1px; font-size: 1.15rem;
            position: relative;
        }

        .header::after {
            content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 8px;
            background: #000; border-bottom: 3px solid var(--ge-red);
        }

        .sidebar { background: var(--ge-paper-dark); padding: 25px; border-right: 2px solid rgba(0,0,0,0.2); }

        .id-photo {
            width: 100%; height: 170px; background: #000; border: 4px solid #fff;
            margin-bottom: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center;
        }
        .id-photo img { 
            width: 100%; height: 100%; object-fit: cover; 
            filter: contrast(1.1) grayscale(1);
            display: block; 
        }

        .status-label { color: #000; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; margin-top: 10px; }
        .bar-container { height: 16px; background: #000; margin: 4px 0 12px 0; border: 1px solid #fff; position: relative; }
        .bar-red { height: 100%; background: var(--ge-red); width: 0%; transition: 1.5s ease-in-out; box-shadow: 0 0 10px var(--ge-red); }
        .bar-blue { height: 100%; background: var(--ge-blue); width: 0%; transition: 1.5s ease-in-out; box-shadow: 0 0 10px var(--ge-blue); }

        .main { padding: 40px; background: url('https://www.transparenttextures.com/patterns/paper-fibers.png'); }

        h2 { color: #000; border-bottom: 3px solid #000; text-transform: uppercase; margin: 30px 0 15px 0; }
        h2:first-child { margin-top: 0; }

        .menu-item {
            display: inline-block; padding: 5px 0; font-size: 1.1rem;
            color: #000; text-decoration: none; font-weight: bold; text-transform: uppercase;
            transition: color 0.2s;
        }
        .menu-item::before { content: "[ "; opacity: 0; }
        .menu-item::after { content: " ]"; opacity: 0; }
        .menu-item:hover { color: var(--ge-red); cursor: pointer; }
        .menu-item:hover::before, .menu-item:hover::after { opacity: 1; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        .footer {
            grid-column: 1 / 3; background: #000; color: #fff;
            font-family: 'Share Tech Mono'; padding: 0 25px;
            display: flex; align-items: center; font-size: 0.75rem;
            text-transform: uppercase;
        }

        /* Responsive Logic */
        @media (max-width: 850px) {
            .folder {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto auto;
                width: 95vw; margin: 20px auto; min-height: auto;
            }
            .header { grid-column: 1 / 2; font-size: 1rem; text-align: center; height: auto; padding: 15px; }
            .sidebar { border-right: none; border-bottom: 2px solid rgba(0,0,0,0.2); align-items: center; display: flex; flex-direction: column; }
            .id-photo { width: 220px; height: 140px; }
            .bar-container { width: 100%; }
            .grid-2 { grid-template-columns: 1fr; gap: 10px; }
            .main { padding: 25px; }
            .footer { grid-column: 1 / 2; text-align: center; }
            .menu-item { padding: 10px 0; width: 100%; } /* Larger tap target */
        }
    </style>
</head>
<body>

<div class="folder">
    <header class="header">
        Timothy Rowland | Systems Architect, IT Support, Developer
    </header>

    <aside class="sidebar">
        <a href="https://www.linkedin.com/in/timothy-rowland/" target="_blank">
            <div class="id-photo">
                <img src="images/TimothyRowland.png?v=<?php echo time(); ?>" alt="Agent Rowland">
            </div>
        </a>
        <div class="status-label">(CPU)</div>
        <div class="bar-container"><div id="cpu-bar" class="bar-red"></div></div>
        
        <div class="status-label">(RAM)</div>
        <div class="bar-container"><div id="ram-bar" class="bar-blue"></div></div>
        <div id="ram-text" style="font-family: 'Share Tech Mono'; font-size: 0.8rem; text-align: center; color: #000;">0.00 / 11.23 GB</div>
        
        <hr style="border: 0; border-top: 2px solid rgba(0,0,0,0.1); margin: 20px 0; width: 100%;">
        <div style="font-size: 0.85rem; font-weight: bold; color: #000; width: 100%;">
            AGENT: ROWLAND, TIMOTHY<br>
            ROLE: SYSTEMS ARCHITECT & DEVELOPER<br>
            AVAILABILITY: 9AM - 5PM CST<br>
            SERVER STATUS: ACTIVE<br>
        </div>
    </aside>

    <main class="main">
        <h2>EXECUTIVE SUMMARY</h2>
        <p style="font-size: 1.1rem; font-weight: bold; margin-bottom: 25px;">
            Lead Architect: Cloud Virtualization (OCI) & High-Fidelity Simulations.
        </p>

        <div class="grid-2">
            <div>
                <div class="status-label" style="color: var(--ge-red); margin-bottom: 5px;">// Specialties</div>
                <div class="menu-item">OCI A1-Shape Arch</div><br>
                <div class="menu-item">Hardened Linux</div><br>
                <div class="menu-item">Tenancy Governance</div>
            </div>
            <div>
                <div class="status-label" style="color: var(--ge-red); margin-bottom: 5px;">// Tech Assets</div>
                <div class="menu-item">C# & Unity Engine</div><br>
                <div class="menu-item">Perf Profiling</div><br>
                <div class="menu-item">Component Systems</div>
            </div>
        </div>

        <h2>CERTIFICATIONS</h2>
        <div class="grid-2">
            <a href="images/cert1.png" target="_blank" class="menu-item">OP_SPARK_DEV_CERT</a>
            <a href="images/cert2.png" target="_blank" class="menu-item">NCCER_ELEC_L1_CERT</a>
        </div>

        <h2>Purpose-Built Legacy Conversion</h2>
        <div class="grid-2">
            <a href="images/Optiplex790Before.jpg" target="_blank" class="menu-item">Optiplex 790 Factory</a>
            <a href="images/optiplex-wip.png" target="_blank" class="menu-item">Optiplex 790: Internal Swap</a><br>
            <a href="images/optiplex-790.png" target="_blank" class="menu-item">Optiplex 790: Chassis Finish</a>
        </div>
        <h2 style="margin-top: 40px;">CONTACT INFORMATION</h2>
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <a href="https://github.com/theyseemerowland" target="_blank" class="menu-item">GITHUB</a>
            <a href="https://www.linkedin.com/in/timothy-rowland/" target="_blank" class="menu-item">LINKEDIN</a>
            <a href="mailto:trowlandemails@gmail.com" class="menu-item">EMAIL</a>
        </div>
    </main>

    <footer class="footer">
        Inspired by GoldenEye 007 // Please ask for permission if you wish to see certifications.
    </footer>
</div>

<script>
    async function updateTelemetry() {
        try {
            const res = await fetch('?telemetry=1');
            const data = await res.json();
            document.getElementById('cpu-bar').style.width = data.cpu + '%';
            const ramTotal = 11.23;
            const ramUsed = parseFloat(data.used);
            const ramPercent = (ramUsed / ramTotal) * 100 || 0;
            document.getElementById('ram-bar').style.width = Math.min(100, ramPercent) + '%';
            document.getElementById('ram-text').innerText = `${data.used} / ${ramTotal} GB`;
        } catch (e) {
            console.error("ERROR");
        }
    }
    updateTelemetry();
    setInterval(updateTelemetry, 5000);
</script>
</body>
</html>