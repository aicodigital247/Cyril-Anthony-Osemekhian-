<?php
/**
 * BETELITE - Header Include
 */
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";
require_once __DIR__ . "/../config/functions.php";

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_SLOGAN; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkBg: '#020617',
                        darkSec: '#0f172a',
                        glassBg: 'rgba(17, 24, 39, 0.75)',
                        borderSl: '#1e293b',
                        betGreen: '#22c55e',
                        electricGreen: '#00FF88',
                        greenHover: '#16a34a',
                        vipGold: '#f59e0b',
                        dangerRed: '#ef4444',
                        mutedText: '#94a3b8'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- jQuery for AJAX calls -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Custom Sportsbook Global Inline CSS -->
    <style>
        body {
            background-color: #020617;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        /* Glassmorphism Classes */
        .glass-card {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(30, 41, 59, 0.8);
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            border-color: rgba(0, 255, 136, 0.3);
            box-shadow: 0 12px 40px 0 rgba(0, 255, 136, 0.05);
        }

        .glass-nav {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(30, 41, 59, 1);
        }

        .glass-input {
            background: rgba(15, 23, 42, 0.9) !important;
            border: 1px solid rgba(30, 41, 59, 1) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            transition: all 0.2s ease-in-out;
        }

        .glass-input:focus {
            border-color: #00FF88 !important;
            box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.15) !important;
            background: rgba(15, 23, 42, 1) !important;
        }

        /* Betting Specific Components */
        .match-card {
            border-left: 4px solid #1e293b;
            transition: all 0.25s linear;
        }
        .match-card.live-active {
            border-left: 4px solid #00FF88;
        }

        /* Custom Scrollbar for sports slip tables */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #020617;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #00FF88;
        }

        /* Neon glow live pulses */
        .live-pulse {
            width: 8px;
            height: 8px;
            background-color: #00FF88;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(0, 255, 136, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 255, 136, 0);
            }
        }

        /* Shake live goals score adjustments */
        .odds-up {
            animation: flashGreen 0.7s ease-out;
        }
        .odds-down {
            animation: flashRed 0.7s ease-out;
        }

        @keyframes flashGreen {
            0% { background-color: rgba(34, 197, 94, 0.2); }
            100% { background-color: transparent; }
        }
        @keyframes flashRed {
            0% { background-color: rgba(239, 68, 68, 0.2); }
            100% { background-color: transparent; }
        }

        /* Mobile Layout Optimizations */
        @media (max-width: 768px) {
            body {
                padding-bottom: 70px; /* Leave space for bottom nav */
            }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
<!-- Outer Main Layout Wrapper -->
