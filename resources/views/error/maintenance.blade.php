<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CISAM // Under Maintenance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0a0c10;
            --widget-bg: rgba(22, 27, 34, 0.6);
            --border-color: #30363d;
            --text-primary: #e6edf3;
            --text-secondary: #7d8590;
            --accent-cyan: #39c5f7;
            --status-red-glow: #f85149;
            --font-main: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-mono: 'SF Mono', 'Consolas', 'Liberation Mono', Menlo, monospace;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: var(--font-main);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
            text-align: center;
            padding: 1rem;
        }

        /* The animated grid background you liked */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(to right, var(--border-color) 1px, transparent 1px),
                linear-gradient(to bottom, var(--border-color) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.15;
            z-index: -1;
            animation: pan-grid 90s linear infinite;
        }

        @keyframes pan-grid {
            0% { background-position: 0 0; }
            100% { background-position: 500px 500px; }
        }

        .maintenance-container {
            background: var(--widget-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px 50px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            animation: fade-in 0.8s ease-out forwards;
        }
        
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .maintenance-icon {
            font-size: 48px;
            color: var(--accent-cyan);
            margin-bottom: 24px;
        }
        
        .project-title {
            font-family: var(--font-mono);
            font-size: 28px;
            color: var(--text-primary);
            margin: 0 0 10px 0;
        }

        .main-message {
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 16px 0;
        }

        .sub-message {
            font-size: 16px;
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 0 0 24px 0;
        }

        .status-box {
            font-family: var(--font-mono);
            font-size: 14px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            padding: 10px 20px;
            border-radius: 6px;
            display: inline-block;
        }
        
        .status-box span {
            color: var(--status-red-glow);
            font-weight: bold;
            animation: pulse-status 2s infinite;
        }
        
        @keyframes pulse-status {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        /* --- MOBILE FRIENDLY STYLES --- */
        @media (max-width: 600px) {
            .maintenance-container {
                padding: 30px 25px; /* Reduced padding for small screens */
            }
            .maintenance-icon {
                font-size: 40px; /* Slightly smaller icon */
            }
            .project-title {
                font-size: 24px; /* Smaller title */
            }
            .main-message {
                font-size: 20px; /* Smaller main message */
            }
            .sub-message {
                font-size: 15px; /* Smaller sub message */
            }
        }

    </style>
</head>
<body>

    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-person-digging"></i>
        </div>

        <h1 class="project-title">CISAM</h1>
        <h2 class="main-message">System Maintenance In Progress</h2>
        <p class="sub-message">
            We're currently performing scheduled upgrades to improve your experience. We appreciate your patience and will be back online shortly.
        </p>
        <div class="status-box">
            STATUS: <span>OFFLINE</span>
        </div>
    </div>

</body>
</html>