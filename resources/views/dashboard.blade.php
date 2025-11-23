<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::user()->id ?? '' }}">
    <meta http-equiv="Permissions-Policy" content="geolocation=(self)">
    <title>CISAM | Dashboard</title>
    
    <!-- Preconnect to external resources for faster DNS resolution -->
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Critical CSS first -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
    <link rel="icon" type="image/x-icon" href="/img/favicon.png">
    
    <!-- Critical CSS for layout - load synchronously to prevent FOUC -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Defer non-critical CSS and scripts for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" media="print" onload="this.media='all'; this.onload=null;">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    
    <!-- Validation Utilities - Load synchronously -->
    <script src="{{ asset('js/validation-utils.js') }}"></script>
    
    <style>
        /* Inline critical font import */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f8fafc;
            min-height: 100vh;
        }

        .sidebar {
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
        }

        .card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .gradient-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .gradient-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .gradient-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .gradient-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            margin: 0.25rem 0.75rem;
            border-radius: 12px;
            text-decoration: none;
            color: #64748b;
            transition: all 0.2s ease;
            cursor: pointer;
            font-weight: 500;
            position: relative;
        }

        .sidebar-link:hover {
            background: #f1f5f9;
            color: #6366f1;
        }

        .sidebar-link.active {
            background: #eef2ff;
            color: #6366f1;
            font-weight: 600;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 0 4px 4px 0;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .modal-blur {
            background-color: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }

        @keyframes shake {
            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-10px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(10px);
            }
        }

        .action-card {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
        }

        .btn-modern {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-modern:hover {
            transform: scale(1.02);
        }

        .section-content {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #checkin-map,
        #setup-map,
        #workplace-map,
        #special-checkin-map {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .leaflet-container {
            border-radius: 16px;
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
        }

        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        .location-status-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
        }

        .workflow-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .workflow-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 24px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .section-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 4px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
        }

        .activity-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .activity-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .map-legend-item {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: #64748b;
        }

        .map-legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .workplace-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .workplace-item:hover {
            border-color: #a5b4fc;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .workplace-item.ring-2 {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Utility class for text truncation on multiple lines */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body class="flex">

    <!-- Clean Sidebar -->
    <aside
        class="sidebar w-72 fixed h-screen shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-[60]"
        id="sidebar">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 gradient-primary rounded-xl flex items-center justify-center shadow-md">
                    <i class="fas fa-map-marker-alt text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">CISAM</h1>
                    <p class="text-xs text-gray-500 font-medium">Attendance Monitor</p>
                </div>
            </div>
        </div>

        <nav class="mt-6 space-y-1 px-2">
            <a href="javascript:void(0)" class="sidebar-link active" data-section="dashboard">
                <i class="fas fa-home w-5"></i>
                <span class="ml-3">Dashboard</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-link" data-section="my-workplace">
                <i class="fas fa-building w-5"></i>
                <span class="ml-3">Workplace</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-link" data-section="gps-checkin">
                <i class="fas fa-map-pin w-5"></i>
                <span class="ml-3">Check In/Out</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-link" data-section="special-checkin">
                <i class="fas fa-star w-5"></i>
                <span class="ml-3">Special Check-In</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-link" data-section="attendance-history">
                <i class="fas fa-history w-5"></i>
                <span class="ml-3">History</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-link" data-section="absence-history">
                <i class="fas fa-calendar-times w-5"></i>
                <span class="ml-3">Absence Records</span>
            </a>
            <a href="javascript:void(0)" class="sidebar-link" data-section="profile">
                <i class="fas fa-user-cog w-5"></i>
                <span class="ml-3">Profile</span>
            </a>
        </nav>

        <div class="absolute bottom-6 left-4 right-4">
            <div class="location-status-card rounded-xl p-4 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full pulse-dot"></div>
                    <div class="flex-1">
                        <p class="text-xs text-green-700 font-semibold">Location Status</p>
                        <p class="text-sm font-bold text-green-800" id="location-status">Active</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-0 lg:ml-72 flex-1 min-h-screen">
        <!-- Clean Topbar -->
        <div class="topbar shadow-sm p-4 lg:p-6 sticky top-0 z-50">
            <div class="flex justify-between items-center">
                <button class="lg:hidden text-gray-600 hover:text-indigo-600 mr-3 transition-colors"
                    onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <div class="flex-1">
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 leading-none" id="page-title">My Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-0.5 hidden sm:block leading-none" id="page-subtitle">Welcome to your attendance portal</p>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="text-right hidden md:block">
                        <p class="text-xs text-gray-500 font-medium">Welcome back,</p>
                        <p class="text-sm font-bold text-gray-900">{{ Auth::user()->name ?? 'User' }}</p>
                    </div>
                    <div
                        class="w-11 h-11 gradient-primary rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-md">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-4 py-2 gradient-info text-white rounded-lg shadow-sm hover:shadow-md transition-all text-sm font-semibold btn-modern">
                                <i class="fas fa-user-shield mr-2"></i>
                                <span class="hidden sm:inline">Admin</span>
                            </a>
                        @endif
                        <a href="{{ route('logout.get') }}"
                            class="px-4 py-2 gradient-danger text-white rounded-lg shadow-sm hover:shadow-md transition-all text-sm font-semibold btn-modern">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section-content">
                <!-- Primary Action Card - Mobile First -->
                <div class="mb-6">
                    <div class="card-modern rounded-2xl shadow-md overflow-hidden gradient-success text-white">
                        <div class="p-6 lg:p-8">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 lg:w-14 lg:h-14 bg-[#E1AD01] shadow-lg bg-opacity-20 rounded-xl flex items-center justify-center mr-3 lg:mr-4">
                                        <i class="fas fa-map-marker-alt text-white text-xl lg:text-2xl"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl lg:text-2xl font-bold">Check In/Out</h2>
                                        <p class="text-xs lg:text-sm opacity-90">Quick GPS verification</p>
                                    </div>
                                </div>
                                <div class="text-right hidden lg:block">
                                    <div class="text-2xl font-bold" id="current-time">--:--</div>
                                    <div class="text-xs opacity-75" id="current-date">Loading...</div>
                                </div>
                            </div>
                            <button onclick="switchToSection('gps-checkin')"
                                class="w-full px-6 py-3 bg-white text-green-600 rounded-xl font-bold hover:bg-gray-50 transition-all shadow-sm text-base lg:text-lg">
                                <i class="fas fa-location-crosshairs mr-2"></i>Start Check In
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Compact Stats - Mobile Optimized -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Today's Overview
                    </h3>
                    <div class="grid grid-cols-3 gap-3 lg:gap-6">
                        <!-- Days Present -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-success rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3">
                                    <i class="fas fa-calendar-check text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1"
                                    id="my-checkins">
                                    <i class="fas fa-spinner fa-spin text-sm lg:text-lg text-gray-400"></i>
                                </h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">Days
                                    Present</p>
                                <p class="text-[9px] lg:text-xs text-green-600 font-medium hidden lg:block"
                                    id="attendance-rate">Loading...</p>
                            </div>
                        </div>

                        <!-- Average Check-in -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-info rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3">
                                    <i class="fas fa-clock text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1"
                                    id="avg-checkin">
                                    <i class="fas fa-spinner fa-spin text-sm lg:text-lg text-gray-400"></i>
                                </h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">Avg
                                    Check-in</p>
                                <p class="text-[9px] lg:text-xs text-blue-600 font-medium hidden lg:block"
                                    id="checkin-trend">Loading...</p>
                            </div>
                        </div>

                        <!-- Work Hours -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-primary rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3">
                                    <i class="fas fa-hourglass-half text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1"
                                    id="hours-worked">
                                    <i class="fas fa-spinner fa-spin text-sm lg:text-lg text-gray-400"></i>
                                </h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">Work
                                    Hours</p>
                                <p class="text-[9px] lg:text-xs text-indigo-600 font-medium hidden lg:block"
                                    id="work-status">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile: Show extra stats info -->
                    <div class="lg:hidden mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="text-[9px] text-green-600 font-medium" id="attendance-rate-mobile">Loading...
                        </div>
                        <div class="text-[9px] text-blue-600 font-medium" id="checkin-trend-mobile">Loading...</div>
                        <div class="text-[9px] text-indigo-600 font-medium" id="work-status-mobile">Loading...</div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6">
                        <!-- Special Check-in -->
                        <div class="action-card gradient-warning text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer"
                            onclick="switchToSection('special-checkin')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-star text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">Special Check-In</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Up to 4
                                    check-ins per day</p>
                            </div>
                        </div>

                        <!-- History -->
                        <div class="action-card gradient-info text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer"
                            onclick="switchToSection('attendance-history')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-history text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">View History</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Attendance
                                    records</p>
                            </div>
                        </div>

                        <!-- My Workplace -->
                        <div class="action-card bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer col-span-2 lg:col-span-1"
                            onclick="switchToSection('my-workplace')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-building text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">Workplace</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Manage
                                    locations</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Workflow - Combined Activity -->
                <div class="card-modern rounded-2xl shadow-sm p-6 lg:p-8" id="todays-schedule-section">
                    <div class="section-header">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-primary rounded-xl flex items-center justify-center mr-3 lg:mr-4">
                                    <i class="fas fa-clock text-white text-base lg:text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg lg:text-2xl font-bold text-gray-900">Today's Complete Activity</h3>
                                    <p class="text-xs lg:text-sm text-gray-600">All check-ins and check-outs today</p>
                                </div>
                            </div>
                            <button onclick="fetchTodaysSchedule()" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                <i class="fas fa-sync-alt mr-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                    <div class="space-y-3" id="schedule-content">
                        <div class="flex items-center justify-center p-8 lg:p-12 text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-2xl lg:text-3xl mb-3 lg:mb-4 text-gray-300"></i>
                                <p class="font-medium text-sm lg:text-base">Loading activity...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPS Check-in Section -->
            <div id="gps-checkin-section" class="section-content hidden">
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- LEFT COLUMN: Check-In Interface -->
                    <div class="space-y-8">
                        <!-- HEADER CARD -->
                        <div
                            class="bg-gradient-to-r from-teal-500 to-cyan-500 text-white rounded-2xl shadow-md p-6 text-center">
                            <div class="flex justify-center mb-3">
                                <i class="fas fa-map-marker-alt text-4xl"></i>
                            </div>
                            <h2 class="text-2xl font-extrabold">GPS Check-In</h2>
                            <p class="text-cyan-100 mt-1 text-sm">Verify your location and check-in to start your
                                workday</p>
                        </div>

                        <!-- WORKPLACE INFO -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="font-semibold text-gray-800 flex items-center mb-4">
                                <i class="fas fa-building text-cyan-500 mr-2"></i>Workplace Information
                            </h3>
                            
                            <!-- Primary Workplace -->
                            <div class="mb-3 pb-3 border-b border-gray-100">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-500 font-medium">Primary Workplace:</span>
                                    <span id="primary-distance" class="text-gray-400 text-xs">-- m</span>
                                </div>
                                <span id="primary-workplace-display" class="text-sm font-semibold text-gray-700 block">Not set</span>
                            </div>
                            
                            <!-- Current Workplace (Detected) -->
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-500 font-medium">Current Location:</span>
                                    <span id="current-distance" class="text-cyan-600 font-semibold text-xs">-- m</span>
                                </div>
                                <span id="current-workplace-display" class="text-sm font-semibold text-gray-700 block">Detecting...</span>
                                <p id="current-workplace-address" class="text-xs text-gray-500 mt-1">
                                    Waiting for location...
                                </p>
                            </div>
                        </div>

                        <!-- LOCATION STATUS -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-700">Location Status:</span>
                                <div class="flex items-center gap-2">
                                    <span id="location-badge"
                                        class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Checking...</span>
                                    <button onclick="showLocationTroubleshooting()" title="Location Help"
                                        class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-question-circle"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="current-location" class="text-sm text-gray-600 mb-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Getting your location...
                            </div>

                            <!-- Troubleshooting Panel -->
                            <div id="location-troubleshooting"
                                class="hidden bg-violet-50 border border-violet-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-sm font-semibold text-violet-900 flex items-center">
                                        <i class="fas fa-tools mr-2"></i>Location Diagnostics
                                    </h4>
                                    <button onclick="runLocationDiagnostics()"
                                        class="text-violet-600 hover:text-violet-800 text-xs font-medium">
                                        <i class="fas fa-sync-alt mr-1"></i>Run Diagnostics
                                    </button>
                                </div>

                                <div id="diagnostic-results" class="space-y-2 text-xs text-gray-700 mb-3">
                                    <div id="geolocation-support" class="flex items-center"><i
                                            class="fas fa-circle text-gray-400 mr-2"></i>Checking geolocation
                                        support...</div>
                                    <div id="permission-status" class="flex items-center"><i
                                            class="fas fa-circle text-gray-400 mr-2"></i>Checking location
                                        permissions...</div>
                                    <div id="connection-status" class="flex items-center"><i
                                            class="fas fa-circle text-gray-400 mr-2"></i>Checking internet
                                        connection...</div>
                                    <div id="https-status" class="flex items-center"><i
                                            class="fas fa-circle text-gray-400 mr-2"></i>Checking secure connection...
                                    </div>
                                </div>

                                <div id="error-details"
                                    class="hidden bg-red-50 border border-red-200 text-xs p-2 rounded mb-2">
                                    <div class="font-medium text-red-800 mb-1">Error Details:</div>
                                    <div id="error-message" class="text-red-700"></div>
                                </div>

                                <div id="recommended-actions" class="space-y-1 text-xs text-violet-800 mb-3"></div>

                                <div class="flex flex-wrap gap-2">
                                    <button onclick="testBasicLocation()"
                                        class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700">
                                        <i class="fas fa-map-marker-alt mr-1"></i>Basic Test
                                    </button>
                                    <button onclick="retryLocationAccess()"
                                        class="px-3 py-1.5 bg-cyan-600 text-white rounded-lg text-xs hover:bg-cyan-700">
                                        <i class="fas fa-redo mr-1"></i>Retry
                                    </button>
                                    <button onclick="clearLocationCache()"
                                        class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs hover:bg-amber-600">
                                        <i class="fas fa-trash mr-1"></i>Clear
                                    </button>
                                    <button onclick="testHighAccuracy()"
                                        class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs hover:bg-emerald-700">
                                        <i class="fas fa-crosshairs mr-1"></i>High Accuracy
                                    </button>
                                    <button onclick="toggleLocationTroubleshooting()"
                                        class="px-3 py-1.5 border border-violet-300 text-violet-700 rounded-lg text-xs hover:bg-violet-100">
                                        Close
                                    </button>
                                </div>
                            </div>

                            <!-- Admin Testing Panel -->
                            @if (Auth::user()->isAdmin())
                                <div id="testing-mode-panel"
                                    class="mt-5 bg-amber-50 border border-amber-200 p-4 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="text-sm font-semibold text-amber-900 flex items-center">
                                            <i class="fas fa-flask mr-2"></i>Testing Mode (Admin)
                                        </h4>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="testing-mode-toggle" class="sr-only peer"
                                                onchange="toggleTestingMode()">
                                            <div
                                                class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-amber-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:h-5 after:w-5 after:rounded-full after:transition-all peer-checked:after:translate-x-full">
                                            </div>
                                        </label>
                                    </div>

                                    <div id="testing-mode-content" class="hidden text-xs text-amber-800">
                                        <p class="mb-3">Testing mode allows simulated GPS locations.</p>

                                        <div class="mb-3">
                                            <h5 class="font-medium mb-2">Quick Preset Locations:</h5>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button
                                                    onclick="setPresetLocation(14.2784642,120.8676613,'DepEd Cavite')"
                                                    class="px-2 py-1.5 bg-amber-500 text-white rounded hover:bg-amber-600 text-xs">DepEd
                                                    Cavite</button>
                                                <button onclick="setPresetLocation(14.3971478,120.8530243,'Tanza HS')"
                                                    class="px-2 py-1.5 bg-amber-500 text-white rounded hover:bg-amber-600 text-xs">Tanza
                                                    HS</button>
                                                <button
                                                    onclick="setPresetLocation(14.3186223,120.8591034,'Tanza Trade')"
                                                    class="px-2 py-1.5 bg-amber-500 text-white rounded hover:bg-amber-600 text-xs">Tanza
                                                    Trade</button>
                                                <button
                                                    onclick="setPresetLocation(14.287075,120.8687556,'Trece Elementary')"
                                                    class="px-2 py-1.5 bg-amber-500 text-white rounded hover:bg-amber-600 text-xs">Trece
                                                    Elementary</button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 mb-2">
                                            <div>
                                                <label class="block font-medium mb-1">Latitude</label>
                                                <input type="number" id="admin-test-lat" step="any"
                                                    placeholder="14.2785"
                                                    class="w-full px-2 py-1 border border-amber-300 rounded text-xs">
                                            </div>
                                            <div>
                                                <label class="block font-medium mb-1">Longitude</label>
                                                <input type="number" id="admin-test-lng" step="any"
                                                    placeholder="120.8677"
                                                    class="w-full px-2 py-1 border border-amber-300 rounded text-xs">
                                            </div>
                                        </div>

                                        <div class="flex gap-2 mb-2">
                                            <button onclick="setCustomTestLocation()"
                                                class="flex-1 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded text-xs font-medium">Set
                                                Custom</button>
                                            <button onclick="clearTestLocation()"
                                                class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-medium">Clear</button>
                                        </div>

                                        <div class="bg-amber-100 p-2 rounded">
                                            <strong>Status:</strong> <span id="testing-mode-status">Ready</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Manual Location Entry (for users) -->
                            @if (!Auth::user()->isAdmin())
                                <div class="mt-5 bg-violet-50 border border-violet-200 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-violet-900 mb-2 flex items-center">
                                        <i class="fas fa-map-pin mr-2"></i>Having Location Issues?
                                    </h4>
                                    <p class="text-xs text-violet-700 mb-3">If your GPS isn't working properly, you can
                                        manually select your workplace location.</p>
                                    <button onclick="openManualLocationEntry()"
                                        class="w-full px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-sm font-semibold">
                                        <i class="fas fa-edit mr-2"></i>Use Manual Location Entry
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- CHECK-IN BUTTON -->
                        <button id="checkin-btn" disabled
                            class="w-full py-4 bg-gray-300 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm">
                            <i class="fas fa-location-crosshairs mr-2"></i>Waiting for Location...
                        </button>

                        <!-- TODAY'S REGULAR ACTIVITY -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                        <i class="fas fa-history text-indigo-600 mr-2"></i>Today's Regular Activity
                                    </h3>
                                    <p class="text-xs text-gray-600 mt-1">Regular check-in/out logs only</p>
                                </div>
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">
                                    Regular Only
                                </span>
                            </div>
                            <div id="todays-activity" class="space-y-3">
                                <div class="flex justify-center text-gray-500 py-8">
                                    <div class="text-center">
                                        <i class="fas fa-calendar-day text-3xl mb-3 text-gray-300"></i>
                                        <p class="font-medium">No regular activity recorded today</p>
                                        <p class="text-sm text-gray-400 mt-1">Check in to start tracking</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Map Section -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-map text-cyan-600 mr-2"></i>Location Verification
                            </h3>
                            <button onclick="initializeCheckinMap()"
                                class="px-3 py-1.5 bg-cyan-100 text-cyan-700 rounded-lg text-sm hover:bg-cyan-200 font-medium">
                                <i class="fas fa-redo mr-1"></i>Reload
                            </button>
                        </div>

                        <div id="checkin-map" class="w-full h-96 bg-gray-100 rounded-xl relative overflow-hidden">
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <i class="fas fa-map text-3xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500 font-medium">Map will load when you visit</p>
                                <button onclick="initializeCheckinMap()"
                                    class="mt-3 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-sm font-semibold">
                                    <i class="fas fa-play mr-2"></i>Load Map
                                </button>
                            </div>
                        </div>

                        <!-- Map Legend -->
                        <div class="mt-5 bg-gray-50 border border-gray-100 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Map Legend:</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                                <div class="flex items-center gap-2"><span
                                        class="w-3 h-3 rounded-full bg-blue-500"></span>Your Location</div>
                                <div class="flex items-center gap-2"><span
                                        class="w-3 h-3 rounded-full bg-green-500"></span>Workplace</div>
                                <div class="flex items-center gap-2"><span
                                        class="w-3 h-3 rounded-full border-2 border-dashed border-red-500"></span>Geofence
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- My Workplace Section -->
            <div id="my-workplace-section" class="section-content hidden">
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-building text-indigo-600 mr-3"></i>
                        My Workplaces
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Select and view your assigned workplace locations</p>
                </div>

                <!-- Workplaces Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Quick Overview</h3>
                    <div class="grid grid-cols-2 gap-3 lg:gap-6">
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-map-marked-alt text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="workplaces-count-stat">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Assigned Locations</p>
                                <p class="text-xs text-green-600 mt-1">Available workplaces</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-star text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="primary-workplace-name">-</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Primary Workplace</p>
                                <p class="text-xs text-yellow-600 mt-1" id="primary-workplace-subtitle">Not set</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Workplaces Table -->
                <div class="card-modern rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">Your Workplaces</h3>
                                    <p class="text-xs text-gray-600 mt-0.5">Manage and view your assigned workplace locations</p>
                                </div>
                                <button onclick="refreshWorkplaces()"
                                    class="w-9 h-9 sm:w-auto sm:px-3 sm:py-1.5 gradient-success text-white text-xs sm:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern flex items-center justify-center"
                                    title="Refresh workplaces list">
                                    <i class="fas fa-sync-alt sm:mr-2"></i><span class="hidden sm:inline">Refresh</span>
                                </button>
                            </div>
                            
                            <!-- Search, Filter and View All -->
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1">
                                    <input type="text" id="workplace-search" placeholder="Search workplaces..."
                                        class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                                </div>
                                <div class="flex gap-2">
                                    <select id="workplace-filter" onchange="filterWorkplaces()" 
                                        class="flex-1 sm:flex-none px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        title="Filter workplaces by status">
                                        <option value="all">All Workplaces</option>
                                        <option value="primary">Primary Only</option>
                                        <option value="secondary">Secondary</option>
                                    </select>
                                    <button onclick="toggleAllWorkplaces()" id="view-all-btn"
                                        class="px-3 py-2 bg-indigo-100 text-indigo-700 text-sm font-semibold rounded-lg hover:bg-indigo-200 transition-all whitespace-nowrap flex items-center gap-1.5"
                                        title="View all system workplaces">
                                        <i class="fas fa-globe"></i>
                                        <span>View All</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="workplaces-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workplace</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Radius</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="workplaces-table-body">
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-spinner fa-spin text-3xl text-gray-300 mb-3"></i>
                                            <p class="text-sm font-medium">Loading workplaces...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden" id="workplaces-cards">
                        <div class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-spinner fa-spin text-3xl text-gray-300 mb-3"></i>
                                <p class="text-sm font-medium">Loading workplaces...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200" id="workplaces-pagination" style="display: none;">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="text-xs sm:text-sm text-gray-700" id="workplaces-pagination-info">
                                Showing 0 of 0 workplaces
                            </div>
                            <div class="flex items-center gap-1 justify-center sm:justify-end">
                                <button id="workplaces-prev-btn"
                                    class="px-2 lg:px-3 py-1 lg:py-1.5 border border-gray-300 rounded-lg text-xs lg:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    onclick="changeWorkplacesPage(-1)" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div id="workplaces-page-numbers" class="flex gap-1">
                                    <!-- Page numbers will be dynamically generated -->
                                </div>
                                <button id="workplaces-next-btn"
                                    class="px-2 lg:px-3 py-1 lg:py-1.5 border border-gray-300 rounded-lg text-xs lg:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    onclick="changeWorkplacesPage(1)" disabled>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- No Workplaces Message -->
                    <div id="no-workplaces-message" class="hidden p-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
                            <h4 class="text-lg font-bold text-gray-900 mb-2">No Workplaces Assigned</h4>
                            <p class="text-gray-600 mb-4 text-sm">Contact your administrator to assign a workplace</p>
                            <button onclick="refreshWorkplaces()"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold text-sm">
                                <i class="fas fa-sync-alt mr-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selected Workplace Details -->
                <div class="card-modern rounded-2xl shadow-sm overflow-hidden" id="workplace-details-card" style="display: none;">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Workplace Details
                        </h3>
                    </div>

                    <div id="selected-workplace-details" class="p-4 sm:p-5 lg:p-6">
                        <!-- Details will be populated by JS -->
                    </div>

                    <div id="workplace-map-container" class="p-4 sm:p-5 lg:p-6 pt-0">
                        <h4 class="text-base lg:text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-map text-green-600 mr-2"></i>
                            Location Map
                        </h4>
                        <div id="workplace-map" class="w-full h-64 lg:h-80 bg-gray-100 rounded-xl relative overflow-hidden shadow-inner">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-map text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500 font-medium text-sm">Loading map...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Check-in Section -->
            <div id="special-checkin-section" class="section-content hidden">
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <div class="card-modern rounded-2xl shadow-sm p-8">
                            <div class="text-center mb-6">
                                <div class="icon-wrapper gradient-warning mx-auto mb-4">
                                    <i class="fas fa-star text-white text-3xl"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">Special Check In/Out</h2>
                                <p class="text-gray-600">Check in/out up to 4 times per day at any location</p>
                            </div>

                            <!-- Workplace Information -->
                            <div class="info-card mb-6 border-yellow-200 bg-yellow-50">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-building text-yellow-600 mr-2"></i>Workplace Information
                                </h4>
                                
                                <!-- Primary Workplace -->
                                <div class="mb-3 pb-3 border-b border-yellow-200">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-gray-600 font-medium">Primary Workplace:</span>
                                        <span id="special-primary-distance" class="text-gray-400 text-xs">-- m</span>
                                    </div>
                                    <span id="special-primary-workplace-display" class="text-sm font-semibold text-gray-700 block">Not set</span>
                                </div>
                                
                                <!-- Current Workplace (Detected) -->
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-gray-600 font-medium">Current Location:</span>
                                        <span id="special-current-distance" class="text-yellow-600 font-semibold text-xs">-- m</span>
                                    </div>
                                    <span id="special-current-workplace-display" class="text-sm font-semibold text-gray-700 block">Detecting...</span>
                                    <p id="special-current-workplace-address" class="text-xs text-gray-500 mt-1">
                                        Waiting for location...
                                    </p>
                                </div>
                            </div>

                            <!-- Location Status -->
                            <div class="info-card mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-gray-700">Location Status:</span>
                                    <span class="status-badge bg-yellow-100 text-yellow-800"
                                        id="special-location-badge">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600" id="special-current-location">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Getting your location...
                                </div>
                            </div>

                            <!-- Special Check-in Button -->
                            <button id="special-checkin-btn"
                                class="w-full py-4 bg-gray-400 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm mb-4"
                                disabled>
                                <i class="fas fa-location-crosshairs mr-2"></i>
                                Waiting for Location...
                            </button>

                            <!-- Special Check-in Info -->
                            <div class="info-card border-blue-200 bg-blue-50">
                                <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>About Special Check-in
                                </h4>
                                <ul class="text-xs text-blue-800 space-y-1.5">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                        <span>Up to 4 check-in/out pairs per day (8 total actions)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                        <span>Can check in at ANY workplace in the system</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                        <span>First check-in locks you to that workplace for the day</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                        <span>No lunch break required</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-600 mr-2 mt-0.5"></i>
                                        <span>Perfect for field work or off-site assignments</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Today's Special Activity -->
                        <div class="card-modern rounded-2xl shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                        <i class="fas fa-star text-yellow-600 mr-2"></i>
                                        Today's Special Activity
                                    </h3>
                                    <p class="text-xs text-gray-600 mt-1">Special check-in/out logs only (max 4 pairs)</p>
                                </div>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                    Special Only
                                </span>
                            </div>
                            <div class="space-y-3" id="special-todays-activity">
                                <div class="flex items-center justify-center p-8 text-gray-500"
                                    id="special-activity-empty">
                                    <div class="text-center">
                                        <i class="fas fa-calendar-day text-3xl mb-3 text-gray-300"></i>
                                        <p class="font-medium">No special activity today</p>
                                        <p class="text-sm text-gray-400 mt-1">Check in to start tracking</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Special Location Map -->
                    <div class="card-modern rounded-2xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-map text-yellow-600 mr-2"></i>
                                Special Location Verification
                            </h3>
                            <button onclick="initializeSpecialCheckinMap()"
                                class="px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm hover:bg-yellow-200 transition-colors font-medium">
                                <i class="fas fa-redo mr-1"></i>Reload
                            </button>
                        </div>
                        <div id="special-checkin-map"
                            class="w-full h-96 bg-gray-100 rounded-xl relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-map text-3xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-500 mb-2 font-medium">Map will load when you visit</p>
                                    <button onclick="initializeSpecialCheckinMap()"
                                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-semibold">
                                        <i class="fas fa-play mr-2"></i>Load Map
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 info-card">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Map Legend:</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="map-legend-item">
                                    <div class="map-legend-dot bg-yellow-500"></div>
                                    <span>Your Location</span>
                                </div>
                                <div class="map-legend-item">
                                    <div class="map-legend-dot bg-yellow-500"></div>
                                    <span>Special Location</span>
                                </div>
                                <div class="map-legend-item">
                                    <div class="map-legend-dot border-2 border-dashed border-yellow-500"></div>
                                    <span>Geofence</span>
                                </div>
                            </div>
                        </div>

                        <!-- Special Workflow Status -->
                        <div class="mt-6 info-card border-yellow-200 bg-yellow-50">
                            <h4 class="text-sm font-semibold text-yellow-900 mb-3 flex items-center">
                                <i class="fas fa-tasks mr-2"></i>Special Workflow Status
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-yellow-700 font-medium">Today's Activity:</span>
                                    <span class="font-bold text-yellow-900" id="special-checkins-count">0/4
                                        pairs</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-yellow-700 font-medium">Last Action:</span>
                                    <span class="font-bold text-yellow-900" id="special-last-action">None</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-yellow-700 font-medium">Status:</span>
                                    <span class="font-bold text-yellow-900" id="special-workflow-status">Ready</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance History Section -->
            <div id="attendance-history-section" class="section-content hidden">
                <div class="card-modern rounded-2xl shadow-sm p-6 lg:p-8">
                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 flex items-center mb-4">
                            <i class="fas fa-history text-indigo-600 mr-3"></i>
                            My Attendance History
                        </h2>

                        <!-- Controls -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <!-- View Toggle -->
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 font-medium">View:</span>
                                <div class="flex bg-gray-100 rounded-lg p-1">
                                    <button id="detailed-view-btn"
                                        class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white transition-colors font-semibold"
                                        onclick="switchAttendanceView('detailed')">
                                        Detailed
                                    </button>
                                    <button id="summary-view-btn"
                                        class="px-4 py-2 text-sm rounded-lg text-gray-600 hover:bg-white transition-colors font-medium"
                                        onclick="switchAttendanceView('summary')">
                                        Summary
                                    </button>
                                </div>
                            </div>

                            <!-- Records per page and filter -->
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center space-x-2">
                                    <label for="records-per-page"
                                        class="text-sm text-gray-600 font-medium whitespace-nowrap">Per page:</label>
                                    <select id="records-per-page"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 font-medium"
                                        onchange="changeRecordsPerPage(this.value)">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                                <select id="attendance-period-filter"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 font-medium"
                                    onchange="filterAttendanceByPeriod(this.value)">
                                    <option value="thisweek">This Week</option>
                                    <option value="lastweek">Last Week</option>
                                    <option value="thismonth">This Month</option>
                                    <option value="lastmonth">Last Month</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Summary -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div class="info-card border-green-200 bg-green-50">
                            <div class="text-center">
                                <h3 class="text-2xl lg:text-3xl font-bold text-green-600" id="weekly-hours">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-sm text-green-700 font-semibold">Total Hours</p>
                                <p class="text-xs text-green-600 mt-1">This Week</p>
                            </div>
                        </div>
                        <div class="info-card border-blue-200 bg-blue-50">
                            <div class="text-center">
                                <h3 class="text-2xl lg:text-3xl font-bold text-blue-600" id="weekly-days">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-sm text-blue-700 font-semibold">Days Present</p>
                                <p class="text-xs text-blue-500" id="weekly-date-range">Loading...</p>
                                <p class="text-sm text-blue-600 mt-1" id="weekly-days-total">Out of 0</p>
                            </div>
                        </div>
                        <div class="info-card border-yellow-200 bg-yellow-50">
                            <div class="text-center">
                                <h3 class="text-2xl lg:text-3xl font-bold text-yellow-600" id="weekly-avg-checkin">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-sm text-yellow-700 font-semibold">Avg Check-in</p>
                                <p class="text-sm text-yellow-600 mt-1" id="weekly-checkin-trend">Loading...</p>
                            </div>
                        </div>
                        <div class="info-card border-indigo-200 bg-indigo-50">
                            <div class="text-center">
                                <h3 class="text-2xl lg:text-3xl font-bold text-indigo-600" id="weekly-attendance">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-sm text-indigo-700 font-semibold">Attendance</p>
                                <p class="text-sm text-indigo-600 mt-1" id="weekly-performance">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <!-- Desktop Table View (hidden on mobile) -->
                    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Action</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Time</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Shift Type</th>
                                    <th
                                        class="hidden lg:table-cell px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Location</th>
                                    <th
                                        class="px-4 lg:px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="attendance-history-tbody">
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-spinner fa-spin text-2xl mb-3 text-gray-300"></i>
                                            <p class="font-medium">Loading attendance history...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (visible only on mobile) -->
                    <div class="md:hidden space-y-3" id="attendance-history-mobile">
                        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-spinner fa-spin text-xl mb-2 text-gray-300"></i>
                                <p class="font-medium text-sm">Loading attendance history...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination - Responsive -->
                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row items-center justify-between gap-3"
                        id="attendance-pagination">
                        <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left" id="pagination-info">
                            No records to show
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2" id="pagination-controls">
                            <button id="prev-btn"
                                class="px-2 sm:px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="changePage(-1)" disabled>
                                Prev
                            </button>
                            <div id="page-numbers" class="flex space-x-1">
                                <!-- Page numbers will be dynamically generated -->
                            </div>
                            <button id="next-btn"
                                class="px-2 sm:px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="changePage(1)" disabled>
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absence History Section -->
            <div id="absence-history-section" class="section-content hidden">
                <div class="section-header">
                    <h2 class="text-3xl font-bold text-gray-800">Absence History</h2>
                    <p class="text-gray-600 mt-2">View your absence records and statistics</p>
                </div>

                <!-- Info Banner -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400 text-xl mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong class="font-semibold">Information:</strong> This is a read-only view of your
                                absence records for informational purposes.
                                Absences are automatically tracked based on workdays without check-ins. If you need to
                                update any information or have concerns about your records,
                                please contact your administrator.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Request Leave Section -->
                <div class="card-modern rounded-xl shadow-sm overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-calendar-plus mr-2 text-indigo-600"></i>
                                    Request Leave
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Inform your administrator about upcoming leave in
                                    advance</p>
                            </div>
                            <button onclick="toggleAbsenceRequestForm()" id="toggle-absence-form-btn"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>New Request
                            </button>
                        </div>
                    </div>

                    <!-- Leave Request Form (Hidden by default) -->
                    <div id="absence-request-form" class="p-6 border-b border-gray-200 bg-white hidden">
                        <form id="absence-request-form-element" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="absence-start-date"
                                        class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                    <input type="date" id="absence-start-date" name="start_date" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="absence-end-date"
                                        class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                    <input type="date" id="absence-end-date" name="end_date" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label for="absence-reason"
                                    class="block text-sm font-medium text-gray-700 mb-2">Reason for Leave</label>
                                <textarea id="absence-reason" name="reason" rows="3" required minlength="10" maxlength="500"
                                    placeholder="Please provide a detailed reason for your leave (minimum 10 characters)..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                <p class="text-xs text-gray-500 mt-1"><span id="reason-char-count">0</span>/500
                                    characters</p>
                            </div>
                            <div class="flex gap-3">
                                <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                    <i class="fas fa-paper-plane mr-2"></i>Submit Request
                                </button>
                                <button type="button" onclick="toggleAbsenceRequestForm()"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- My Leave Requests -->
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">My Leave Requests</h4>
                        <div id="absence-requests-list" class="space-y-3">
                            <div class="flex items-center justify-center py-8 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mr-3"></i>
                                <span>Loading requests...</span>
                            </div>
                        </div>

                        <!-- Pagination for Leave Requests -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-200"
                            id="absence-requests-pagination" style="display: none;">
                            <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left"
                                id="requests-pagination-info">
                                Showing 0 of 0 requests
                            </div>
                            <div class="flex flex-wrap items-center justify-center gap-2"
                                id="requests-pagination-controls">
                                <button id="requests-prev-btn"
                                    class="px-2 sm:px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    onclick="changeRequestsPage(-1)" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div id="requests-page-numbers" class="flex space-x-1">
                                    <!-- Page numbers will be dynamically generated -->
                                </div>
                                <button id="requests-next-btn"
                                    class="px-2 sm:px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    onclick="changeRequestsPage(1)" disabled>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absence Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <!-- This Week Absences -->
                    <div class="card-modern rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">This Week</p>
                                <h3 class="text-3xl font-bold text-red-600 mt-2" id="week-absences">0</h3>
                                <p class="text-xs text-gray-500 mt-1">Absent days</p>
                            </div>
                            <div class="icon-wrapper bg-red-100">
                                <i class="fas fa-calendar-times text-2xl text-red-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- This Month Absences -->
                    <div class="card-modern rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">This Month</p>
                                <h3 class="text-3xl font-bold text-orange-600 mt-2" id="month-absences">0</h3>
                                <p class="text-xs text-gray-500 mt-1">Total absences</p>
                            </div>
                            <div class="icon-wrapper bg-orange-100">
                                <i class="fas fa-calendar-xmark text-2xl text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Excused Absences -->
                    <div class="card-modern rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Excused</p>
                                <h3 class="text-3xl font-bold text-blue-600 mt-2" id="excused-absences">0</h3>
                                <p class="text-xs text-gray-500 mt-1">Approved</p>
                            </div>
                            <div class="icon-wrapper bg-blue-100">
                                <i class="fas fa-check-circle text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unexcused Absences -->
                    <div class="card-modern rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Unexcused</p>
                                <h3 class="text-3xl font-bold text-red-700 mt-2" id="unexcused-absences">0</h3>
                                <p class="text-xs text-gray-500 mt-1">Needs attention</p>
                            </div>
                            <div class="icon-wrapper bg-red-200">
                                <i class="fas fa-exclamation-triangle text-2xl text-red-700"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Rate -->
                    <div class="card-modern rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Attendance Rate</p>
                                <h3 class="text-3xl font-bold text-green-600 mt-2" id="attendance-rate-absence">0%
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">This month</p>
                            </div>
                            <div class="icon-wrapper bg-green-100">
                                <i class="fas fa-chart-line text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absence Records Table -->
                <div class="card-modern rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <h3 class="text-xl font-semibold text-gray-800">Absence Records</h3>
                            <div class="flex items-center gap-3">
                                <select id="absence-month-filter"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="current">Current Month</option>
                                    <option value="last30">Last 30 Days</option>
                                    <option value="last90">Last 90 Days</option>
                                </select>
                                <button onclick="refreshAbsenceData()"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View (hidden on mobile) -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Day</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Reason</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody id="absence-history-tbody" class="divide-y divide-gray-200">
                                <!-- Data will be populated by JavaScript -->
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                        <p>Loading absence data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (visible only on mobile) -->
                    <div class="md:hidden space-y-3 p-4" id="absence-history-mobile">
                        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-spinner fa-spin text-xl mb-2 text-gray-300"></i>
                                <p class="font-medium text-sm">Loading absence data...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="absence-empty-state" class="hidden p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <i class="fas fa-check-circle text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Perfect Attendance!</h3>
                        <p class="text-gray-600">You have no absence records for the selected period. Keep up the great
                            work!</p>
                    </div>

                    <!-- Pagination for Absence Records -->
                    <div class="p-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200"
                        id="absence-records-pagination" style="display: none;">
                        <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left"
                            id="records-pagination-info">
                            Showing 0 of 0 records
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2"
                            id="records-pagination-controls">
                            <button id="records-prev-btn"
                                class="px-2 sm:px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="changeRecordsPage(-1)" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div id="records-page-numbers" class="flex space-x-1">
                                <!-- Page numbers will be dynamically generated -->
                            </div>
                            <button id="records-next-btn"
                                class="px-2 sm:px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="changeRecordsPage(1)" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profile-section" class="section-content hidden">
                <div class="card-modern rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-user-cog mr-3"></i>
                            Profile Settings
                        </h3>
                        <p class="text-purple-100 text-sm mt-1">Update your account information and preferences</p>
                    </div>

                    <div class="p-6">
                        <form id="profile-form" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-user text-indigo-600 mr-2"></i>Full Name
                                    </label>
                                    <input type="text" id="profile-name" value="{{ Auth::user()->name }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Enter your full name">
                                </div>

                                <!-- Email (Read-only) -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-envelope text-indigo-600 mr-2"></i>Email Address
                                    </label>
                                    <input type="email" value="{{ Auth::user()->email }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                                        readonly disabled>
                                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed. Contact admin if needed.</p>
                                </div>

                                <!-- Phone Number -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-phone text-green-600 mr-2"></i>Phone Number
                                    </label>
                                    <input type="tel" id="profile-phone" value="{{ Auth::user()->phone_number ?? '' }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        placeholder="+639171234567">
                                    <p class="text-xs text-gray-500 mt-1">Required for SMS notifications. Format: +639XXXXXXXXX</p>
                                </div>

                                <!-- Password (Optional) -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-lock text-red-600 mr-2"></i>New Password
                                    </label>
                                    <input type="password" id="profile-password"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                                        placeholder="Leave blank to keep current password">
                                    <p class="text-xs text-gray-500 mt-1">Only fill if you want to change your password</p>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                                <button type="button" onclick="resetProfileForm()"
                                    class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-all">
                                    <i class="fas fa-undo mr-2"></i>Reset
                                </button>
                                <button type="submit"
                                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.125rem 0;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #64748b;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            -webkit-tap-highlight-color: rgba(79, 70, 229, 0.1);
            user-select: none;
            -webkit-user-select: none;
        }

        .sidebar-link:hover {
            background-color: #f8fafc;
            color: #4f46e5;
        }

        /* Active state for touch devices */
        .sidebar-link:active {
            background-color: #e0e7ff;
            color: #4f46e5;
            transform: scale(0.98);
        }

        .sidebar-link.active {
            background-color: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
        }

        .sidebar-link i {
            width: 1.25rem;
            margin-right: 0.75rem;
        }

        .section-content {
            display: block;
        }

        .section-content.hidden {
            display: none;
        }

        /* Map container styling */
        #checkin-map,
        #setup-map {
            width: 100%;
            height: 24rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        /* Leaflet popup customization */
        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .leaflet-popup-content {
            margin: 12px 16px;
            font-family: inherit;
        }

        /* Custom marker styles */
        .user-location-marker,
        .workplace-marker {
            position: relative;
        }

        /* Fix z-index for Leaflet maps to prevent overlapping with modals */
        .leaflet-container,
        .leaflet-map-pane,
        .leaflet-popup-pane,
        .leaflet-marker-pane,
        .leaflet-tile-pane,
        .leaflet-shadow-pane,
        .leaflet-overlay-pane,
        .leaflet-control-container {
            z-index: 1 !important;
        }

        .leaflet-popup {
            z-index: 1000 !important;
        }

        /* Ensure map containers have proper z-index */
        #checkin-map,
        #workplace-map,
        #setup-map {
            z-index: 1 !important;
            position: relative;
        }

        /* Make sure modals always stay on top */
        .modal-blur,
        .fixed.inset-0 {
            z-index: 9999 !important;
        }

        /* Pulse animation for location indicator */
        @keyframes pulse-ring {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 0;
            }
        }

        /* Special Check-in Styles */
        .special-location-marker {
            z-index: 1000;
        }

        .special-geofence-circle {
            stroke-dasharray: 5, 5;
            animation: dash 20s linear infinite;
        }

        @keyframes dash {
            to {
                stroke-dashoffset: -100;
            }
        }

        /* Yellow theme for special check-in */
        .bg-special-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .text-special {
            color: #d97706;
        }

        .bg-special-light {
            background-color: #fef3c7;
        }

        .border-special {
            border-color: #f59e0b;
        }
    </style>

    <script>
        // Simple mobile sidebar toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
        }

        // Section switching functionality
        function switchToSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });

            // Show selected section
            const sectionId = sectionName + '-section';
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
                console.log('Switched to section:', sectionId);
            } else {
                console.warn('Section not found:', sectionId);
            }

            // Close sidebar on mobile ONLY if it's currently open
            if (window.innerWidth < 1024) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                    toggleMobileSidebar();
                }
            }

            // Update sidebar active state
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });

            const activeLink = document.querySelector(`[data-section="${sectionName}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }

            // Update page title and subtitle
            const titles = {
                'dashboard': ['My Dashboard', 'Welcome to your attendance management portal'],
                'my-workplace': ['Workplace', 'View and select your assigned workplace locations'],
                'gps-checkin': ['Check In/Out', 'GPS location verification for attendance tracking'],
                'special-checkin': ['Special Check In', 'Up to 4 check-ins/outs per day at assigned locations'],
                'attendance-history': ['History', 'View your detailed attendance records and summaries'],
                'absence-history': ['Absence Records', 'View your absence history and attendance statistics'],
                'profile': ['Profile', 'Manage your account settings and preferences']
            };

            if (titles[sectionName]) {
                document.getElementById('page-title').textContent = titles[sectionName][0];
                document.getElementById('page-subtitle').textContent = titles[sectionName][1];
            }

            // Refresh data when switching to attendance history
            if (sectionName === 'attendance-history') {
                console.log('Refreshing attendance history data...');
                fetchAttendanceHistory();
            }

            // Refresh data when switching to absence history
            if (sectionName === 'absence-history') {
                console.log('Loading absence data...');
                fetchAbsenceRecords();
                fetchWeeklyAbsenceSummary();
                fetchMonthlyAbsenceSummary();
                fetchAbsenceRequests(); // Load user's absence requests
            }

            // Refresh data when switching to my-workplace
            if (sectionName === 'my-workplace') {
                console.log('Loading workplace data...');
                fetchUserWorkplaces();
            }

            // Initialize GPS check-in map only when user switches to that section
            if (sectionName === 'gps-checkin') {
                console.log('Initializing GPS check-in section...');
                setTimeout(() => {
                    initializeCheckinMap();
                    checkTodayCheckinType(); // Check if special check-in was used

                    // Refresh location and status when entering GPS check-in section
                    if (userLocation && hasLocationPermission) {
                        console.log('Refreshing location status for GPS check-in section...');
                        updateLocationStatus('success', userLocation);
                        updateGeofenceStatus(userLocation);
                        fetchCurrentStatus();
                    } else {
                        console.log('No location available, initializing location tracking...');
                        initializeSmartLocation();
                    }
                }, 100); // Small delay to ensure DOM is ready
            }

            if (sectionName === 'special-checkin') {
                console.log('Initializing special check-in section...');
                setTimeout(() => {
                    initializeSpecialCheckinMap();
                    fetchSpecialLocations();
                    fetchSpecialCheckinLogs();
                    checkTodayCheckinType(); // Check if regular check-in was used

                    // Update location status if we have it
                    if (userLocation && hasLocationPermission) {
                        console.log('Refreshing location status for special check-in section...');
                        updateSpecialLocationStatus(userLocation);
                    } else {
                        console.log('No location available, initializing location tracking...');
                        initializeSmartLocation();
                    }
                }, 100);
            }

            // Refresh other sections as needed
            if (sectionName === 'dashboard') {
                fetchUserStats();
                fetchTodaysActivity();
                fetchTodaysSchedule();
                fetchCurrentStatus();
            }
        }




        // API Functions to fetch real data
        async function fetchUserStats(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                const response = await fetch(`/api/user-stats/${userId}`);
                const data = await response.json();

                // Update dashboard stats with real data
                const checkinsEl = document.getElementById('my-checkins');
                const avgCheckinEl = document.getElementById('avg-checkin');
                const hoursEl = document.getElementById('hours-worked');

                if (checkinsEl) checkinsEl.textContent = data.days_present_this_month || '0';
                if (avgCheckinEl) avgCheckinEl.textContent = data.average_checkin_time || 'N/A';
                if (hoursEl) hoursEl.textContent = data.today_hours || '0.0 hrs';

                // Update attendance rate display with more context
                const attendanceRateEl = document.getElementById('attendance-rate');
                const attendanceRateMobileEl = document.getElementById('attendance-rate-mobile');
                if (attendanceRateEl || attendanceRateMobileEl) {
                    const daysPresent = data.days_present_this_month || 0;
                    const totalWorkDays = data.total_work_days_this_month || 0;

                    let rateText, mobileText;
                    if (totalWorkDays > 0) {
                        const percentage = Math.round((daysPresent / totalWorkDays) * 100);
                        rateText = `${percentage}% (${daysPresent} of ${totalWorkDays} work days)`;
                        mobileText = `${percentage}% rate`;
                    } else if (daysPresent > 0) {
                        rateText = `${daysPresent} day(s) this month`;
                        mobileText = `${daysPresent} days`;
                    } else {
                        rateText = 'No attendance data yet';
                        mobileText = 'No data';
                    }

                    if (attendanceRateEl) attendanceRateEl.textContent = rateText;
                    if (attendanceRateMobileEl) attendanceRateMobileEl.textContent = mobileText;
                }

                // Update check-in trend display
                const checkinTrendEl = document.getElementById('checkin-trend');
                const checkinTrendMobileEl = document.getElementById('checkin-trend-mobile');
                if (checkinTrendEl || checkinTrendMobileEl) {
                    let trendText, mobileTrend;
                    if (data.days_present_this_month > 0) {
                        trendText = 'Based on ' + data.days_present_this_month + ' day(s)';
                        mobileTrend = data.days_present_this_month + ' days';
                    } else {
                        trendText = 'No check-ins yet';
                        mobileTrend = 'No data';
                    }

                    if (checkinTrendEl) checkinTrendEl.textContent = trendText;
                    if (checkinTrendMobileEl) checkinTrendMobileEl.textContent = mobileTrend;
                }

                // Update status text
                const workStatusEl = document.getElementById('work-status');
                const workStatusMobileEl = document.getElementById('work-status-mobile');
                const statusText = data.current_status || 'Not checked in';
                const mobileStatus = data.current_status === 'Checked In' ? 'Active' : 'Inactive';

                if (workStatusEl) workStatusEl.textContent = statusText;
                if (workStatusMobileEl) workStatusMobileEl.textContent = mobileStatus;

                console.log('User stats updated:', data);

            } catch (error) {
                console.error('Failed to fetch user stats:', error);
                // Show error states instead of loading
                const checkinsEl = document.getElementById('my-checkins');
                const avgCheckinEl = document.getElementById('avg-checkin');
                const hoursEl = document.getElementById('hours-worked');

                if (checkinsEl) checkinsEl.textContent = 'Error';
                if (avgCheckinEl) avgCheckinEl.textContent = 'Error';
                if (hoursEl) hoursEl.textContent = 'Error';
            }
        }

        async function fetchAttendanceHistory(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                // Fetch summary data and attendance logs separately
                const [summaryResponse] = await Promise.all([
                    fetch(`/api/attendance-history/${userId}`)
                ]);

                const summaryData = await summaryResponse.json();
                let logsData = [];

                // Fetch ALL attendance logs (including today's) from a single source
                try {
                    const logsResponse = await fetch(`/api/attendance-logs/${userId}`);
                    if (logsResponse.ok) {
                        logsData = await logsResponse.json();
                        console.log('Raw logs fetched from API:', logsData);

                        // Remove any potential duplicates by creating a unique key
                        const uniqueLogs = [];
                        const seenKeys = new Set();

                        logsData.forEach(log => {
                            // Create unique key based on date, time, and action
                            const uniqueKey = `${log.date_raw}-${log.timestamp}-${log.action}`;
                            if (!seenKeys.has(uniqueKey)) {
                                seenKeys.add(uniqueKey);
                                uniqueLogs.push(log);
                            } else {
                                console.warn('Duplicate log detected and removed:', log);
                            }
                        });

                        logsData = uniqueLogs;
                        console.log('Deduplicated logs:', logsData);

                        // Debug: Check for check-out logs
                        const checkOutLogs = logsData.filter(log => log.action === 'check_out');
                        console.log('Check-out logs found:', checkOutLogs);
                    }
                } catch (logError) {
                    console.log('Logs endpoint not available:', logError);
                    // Fallback: Try to get today's logs from current status
                    try {
                        const statusResponse = await fetch(`/api/current-status/${userId}`);
                        if (statusResponse.ok) {
                            const statusData = await statusResponse.json();
                            if (statusData.logs && statusData.logs.length > 0) {
                                logsData = statusData.logs.map(log => ({
                                    action: log.action,
                                    timestamp: log.timestamp,
                                    shift_type: log.shift_type || 'regular',
                                    location: 'Workplace',
                                    date: new Date().toLocaleDateString(),
                                    date_raw: new Date().toISOString().split('T')[0]
                                }));
                            }
                        }
                    } catch (statusError) {
                        console.log('Status endpoint also failed:', statusError);
                    }
                }

                // Enhanced: Attach logs to summary data for proper hour calculation  
                const enhancedSummaryData = summaryData.map(attendance => {
                    // Find logs for this date
                    const dayLogs = logsData.filter(log => log.date_raw === attendance.date_raw);
                    return {
                        ...attendance,
                        logs: dayLogs
                    };
                });

                // Only create mock logs if we have no real logs at all
                if (logsData.length === 0 && summaryData.length > 0) {
                    console.log('No real logs found, creating mock logs from summary data');
                    logsData = createDetailedLogsFromSummary(summaryData);
                } else if (logsData.length > 0) {
                    console.log(`Using ${logsData.length} real logs from database`);
                }

                // Cache the enhanced data
                cachedAttendanceData.summary = enhancedSummaryData;
                cachedAttendanceData.logs = logsData;

                // Reset pagination when new data is loaded
                attendancePagination.currentPage = 1;

                // Display based on current view mode
                displayAttendanceData();

                // Update weekly summary based on enhanced summary data and current period filter
                const filteredData = getFilteredAttendanceByPeriod(enhancedSummaryData, currentAttendancePeriod);
                updateWeeklySummary(filteredData, currentAttendancePeriod);

            } catch (error) {
                console.error('Failed to fetch attendance history:', error);
                // Show error state
                const tbody = document.getElementById('attendance-history-tbody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-red-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                                    <p>Failed to load attendance history</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        }

        // Add this after the regular GPS check-in functions (around line 1500)

        // Special Check-in Variables
        let specialCheckinMap = null;
        let specialUserMarker = null;
        let specialWorkplaceMarkers = [];
        let specialWorkplaceCircles = [];
        let selectedSpecialLocationId = null;

        // Initialize Special Check-in Map
        function initializeSpecialCheckinMap() {
            const mapContainer = document.getElementById('special-checkin-map');
            if (!mapContainer) {
                console.warn('Special check-in map container not found');
                return;
            }

            // If map already exists, just refresh data
            if (specialCheckinMap) {
                console.log('Special check-in map already initialized, refreshing data...');
                refreshSpecialCheckinMapData();
                return;
            }

            // Show loading state
            showMapLoadingState('special-checkin-map');

            // Initialize with user location or fallback
            let lat = 14.5995;
            let lng = 120.9842;
            let hasUserLocation = false;

            if (userLocation && userLocation.coords) {
                lat = userLocation.coords.latitude;
                lng = userLocation.coords.longitude;
                hasUserLocation = true;
            }

            try {
                // Remove existing map if present
                if (specialCheckinMap) {
                    specialCheckinMap.remove();
                    specialCheckinMap = null;
                }

                // Initialize Leaflet map - Always center on user location
                specialCheckinMap = L.map('special-checkin-map', {
                    zoomControl: true,
                    attributionControl: false,
                    maxZoom: 18,
                    minZoom: 10,
                    preferCanvas: true
                }).setView([lat, lng], hasUserLocation ? 17 : 12);

                // Add tile layer
                const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 18,
                    tileSize: 256,
                    crossOrigin: true
                });

                tileLayer.on('load', () => {
                    console.log('Special check-in map tiles loaded');
                    hideMapLoadingState('special-checkin-map');
                });

                tileLayer.addTo(specialCheckinMap);

                // Add markers and data
                addSpecialCheckinMapMarkers(lat, lng, hasUserLocation);

                // Set timeout fallback
                setTimeout(() => {
                    hideMapLoadingState('special-checkin-map');
                }, 5000);

            } catch (error) {
                console.error('Error initializing special check-in map:', error);
                showMapError('special-checkin-map', 'Failed to load map. Please try refreshing.');
            }
        }

        // Add markers to special check-in map
        function addSpecialCheckinMapMarkers(lat, lng, hasUserLocation) {
            if (!specialCheckinMap) return;

            // Add user location marker if available - make it prominent with pin shape
            if (hasUserLocation && userLocation) {
                specialUserMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: `<div style="position: relative; width: 30px; height: 40px;">
                            <svg width="30" height="40" viewBox="0 0 30 40" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 0C8.373 0 3 5.373 3 12c0 9 12 28 12 28s12-19 12-28c0-6.627-5.373-12-12-12z" 
                                    fill="#3b82f6" stroke="#ffffff" stroke-width="2"/>
                                <circle cx="15" cy="12" r="5" fill="#ffffff"/>
                            </svg>
                        </div>`,
                        iconSize: [30, 40],
                        iconAnchor: [15, 40],
                        popupAnchor: [0, -40]
                    }),
                    zIndexOffset: 1000
                }).addTo(specialCheckinMap);

                specialUserMarker.bindPopup('<b>Your Current Location</b>');
                
                // Start by centering on user location at close zoom
                specialCheckinMap.setView([lat, lng], 18);
            }

            // Fetch and add all assigned special locations
            fetchSpecialLocations();
        }

        // Refresh special check-in map data
        function refreshSpecialCheckinMapData() {
            if (!specialCheckinMap) return;

            console.log('Refreshing special check-in map data...');

            // Remove existing markers and circles
            specialWorkplaceMarkers.forEach(marker => specialCheckinMap.removeLayer(marker));
            specialWorkplaceCircles.forEach(circle => specialCheckinMap.removeLayer(circle));
            specialWorkplaceMarkers = [];
            specialWorkplaceCircles = [];

            if (specialUserMarker) {
                specialCheckinMap.removeLayer(specialUserMarker);
            }

            // Re-add markers with current data
            const lat = userLocation ? userLocation.coords.latitude : 14.5995;
            const lng = userLocation ? userLocation.coords.longitude : 120.9842;
            const hasUserLocation = userLocation && userLocation.coords;

            addSpecialCheckinMapMarkers(lat, lng, hasUserLocation);
        }

        // Fetch assigned special locations
        async function fetchSpecialLocations(userId = null) {
            userId = userId || getCurrentUserId();

            try {
                const response = await fetch(`/api/user-workplaces/${userId}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Special locations fetched:', data);

                const locationSelect = document.getElementById('special-location-select');
                const locationInfo = document.getElementById('special-location-info');

                if (data.workplaces && data.workplaces.length > 0) {
                    // Populate dropdown
                    let optionsHtml = '<option value="">Select a location...</option>';
                    data.workplaces.forEach(workplace => {
                        optionsHtml += `<option value="${workplace.id}" 
                    data-lat="${workplace.latitude}" 
                    data-lng="${workplace.longitude}" 
                    data-radius="${workplace.radius}">
                    ${workplace.name}
                </option>`;
                    });

                    if (locationSelect) {
                        locationSelect.innerHTML = optionsHtml;
                        locationSelect.disabled = false;
                    }

                    if (locationInfo) {
                        locationInfo.textContent =
                            `${data.workplaces.length} location(s) available for special check-in`;
                    }

                    // Add workplaces to map
                    data.workplaces.forEach(workplace => {
                        if (specialCheckinMap && workplace.latitude && workplace.longitude) {
                            // Add marker
                            const marker = L.marker([workplace.latitude, workplace.longitude], {
                                icon: L.divIcon({
                                    className: 'special-location-marker',
                                    html: '<div style="background: #f59e0b; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                                    iconSize: [16, 16],
                                    iconAnchor: [8, 8]
                                })
                            }).addTo(specialCheckinMap);

                            marker.bindPopup(
                                `<b>${workplace.name}</b><br>${workplace.address || 'Special Location'}`);
                            specialWorkplaceMarkers.push(marker);

                            // Add geofence circle
                            const circle = L.circle([workplace.latitude, workplace.longitude], {
                                color: '#f59e0b',
                                fillColor: '#f59e0b',
                                fillOpacity: 0.1,
                                radius: workplace.radius || 100,
                                weight: 2,
                                dashArray: '5, 5',
                                className: 'special-geofence-circle'
                            }).addTo(specialCheckinMap);

                            specialWorkplaceCircles.push(circle);
                        }
                    });

                    // Show user location first, then fit bounds to show all locations
                    // This gives user context of their position, then shows full picture
                    if (data.workplaces.length > 0 && specialCheckinMap) {
                        if (specialUserMarker) {
                            // User location is already centered from addSpecialCheckinMapMarkers
                            // After a brief moment, adjust to show all workplaces
                            setTimeout(() => {
                                if (data.workplaces.length > 1) {
                                    const group = L.featureGroup([specialUserMarker, ...specialWorkplaceMarkers]);
                                    specialCheckinMap.fitBounds(group.getBounds().pad(0.2), {
                                        maxZoom: 16,
                                        animate: true,
                                        duration: 0.8
                                    });
                                } else if (data.workplaces.length === 1 && userLocation) {
                                    // For single workplace, show both user and workplace nicely
                                    const group = L.featureGroup([specialUserMarker, specialWorkplaceMarkers[0]]);
                                    specialCheckinMap.fitBounds(group.getBounds().pad(0.3), {
                                        maxZoom: 17,
                                        animate: true,
                                        duration: 0.8
                                    });
                                }
                            }, 1500); // 1.5 second delay to let user see their location first
                        } else if (data.workplaces.length > 1) {
                            // No user location, just show workplaces
                            const group = L.featureGroup(specialWorkplaceMarkers);
                            specialCheckinMap.fitBounds(group.getBounds().pad(0.1));
                        }
                    }

                } else {
                    if (locationSelect) {
                        locationSelect.innerHTML = '<option value="">No special locations assigned</option>';
                        locationSelect.disabled = true;
                    }
                    if (locationInfo) {
                        locationInfo.textContent = 'No locations available. Contact your administrator.';
                    }
                }

            } catch (error) {
                console.error('Failed to fetch special locations:', error);
                const locationSelect = document.getElementById('special-location-select');
                const locationInfo = document.getElementById('special-location-info');

                if (locationSelect) {
                    locationSelect.innerHTML = '<option value="">Error loading locations</option>';
                    locationSelect.disabled = true;
                }
                if (locationInfo) {
                    locationInfo.textContent = 'Failed to load locations. Please refresh the page.';
                }
            }
        }

        // Update special location status
        function updateSpecialLocationStatus(position) {
            if (!position) return;

            const locationBadge = document.getElementById('special-location-badge');
            const currentLocation = document.getElementById('special-current-location');
            const checkinBtn = document.getElementById('special-checkin-btn');

            if (!locationBadge || !currentLocation || !checkinBtn) return;

            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const accuracy = Math.round(position.coords.accuracy);

            // Update location display
            currentLocation.innerHTML = `<i class="fas fa-map-marker-alt text-yellow-600 mr-2"></i>` +
                `Location: ${userLat.toFixed(6)}, ${userLng.toFixed(6)} ` +
                `<span class="text-xs text-gray-600">(±${accuracy}m)</span>`;

            // Check if location select has a value
            const locationSelect = document.getElementById('special-location-select');
            if (!locationSelect || !locationSelect.value) {
                locationBadge.className = 'px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium';
                locationBadge.textContent = 'Select Location';

                checkinBtn.className =
                    'w-full py-4 bg-gray-400 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
                checkinBtn.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Select a Location First';
                checkinBtn.disabled = true;
                return;
            }

            // Get selected location data
            const selectedOption = locationSelect.options[locationSelect.selectedIndex];
            const workplaceLat = parseFloat(selectedOption.dataset.lat);
            const workplaceLng = parseFloat(selectedOption.dataset.lng);
            const workplaceRadius = parseInt(selectedOption.dataset.radius);

            // Calculate distance
            const distance = calculateDistance(userLat, userLng, workplaceLat, workplaceLng);
            const inRange = distance <= workplaceRadius;

            // Update badge and button
            if (inRange) {
                locationBadge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
                locationBadge.textContent = 'In Range';

                checkinBtn.className =
                    'w-full py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-semibold text-lg transition-colors';
                checkinBtn.innerHTML = '<i class="fas fa-star mr-2"></i>Special Check In/Out';
                checkinBtn.disabled = false;
                checkinBtn.onclick = performSpecialCheckin;
            } else {
                locationBadge.className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium';
                locationBadge.textContent = `${Math.round(distance)}m away`;

                checkinBtn.className =
                    'w-full py-4 bg-red-500 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
                checkinBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Outside Location Range';
                checkinBtn.disabled = true;
            }
        }

        // Handle location selection change
        document.addEventListener('DOMContentLoaded', function() {
            const locationSelect = document.getElementById('special-location-select');
            if (locationSelect) {
                locationSelect.addEventListener('change', function() {
                    selectedSpecialLocationId = this.value;

                    if (userLocation) {
                        updateSpecialLocationStatus(userLocation);
                    }

                    // Zoom to selected location on map
                    if (this.value && specialCheckinMap) {
                        const selectedOption = this.options[this.selectedIndex];
                        const lat = parseFloat(selectedOption.dataset.lat);
                        const lng = parseFloat(selectedOption.dataset.lng);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            specialCheckinMap.setView([lat, lng], 16);
                        }
                    }
                });
            }

            // Refresh button for special locations
            const refreshBtn = document.getElementById('refresh-special-locations');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    fetchSpecialLocations();
                    showSimpleNotification('Refreshing special locations...', 'info');
                });
            }
        });

        // Perform special check-in
        async function performSpecialCheckin() {
            if (!userLocation || !selectedSpecialLocationId) {
                showNotification('Location or workplace not selected', 'error');
                return;
            }

            const checkinBtn = document.getElementById('special-checkin-btn');
            const originalContent = checkinBtn.innerHTML;

            checkinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            checkinBtn.disabled = true;

            try {
                // Get today's special check-ins count first
                const statusResponse = await fetch(`/api/special-checkin-logs/${getCurrentUserId()}`);
                const statusData = await statusResponse.json();

                const currentCount = statusData.count || 0;

                if (currentCount >= 8) {
                    showNotification('Maximum 4 check-in/out pairs (8 actions) reached for today', 'error');
                    checkinBtn.innerHTML = originalContent;
                    checkinBtn.disabled = false;
                    return;
                }

                // Determine action based on logs for THIS specific workplace
                const workplaceLogs = statusData.logs.filter(log => log.workplace_id == selectedSpecialLocationId);
                const lastWorkplaceLog = workplaceLogs[workplaceLogs.length - 1];

                let action;
                if (!lastWorkplaceLog || lastWorkplaceLog.action === 'check_out') {
                    action = 'check_in';
                } else {
                    action = 'check_out';
                }

                const response = await fetch('/api/special-checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    body: JSON.stringify({
                        user_id: getCurrentUserId(),
                        workplace_id: selectedSpecialLocationId,
                        action: action,
                        latitude: userLocation.coords.latitude,
                        longitude: userLocation.coords.longitude,
                        address: `Special ${action === 'check_in' ? 'Check-in' : 'Check-out'}`
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification(result.message + ` (${result.remaining_actions} actions remaining)`, 'success');

                    // Refresh special check-in data
                    fetchSpecialCheckinLogs();
                    updateSpecialWorkflowStatus();
                    
                    // Refresh regular check-in status (to disable/enable it based on special check-in)
                    fetchCurrentStatus();
                    checkTodayCheckinType(); // Update mutual exclusivity status
                    
                    // Refresh dashboard activity
                    fetchTodaysSchedule();

                    // Reset button
                    checkinBtn.innerHTML = originalContent;
                    updateSpecialLocationStatus(userLocation);
                } else {
                    showNotification(result.error || 'Special check-in failed', 'error');
                    
                    // Handle mutual exclusivity error
                    if (result.locked_type) {
                        checkTodayCheckinType(); // Refresh the UI to show locked state
                    }
                    
                    checkinBtn.innerHTML = originalContent;
                    checkinBtn.disabled = false;
                }
            } catch (error) {
                console.error('Special check-in error:', error);
                showNotification('Special check-in failed: ' + error.message, 'error');
                checkinBtn.innerHTML = originalContent;
                checkinBtn.disabled = false;
            }
        }

        // Fetch today's special check-in logs
        async function fetchSpecialCheckinLogs(userId = null) {
            userId = userId || getCurrentUserId();

            try {
                const response = await fetch(`/api/special-checkin-logs/${userId}`);
                const data = await response.json();

                const activityContainer = document.getElementById('special-todays-activity');
                const emptyMessage = document.getElementById('special-activity-empty');

                if (data.logs && data.logs.length > 0) {
                    if (emptyMessage) emptyMessage.classList.add('hidden');

                    let html = '';
                    data.logs.forEach((log, index) => {
                        const isCheckIn = log.action === 'check_in';
                        const color = isCheckIn ? 'yellow' : 'orange';
                        const icon = isCheckIn ? 'fa-star' : 'fa-sign-out-alt';
                        const locationInfo = log.location ? ` • <i class="fas fa-map-marker-alt"></i> ${log.location}` : '';

                        html += `
                    <div class="flex items-center p-3 bg-${color}-50 rounded-lg border border-${color}-200">
                        <div class="w-10 h-10 bg-${color}-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas ${icon} text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-${color}-800">${isCheckIn ? 'Special Check-in' : 'Special Check-out'} #${index + 1}</p>
                            <p class="text-sm text-${color}-600">${log.timestamp}${locationInfo}</p>
                        </div>
                        <div class="text-${color}-600">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                `;
                    });

                    if (activityContainer) {
                        activityContainer.innerHTML = html;
                    }
                } else {
                    if (emptyMessage) emptyMessage.classList.remove('hidden');
                    if (activityContainer) activityContainer.innerHTML = '';
                }

                // Update workflow status
                updateSpecialWorkflowStatus(data.count || 0, data.logs);

            } catch (error) {
                console.error('Failed to fetch special check-in logs:', error);
            }
        }

        // Update special workflow status
        function updateSpecialWorkflowStatus(count = null, logs = null) {
            const checkinsCount = document.getElementById('special-checkins-count');
            const lastAction = document.getElementById('special-last-action');
            const workflowStatus = document.getElementById('special-workflow-status');

            if (count === null) {
                // Fetch current status
                fetch(`/api/special-checkin-logs/${getCurrentUserId()}`)
                    .then(response => response.json())
                    .then(data => {
                        updateSpecialWorkflowStatus(data.count || 0, data.logs);
                    });
                return;
            }

            if (checkinsCount) {
                const pairsCount = Math.floor(count / 2);
                checkinsCount.textContent = `${pairsCount}/4 pairs (${count}/8 actions)`;
            }

            if (lastAction && logs && logs.length > 0) {
                const latest = logs[logs.length - 1];
                lastAction.textContent =
                `${latest.action === 'check_in' ? 'Check-in' : 'Check-out'} at ${latest.timestamp}`;
            } else if (lastAction) {
                lastAction.textContent = 'None';
            }

            if (workflowStatus) {
                if (count >= 8) {
                    workflowStatus.textContent = 'Daily limit reached (4 pairs)';
                } else if (count > 0) {
                    const pairsComplete = Math.floor(count / 2);
                    const nextAction = (count % 2 === 0) ? 'Check-in' : 'Check-out';
                    workflowStatus.textContent = `${pairsComplete} pair(s) complete, ready for ${nextAction}`;
                } else {
                    workflowStatus.textContent = 'Ready';
                }
            }
        }

        function createDetailedLogsFromSummary(summaryData) {
            // Create mock detailed logs from summary data to provide better detail view
            const detailedLogs = [];

            summaryData.forEach(attendance => {
                const date = attendance.date_raw || new Date().toISOString().split('T')[0];
                const displayDate = attendance.date || new Date().toLocaleDateString();

                // Add check-in log
                if (attendance.check_in && attendance.check_in !== '--') {
                    detailedLogs.push({
                        action: 'check_in',
                        timestamp: attendance.check_in,
                        shift_type: 'regular',
                        location: attendance.location || 'Workplace',
                        date: displayDate,
                        date_raw: date
                    });
                }

                // Add check-out log if available
                if (attendance.check_out && attendance.check_out !== '--' && attendance.check_out !==
                    'Still working') {
                    detailedLogs.push({
                        action: 'check_out',
                        timestamp: attendance.check_out,
                        shift_type: 'regular',
                        location: attendance.location || 'Workplace',
                        date: displayDate,
                        date_raw: date
                    });
                }
            });

            return detailedLogs;
        }

        // Current selected period for attendance filtering
        let currentAttendancePeriod = 'thisweek';

        // Filter attendance by period
        function filterAttendanceByPeriod(period) {
            currentAttendancePeriod = period;
            console.log('Filtering attendance by period:', period);
            
            // Reset pagination
            attendancePagination.currentPage = 1;
            
            // Redisplay with new filter
            displayAttendanceData();
            
            // Update summary based on filtered data
            const filteredData = getFilteredAttendanceByPeriod(cachedAttendanceData.summary, period);
            updateWeeklySummary(filteredData, period);
        }

        // Get filtered attendance data based on period
        function getFilteredAttendanceByPeriod(data, period) {
            if (!data || data.length === 0) return [];
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            let startDate, endDate;
            
            switch(period) {
                case 'thisweek':
                    // Monday to Sunday of current week
                    const currentDay = today.getDay();
                    const daysFromMonday = currentDay === 0 ? 6 : currentDay - 1;
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - daysFromMonday);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                    
                case 'lastweek':
                    // Monday to Sunday of last week
                    const lastWeekDay = today.getDay();
                    const daysFromLastMonday = lastWeekDay === 0 ? 6 : lastWeekDay - 1;
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - daysFromLastMonday - 7);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                    
                case 'thismonth':
                    // First day to last day of current month
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                    
                case 'lastmonth':
                    // First day to last day of last month
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    endDate.setHours(23, 59, 59, 999);
                    break;
                    
                default:
                    return data;
            }
            
            console.log(`Filtering ${period}:`, startDate.toDateString(), 'to', endDate.toDateString());
            
            return data.filter(record => {
                const recordDate = new Date(record.date_raw);
                return recordDate >= startDate && recordDate <= endDate;
            });
        }

        function displayAttendanceData() {
            const tbody = document.getElementById('attendance-history-tbody');
            const mobileContainer = document.getElementById('attendance-history-mobile');
            if (!tbody) return;

            // Filter data by current period
            const filteredSummary = getFilteredAttendanceByPeriod(cachedAttendanceData.summary, currentAttendancePeriod);
            const filteredLogs = getFilteredAttendanceByPeriod(cachedAttendanceData.logs, currentAttendancePeriod);
            
            const logsData = filteredLogs;
            const summaryData = filteredSummary;

            if (currentAttendanceView === 'detailed' && logsData.length > 0) {
                updateTableHeaders('detailed');
                displayDetailedAttendanceLogs(logsData, tbody);
                if (mobileContainer) displayMobileAttendanceCards(logsData, mobileContainer, 'detailed');
            } else if (summaryData.length > 0) {
                updateTableHeaders('summary');
                displaySummaryAttendanceData(summaryData, tbody);
                if (mobileContainer) displayMobileAttendanceCards(summaryData, mobileContainer, 'summary');
            } else {
                // Show empty state for desktop
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-3xl mb-3 text-gray-300"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Attendance Records</h3>
                                <p class="text-gray-500">You haven't checked in yet. Visit the Check In/Out section to get started.</p>
                                <button onclick="switchToSection('gps-checkin')" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    Go to Check In
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                // Show empty state for mobile
                if (mobileContainer) {
                    mobileContainer.innerHTML = `
                        <div class="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-2xl mb-2 text-gray-300"></i>
                                <h3 class="text-base font-medium text-gray-900 mb-1">No Attendance Records</h3>
                                <p class="text-sm text-gray-500 mb-3">You haven't checked in yet.</p>
                                <button onclick="switchToSection('gps-checkin')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                                    Go to Check In
                                </button>
                            </div>
                        </div>
                    `;
                }
                updatePaginationControls(0);
            }
        }

        function displayMobileAttendanceCards(data, container, viewType) {
            if (!container) return;

            container.innerHTML = '';

            if (viewType === 'detailed') {
                // Group logs by date
                const logsByDate = {};
                data.forEach(log => {
                    const dateKey = log.date_raw || log.date || new Date(log.timestamp).toISOString().split('T')[0];
                    if (!logsByDate[dateKey]) {
                        logsByDate[dateKey] = [];
                    }
                    logsByDate[dateKey].push(log);
                });

                // Sort dates (newest first)
                const sortedDates = Object.keys(logsByDate).sort((a, b) => new Date(b) - new Date(a));
                const paginatedDates = getPaginatedData(sortedDates);

                paginatedDates.forEach(dateKey => {
                    let dateLogs = logsByDate[dateKey];

                    // DEDUPLICATE logs - remove duplicates based on action, timestamp, and shift_type
                    const uniqueLogs = [];
                    const seenKeys = new Set();
                    
                    dateLogs.forEach(log => {
                        const uniqueKey = `${log.action}-${log.timestamp}-${log.shift_type || 'regular'}`;
                        if (!seenKeys.has(uniqueKey)) {
                            seenKeys.add(uniqueKey);
                            uniqueLogs.push(log);
                        }
                    });
                    
                    dateLogs = uniqueLogs;

                    // Sort logs by timestamp with action sequence as secondary sort
                    const actionOrder = {
                        'check_in': 1,
                        'break_start': 2,
                        'start_lunch': 2,
                        'lunch_start': 2,
                        'break_end': 3,
                        'end_lunch': 3,
                        'lunch_end': 3,
                        'check_out': 4
                    };

                    dateLogs.sort((a, b) => {
                        const timeA = new Date('1970/01/01 ' + (a.timestamp || '00:00')).getTime();
                        const timeB = new Date('1970/01/01 ' + (b.timestamp || '00:00')).getTime();

                        // First sort by timestamp
                        if (timeA !== timeB) {
                            return timeA - timeB;
                        }

                        // If timestamps are equal, sort by action sequence
                        const orderA = actionOrder[a.action] || 99;
                        const orderB = actionOrder[b.action] || 99;
                        return orderA - orderB;
                    });

                    const displayDate = dateKey === new Date().toISOString().split('T')[0] ? 'Today' :
                        new Date(dateKey).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });

                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-lg border border-gray-200 p-4';

                    const checkIn = dateLogs.find(log => log.action === 'check_in');
                    const checkOut = dateLogs.find(log => log.action === 'check_out');

                    card.innerHTML = `
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="text-sm font-bold text-gray-900">${displayDate}</div>
                                <div class="text-xs text-gray-500 mt-0.5">${dateLogs.length} action${dateLogs.length !== 1 ? 's' : ''}</div>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${checkOut ? 'bg-green-100 text-green-800' : checkIn ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                ${checkOut ? 'Completed' : checkIn ? 'Active' : 'No Data'}
                            </span>
                        </div>
                        <div class="space-y-2 text-xs">
                            ${dateLogs.map(log => {
                                const actionIcons = {
                                    'check_in': 'fa-sign-in-alt',
                                    'check_out': 'fa-sign-out-alt',
                                    'break_start': 'fa-utensils',
                                    'break_end': 'fa-play'
                                };
                                const actionColors = {
                                    'check_in': 'text-green-600',
                                    'check_out': 'text-red-600',
                                    'break_start': 'text-yellow-600',
                                    'break_end': 'text-blue-600'
                                };
                                const icon = actionIcons[log.action] || 'fa-clock';
                                const color = actionColors[log.action] || 'text-gray-600';
                                const label = log.action.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                                
                                return `
                                    <div class="flex items-center space-x-2 py-1 border-t border-gray-100">
                                        <i class="fas ${icon} ${color} w-3"></i>
                                        <span class="font-medium ${color}">${label}</span>
                                        <span class="text-gray-500 ml-auto">${log.timestamp}</span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;
                    container.appendChild(card);
                });
            } else {
                // Summary view
                const paginatedData = getPaginatedData(data);

                paginatedData.forEach(attendance => {
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-lg border border-gray-200 p-4';

                    const workHours = attendance.total_hours || '0.0 hrs';
                    const checkOut = attendance.check_out || attendance.attendance_check_out || '--';
                    const isComplete = checkOut !== '--' && checkOut !== 'Still working';

                    card.innerHTML = `
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="text-sm font-bold text-gray-900">${attendance.date_raw === new Date().toISOString().split('T')[0] ? 'Today' : attendance.date}</div>
                                <div class="text-xs text-gray-500 mt-0.5">${attendance.location || 'Workplace'}</div>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${isComplete ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                                ${isComplete ? 'Completed' : 'Active'}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <div class="text-gray-500 mb-1">Check In</div>
                                <div class="font-medium text-green-600">
                                    <i class="fas fa-sign-in-alt mr-1"></i>${attendance.check_in || '--'}
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500 mb-1">Check Out</div>
                                <div class="font-medium ${isComplete ? 'text-red-600' : 'text-blue-600'}">
                                    ${isComplete ? `<i class="fas fa-sign-out-alt mr-1"></i>${checkOut}` : '<span>Still working</span>'}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Total Hours</span>
                                <span class="font-bold text-gray-900">${workHours}</span>
                            </div>
                        </div>
                    `;
                    container.appendChild(card);
                });
            }

            if (data.length === 0 || getPaginatedData(data).length === 0) {
                container.innerHTML = `
                    <div class="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-500">
                        <i class="fas fa-info-circle text-xl mb-2 text-gray-300"></i>
                        <p class="text-sm">No records available</p>
                    </div>
                `;
            }
        }

        function switchAttendanceView(viewType) {
            currentAttendanceView = viewType;

            // Reset pagination when switching views
            attendancePagination.currentPage = 1;

            // Update button styles
            const detailedBtn = document.getElementById('detailed-view-btn');
            const summaryBtn = document.getElementById('summary-view-btn');

            if (viewType === 'detailed') {
                detailedBtn.className = 'px-3 py-1 text-sm rounded-md bg-indigo-600 text-white transition-colors';
                summaryBtn.className = 'px-3 py-1 text-sm rounded-md text-gray-600 hover:bg-white transition-colors';
            } else {
                detailedBtn.className = 'px-3 py-1 text-sm rounded-md text-gray-600 hover:bg-white transition-colors';
                summaryBtn.className = 'px-3 py-1 text-sm rounded-md bg-indigo-600 text-white transition-colors';
            }

            // Redisplay data with new view
            displayAttendanceData();
        }

        function updateTableHeaders(viewType) {
            const headerRow = document.querySelector('#attendance-history-section thead tr');
            if (!headerRow) return;

            if (viewType === 'detailed') {
                headerRow.innerHTML = `
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                `;
            } else {
                headerRow.innerHTML = `
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                `;
            }
        }

        function displayDetailedAttendanceLogs(logs, tbody) {
            tbody.innerHTML = ''; // Clear existing content

            const actionIcons = {
                'check_in': 'fa-sign-in-alt',
                'break_start': 'fa-utensils',
                'break_end': 'fa-play',
                'check_out': 'fa-sign-out-alt',
                'lunch_start': 'fa-utensils',
                'lunch_end': 'fa-play',
                'start_lunch': 'fa-utensils',
                'end_lunch': 'fa-play'
            };

            const actionColors = {
                'check_in': 'text-green-600',
                'break_start': 'text-yellow-600',
                'break_end': 'text-blue-600',
                'check_out': 'text-red-600',
                'lunch_start': 'text-yellow-600',
                'lunch_end': 'text-blue-600',
                'start_lunch': 'text-yellow-600',
                'end_lunch': 'text-blue-600'
            };

            const actionLabels = {
                'check_in': 'Check In',
                'break_start': 'Start Lunch',
                'break_end': 'End Lunch',
                'check_out': 'Check Out',
                'lunch_start': 'Start Lunch',
                'lunch_end': 'End Lunch',
                'start_lunch': 'Start Lunch',
                'end_lunch': 'End Lunch'
            };

            console.log('Displaying detailed logs:', logs);

            // Group logs by date for better organization
            const logsByDate = {};
            logs.forEach(log => {
                const dateKey = log.date_raw || log.date || new Date(log.timestamp).toISOString().split('T')[0];
                if (!logsByDate[dateKey]) {
                    logsByDate[dateKey] = [];
                }
                logsByDate[dateKey].push(log);
            });

            // Sort dates in reverse order (newest first)
            const sortedDates = Object.keys(logsByDate).sort((a, b) => {
                return new Date(b) - new Date(a);
            });

            // Create combined daily rows
            let dailyRows = [];
            sortedDates.forEach(dateKey => {
                let dateLogs = logsByDate[dateKey];

                // Remove duplicates within the same date
                const uniqueLogs = [];
                const seenKeys = new Set();

                dateLogs.forEach(log => {
                    const uniqueKey = `${log.action}-${log.timestamp}`;
                    if (!seenKeys.has(uniqueKey)) {
                        seenKeys.add(uniqueKey);
                        uniqueLogs.push(log);
                    } else {
                        console.warn('Duplicate log in display detected and removed:', log);
                    }
                });

                dateLogs = uniqueLogs;
                console.log(`Date ${dateKey} has ${dateLogs.length} unique logs:`, dateLogs);

                // Sort logs by timestamp within each date, with action sequence as secondary sort
                const actionOrder = {
                    'check_in': 1,
                    'break_start': 2,
                    'start_lunch': 2,
                    'lunch_start': 2,
                    'break_end': 3,
                    'end_lunch': 3,
                    'lunch_end': 3,
                    'check_out': 4
                };

                dateLogs.sort((a, b) => {
                    const timeA = new Date('1970/01/01 ' + (a.timestamp || '00:00')).getTime();
                    const timeB = new Date('1970/01/01 ' + (b.timestamp || '00:00')).getTime();

                    // First sort by timestamp
                    if (timeA !== timeB) {
                        return timeA - timeB;
                    }

                    // If timestamps are equal, sort by action sequence
                    const orderA = actionOrder[a.action] || 99;
                    const orderB = actionOrder[b.action] || 99;
                    return orderA - orderB;
                });

                const displayDate = dateKey === new Date().toISOString().split('T')[0] ? 'Today' :
                    new Date(dateKey).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });

                // Create summary for this date
                const checkIn = dateLogs.find(log => log.action === 'check_in');
                const checkOut = dateLogs.find(log => log.action === 'check_out');
                const breakStart = dateLogs.find(log => log.action === 'break_start' || log.action ===
                    'start_lunch');
                const breakEnd = dateLogs.find(log => log.action === 'break_end' || log.action === 'end_lunch');

                // Calculate total hours for this date
                let totalHours = '0.0';

                // Check if this is a special attendance (multiple check-in/out pairs)
                const isSpecialAttendance = dateLogs.some(log => log.shift_type === 'special');

                if (isSpecialAttendance) {
                    // For special attendance, calculate hours by pairing check-ins with check-outs
                    let totalMinutes = 0;
                    const openCheckIns = [];

                    // Sort logs by timestamp to ensure proper pairing
                    const sortedLogs = [...dateLogs].sort((a, b) => {
                        const timeA = parseTime(a.timestamp);
                        const timeB = parseTime(b.timestamp);
                        return timeA - timeB;
                    });

                    sortedLogs.forEach(log => {
                        if (log.action === 'check_in') {
                            openCheckIns.push(log);
                        } else if (log.action === 'check_out' && openCheckIns.length > 0) {
                            const checkInLog = openCheckIns.pop();
                            const startTime = parseTime(checkInLog.timestamp);
                            const endTime = parseTime(log.timestamp);
                            if (startTime && endTime) {
                                const minutes = (endTime - startTime) / (1000 * 60);
                                console.log(
                                    `Special pair: ${checkInLog.timestamp} to ${log.timestamp} = ${minutes.toFixed(1)} minutes`
                                    );
                                totalMinutes += minutes;
                            }
                        }
                    });

                    console.log(
                        `Total minutes for special attendance: ${totalMinutes}, Total hours: ${(totalMinutes / 60).toFixed(1)}`
                        );
                    totalHours = (totalMinutes / 60).toFixed(1);
                } else {
                    // Regular attendance - calculate from first check-in to last check-out
                    if (checkIn && checkOut) {
                        const startTime = parseTime(checkIn.timestamp);
                        const endTime = parseTime(checkOut.timestamp);
                        if (startTime && endTime) {
                            let hours = (endTime - startTime) / (1000 * 60 * 60);
                            // Subtract lunch break if present
                            if (breakStart && breakEnd) {
                                const lunchStart = parseTime(breakStart.timestamp);
                                const lunchEnd = parseTime(breakEnd.timestamp);
                                if (lunchStart && lunchEnd) {
                                    const lunchDuration = (lunchEnd - lunchStart) / (1000 * 60 * 60);
                                    hours -= lunchDuration;
                                }
                            }
                            totalHours = Math.max(0, hours).toFixed(1);
                        }
                    } else if (checkIn && !checkOut) {
                        // Still working
                        const startTime = parseTime(checkIn.timestamp);
                        if (startTime) {
                            const now = new Date();
                            let hours = (now - startTime) / (1000 * 60 * 60);
                            // Subtract lunch break if taken
                            if (breakStart && breakEnd) {
                                const lunchStart = parseTime(breakStart.timestamp);
                                const lunchEnd = parseTime(breakEnd.timestamp);
                                if (lunchStart && lunchEnd) {
                                    const lunchDuration = (lunchEnd - lunchStart) / (1000 * 60 * 60);
                                    hours -= lunchDuration;
                                }
                            }
                            totalHours = Math.max(0, hours).toFixed(1);
                        }
                    }
                }

                dailyRows.push({
                    dateKey,
                    displayDate,
                    logs: dateLogs,
                    checkIn: checkIn ? checkIn.timestamp : null,
                    checkOut: checkOut ? checkOut.timestamp : (checkIn ? 'Still working' : null),
                    totalHours: totalHours + ' hrs',
                    hasLogs: dateLogs.length > 0
                });
            });

            // Get paginated daily rows
            const paginatedDays = getPaginatedData(dailyRows);

            // Display paginated daily rows
            paginatedDays.forEach((dayData, index) => {
                const rowId = `day-row-${dayData.dateKey}`;
                const detailsId = `day-details-${dayData.dateKey}`;

                // Main day summary row
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 cursor-pointer border-b border-gray-100';
                row.id = rowId;
                row.onclick = () => toggleDayDetails(detailsId, rowId);

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right transform transition-transform duration-200 mr-2 text-gray-400" id="${rowId}-chevron"></i>
                            ${dayData.displayDate}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex items-center space-x-4">
                            ${dayData.checkIn ? `<span class="text-green-600"><i class="fas fa-sign-in-alt mr-1"></i>${dayData.checkIn}</span>` : '<span class="text-gray-400">--</span>'}
                            ${dayData.checkOut && dayData.checkOut !== 'Still working' ? `<span class="text-red-600"><i class="fas fa-sign-out-alt mr-1"></i>${dayData.checkOut}</span>` : 
                              dayData.checkOut === 'Still working' ? '<span class="text-blue-600 font-medium">Still working</span>' : ''}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="font-medium">${dayData.totalHours}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="text-gray-500">${dayData.logs.length} action${dayData.logs.length !== 1 ? 's' : ''}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Workplace</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${dayData.checkOut && dayData.checkOut !== 'Still working' ? 'bg-green-100 text-green-800' : dayData.checkIn ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                            ${dayData.checkOut && dayData.checkOut !== 'Still working' ? 'Completed' : dayData.checkIn ? 'Active' : 'No Data'}
                        </span>
                    </td>
                `;
                tbody.appendChild(row);

                // Expandable details row
                const detailsRow = document.createElement('tr');
                detailsRow.id = detailsId;
                detailsRow.className = 'hidden';
                detailsRow.innerHTML = `
                    <td colspan="6" class="px-6 py-0 bg-gray-50">
                        <div class="py-4 space-y-2">
                            ${dayData.logs.map(log => {
                                const icon = actionIcons[log.action] || 'fa-clock';
                                const color = actionColors[log.action] || 'text-gray-600';
                                const label = actionLabels[log.action] || log.action.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                                
                                return `
                                                        <div class="flex items-center space-x-3 py-1">
                                                            <i class="fas ${icon} ${color} w-4"></i>
                                                            <span class="text-sm font-medium ${color}">${label}</span>
                                                            <span class="text-sm text-gray-500">${log.timestamp}</span>
                                                            <span class="text-xs text-gray-400">${log.shift_type ? log.shift_type.toUpperCase() + ' Shift' : 'Regular'}</span>
                                                        </div>
                                                    `;
                            }).join('')}
                        </div>
                    </td>
                `;
                tbody.appendChild(detailsRow);
            });

            // Update pagination controls
            updatePaginationControls(dailyRows.length);

            // If no logs found, show message and update pagination
            if (logs.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-info-circle text-2xl mb-2 text-gray-300"></i>
                            <p>No detailed logs available yet.</p>
                            <p class="text-sm text-gray-400 mt-1">Perform some actions to see them here.</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
                updatePaginationControls(0);
            }
        }

        // Function to toggle day details
        function toggleDayDetails(detailsId, rowId) {
            const detailsRow = document.getElementById(detailsId);
            const chevron = document.getElementById(`${rowId}-chevron`);

            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
                chevron.classList.add('rotate-90');
            } else {
                detailsRow.classList.add('hidden');
                chevron.classList.remove('rotate-90');
            }
        }

        function displaySummaryAttendanceData(summaryData, tbody) {
            tbody.innerHTML = ''; // Clear existing content

            // Get paginated data
            const paginatedData = getPaginatedData(summaryData);

            paginatedData.forEach(attendance => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                // If this summary row represents a special pair (pair_index > 0),
                // prefer the row-level fields produced by the API (check_in/check_out/total_hours).
                // Otherwise fall back to calculating from attached logs.
                let workHours;
                if (attendance.pair_index && attendance.pair_index > 0) {
                    workHours = {
                        total: attendance.total_hours || '0.0 hrs',
                        breakdown: null
                    };
                } else {
                    workHours = calculateWorkHours(attendance);
                }

                // Prefer row-level check_in/check_out values (set by API for special pairs).
                // Fall back to attendance_check_in / attendance_check_out for compatibility,
                // then to attached logs if necessary.
                let checkInDisplay = attendance.check_in || attendance.attendance_check_in || attendance.check_in ||
                    '--';
                let checkOutDisplay = attendance.check_out || attendance.attendance_check_out || '--';

                // If we still don't have a check_out and logs exist, try to find one in logs
                if ((checkOutDisplay === '--' || checkOutDisplay === null) && attendance.logs && attendance.logs
                    .length > 0) {
                    const checkOutLog = attendance.logs.find(log => log.action === 'check_out');
                    if (checkOutLog) {
                        checkOutDisplay = checkOutLog.timestamp;
                        console.log(`Found check-out log for ${attendance.date}: ${checkOutLog.timestamp}`);
                    }
                }

                // If still marked as 'Still working', render a styled label
                if (attendance.check_out === 'Still working' || attendance.attendance_check_out ===
                    'Still working') {
                    checkOutDisplay = '<span class="text-blue-600 font-medium">Still working</span>';
                }

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${attendance.date_raw === new Date().toISOString().split('T')[0] ? 'Today' : attendance.date}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attendance.check_in || '--'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${checkOutDisplay}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex flex-col">
                            <span class="font-medium">${workHours.total}</span>
                            ${workHours.breakdown ? `<span class="text-xs text-gray-500">${workHours.breakdown}</span>` : ''}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attendance.location}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${attendance.status_class}">
                            ${attendance.status}
                        </span>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Update pagination after displaying data
            updatePaginationControls(summaryData.length);
        }

        // Add function to calculate work hours from attendance logs
        function calculateWorkHours(attendance) {
            if (!attendance.logs || attendance.logs.length === 0) {
                return {
                    total: attendance.total_hours || '0.0 hrs',
                    breakdown: null
                };
            }

            const logs = attendance.logs;
            let checkIn = null;
            let breakStart = null;
            let breakEnd = null;
            let checkOut = null;

            console.log('Calculating work hours for logs:', logs);

            // Extract timestamps for each action
            logs.forEach(log => {
                console.log(`Processing log: ${log.action} at ${log.timestamp}`);
                switch (log.action) {
                    case 'check_in':
                        checkIn = parseTime(log.timestamp);
                        console.log('Check-in time parsed:', checkIn);
                        break;
                    case 'break_start':
                    case 'start_lunch':
                        breakStart = parseTime(log.timestamp);
                        console.log('Break start time parsed:', breakStart);
                        break;
                    case 'break_end':
                    case 'end_lunch':
                        breakEnd = parseTime(log.timestamp);
                        console.log('Break end time parsed:', breakEnd);
                        break;
                    case 'check_out':
                        checkOut = parseTime(log.timestamp);
                        console.log('Check-out time parsed:', checkOut);
                        break;
                }
            });

            // Calculate AM shift (check_in to break_start)
            let amShiftHours = 0;
            if (checkIn && breakStart) {
                amShiftHours = (breakStart - checkIn) / (1000 * 60 * 60);
                console.log('AM shift hours:', amShiftHours);
            }

            // Calculate PM shift (break_end to check_out)
            let pmShiftHours = 0;
            if (breakEnd && checkOut) {
                pmShiftHours = (checkOut - breakEnd) / (1000 * 60 * 60);
                console.log('PM shift hours:', pmShiftHours);
            }

            // Handle cases where there's no lunch break
            let totalHours = 0;
            let breakdown = '';

            if (amShiftHours > 0 && pmShiftHours > 0) {
                // Full day with lunch break
                totalHours = amShiftHours + pmShiftHours;
                breakdown = `AM: ${amShiftHours.toFixed(1)}h + PM: ${pmShiftHours.toFixed(1)}h`;
            } else if (checkIn && checkOut && !breakStart && !breakEnd) {
                // Full day without lunch break
                totalHours = (checkOut - checkIn) / (1000 * 60 * 60);
                breakdown = `Continuous shift`;
                console.log('Continuous shift hours:', totalHours);
            } else if (checkIn && breakStart && !breakEnd && !checkOut) {
                // Only AM shift completed
                totalHours = amShiftHours;
                breakdown = `AM shift only: ${amShiftHours.toFixed(1)}h`;
            } else if (checkIn && !checkOut) {
                // Still working
                const now = new Date();
                const currentTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours(), now
                    .getMinutes());

                if (breakStart && !breakEnd) {
                    // On lunch break
                    totalHours = amShiftHours;
                    breakdown = `On lunch break (AM: ${amShiftHours.toFixed(1)}h)`;
                } else if (breakEnd) {
                    // Working PM shift
                    const pmSoFar = (currentTime - breakEnd) / (1000 * 60 * 60);
                    totalHours = amShiftHours + pmSoFar;
                    breakdown = `Working (AM: ${amShiftHours.toFixed(1)}h + PM: ${pmSoFar.toFixed(1)}h)`;
                } else {
                    // Working AM shift or continuous
                    totalHours = (currentTime - checkIn) / (1000 * 60 * 60);
                    breakdown = `Working (${totalHours.toFixed(1)}h so far)`;
                }
            }

            const result = {
                total: totalHours > 0 ? `${totalHours.toFixed(1)} hrs` : '0.0 hrs',
                breakdown: breakdown
            };

            console.log('Final work hours calculation:', result);
            return result;
        }

        // Helper function to parse time string to Date object
        function parseTime(timeString) {
            if (!timeString) return null;

            // Handle both "11:04 AM" format and full datetime strings
            let time, period;

            if (timeString.includes('T') || timeString.includes('-')) {
                // Full datetime string - parse as Date
                return new Date(timeString);
            }

            // Time only format like "11:04 AM"
            const parts = timeString.trim().split(' ');
            time = parts[0];
            period = parts[1];

            if (!time || !period) {
                console.warn('Invalid time format:', timeString);
                return null;
            }

            const [hours, minutes] = time.split(':').map(Number);

            if (isNaN(hours) || isNaN(minutes)) {
                console.warn('Invalid time components:', timeString);
                return null;
            }

            const today = new Date();
            let hour24 = hours;

            if (period.toLowerCase() === 'pm' && hours !== 12) {
                hour24 += 12;
            } else if (period.toLowerCase() === 'am' && hours === 12) {
                hour24 = 0;
            }

            return new Date(today.getFullYear(), today.getMonth(), today.getDate(), hour24, minutes, 0, 0);
        }

        // Update the fetchAttendanceHistory function to include logs
        async function fetchAttendanceHistory(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                // Fetch summary data and current status in parallel
                const [summaryResponse, statusResponse] = await Promise.all([
                    fetch(`/api/attendance-history/${userId}`),
                    fetch(`/api/current-status/${userId}`)
                ]);

                const summaryData = await summaryResponse.json();
                let logsData = [];

                // Get today's detailed logs from current status API
                if (statusResponse.ok) {
                    const statusData = await statusResponse.json();
                    if (statusData.logs && statusData.logs.length > 0) {
                        // Process today's logs with proper formatting
                        logsData = statusData.logs.map(log => ({
                            action: log.action,
                            timestamp: log.timestamp,
                            shift_type: log.shift_type || 'regular',
                            location: 'Workplace',
                            date: new Date().toLocaleDateString(),
                            date_raw: new Date().toISOString().split('T')[0]
                        }));

                        console.log('Today\'s detailed logs:', logsData);
                    }
                }

                // Fetch historical attendance logs with all actions
                try {
                    const historicalLogsResponse = await fetch(`/api/attendance-logs/${userId}`);
                    if (historicalLogsResponse.ok) {
                        const historicalLogs = await historicalLogsResponse.json();
                        // Merge historical logs with today's logs
                        logsData = [...historicalLogs, ...logsData];
                    }
                } catch (logError) {
                    console.log('Historical logs endpoint not available, showing only today\'s logs');
                }

                // Enhanced: Attach logs to summary data for proper hour calculation
                const enhancedSummaryData = summaryData.map(attendance => {
                    // Find logs for this date
                    const dayLogs = logsData.filter(log => log.date_raw === attendance.date_raw);
                    return {
                        ...attendance,
                        logs: dayLogs
                    };
                });

                // If no detailed logs available, create mock detailed logs from summary data
                if (logsData.length === 0 && summaryData.length > 0) {
                    logsData = createDetailedLogsFromSummary(summaryData);
                }

                // Cache the enhanced data
                cachedAttendanceData.summary = enhancedSummaryData;
                cachedAttendanceData.logs = logsData;

                // Reset pagination when new data is loaded
                attendancePagination.currentPage = 1;

                // Display based on current view mode
                displayAttendanceData();

                // Update weekly summary based on enhanced summary data and current period filter
                const filteredDataSecond = getFilteredAttendanceByPeriod(enhancedSummaryData, currentAttendancePeriod);
                updateWeeklySummary(filteredDataSecond, currentAttendancePeriod);

            } catch (error) {
                console.error('Failed to fetch attendance history:', error);
                // Show error state
                const tbody = document.getElementById('attendance-history-tbody');
                if (tbody) {
                    tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-red-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                            <p>Failed to load attendance history</p>
                        </div>
                    </td>
                </tr>
            `;
                }
            }
        }



        // Update the updateWeeklySummary function to handle the new hour calculation
        function updateWeeklySummary(attendanceData, period = 'thisweek') {
            const weeklyHours = document.getElementById('weekly-hours');
            const weeklyDays = document.getElementById('weekly-days');
            const weeklyAvgCheckin = document.getElementById('weekly-avg-checkin');
            const weeklyAttendance = document.getElementById('weekly-attendance');
            const weeklyDaysTotal = document.getElementById('weekly-days-total');
            const weeklyCheckinTrend = document.getElementById('weekly-checkin-trend');
            const weeklyPerformance = document.getElementById('weekly-performance');
            const weeklyDateRange = document.getElementById('weekly-date-range');

            // Get period boundaries
            const today = new Date();
            let startDate, endDate, dateRangeText, periodLabel;
            
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            switch(period) {
                case 'thisweek':
                    const currentDay = today.getDay();
                    const daysFromMonday = currentDay === 0 ? 6 : currentDay - 1;
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - daysFromMonday);
                    startDate.setHours(0, 0, 0, 0);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    endDate.setHours(23, 59, 59, 999);
                    dateRangeText = `${monthNames[startDate.getMonth()]} ${startDate.getDate()} - ${monthNames[endDate.getMonth()]} ${endDate.getDate()}`;
                    periodLabel = 'This Week';
                    break;
                    
                case 'lastweek':
                    const lastWeekDay = today.getDay();
                    const daysFromLastMonday = lastWeekDay === 0 ? 6 : lastWeekDay - 1;
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - daysFromLastMonday - 7);
                    startDate.setHours(0, 0, 0, 0);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    endDate.setHours(23, 59, 59, 999);
                    dateRangeText = `${monthNames[startDate.getMonth()]} ${startDate.getDate()} - ${monthNames[endDate.getMonth()]} ${endDate.getDate()}`;
                    periodLabel = 'Last Week';
                    break;
                    
                case 'thismonth':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    endDate.setHours(23, 59, 59, 999);
                    dateRangeText = monthNames[startDate.getMonth()] + ' ' + startDate.getFullYear();
                    periodLabel = 'This Month';
                    break;
                    
                case 'lastmonth':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    endDate.setHours(23, 59, 59, 999);
                    dateRangeText = monthNames[startDate.getMonth()] + ' ' + startDate.getFullYear();
                    periodLabel = 'Last Month';
                    break;
                    
                default:
                    startDate = new Date(today);
                    endDate = new Date(today);
                    dateRangeText = 'All Time';
                    periodLabel = 'All Time';
            }

            // Update date range display
            if (weeklyDateRange) weeklyDateRange.textContent = dateRangeText;

            console.log(`${periodLabel} (local time):`, startDate.toString(), 'to', endDate.toString());

            // Use the already filtered data passed in
            const thisWeekData = attendanceData;

            console.log(`${periodLabel} attendance data:`, thisWeekData);

            if (thisWeekData.length > 0) {
                let totalWeeklyHours = 0;
                let checkinTimes = [];

                // Group by date to avoid counting the same day multiple times (for special attendance)
                const uniqueDates = new Set();
                const dateGroups = {};

                thisWeekData.forEach(record => {
                    if (!dateGroups[record.date_raw]) {
                        dateGroups[record.date_raw] = [];
                    }
                    dateGroups[record.date_raw].push(record);
                });

                // Count unique work days - ONLY count days where status indicates actual presence
                // Status must be one of: 'present', 'late', 'special' (case-insensitive)
                // Do NOT count: 'absent', 'excused', or any other status
                let workDays = 0;
                Object.keys(dateGroups).forEach(dateKey => {
                    const records = dateGroups[dateKey];
                    // Check if ANY record for this date has a "present" status
                    const hasPresenceStatus = records.some(record => {
                        const status = (record.status || '').toLowerCase();
                        return status === 'present' || status === 'late' || status === 'special';
                    });
                    if (hasPresenceStatus) {
                        workDays++;
                    }
                });

                // Calculate hours from actual logs for each unique date
                Object.keys(dateGroups).forEach(dateKey => {
                    const records = dateGroups[dateKey];

                    // Combine all logs from all records of this date and deduplicate
                    let allLogsForDate = [];
                    const seenLogIds = new Set();

                    records.forEach(record => {
                        if (record.logs && record.logs.length > 0) {
                            record.logs.forEach(log => {
                                // Create unique key for each log entry
                                const logKey =
                                    `${log.action}-${log.timestamp}-${log.shift_type || 'regular'}`;
                                if (!seenLogIds.has(logKey)) {
                                    seenLogIds.add(logKey);
                                    allLogsForDate.push(log);
                                }
                            });
                        }
                    });

                    console.log(`Date ${dateKey} has ${allLogsForDate.length} unique logs after deduplication`);

                    if (allLogsForDate.length > 0) {
                        const checkIn = allLogsForDate.find(log => log.action === 'check_in');
                        const breakStart = allLogsForDate.find(log => log.action === 'break_start' || log.action ===
                            'start_lunch');
                        const breakEnd = allLogsForDate.find(log => log.action === 'break_end' || log.action ===
                            'end_lunch');

                        if (checkIn) {
                            checkinTimes.push(checkIn.timestamp);
                        }

                        // Check if this is a special attendance
                        const isSpecialAttendance = allLogsForDate.some(log => log.shift_type === 'special');

                        if (isSpecialAttendance) {
                            // For special attendance, calculate hours by pairing check-ins with check-outs
                            let totalMinutes = 0;
                            const openCheckIns = [];

                            // Sort logs by timestamp to ensure proper pairing
                            allLogsForDate.sort((a, b) => {
                                const timeA = parseTime(a.timestamp);
                                const timeB = parseTime(b.timestamp);
                                return timeA - timeB;
                            });

                            allLogsForDate.forEach(log => {
                                if (log.action === 'check_in') {
                                    openCheckIns.push(log);
                                } else if (log.action === 'check_out' && openCheckIns.length > 0) {
                                    const checkInLog = openCheckIns.pop();
                                    const startTime = parseTime(checkInLog.timestamp);
                                    const endTime = parseTime(log.timestamp);
                                    if (startTime && endTime) {
                                        totalMinutes += (endTime - startTime) / (1000 * 60);
                                    }
                                }
                            });

                            const dayHours = totalMinutes / 60;
                            totalWeeklyHours += Math.max(0, dayHours);
                            console.log(`Special attendance day hours for ${dateKey}: ${dayHours.toFixed(1)}`);
                        } else {
                            // Regular attendance - calculate from first check-in to last check-out
                            const checkOut = allLogsForDate.find(log => log.action === 'check_out');

                            if (checkOut) {
                                // Calculate total hours for completed day
                                const startTime = parseTime(checkIn.timestamp);
                                const endTime = parseTime(checkOut.timestamp);
                                if (startTime && endTime) {
                                    let dayHours = (endTime - startTime) / (1000 * 60 * 60);
                                    // Subtract lunch break if present
                                    if (breakStart && breakEnd) {
                                        const lunchStart = parseTime(breakStart.timestamp);
                                        const lunchEnd = parseTime(breakEnd.timestamp);
                                        if (lunchStart && lunchEnd) {
                                            const lunchDuration = (lunchEnd - lunchStart) / (1000 * 60 * 60);
                                            dayHours -= lunchDuration;
                                        }
                                    }
                                    totalWeeklyHours += Math.max(0, dayHours);
                                    console.log(`Day hours for ${dateKey}: ${dayHours.toFixed(1)}`);
                                }
                            } else if (dateKey === new Date().toISOString().split('T')[0]) {
                                // Still working today - calculate partial hours
                                const startTime = parseTime(checkIn.timestamp);
                                if (startTime) {
                                    const now = new Date();
                                    let dayHours = (now - startTime) / (1000 * 60 * 60);
                                    // Subtract lunch break if taken
                                    if (breakStart && breakEnd) {
                                        const lunchStart = parseTime(breakStart.timestamp);
                                        const lunchEnd = parseTime(breakEnd.timestamp);
                                        if (lunchStart && lunchEnd) {
                                            const lunchDuration = (lunchEnd - lunchStart) / (1000 * 60 * 60);
                                            dayHours -= lunchDuration;
                                        }
                                    }
                                    totalWeeklyHours += Math.max(0, dayHours);
                                    console.log(`Partial day hours (today): ${dayHours.toFixed(1)}`);
                                }
                            }
                        }
                    }
                });

                console.log('Total weekly hours calculated:', totalWeeklyHours);

                // Calculate average check-in time
                let avgCheckinDisplay = 'N/A';
                if (checkinTimes.length > 0) {
                    avgCheckinDisplay = checkinTimes[0]; // Show first check-in as example
                }

                // Calculate work days based on period
                let workDaysElapsed, totalWorkDays;
                const today_calc = new Date();
                
                if (period === 'thisweek') {
                    const currentDayOfWeek_calc = today_calc.getDay();
                    if (currentDayOfWeek_calc === 0) { // Sunday
                        workDaysElapsed = 5;
                    } else if (currentDayOfWeek_calc === 6) { // Saturday  
                        workDaysElapsed = 5;
                    } else {
                        workDaysElapsed = currentDayOfWeek_calc;
                    }
                    totalWorkDays = 5;
                } else if (period === 'lastweek') {
                    // Last week is complete - always 5 work days
                    workDaysElapsed = 5;
                    totalWorkDays = 5;
                } else if (period === 'thismonth' || period === 'lastmonth') {
                    // Count weekdays in the period
                    let currentDate = new Date(startDate);
                    let workDaysInPeriod = 0;
                    let workDaysElapsedInPeriod = 0;
                    
                    while (currentDate <= endDate) {
                        const dayOfWeek = currentDate.getDay();
                        if (dayOfWeek >= 1 && dayOfWeek <= 5) { // Monday to Friday
                            workDaysInPeriod++;
                            if (period === 'lastmonth' || currentDate <= today_calc) {
                                workDaysElapsedInPeriod++;
                            }
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                    
                    workDaysElapsed = workDaysElapsedInPeriod;
                    totalWorkDays = workDaysInPeriod;
                } else {
                    workDaysElapsed = 5;
                    totalWorkDays = 5;
                }

                console.log(`Work days elapsed for ${periodLabel}:`, workDaysElapsed);
                console.log(`Period range: ${dateRangeText}`);
                console.log('Days present in database:', workDays, 'out of', workDaysElapsed, 'work days');

                const attendanceRate = workDaysElapsed > 0 ? Math.round((workDays / workDaysElapsed) * 100) : 0;

                if (weeklyHours) weeklyHours.textContent = totalWeeklyHours.toFixed(1);
                if (weeklyDays) weeklyDays.textContent = workDays;
                if (weeklyAttendance) weeklyAttendance.textContent = attendanceRate + '%';
                if (weeklyAvgCheckin) weeklyAvgCheckin.textContent = avgCheckinDisplay;
                if (weeklyDaysTotal) weeklyDaysTotal.textContent = `Out of ${totalWorkDays}`;
                if (weeklyCheckinTrend) weeklyCheckinTrend.textContent = totalWeeklyHours >= 32 ? 'Good hours' : 'Needs improvement';
                if (weeklyPerformance) weeklyPerformance.textContent = totalWeeklyHours > 0 ? 'Active!' : 'Start tracking!';
            } else {
                // Calculate work days elapsed even when no data
                const today = new Date();
                const currentDayOfWeek = today.getDay();
                let workDaysElapsed;
                if (currentDayOfWeek === 0) { // Sunday
                    workDaysElapsed = 5;
                } else if (currentDayOfWeek === 6) { // Saturday  
                    workDaysElapsed = 5;
                } else {
                    workDaysElapsed = currentDayOfWeek;
                }

                // Update date range display for empty state too
                const today_date = new Date();
                const currentDay_empty = today_date.getDay();
                const monday_empty = new Date(today_date);
                monday_empty.setDate(today_date.getDate() - (currentDay_empty === 0 ? 6 : currentDay_empty - 1));

                const sunday_empty = new Date(monday_empty);
                sunday_empty.setDate(monday_empty.getDate() + 6);

                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const mondayStr_empty = `${monthNames[monday_empty.getMonth()]} ${monday_empty.getDate()}`;
                const sundayStr_empty = `${monthNames[sunday_empty.getMonth()]} ${sunday_empty.getDate()}`;
                const dateRangeText_empty = `${mondayStr_empty} - ${sundayStr_empty}`;

                if (weeklyDateRange) weeklyDateRange.textContent = dateRangeText_empty;

                // Empty states
                if (weeklyHours) weeklyHours.textContent = '0';
                if (weeklyDays) weeklyDays.textContent = '0';
                if (weeklyAttendance) weeklyAttendance.textContent = 'N/A';
                if (weeklyAvgCheckin) weeklyAvgCheckin.textContent = 'N/A';
                if (weeklyDaysTotal) weeklyDaysTotal.textContent = `Out of 5`; // Always show 5 work days
                if (weeklyCheckinTrend) weeklyCheckinTrend.textContent = 'No data';
                if (weeklyPerformance) weeklyPerformance.textContent = 'Start tracking!';
            }
        }

        // Pagination Functions
        function getPaginatedData(data) {
            const startIndex = (attendancePagination.currentPage - 1) * attendancePagination.recordsPerPage;
            const endIndex = startIndex + attendancePagination.recordsPerPage;
            return data.slice(startIndex, endIndex);
        }

        function updatePaginationControls(totalRecords) {
            attendancePagination.totalRecords = totalRecords;
            attendancePagination.totalPages = Math.ceil(totalRecords / attendancePagination.recordsPerPage);

            // Update pagination info text
            const paginationInfo = document.getElementById('pagination-info');
            if (paginationInfo) {
                if (totalRecords === 0) {
                    paginationInfo.textContent = 'No records to show';
                } else {
                    const startRecord = (attendancePagination.currentPage - 1) * attendancePagination.recordsPerPage + 1;
                    const endRecord = Math.min(attendancePagination.currentPage * attendancePagination.recordsPerPage,
                        totalRecords);
                    paginationInfo.textContent = `Showing ${startRecord} to ${endRecord} of ${totalRecords} records`;
                }
            }

            // Update pagination buttons
            updatePaginationButtons();
        }

        function updatePaginationButtons() {
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const pageNumbers = document.getElementById('page-numbers');

            // Update Previous button
            if (prevBtn) {
                prevBtn.disabled = attendancePagination.currentPage <= 1;
            }

            // Update Next button
            if (nextBtn) {
                nextBtn.disabled = attendancePagination.currentPage >= attendancePagination.totalPages;
            }

            // Generate page number buttons
            if (pageNumbers) {
                pageNumbers.innerHTML = '';

                // Show up to 5 page numbers
                const maxVisiblePages = 5;
                let startPage = Math.max(1, attendancePagination.currentPage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(attendancePagination.totalPages, startPage + maxVisiblePages - 1);

                // Adjust start page if we're near the end
                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.textContent = i;
                    pageBtn.onclick = () => goToPage(i);

                    if (i === attendancePagination.currentPage) {
                        pageBtn.className = 'px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700';
                    } else {
                        pageBtn.className = 'px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
                    }

                    pageNumbers.appendChild(pageBtn);
                }
            }
        }

        function changePage(direction) {
            const newPage = attendancePagination.currentPage + direction;
            if (newPage >= 1 && newPage <= attendancePagination.totalPages) {
                goToPage(newPage);
            }
        }

        function goToPage(pageNumber) {
            if (pageNumber >= 1 && pageNumber <= attendancePagination.totalPages) {
                attendancePagination.currentPage = pageNumber;
                displayAttendanceData(); // Refresh the display with new page
            }
        }

        function changeRecordsPerPage(newRecordsPerPage) {
            attendancePagination.recordsPerPage = parseInt(newRecordsPerPage);
            attendancePagination.currentPage = 1; // Reset to first page
            displayAttendanceData(); // Refresh the display
        }



        async function fetchUserWorkplace(userId = null) {
            userId = userId || getCurrentUserId();

            if (!userId) {
                console.error('No valid user ID available for workplace fetch');
                return null;
            }

            try {
                const response = await fetch(`/api/user-workplace/${userId}`);
                if (response.ok) {
                    const workplace = await response.json();

                    // Update workLocation with database data
                    workLocations.mainOffice = {
                        lat: workplace.latitude,
                        lng: workplace.longitude,
                        name: workplace.name,
                        address: workplace.address,
                        radius: workplace.radius
                    };

                    // Update workplace display
                    updateWorkplaceDisplay();

                    // Refresh existing maps if they are active
                    if (checkinMap && mapInitializationState.checkinMap) {
                        refreshCheckinMapData();
                    }
                    if (setupMap && mapInitializationState.setupMap) {
                        populateWorkplaceForm(workLocations.mainOffice);
                        if (workLocations.mainOffice.lat && workLocations.mainOffice.lng) {
                            setWorkplaceLocation(workLocations.mainOffice.lat, workLocations.mainOffice.lng, false);
                        }
                    }
                } else {
                    console.log('No workplace configured in database, clearing any cached data');
                    // Clear workplace data when user has no workplace configured
                    workLocations.mainOffice = null;

                    // Clear user-specific localStorage
                    const currentUserId = getCurrentUserId();
                    if (currentUserId) {
                        const userSpecificKey = `${STORAGE_KEYS.workplace}_user_${currentUserId}`;
                        localStorage.removeItem(userSpecificKey);
                    }

                    // Also clear old non-user-specific key for cleanup
                    localStorage.removeItem(STORAGE_KEYS.workplace);

                    updateWorkplaceDisplay();
                }
            } catch (error) {
                console.error('Failed to fetch workplace:', error);
            }
        }

        async function fetchUserWorkplaces(userId = null) {
            userId = userId || getCurrentUserId();
            console.log('Fetching workplaces for user:', userId);

            try {
                const response = await fetch(`/api/user-workplaces/${userId}`);
                console.log('Workplace API response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Workplace API data received:', data);
                console.log('Primary workplace from API:', data.primary_workplace);

                const workplacesList = document.getElementById('assigned-workplaces-list');
                const noWorkplacesMessage = document.getElementById('no-workplaces-message');
                const workplacesCount = document.getElementById('workplaces-count');
                const workplacesCountStat = document.getElementById('workplaces-count-stat');

                if (data.workplaces && data.workplaces.length > 0) {
                    // Cache the workplaces data
                    cachedWorkplacesData = data.workplaces;

                    // Update pagination
                    workplacesPagination.totalItems = data.workplaces.length;
                    workplacesPagination.totalPages = Math.ceil(data.workplaces.length / workplacesPagination
                        .itemsPerPage);
                    workplacesPagination.currentPage = 1; // Reset to first page

                    // Update count displays
                    if (workplacesCount) {
                        workplacesCount.textContent =
                            `${data.workplaces.length} location${data.workplaces.length !== 1 ? 's' : ''}`;
                    }
                    if (workplacesCountStat) {
                        workplacesCountStat.textContent = data.workplaces.length;
                    }

                    // Hide no workplaces message
                    if (noWorkplacesMessage) {
                        noWorkplacesMessage.classList.add('hidden');
                    }

                    // Display paginated workplaces
                    displayPaginatedWorkplaces();

                    // Update primary workplace info
                    updatePrimaryWorkplaceInfo(data.primary_workplace);

                } else {
                    // Clear cache
                    cachedWorkplacesData = [];
                    workplacesPagination.totalItems = 0;
                    workplacesPagination.totalPages = 0;

                    // Show no workplaces message
                    if (workplacesList) {
                        workplacesList.innerHTML = '';
                    }
                    if (noWorkplacesMessage) {
                        noWorkplacesMessage.classList.remove('hidden');
                    }
                    if (workplacesCount) {
                        workplacesCount.textContent = '0 locations';
                    }
                    if (workplacesCountStat) {
                        workplacesCountStat.textContent = '0';
                    }

                    // Hide pagination
                    const paginationContainer = document.getElementById('workplaces-pagination');
                    if (paginationContainer) {
                        paginationContainer.style.display = 'none';
                    }

                    // Clear primary workplace info
                    updatePrimaryWorkplaceInfo(null);
                }

                console.log('Workplaces loaded:', data);

            } catch (error) {
                console.error('Failed to fetch user workplaces:', error);
                const workplacesList = document.getElementById('assigned-workplaces-list');
                const noWorkplacesMessage = document.getElementById('no-workplaces-message');

                if (workplacesList) {
                    workplacesList.innerHTML = `
                        <div class="flex items-center justify-center p-6 sm:p-8 text-red-500">
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle text-2xl sm:text-3xl mb-3"></i>
                                <p class="text-sm sm:text-base">Failed to load workplaces</p>
                                <p class="text-xs sm:text-sm text-gray-500 mt-1">${error.message}</p>
                                <button onclick="fetchUserWorkplaces()" class="mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm sm:text-base">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    `;
                }

                if (noWorkplacesMessage) {
                    noWorkplacesMessage.classList.add('hidden');
                }
            }
        }

        // Display paginated workplaces
        function displayPaginatedWorkplaces() {
            const tableBody = document.getElementById('workplaces-table-body');
            const cardsContainer = document.getElementById('workplaces-cards');
            if (!tableBody && !cardsContainer) return;

            // Calculate start and end indices
            const startIndex = (workplacesPagination.currentPage - 1) * workplacesPagination.itemsPerPage;
            const endIndex = Math.min(startIndex + workplacesPagination.itemsPerPage, cachedWorkplacesData.length);
            const paginatedWorkplaces = cachedWorkplacesData.slice(startIndex, endIndex);

            if (paginatedWorkplaces.length === 0) {
                const emptyState = `
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                        <p class="text-sm font-medium">No workplaces found</p>
                        <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                    </div>
                `;
                if (tableBody) {
                    tableBody.innerHTML = `<tr><td colspan="5">${emptyState}</td></tr>`;
                }
                if (cardsContainer) {
                    cardsContainer.innerHTML = emptyState;
                }
                return;
            }

            // Populate desktop table
            if (tableBody) {
                tableBody.innerHTML = paginatedWorkplaces.map(workplace => {
                    const isPrimary = workplace.is_primary;
                    return `
                        <tr class="hover:bg-gray-50 transition-colors workplace-row" data-workplace-id="${workplace.id}" data-is-primary="${isPrimary}">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br ${isPrimary ? 'from-green-500 to-emerald-600' : 'from-indigo-500 to-purple-600'} rounded-lg flex items-center justify-center shadow-sm mr-3">
                                        <i class="fas ${isPrimary ? 'fa-star' : 'fa-building'} text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">${workplace.name}</div>
                                        ${isPrimary ? '<span class="inline-block mt-0.5 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-medium">Primary</span>' : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs">
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-0.5"></i>
                                    <span class="line-clamp-2">${workplace.address || 'No address provided'}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="flex items-center">
                                    <i class="fas fa-circle-notch text-gray-400 mr-1"></i>
                                    ${workplace.radius}m
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                ${workplace.assigned_at ? `
                                    <div class="text-xs text-gray-600">
                                        <i class="fas fa-calendar mr-1"></i>
                                        ${workplace.assigned_at}
                                    </div>
                                ` : '<span class="text-xs text-gray-400">-</span>'}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button onclick="selectWorkplace(${workplace.id}, '${workplace.name.replace(/'/g, "\\'")}', '${(workplace.address || '').replace(/'/g, "\\'")}', ${workplace.latitude}, ${workplace.longitude}, ${workplace.radius}, ${isPrimary})"
                                        class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200 transition-colors"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${!isPrimary ? `
                                        <button onclick="event.stopPropagation(); setPrimaryWorkplace(${workplace.id}, \`${workplace.name.replace(/`/g, "\\`")}\`)"
                                            class="px-3 py-1.5 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-lg text-xs font-semibold hover:shadow-md transition-all"
                                            title="Set as Primary">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // Populate mobile cards
            if (cardsContainer) {
                cardsContainer.innerHTML = paginatedWorkplaces.map(workplace => {
                    const isPrimary = workplace.is_primary;
                    return `
                        <div class="p-4 border-b border-gray-200 last:border-b-0 workplace-row" data-workplace-id="${workplace.id}" data-is-primary="${isPrimary}">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-12 h-12 bg-gradient-to-br ${isPrimary ? 'from-green-500 to-emerald-600' : 'from-indigo-500 to-purple-600'} rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
                                    <i class="fas ${isPrimary ? 'fa-star' : 'fa-building'} text-white text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-900 text-base mb-1">${workplace.name}</h4>
                                    ${isPrimary ? '<span class="inline-block px-2.5 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold">Primary Workplace</span>' : ''}
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2.5 mb-3">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 text-sm mt-0.5 flex-shrink-0"></i>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-500 uppercase mb-0.5">Address</p>
                                        <p class="text-sm text-gray-700">${workplace.address || 'No address provided'}</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-circle-notch text-gray-400 text-sm flex-shrink-0"></i>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase">Radius</p>
                                            <p class="text-sm text-gray-700 font-semibold">${workplace.radius}m</p>
                                        </div>
                                    </div>
                                    ${workplace.assigned_at ? `
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-calendar text-gray-400 text-sm flex-shrink-0"></i>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase">Assigned</p>
                                                <p class="text-sm text-gray-700 font-semibold">${workplace.assigned_at}</p>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                                
                                ${workplace.role ? `
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-user-tag text-gray-400 text-sm flex-shrink-0"></i>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase">Role</p>
                                            <p class="text-sm text-gray-700 font-semibold capitalize">${workplace.role}</p>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="selectWorkplace(${workplace.id}, '${workplace.name.replace(/'/g, "\\'")}', '${(workplace.address || '').replace(/'/g, "\\'")}', ${workplace.latitude}, ${workplace.longitude}, ${workplace.radius}, ${isPrimary})"
                                    class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-all shadow-sm">
                                    <i class="fas fa-eye mr-2"></i>View Details
                                </button>
                                ${!isPrimary ? `
                                    <button onclick="setPrimaryWorkplace(${workplace.id}, \`${workplace.name.replace(/`/g, "\\`")}\`)"
                                        class="flex-1 px-4 py-2.5 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all shadow-sm">
                                        <i class="fas fa-star mr-2"></i>Set Primary
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // Update pagination controls
            updateWorkplacesPaginationControls();

            // Setup search functionality
            setupWorkplaceSearch();
        }

        // Setup workplace search
        function setupWorkplaceSearch() {
            const searchInput = document.getElementById('workplace-search');
            if (!searchInput) return;

            // Remove old listener if exists
            searchInput.replaceWith(searchInput.cloneNode(true));
            const newSearchInput = document.getElementById('workplace-search');

            newSearchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.workplace-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update count if needed
                console.log(`Found ${visibleCount} workplaces matching "${searchTerm}"`);
            });
        }

        // Filter workplaces
        function filterWorkplaces() {
            const filter = document.getElementById('workplace-filter')?.value || 'all';
            const rows = document.querySelectorAll('.workplace-row');

            rows.forEach(row => {
                const isPrimary = row.dataset.isPrimary === 'true';
                
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'primary' && isPrimary) {
                    row.style.display = '';
                } else if (filter === 'secondary' && !isPrimary) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Toggle view all workplaces
        let viewingAllWorkplaces = false;
        async function toggleAllWorkplaces() {
            const btn = document.getElementById('view-all-btn');
            if (!btn) return;

            viewingAllWorkplaces = !viewingAllWorkplaces;

            if (viewingAllWorkplaces) {
                // Fetch all workplaces from system
                btn.innerHTML = '<i class="fas fa-spinner fa-spin sm:mr-2"></i><span class="hidden sm:inline">Loading...</span>';
                btn.disabled = true;

                try {
                    const response = await fetch('/api/all-workplaces');
                    console.log('All workplaces API response:', response.status);
                    
                    if (!response.ok) throw new Error('Failed to fetch all workplaces');
                    
                    const data = await response.json();
                    console.log('All workplaces data:', data);
                    
                    if (data.success && data.workplaces) {
                        // Store original data
                        window.originalWorkplacesData = cachedWorkplacesData;
                        
                        // Update cached data with all workplaces
                        cachedWorkplacesData = data.workplaces;
                        workplacesPagination.totalItems = data.workplaces.length;
                        workplacesPagination.totalPages = Math.ceil(data.workplaces.length / workplacesPagination.itemsPerPage);
                        workplacesPagination.currentPage = 1;

                        // Update display
                        displayPaginatedWorkplaces();

                        // Update button
                        btn.innerHTML = '<i class="fas fa-user-check sm:mr-2"></i><span class="hidden sm:inline">My Workplaces</span>';
                        btn.classList.remove('bg-indigo-100', 'text-indigo-700', 'hover:bg-indigo-200');
                        btn.classList.add('bg-yellow-100', 'text-yellow-700', 'hover:bg-yellow-200');
                    }
                } catch (error) {
                    console.error('Error fetching all workplaces:', error);
                    showNotification('Failed to load all workplaces', 'error');
                    viewingAllWorkplaces = false;
                } finally {
                    btn.disabled = false;
                }
            } else {
                // Restore original workplaces
                if (window.originalWorkplacesData) {
                    cachedWorkplacesData = window.originalWorkplacesData;
                    workplacesPagination.totalItems = cachedWorkplacesData.length;
                    workplacesPagination.totalPages = Math.ceil(cachedWorkplacesData.length / workplacesPagination.itemsPerPage);
                    workplacesPagination.currentPage = 1;

                    // Update display
                    displayPaginatedWorkplaces();
                }

                // Update button
                btn.innerHTML = '<i class="fas fa-globe sm:mr-2"></i><span class="hidden sm:inline">View All</span>';
                btn.classList.remove('bg-yellow-100', 'text-yellow-700', 'hover:bg-yellow-200');
                btn.classList.add('bg-indigo-100', 'text-indigo-700', 'hover:bg-indigo-200');
            }
        }

        // Update pagination controls for workplaces
        function updateWorkplacesPaginationControls() {
            const paginationContainer = document.getElementById('workplaces-pagination');
            const paginationInfo = document.getElementById('workplaces-pagination-info');
            const prevBtn = document.getElementById('workplaces-prev-btn');
            const nextBtn = document.getElementById('workplaces-next-btn');
            const pageNumbersContainer = document.getElementById('workplaces-page-numbers');

            if (!paginationContainer) return;

            // Show/hide pagination based on total items
            if (workplacesPagination.totalItems > workplacesPagination.itemsPerPage) {
                paginationContainer.style.display = 'block';
            } else {
                paginationContainer.style.display = 'none';
                return;
            }

            // Update info text
            const startIndex = (workplacesPagination.currentPage - 1) * workplacesPagination.itemsPerPage + 1;
            const endIndex = Math.min(workplacesPagination.currentPage * workplacesPagination.itemsPerPage,
                workplacesPagination.totalItems);
            if (paginationInfo) {
                paginationInfo.textContent =
                    `Showing ${startIndex}-${endIndex} of ${workplacesPagination.totalItems} workplace${workplacesPagination.totalItems !== 1 ? 's' : ''}`;
            }

            // Update prev/next buttons
            if (prevBtn) {
                prevBtn.disabled = workplacesPagination.currentPage === 1;
            }
            if (nextBtn) {
                nextBtn.disabled = workplacesPagination.currentPage === workplacesPagination.totalPages;
            }

            // Generate page numbers
            if (pageNumbersContainer) {
                let pageNumbersHtml = '';
                const maxVisiblePages = 5;
                let startPage = Math.max(1, workplacesPagination.currentPage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(workplacesPagination.totalPages, startPage + maxVisiblePages - 1);

                // Adjust start page if we're near the end
                if (endPage - startPage < maxVisiblePages - 1) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const isActive = i === workplacesPagination.currentPage;
                    pageNumbersHtml += `
                        <button onclick="goToWorkplacesPage(${i})" 
                                class="px-3 py-1 border ${isActive ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-gray-300 hover:bg-gray-50'} rounded-lg text-xs lg:text-sm transition-colors font-medium">
                            ${i}
                        </button>
                    `;
                }
                pageNumbersContainer.innerHTML = pageNumbersHtml;
            }
        }

        // Navigate to specific page
        function goToWorkplacesPage(page) {
            if (page < 1 || page > workplacesPagination.totalPages) return;
            workplacesPagination.currentPage = page;
            displayPaginatedWorkplaces();
        }

        // Change page (next/prev)
        function changeWorkplacesPage(direction) {
            const newPage = workplacesPagination.currentPage + direction;
            if (newPage >= 1 && newPage <= workplacesPagination.totalPages) {
                goToWorkplacesPage(newPage);
            }
        }

        async function performCheckinAPI() {
            if (!userLocation) {
                showNotification('Location not available', 'error');
                return;
            }

            const checkinBtn = document.getElementById('checkin-btn');
            const originalContent = checkinBtn.innerHTML;

            // Update button to show loading state
            checkinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking In...';
            checkinBtn.disabled = true;

            try {
                const response = await fetch('/api/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    body: JSON.stringify({
                        user_id: getCurrentUserId(),
                        latitude: userLocation.coords.latitude,
                        longitude: userLocation.coords.longitude,
                        accuracy: userLocation.coords.accuracy
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification(result.message, 'success');
                    isCurrentlyCheckedIn = true; // Update status after successful check-in
                    updateTodaysActivity(result.attendance);
                    // Refresh stats and history
                    fetchUserStats();
                    fetchAttendanceHistory();
                    fetchTodaysActivity();
                    fetchTodaysSchedule(); // Refresh schedule to show real times

                    // Update button to show check-out option
                    if (userLocation) {
                        updateGeofenceStatus(userLocation);
                    }
                } else {
                    showNotification(result.error || 'Check-in failed', 'error');

                    // Handle mutual exclusivity error
                    if (result.locked_type) {
                        checkTodayCheckinType(); // Refresh the UI to show locked state
                    }

                    // Handle specific error cases
                    if (result.redirect === 'my-workplace') {
                        setTimeout(() => {
                            switchToSection('my-workplace');
                        }, 2000);
                    }

                    // Reset button
                    checkinBtn.innerHTML = originalContent;
                    checkinBtn.disabled = false;
                }
            } catch (error) {
                console.error('Check-in error:', error);
                showNotification('Check-in failed: ' + error.message, 'error');
                // Reset button
                checkinBtn.innerHTML = originalContent;
                checkinBtn.disabled = false;
            }
        }

        function updateTodaysActivity(attendance) {
            const activityContainer = document.getElementById('todays-activity');
            if (!activityContainer || !attendance) return;

            const checkInTime = attendance.check_in_time ?
                new Date(attendance.check_in_time).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }) : null;

            const checkOutTime = attendance.check_out_time ?
                new Date(attendance.check_out_time).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }) : null;

            let html = '';

            if (checkInTime) {
                html += `
                    <div class="flex items-center p-3 bg-green-50 rounded-lg">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-sign-in-alt text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-green-800">Check-in</p>
                            <p class="text-sm text-green-600">Workplace • ${checkInTime}</p>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="flex items-center p-3 bg-gray-50 rounded-lg ${checkOutTime ? '' : 'opacity-50'}">
                    <div class="w-10 h-10 ${checkOutTime ? 'bg-red-500' : 'bg-gray-400'} rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-sign-out-alt text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium ${checkOutTime ? 'text-red-800' : 'text-gray-600'}">Check-out</p>
                        <p class="text-sm ${checkOutTime ? 'text-red-600' : 'text-gray-500'}">${checkOutTime || 'Pending'}</p>
                    </div>
                    ${checkOutTime ? '<div class="text-red-600"><i class="fas fa-check"></i></div>' : ''}
                </div>
            `;

            activityContainer.innerHTML = html;
        }

        async function performCheckoutAPI() {
            if (!userLocation) {
                showNotification('Location not available', 'error');
                return;
            }

            const checkinBtn = document.getElementById('checkin-btn');
            const originalContent = checkinBtn.innerHTML;

            // Update button to show loading state
            checkinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking Out...';
            checkinBtn.disabled = true;

            try {
                const response = await fetch('/api/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    body: JSON.stringify({
                        user_id: getCurrentUserId(),
                        latitude: userLocation.coords.latitude,
                        longitude: userLocation.coords.longitude,
                        accuracy: userLocation.coords.accuracy
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification(result.message + ` (Worked ${result.total_hours} hours)`, 'success');
                    isCurrentlyCheckedIn = false; // Update status
                    updateTodaysActivity(result.attendance);
                    // Refresh stats and history
                    fetchUserStats();
                    fetchAttendanceHistory();
                    fetchTodaysActivity();

                    // Update button back to check-in state
                    if (userLocation) {
                        updateGeofenceStatus(userLocation);
                    }
                } else {
                    showNotification(result.error || 'Check-out failed', 'error');

                    // Reset button
                    checkinBtn.innerHTML = originalContent;
                    checkinBtn.disabled = false;
                }
            } catch (error) {
                console.error('Check-out error:', error);
                showNotification('Check-out failed: ' + error.message, 'error');
                // Reset button
                checkinBtn.innerHTML = originalContent;
                checkinBtn.disabled = false;
            }
        }

        async function performActionAPI() {
            if (!userLocation) {
                showNotification('Location not available', 'error');
                return;
            }

            const checkinBtn = document.getElementById('checkin-btn');
            const originalContent = checkinBtn.innerHTML;

            // Update button to show loading state
            checkinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            checkinBtn.disabled = true;

            try {
                const response = await fetch('/api/perform-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    body: JSON.stringify({
                        user_id: getCurrentUserId(),
                        latitude: userLocation.coords.latitude,
                        longitude: userLocation.coords.longitude,
                        accuracy: userLocation.coords.accuracy
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    // Show success message
                    showNotification(result.message, 'success');

                    // Show warning if checking in at non-assigned workplace
                    if (result.warning) {
                        setTimeout(() => {
                            showNotification(result.warning, 'warning');
                        }, 2000);
                        
                        if (result.info) {
                            setTimeout(() => {
                                showNotification(result.info, 'info');
                            }, 3500);
                        }
                    }

                    // Refresh all data
                    fetchCurrentStatus();
                    fetchUserStats();
                    fetchAttendanceHistory();
                    fetchTodaysActivity();
                    fetchTodaysSchedule();
                } else {
                    showNotification(result.error || 'Action failed', 'error');

                    // Handle specific error cases
                    if (result.redirect === 'my-workplace') {
                        setTimeout(() => {
                            switchToSection('my-workplace');
                        }, 2000);
                    }

                    // Reset button
                    checkinBtn.innerHTML = originalContent;
                    checkinBtn.disabled = false;
                }
            } catch (error) {
                console.error('Action error:', error);
                showNotification('Action failed: ' + error.message, 'error');
                // Reset button
                checkinBtn.innerHTML = originalContent;
                checkinBtn.disabled = false;
            }
        }

        async function fetchCurrentStatus(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                const response = await fetch(`/api/current-status/${userId}`);
                const data = await response.json();

                // Check if there's an active special check-in today
                let hasActiveSpecialCheckin = false;
                try {
                    const specialResponse = await fetch(`/api/special-checkin-logs/${userId}`);
                    if (specialResponse.ok) {
                        const specialData = await specialResponse.json();
                        // Check if last action was a check-in (meaning they haven't checked out yet)
                        if (specialData.logs && specialData.logs.length > 0) {
                            const lastLog = specialData.logs[specialData.logs.length - 1];
                            hasActiveSpecialCheckin = lastLog.action === 'check_in';
                        }
                    }
                } catch (specialError) {
                    console.log('Could not check special check-in status:', specialError);
                }

                // Check if user is within geofence before updating button
                const workplace = workLocations.mainOffice;
                let inWorkplaceGeofence = false;

                if (workplace && workplace.lat && workplace.lng && userLocation && userLocation.coords) {
                    const workplaceDistance = calculateDistance(
                        userLocation.coords.latitude,
                        userLocation.coords.longitude,
                        workplace.lat,
                        workplace.lng
                    );
                    inWorkplaceGeofence = workplaceDistance <= workplace.radius;
                }

                // Update button based on current status
                const checkinBtn = document.getElementById('checkin-btn');
                if (checkinBtn) {
                    // If there's an active special check-in, disable regular check-in
                    if (hasActiveSpecialCheckin) {
                        checkinBtn.className =
                            'w-full py-4 bg-amber-500 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm';
                        checkinBtn.innerHTML =
                            '<i class="fas fa-star mr-2"></i>Special Check-in Active - Check out first';
                        checkinBtn.disabled = true;
                        checkinBtn.onclick = null;
                        return; // Don't process further
                    }

                    // Show action button - backend will validate location
                    if (data.can_perform_action) {
                        const colorClasses = {
                            'green': 'bg-green-600 hover:bg-green-700',
                            'yellow': 'bg-yellow-600 hover:bg-yellow-700',
                            'blue': 'bg-blue-600 hover:bg-blue-700',
                            'red': 'bg-red-600 hover:bg-red-700'
                        };

                        const colorClass = colorClasses[data.button_color] || 'bg-gray-600';
                        checkinBtn.className =
                            `w-full py-4 ${colorClass} text-white rounded-xl font-bold text-lg transition-colors duration-200 shadow-sm`;
                        checkinBtn.innerHTML = `<i class="fas fa-clock mr-2"></i>${data.button_text}`;
                        checkinBtn.disabled = false;
                        checkinBtn.onclick = performCheckin;
                    } else {
                        checkinBtn.className =
                            'w-full py-4 bg-gray-500 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm';
                        checkinBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Work Day Complete';
                        checkinBtn.disabled = true;
                        checkinBtn.onclick = null;
                    }
                }

                // Update today's activity with the logs
                updateTodaysActivityFromLogs(data.logs);

                console.log('Current status:', data);

            } catch (error) {
                console.error('Failed to fetch current status:', error);
            }
        }

        function updateTodaysActivityFromLogs(logs) {
            const activityContainer = document.getElementById('todays-activity');
            if (!activityContainer) return;
            
            // Filter only REGULAR shift logs (not special)
            const regularLogs = logs ? logs.filter(log => log.shift_type !== 'special') : [];
            
            if (regularLogs.length === 0) {
                // Show empty state
                activityContainer.innerHTML = `
                    <div class="flex items-center justify-center p-8 text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-calendar-day text-3xl mb-3 text-gray-300"></i>
                            <p class="font-medium">No regular activity recorded today</p>
                            <p class="text-sm text-gray-400 mt-1">Check in to start tracking</p>
                        </div>
                    </div>
                `;
                return;
            }

            let html = '';

            regularLogs.forEach((log, index) => {
                const actionIcons = {
                    'check_in': 'fa-sign-in-alt',
                    'break_start': 'fa-utensils',
                    'break_end': 'fa-play',
                    'check_out': 'fa-sign-out-alt'
                };

                const actionColors = {
                    'check_in': {
                        bg: 'bg-green-50',
                        text: 'text-green-800',
                        dot: 'bg-green-500',
                        icon: 'text-green-600'
                    },
                    'break_start': {
                        bg: 'bg-yellow-50',
                        text: 'text-yellow-800',
                        dot: 'bg-yellow-500',
                        icon: 'text-yellow-600'
                    },
                    'break_end': {
                        bg: 'bg-blue-50',
                        text: 'text-blue-800',
                        dot: 'bg-blue-500',
                        icon: 'text-blue-600'
                    },
                    'check_out': {
                        bg: 'bg-red-50',
                        text: 'text-red-800',
                        dot: 'bg-red-500',
                        icon: 'text-red-600'
                    }
                };

                const actionLabels = {
                    'check_in': 'Checked In',
                    'break_start': 'Lunch Break Started',
                    'break_end': 'Lunch Break Ended',
                    'check_out': 'Checked Out'
                };

                const colors = actionColors[log.action];
                const icon = actionIcons[log.action] || 'fa-clock';
                const label = actionLabels[log.action] || log.action;

                html += `
                    <div class="flex items-center p-3 ${colors.bg} rounded-lg border border-gray-200">
                        <div class="w-10 h-10 ${colors.dot} rounded-full flex items-center justify-center mr-3">
                            <i class="fas ${icon} text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium ${colors.text}">${label}</p>
                            <p class="text-sm ${colors.icon}">${log.shift_type.toUpperCase()} Shift • ${log.timestamp}</p>
                        </div>
                        <div class="${colors.icon}">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                `;
            });

            activityContainer.innerHTML = html;
        }

        async function fetchTodaysActivity(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                const today = new Date().toISOString().split('T')[0];
                const response = await fetch(`/api/attendance-history/${userId}`);
                const allAttendance = await response.json();

                // Find today's attendance
                const todaysAttendance = allAttendance.find(att => att.date_raw === today);

                if (todaysAttendance) {
                    // Update check-in status
                    isCurrentlyCheckedIn = todaysAttendance.check_in &&
                        (todaysAttendance.check_out === 'Still working' || !todaysAttendance.check_out);

                    // Convert API format to attendance object format for updateTodaysActivity
                    const attendance = {
                        check_in_time: todaysAttendance.check_in ? `${today}T${todaysAttendance.check_in}:00` :
                            null,
                        check_out_time: todaysAttendance.check_out && todaysAttendance.check_out !==
                            'Still working' ?
                            `${today}T${todaysAttendance.check_out}:00` : null
                    };
                    updateTodaysActivity(attendance);
                } else {
                    // No activity today - not checked in
                    isCurrentlyCheckedIn = false;
                    console.log('No attendance record for today');
                }

                // Update button status after determining check-in state
                if (userLocation) {
                    updateGeofenceStatus(userLocation);
                }
            } catch (error) {
                console.error('Failed to fetch today\'s activity:', error);
                isCurrentlyCheckedIn = false; // Default to not checked in on error
            }
        }

        // Check today's check-in type to enforce mutual exclusivity
        async function checkTodayCheckinType(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                const response = await fetch(`/api/today-checkin-type/${userId}`);
                const data = await response.json();

                console.log('Today\'s check-in type:', data);

                // Update Regular Check-In section
                const checkinBtn = document.getElementById('checkin-btn');
                const regularSection = document.getElementById('gps-checkin-section');
                
                if (!data.can_use_regular && data.type === 'special') {
                    // User has already used special check-in, lock regular check-in
                    if (checkinBtn && !regularSection?.classList.contains('hidden')) {
                        const existingWarning = document.getElementById('regular-checkin-locked-warning');
                        if (!existingWarning) {
                            const warningDiv = document.createElement('div');
                            warningDiv.id = 'regular-checkin-locked-warning';
                            warningDiv.className = 'mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg';
                            warningDiv.innerHTML = `
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-lock text-amber-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-bold text-amber-800">Regular Check-In Locked</h3>
                                        <p class="text-sm text-amber-700 mt-1">${data.message}. You cannot use both regular and special check-in on the same day.</p>
                                    </div>
                                </div>
                            `;
                            checkinBtn.parentElement?.insertBefore(warningDiv, checkinBtn);
                        }
                        
                        checkinBtn.className = 'w-full py-4 bg-gray-400 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm opacity-60';
                        checkinBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Locked - Special Check-In Active Today';
                        checkinBtn.disabled = true;
                        checkinBtn.onclick = null;
                    }
                } else {
                    // Remove warning if exists
                    const existingWarning = document.getElementById('regular-checkin-locked-warning');
                    if (existingWarning) {
                        existingWarning.remove();
                    }
                }

                // Update Special Check-In section
                const specialCheckinBtn = document.getElementById('special-checkin-btn');
                const specialSection = document.getElementById('special-checkin-section');
                
                if (!data.can_use_special && data.type === 'regular') {
                    // User has already used regular check-in, lock special check-in
                    if (specialCheckinBtn && !specialSection?.classList.contains('hidden')) {
                        const existingWarning = document.getElementById('special-checkin-locked-warning');
                        if (!existingWarning) {
                            const warningDiv = document.createElement('div');
                            warningDiv.id = 'special-checkin-locked-warning';
                            warningDiv.className = 'mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg';
                            warningDiv.innerHTML = `
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-lock text-blue-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-bold text-blue-800">Special Check-In Locked</h3>
                                        <p class="text-sm text-blue-700 mt-1">${data.message}. You cannot use both regular and special check-in on the same day.</p>
                                    </div>
                                </div>
                            `;
                            specialCheckinBtn.parentElement?.insertBefore(warningDiv, specialCheckinBtn);
                        }
                        
                        specialCheckinBtn.className = 'w-full py-4 bg-gray-400 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm opacity-60';
                        specialCheckinBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Locked - Regular Check-In Active Today';
                        specialCheckinBtn.disabled = true;
                        specialCheckinBtn.onclick = null;
                    }
                } else {
                    // Remove warning if exists
                    const existingWarning = document.getElementById('special-checkin-locked-warning');
                    if (existingWarning) {
                        existingWarning.remove();
                    }
                }

            } catch (error) {
                console.error('Failed to check today\'s check-in type:', error);
            }
        }

        async function fetchTodaysSchedule(userId = null) {
            userId = userId || getCurrentUserId();
            
            const scheduleContent = document.getElementById('schedule-content');
            
            // Show loading animation
            if (scheduleContent) {
                scheduleContent.style.opacity = '0.5';
                scheduleContent.innerHTML = `
                    <div class="flex items-center justify-center p-8 text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-2xl mb-3 text-indigo-600"></i>
                            <p class="font-medium text-sm">Refreshing activity...</p>
                        </div>
                    </div>
                `;
            }
            
            try {
                // Get current status from API to show all activity
                const statusResponse = await fetch(`/api/current-status/${userId}`);
                const statusData = await statusResponse.json();

                // Also fetch special check-in logs
                let specialLogs = [];
                try {
                    const specialResponse = await fetch(`/api/special-checkin-logs/${userId}`);
                    if (specialResponse.ok) {
                        const specialData = await specialResponse.json();
                        console.log('Special logs API response:', specialData);
                        
                        // The API returns { logs: [...], count: x, ... }
                        if (specialData.logs && Array.isArray(specialData.logs)) {
                            const today = new Date().toISOString().split('T')[0];
                            specialLogs = specialData.logs.filter(log => log.date === today);
                            console.log('Filtered special logs for today:', specialLogs);
                        }
                    }
                } catch (error) {
                    console.log('Could not fetch special logs:', error);
                }

                if (!statusData.error) {
                    let scheduleHtml = '';

                    // Combine regular and special logs, sorted by time
                    const allLogs = [];
                    
                    // Add regular logs with type marker
                    if (statusData.logs && statusData.logs.length > 0) {
                        console.log('Adding regular logs:', statusData.logs);
                        statusData.logs.forEach(log => {
                            allLogs.push({
                                ...log,
                                type: 'regular',
                                sort_time: log.timestamp || ''
                            });
                        });
                    }

                    // Add special logs with type marker
                    if (specialLogs.length > 0) {
                        console.log('Adding special logs:', specialLogs);
                        specialLogs.forEach(log => {
                            allLogs.push({
                                action: log.action,
                                timestamp: log.time || log.timestamp,
                                shift_type: 'special',
                                location: log.workplace_name || log.location,
                                type: 'special',
                                sort_time: log.time || log.timestamp || ''
                            });
                        });
                    }

                    console.log('Combined logs with types:', allLogs);

                    // Sort by time
                    allLogs.sort((a, b) => a.sort_time.localeCompare(b.sort_time));

                    if (allLogs.length === 0) {
                        scheduleHtml = `
                            <div class="flex items-center justify-center p-8 text-gray-500">
                                <div class="text-center">
                                    <i class="fas fa-calendar-day text-3xl mb-3 text-gray-300"></i>
                                    <p class="font-medium">No activity recorded today</p>
                                    <p class="text-sm text-gray-400 mt-1">Check in to start tracking</p>
                                </div>
                            </div>
                        `;
                    } else {
                        allLogs.forEach((log, index) => {
                            console.log(`Rendering log ${index}:`, {
                                action: log.action,
                                type: log.type,
                                isRegular: log.type === 'regular',
                                isSpecial: log.type === 'special'
                            });
                            
                            const actionIcons = {
                                'check_in': 'fa-sign-in-alt',
                                'break_start': 'fa-utensils',
                                'break_end': 'fa-play',
                                'check_out': 'fa-sign-out-alt'
                            };

                            const isRegular = log.type === 'regular';
                            const isSpecial = log.type === 'special';
                            
                            console.log(`  -> isRegular: ${isRegular}, isSpecial: ${isSpecial}`);

                            const actionColors = {
                                'check_in': {
                                    bg: isSpecial ? 'bg-yellow-50' : 'bg-green-50',
                                    text: isSpecial ? 'text-yellow-800' : 'text-green-800',
                                    dot: isSpecial ? 'bg-yellow-500' : 'bg-green-500',
                                    icon: isSpecial ? 'text-yellow-600' : 'text-green-600'
                                },
                                'break_start': {
                                    bg: 'bg-orange-50',
                                    text: 'text-orange-800',
                                    dot: 'bg-orange-500',
                                    icon: 'text-orange-600'
                                },
                                'break_end': {
                                    bg: 'bg-blue-50',
                                    text: 'text-blue-800',
                                    dot: 'bg-blue-500',
                                    icon: 'text-blue-600'
                                },
                                'check_out': {
                                    bg: isSpecial ? 'bg-amber-50' : 'bg-red-50',
                                    text: isSpecial ? 'text-amber-800' : 'text-red-800',
                                    dot: isSpecial ? 'bg-amber-500' : 'bg-red-500',
                                    icon: isSpecial ? 'text-amber-600' : 'text-red-600'
                                }
                            };

                            const actionLabels = {
                                'check_in': 'Checked In',
                                'break_start': 'Lunch Break Started',
                                'break_end': 'Lunch Break Ended',
                                'check_out': 'Checked Out'
                            };

                            const colors = actionColors[log.action] || actionColors['check_in'];
                            const icon = actionIcons[log.action] || 'fa-clock';
                            const label = actionLabels[log.action] || log.action;
                            
                            const typeLabel = isSpecial ? 
                                '<span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded-full font-semibold">Special</span>' : 
                                '<span class="px-2 py-0.5 bg-indigo-200 text-indigo-800 text-xs rounded-full font-semibold">Regular</span>';
                            
                            // Add location for special check-ins
                            const locationInfo = isSpecial && log.location ? 
                                ` • <i class="fas fa-map-marker-alt mr-1"></i>${log.location}` : '';

                            scheduleHtml += `
                                <div class="flex items-center p-3 ${colors.bg} rounded-lg border border-gray-200">
                                    <div class="w-10 h-10 ${colors.dot} rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas ${icon} text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-semibold ${colors.text}">${label}</p>
                                            ${typeLabel}
                                        </div>
                                        <p class="text-sm ${colors.icon}">${log.timestamp}${locationInfo}</p>
                                    </div>
                                    <div class="${colors.icon}">
                                        <i class="fas fa-check-circle text-lg"></i>
                                    </div>
                                </div>
                            `;
                        });
                    }

                    scheduleContent.innerHTML = scheduleHtml;
                    
                    // Animate content fade-in
                    scheduleContent.style.opacity = '0';
                    requestAnimationFrame(() => {
                        scheduleContent.style.transition = 'opacity 0.4s ease-in-out';
                        scheduleContent.style.opacity = '1';
                    });
                } else {
                    // No workplace configured or error - show setup prompt
                    scheduleContent.innerHTML = `
                        <div class="flex items-center justify-center p-8 text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-map-marker-alt text-3xl mb-3 text-gray-300"></i>
                                <h4 class="font-medium text-gray-800 mb-2">No Activity Available</h4>
                                <p class="text-gray-500 mb-4">Set up your workplace first to start tracking</p>
                                <button onclick="switchToSection('my-workplace')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    Select Workplace
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Animate content fade-in
                    scheduleContent.style.opacity = '0';
                    requestAnimationFrame(() => {
                        scheduleContent.style.transition = 'opacity 0.4s ease-in-out';
                        scheduleContent.style.opacity = '1';
                    });
                }
            } catch (error) {
                console.error('Failed to fetch workflow status:', error);
                const scheduleContent = document.getElementById('schedule-content');
                if (scheduleContent) {
                    scheduleContent.innerHTML = `
                        <div class="flex items-center justify-center p-8 text-red-500">
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>Failed to load workflow status</p>
                            </div>
                        </div>
                    `;
                    
                    // Animate error message fade-in
                    scheduleContent.style.opacity = '0';
                    requestAnimationFrame(() => {
                        scheduleContent.style.transition = 'opacity 0.4s ease-in-out';
                        scheduleContent.style.opacity = '1';
                    });
                }
            }
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar link clicks
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    switchToSection(section);
                });
            });

            // Load saved workplace data and fetch from API
            loadWorkplaceData();
            updateWorkplaceDisplay();
            
            // ⚡ PARALLEL LOADING: Fetch all data at once instead of sequentially
            Promise.all([
                fetchUserWorkplace(),
                fetchUserStats(),
                fetchAttendanceHistory(),
                fetchTodaysActivity(),
                fetchTodaysSchedule(),
                fetchCurrentStatus()
            ]).catch(error => {
                console.error('Error loading dashboard data:', error);
            });

            // Initialize location with smart approach
            initializeSmartLocation();

            // Initialize testing mode (admin only)
            initializeTestingMode();

            // Start location health monitoring
            startLocationHealthMonitoring();

            // Setup workplace event handlers
            document.getElementById('request-location-btn').addEventListener('click', function() {
                startLocationTracking().then(() => {
                    // Success handled in startLocationTracking
                }).catch(error => {
                    showNotification('Failed to get location: ' + error.message, 'error');
                });
            });

            document.getElementById('use-current-location').addEventListener('click', function() {
                if (userLocation) {
                    setWorkplaceLocation(userLocation.coords.latitude, userLocation.coords.longitude);
                }
            });

            document.getElementById('workplace-radius').addEventListener('change', function() {
                if (workplaceMarker) {
                    const radius = parseInt(this.value) || 100;
                    if (workplaceCircle && setupMap) {
                        setupMap.removeLayer(workplaceCircle);
                        const pos = workplaceMarker.getLatLng();
                        workplaceCircle = L.circle([pos.lat, pos.lng], {
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.1,
                            radius: radius,
                            weight: 2,
                            dashArray: '5, 5'
                        }).addTo(setupMap);
                    }
                }
            });

            document.getElementById('save-workplace').addEventListener('click', saveWorkplace);
            document.getElementById('reset-workplace').addEventListener('click', resetWorkplaceSetup);

            // Start auto-refresh for updates
            startAutoRefresh();
        });

        // Global variables for maps and location
        let userLocation = null;
        let checkinMap = null;
        let setupMap = null;
        let watchId = null;
        let workplaceMarker = null;
        let workplaceCircle = null;
        let hasLocationPermission = false;
        let isCurrentlyCheckedIn = false; // Track check-in status
        let currentAttendanceView = 'detailed'; // Track current view mode
        let cachedAttendanceData = {
            logs: [],
            summary: []
        }; // Cache fetched data
        let attendancePagination = {
            currentPage: 1,
            recordsPerPage: 5,
            totalRecords: 0,
            totalPages: 1
        }; // Pagination state

        // Workplace pagination state
        let cachedWorkplacesData = [];
        let workplacesPagination = {
            currentPage: 1,
            itemsPerPage: 5,
            totalItems: 0,
            totalPages: 1
        };

        // Get current user ID from meta tag
        function getCurrentUserId() {
            const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            if (!userId || userId === '') {
                console.error('No valid user ID found in meta tag');
                return null;
            }
            return parseInt(userId);
        }

        // Storage keys for workplace data and location caching
        const STORAGE_KEYS = {
            workplace: 'cid_ams_workplace_data',
            locationPermission: 'cid_ams_location_permission',
            cachedLocation: 'cid_ams_cached_location',
            locationTimestamp: 'cid_ams_location_timestamp'
        };

        // Location caching functions
        function cacheLocation(position) {
            try {
                const locationData = {
                    coords: {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        altitude: position.coords.altitude,
                        altitudeAccuracy: position.coords.altitudeAccuracy,
                        heading: position.coords.heading,
                        speed: position.coords.speed
                    },
                    timestamp: position.timestamp
                };

                localStorage.setItem(STORAGE_KEYS.cachedLocation, JSON.stringify(locationData));
                localStorage.setItem(STORAGE_KEYS.locationTimestamp, Date.now().toString());
                console.log('Location cached successfully');
            } catch (error) {
                console.warn('Failed to cache location:', error);
            }
        }

        function getCachedLocation() {
            try {
                const cached = localStorage.getItem(STORAGE_KEYS.cachedLocation);
                if (cached) {
                    const locationData = JSON.parse(cached);
                    // Reconstruct the position object format expected by the app
                    return {
                        coords: locationData.coords,
                        timestamp: locationData.timestamp
                    };
                }
            } catch (error) {
                console.warn('Failed to retrieve cached location:', error);
            }
            return null;
        }

        function isCachedLocationValid(cachedLocation) {
            if (!cachedLocation) return false;

            const cacheTimestamp = localStorage.getItem(STORAGE_KEYS.locationTimestamp);
            if (!cacheTimestamp) return false;

            const cacheAge = Date.now() - parseInt(cacheTimestamp);
            const maxCacheAge = 10 * 60 * 1000; // 10 minutes

            return cacheAge < maxCacheAge;
        }

        function clearLocationCache() {
            try {
                // Check if localStorage is available
                if (typeof(Storage) === "undefined") {
                    showSimpleNotification('Local storage not supported', 'error');
                    return;
                }

                // Clear cached location data
                localStorage.removeItem(STORAGE_KEYS.cachedLocation);
                localStorage.removeItem(STORAGE_KEYS.locationTimestamp);
                console.log('Location cache cleared');

                // Reset location state
                userLocation = null;
                hasLocationPermission = false;

                // Clear any existing watch
                if (watchId) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }

                showSimpleNotification('Location cache cleared. Getting fresh location...', 'success');

                // Show loading state immediately
                updateLocationStatus('loading', null, 'Getting fresh location...');

                // Start fresh location request after a short delay
                setTimeout(() => {
                    retryLocationAccess();
                }, 1000);

            } catch (error) {
                console.error('Failed to clear cache:', error);
                showSimpleNotification('Failed to clear location cache: ' + error.message, 'error');
            }
        }

        // Location troubleshooting functions
        function showLocationTroubleshooting() {
            const panel = document.getElementById('location-troubleshooting');
            if (panel) {
                panel.classList.remove('hidden');
                // Run diagnostics when panel is opened
                setTimeout(runLocationDiagnostics, 100);
            }
        }

        function toggleLocationTroubleshooting() {
            const panel = document.getElementById('location-troubleshooting');
            if (panel) {
                panel.classList.toggle('hidden');
            }
        }

        function runLocationDiagnostics() {
            console.log('Running location diagnostics...');

            // Reset all diagnostic indicators
            resetDiagnosticIndicators();

            // Check geolocation support
            checkGeolocationSupport();

            // Check HTTPS
            checkHTTPS();

            // Check connection
            checkConnection();

            // Check permissions
            checkLocationPermissions();
        }

        function resetDiagnosticIndicators() {
            const indicators = ['geolocation-support', 'permission-status', 'connection-status', 'https-status'];
            indicators.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    const icon = element.querySelector('i');
                    const text = element.querySelector('span');
                    if (icon) icon.className = 'fas fa-spinner fa-spin text-blue-500 mr-2';
                    if (text) text.textContent = 'Checking...';
                }
            });

            // Hide error details and recommendations
            document.getElementById('error-details')?.classList.add('hidden');
            document.getElementById('recommended-actions').innerHTML = '';
        }

        function updateDiagnosticResult(elementId, status, message) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const icon = element.querySelector('i');
            const text = element.querySelector('span');

            if (status === 'success') {
                if (icon) icon.className = 'fas fa-check-circle text-green-500 mr-2';
                if (text) text.textContent = message;
                text.className = 'text-green-700';
            } else if (status === 'error') {
                if (icon) icon.className = 'fas fa-times-circle text-red-500 mr-2';
                if (text) text.textContent = message;
                text.className = 'text-red-700';
            } else if (status === 'warning') {
                if (icon) icon.className = 'fas fa-exclamation-triangle text-yellow-500 mr-2';
                if (text) text.textContent = message;
                text.className = 'text-yellow-700';
            }
        }

        function checkGeolocationSupport() {
            setTimeout(() => {
                if (navigator.geolocation) {
                    updateDiagnosticResult('geolocation-support', 'success', 'Geolocation API is supported');
                } else {
                    updateDiagnosticResult('geolocation-support', 'error', 'Geolocation API is not supported');
                    addRecommendation('Use a modern browser that supports geolocation');
                }
            }, 200);
        }

        function checkHTTPS() {
            setTimeout(() => {
                if (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname ===
                    '127.0.0.1') {
                    updateDiagnosticResult('https-status', 'success', 'Secure connection (HTTPS)');
                } else {
                    updateDiagnosticResult('https-status', 'warning',
                        'Insecure connection - location may be limited');
                    addRecommendation('Use HTTPS for better location accuracy');
                }
            }, 400);
        }

        function checkConnection() {
            setTimeout(() => {
                if (navigator.onLine) {
                    updateDiagnosticResult('connection-status', 'success', 'Internet connection is active');
                } else {
                    updateDiagnosticResult('connection-status', 'error', 'No internet connection detected');
                    addRecommendation('Check your internet connection');
                }
            }, 600);
        }

        function checkLocationPermissions() {
            setTimeout(() => {
                if (navigator.permissions) {
                    navigator.permissions.query({
                        name: 'geolocation'
                    }).then(permission => {
                        if (permission.state === 'granted') {
                            updateDiagnosticResult('permission-status', 'success',
                                'Location permission granted');
                        } else if (permission.state === 'denied') {
                            updateDiagnosticResult('permission-status', 'error',
                                'Location permission denied');
                            addRecommendation('Enable location permission in your browser settings');
                            showErrorDetails(
                                'Location permission was denied. Please enable it in your browser settings.'
                            );
                        } else {
                            updateDiagnosticResult('permission-status', 'warning',
                                'Location permission not yet requested');
                            addRecommendation('Allow location access when prompted');
                        }
                    }).catch(() => {
                        updateDiagnosticResult('permission-status', 'warning',
                            'Cannot check permission status');
                    });
                } else {
                    updateDiagnosticResult('permission-status', 'warning', 'Permission API not supported');
                }
            }, 800);
        }

        function addRecommendation(message) {
            const container = document.getElementById('recommended-actions');
            if (container) {
                const item = document.createElement('div');
                item.className = 'flex items-center';
                item.innerHTML = `<i class="fas fa-arrow-right text-blue-600 mr-2"></i><span>${message}</span>`;
                container.appendChild(item);
            }
        }

        function showErrorDetails(message) {
            const errorDetails = document.getElementById('error-details');
            const errorMessage = document.getElementById('error-message');
            if (errorDetails && errorMessage) {
                errorMessage.textContent = message;
                errorDetails.classList.remove('hidden');
            }
        }

        function retryLocationAccess() {
            console.log('Retrying location access...');

            // First check if geolocation is supported
            if (!navigator.geolocation) {
                showSimpleNotification('Geolocation is not supported by this browser', 'error');
                updateLocationStatus('error', null, 'Geolocation not supported');
                return;
            }

            // Clear any existing watch
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }

            // Reset state
            userLocation = null;
            hasLocationPermission = false;

            // Show loading state
            updateLocationStatus('loading', null, 'Retrying location access...');

            // Try multiple approaches for getting location
            const options = {
                enableHighAccuracy: true, // High accuracy for attendance tracking
                timeout: 15000, // 15 seconds timeout for GPS
                maximumAge: 0 // Don't accept cached positions
            };

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    console.log('Location retry successful:', position);
                    userLocation = position;
                    hasLocationPermission = true;

                    // Update all UI elements
                    updateLocationStatus('success', position);
                    updateCurrentLocationDisplay(position);
                    updateGeofenceStatus(position);

                    // Cache the successful location
                    cacheLocation(position);

                    // Start watching for location changes
                    startOptimizedLocationWatch();

                    showSimpleNotification('Location access restored!', 'success');

                    // Close troubleshooting panel after a short delay
                    setTimeout(() => {
                        toggleLocationTroubleshooting();
                    }, 1500);
                },
                function(error) {
                    console.error('Location retry failed:', error);
                    hasLocationPermission = false;

                    // Use enhanced error handling
                    handleLocationError(error, 'retry');

                    let errorMsg = 'Location retry failed: ';
                    let recommendation = '';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg += 'Permission denied';
                            recommendation = 'Please enable location access in your browser settings';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg += 'Position unavailable';
                            recommendation = 'Make sure location services are enabled on your device';
                            break;
                        case error.TIMEOUT:
                            errorMsg += 'Request timed out';
                            recommendation = 'Try again or move to an area with better signal';
                            break;
                        default:
                            errorMsg += error.message || 'Unknown error';
                            recommendation = 'Check your connection and device settings';
                    }

                    updateLocationStatus('error', null, errorMsg);
                    showSimpleNotification(errorMsg + '. ' + recommendation, 'error');

                    // Add the recommendation to the panel
                    addRecommendation(recommendation);
                },
                options
            );
        }

        function testHighAccuracy() {
            console.log('Testing high accuracy location...');
            updateLocationStatus('loading', null, 'Testing high accuracy location...');

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                position => {
                    userLocation = position;
                    updateLocationStatus('success', position);
                    updateGeofenceStatus(position);
                    showSimpleNotification(
                        `High accuracy test successful! Accuracy: ±${Math.round(position.coords.accuracy)}m`,
                        'success');
                },
                error => {
                    let message = 'High accuracy test failed: ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message += 'Permission denied';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += 'Position unavailable';
                            break;
                        case error.TIMEOUT:
                            message += 'Request timed out';
                            break;
                        default:
                            message += error.message;
                    }
                    updateLocationStatus('error', null, message);
                    showSimpleNotification(message, 'error');
                },
                options
            );
        }

        function showSimpleNotification(message, type) {
            // Simple notification without complex DOM creation
            const notification = document.createElement('div');
            notification.className =
                `fixed top-4 right-4 p-3 rounded-lg text-white text-sm transition-all duration-300 max-w-sm`;
            notification.style.zIndex = '10000';

            switch (type) {
                case 'success':
                    notification.className += ' bg-green-500';
                    break;
                case 'error':
                    notification.className += ' bg-red-500';
                    break;
                case 'warning':
                    notification.className += ' bg-yellow-500';
                    break;
                default:
                    notification.className += ' bg-blue-500';
            }

            notification.innerHTML = `
                <div class="flex items-start">
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200 text-xs">✕</button>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Simple location test function for debugging
        function testBasicLocation() {
            console.log('Testing basic geolocation...');
            updateLocationStatus('loading', null, 'Testing basic location access...');

            if (!navigator.geolocation) {
                updateLocationStatus('error', null, 'Geolocation not supported');
                showSimpleNotification('Geolocation is not supported by this browser', 'error');
                return;
            }

            const options = {
                enableHighAccuracy: true, // High accuracy for attendance tracking
                timeout: 15000, // 15 seconds for GPS lock
                maximumAge: 0 // Get fresh location
            };

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    console.log('Basic location test successful:', position);
                    userLocation = position;
                    hasLocationPermission = true;

                    updateLocationStatus('success', position);
                    updateCurrentLocationDisplay(position);
                    updateGeofenceStatus(position);

                    showSimpleNotification(`Location obtained! Accuracy: ±${Math.round(position.coords.accuracy)}m`,
                        'success');
                },
                function(error) {
                    console.error('Basic location test failed:', error);
                    let msg = 'Location test failed: ';

                    switch (error.code) {
                        case 1:
                            msg += 'Permission denied';
                            break;
                        case 2:
                            msg += 'Position unavailable';
                            break;
                        case 3:
                            msg += 'Timeout';
                            break;
                        default:
                            msg += error.message;
                    }

                    updateLocationStatus('error', null, msg);
                    showSimpleNotification(msg, 'error');
                },
                options
            );
        }

        // Enhanced browser-specific location tips
        function getBrowserLocationHelp() {
            const userAgent = navigator.userAgent;
            let tips = [];

            if (userAgent.includes('Chrome')) {
                tips = [
                    'Click the location icon in the address bar',
                    'Select "Always allow" for this site',
                    'Check Chrome Settings > Privacy > Location'
                ];
            } else if (userAgent.includes('Firefox')) {
                tips = [
                    'Click the shield icon in the address bar',
                    'Select "Allow location access"',
                    'Check Firefox Preferences > Privacy & Security'
                ];
            } else if (userAgent.includes('Safari')) {
                tips = [
                    'Check Safari > Preferences > Websites > Location',
                    'Set this website to "Allow"',
                    'Restart Safari if needed'
                ];
            } else if (userAgent.includes('Edge')) {
                tips = [
                    'Click the location icon in the address bar',
                    'Select "Allow" when prompted',
                    'Check Edge Settings > Site Permissions'
                ];
            } else {
                tips = [
                    'Look for location icon in address bar',
                    'Allow location access when prompted',
                    'Check browser location settings'
                ];
            }

            return tips;
        }

        // Smart location initialization - only requests when needed
        function initializeSmartLocation() {
            console.log('Initializing smart location system...');

            // Check if we have a recent cached location
            const cached = getCachedLocation();
            if (cached && isCachedLocationValid(cached)) {
                console.log('Using valid cached location');
                userLocation = cached;
                hasLocationPermission = true;
                updateLocationStatus('success', cached, 'Using cached location');
                updateCurrentLocationDisplay(cached);
                updateGeofenceStatus(cached);

                // Get fresh location in background
                setTimeout(() => {
                    if (navigator.geolocation) {
                        getOptimizedLocation().then(fresh => {
                            console.log('Background location update successful');
                            userLocation = fresh;
                            updateLocationStatus('success', fresh);
                            updateCurrentLocationDisplay(fresh);
                            updateGeofenceStatus(fresh);
                        }).catch(error => {
                            console.log('Background update failed, keeping cached location');
                        });
                    }
                }, 2000);
                return;
            }

            // No valid cache - check permission and get location
            checkLocationPermission().then((hasPermission) => {
                if (hasPermission && !userLocation) {
                    // Permission granted, get location immediately
                    return startLocationTracking();
                }
            }).catch(error => {
                console.warn('Location initialization failed:', error.message);

                // Try fallback location methods
                tryFallbackLocation().then(fallbackLocation => {
                    if (fallbackLocation) {
                        userLocation = fallbackLocation;
                        updateLocationStatus('warning', fallbackLocation, 'Using approximate location');
                        updateCurrentLocationDisplay(fallbackLocation);
                        updateGeofenceStatus(fallbackLocation);
                        showSimpleNotification('Using approximate location. GPS accuracy may be limited.',
                            'warning');
                    } else {
                        updateLocationStatus('error', null, error.message);
                        showLocationAlternatives();
                    }
                }).catch(fallbackError => {
                    console.error('All location methods failed:', fallbackError);
                    updateLocationStatus('error', null, 'Unable to determine location');
                    showLocationAlternatives();
                });
            });
        }

        // Fallback location methods
        function tryFallbackLocation() {
            return new Promise((resolve, reject) => {
                console.log('Trying fallback location methods...');

                // Method 1: Check for test location (for office testing)
                const testLocation = getTestLocation();
                if (testLocation) {
                    console.log('Using test location');
                    resolve(createLocationObject(testLocation.lat, testLocation.lng, 10, 'test'));
                    return;
                }

                // Method 2: Try IP-based geolocation
                tryIPGeolocation().then(location => {
                    if (location) {
                        console.log('IP geolocation successful');
                        resolve(location);
                    } else {
                        // Method 3: Use workplace default location
                        const workplace = getStoredWorkplace();
                        if (workplace && workplace.lat && workplace.lng) {
                            console.log('Using workplace default location');
                            resolve(createLocationObject(workplace.lat, workplace.lng, 500, 'workplace'));
                        } else {
                            // Method 4: Use system default
                            console.log('Using system default location');
                            resolve(createLocationObject(14.5995, 120.9842, 1000, 'default'));
                        }
                    }
                }).catch(error => {
                    console.warn('IP geolocation failed:', error);
                    // Fallback to workplace or default
                    const workplace = getStoredWorkplace();
                    if (workplace && workplace.lat && workplace.lng) {
                        resolve(createLocationObject(workplace.lat, workplace.lng, 500, 'workplace'));
                    } else {
                        resolve(createLocationObject(14.5995, 120.9842, 1000, 'default'));
                    }
                });
            });
        }

        function getTestLocation() {
            try {
                const testLoc = localStorage.getItem('testLocation');
                if (testLoc) {
                    const parsed = JSON.parse(testLoc);
                    // Check if test location is not too old (24 hours)
                    if (Date.now() - parsed.timestamp < 24 * 60 * 60 * 1000) {
                        return parsed;
                    } else {
                        localStorage.removeItem('testLocation');
                    }
                }
            } catch (e) {
                console.error('Error reading test location:', e);
            }
            return null;
        }

        function tryIPGeolocation() {
            return new Promise((resolve, reject) => {
                // Try multiple IP geolocation services
                const services = [
                    'https://ipapi.co/json/',
                    'http://ip-api.com/json/',
                    'https://ipinfo.io/json'
                ];

                let serviceIndex = 0;

                function tryNextService() {
                    if (serviceIndex >= services.length) {
                        reject(new Error('All IP geolocation services failed'));
                        return;
                    }

                    const service = services[serviceIndex];
                    serviceIndex++;

                    fetch(service, {
                            method: 'GET',
                            timeout: 5000
                        })
                        .then(response => response.json())
                        .then(data => {
                            let lat, lng;

                            // Handle different service response formats
                            if (service.includes('ipapi.co')) {
                                lat = data.latitude;
                                lng = data.longitude;
                            } else if (service.includes('ip-api.com')) {
                                lat = data.lat;
                                lng = data.lon;
                            } else if (service.includes('ipinfo.io')) {
                                const coords = data.loc ? data.loc.split(',') : null;
                                if (coords && coords.length === 2) {
                                    lat = parseFloat(coords[0]);
                                    lng = parseFloat(coords[1]);
                                }
                            }

                            if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                                // IP geolocation typically has low accuracy (city-level)
                                resolve(createLocationObject(lat, lng, 5000, 'ip'));
                            } else {
                                tryNextService();
                            }
                        })
                        .catch(error => {
                            console.warn(`IP geolocation service ${service} failed:`, error);
                            tryNextService();
                        });
                }

                tryNextService();
            });
        }

        function createLocationObject(lat, lng, accuracy = 100, source = 'unknown') {
            return {
                coords: {
                    latitude: lat,
                    longitude: lng,
                    accuracy: accuracy,
                    altitude: null,
                    altitudeAccuracy: null,
                    heading: null,
                    speed: null
                },
                timestamp: Date.now(),
                source: source
            };
        }

        function getStoredWorkplace() {
            try {
                const stored = localStorage.getItem(STORAGE_KEYS.workplace);
                return stored ? JSON.parse(stored) : null;
            } catch (e) {
                console.error('Error reading stored workplace:', e);
                return null;
            }
        }

        function showLocationAlternatives() {
            // Show options for manual location entry or alternative methods
            const alertDiv = document.createElement('div');
            alertDiv.className =
                'fixed top-4 right-4 bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow-lg z-50 max-w-sm';
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-800 font-medium">Location Access Needed</p>
                        <p class="text-xs text-yellow-700 mt-1">GPS is required for attendance tracking</p>
                        <div class="mt-2">
                            <button onclick="showManualLocationEntry(); this.parentElement.parentElement.parentElement.parentElement.remove();" 
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs mr-2">
                                Manual Entry
                            </button>
                            <button onclick="retryLocationAccess(); this.parentElement.parentElement.parentElement.parentElement.remove();" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                Retry GPS
                            </button>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            class="ml-auto text-yellow-500 hover:text-yellow-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(alertDiv);

            // Auto remove after 10 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 10000);
        }

        // Function to open manual location entry (triggered by button)
        function openManualLocationEntry() {
            showManualLocationEntry();
        }

        function showManualLocationEntry() {
            // Show code verification modal first
            const verifyModal = document.createElement('div');
            verifyModal.className = 'fixed inset-0 flex items-center justify-center px-4 modal-blur';
            verifyModal.style.zIndex = '9999';
            verifyModal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 rounded-full p-3 mr-3">
                            <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Admin Verification Required</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Manual location entry is restricted to administrators only. Please enter the admin access code to continue.
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Access Code</label>
                        <input type="password" id="admin-access-code" 
                               placeholder="Enter admin code"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p id="code-error" class="text-xs text-red-600 mt-1 hidden">Invalid access code. Please try again.</p>
                    </div>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                        <div class="flex">
                            <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                            <p class="text-xs text-yellow-700">
                                If you don't have the admin code, please contact your system administrator or use the GPS retry option instead.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="verifyAdminCode(this.closest('.fixed'))" 
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-medium">
                            <i class="fas fa-check mr-2"></i>Verify Code
                        </button>
                        <button onclick="document.body.removeChild(this.closest('.fixed'))" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(verifyModal);

            // Focus on the code input
            setTimeout(() => {
                document.getElementById('admin-access-code')?.focus();
            }, 100);
        }

        function verifyAdminCode(modal) {
            const codeInput = document.getElementById('admin-access-code');
            const errorMsg = document.getElementById('code-error');
            const enteredCode = codeInput.value.trim();

            // Fetch the current code from backend
            fetch('/api/manual-entry-code')
                .then(response => response.json())
                .then(data => {
                    const correctCode = data.code || 'DEPED2025';

                    if (enteredCode === correctCode) {
                        // Code is correct, close verification modal and show location entry
                        document.body.removeChild(modal);
                        showLocationEntryForm();
                    } else {
                        // Show error message
                        errorMsg.classList.remove('hidden');
                        codeInput.value = '';
                        codeInput.focus();
                        codeInput.classList.add('border-red-500');

                        // Shake animation
                        codeInput.style.animation = 'shake 0.5s';
                        setTimeout(() => {
                            codeInput.style.animation = '';
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error('Error fetching manual entry code:', error);
                    // Fallback to default code
                    const defaultCode = 'DEPED2025';
                    if (enteredCode === defaultCode) {
                        document.body.removeChild(modal);
                        showLocationEntryForm();
                    } else {
                        errorMsg.classList.remove('hidden');
                        codeInput.value = '';
                        codeInput.focus();
                        codeInput.classList.add('border-red-500');
                    }
                });
        }

        function showLocationEntryForm() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 flex items-center justify-center px-4 modal-blur';
            modal.style.zIndex = '9999';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md w-full">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 rounded-full p-2 mr-3">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Manual Location Entry</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Select your workplace location. This will use your workplace's coordinates for check-in.
                    </p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Your Workplace</label>
                            <select id="manual-workplace-select" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Loading workplaces...</option>
                            </select>
                        </div>
                        <div id="selected-workplace-info" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="text-xs text-blue-900 space-y-1">
                                <div><strong>Address:</strong> <span id="info-address">-</span></div>
                                <div><strong>Coordinates:</strong> <span id="info-coords">-</span></div>
                                <div><strong>Radius:</strong> <span id="info-radius">-</span></div>
                            </div>
                        </div>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-0.5"></i>
                                <p class="text-xs text-yellow-700">
                                    This will set your location to your selected workplace. Only use this if GPS is not working properly.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button onclick="setManualWorkplaceLocation(); document.body.removeChild(this.closest('.fixed'))" 
                                id="set-workplace-btn"
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed"
                                disabled>
                            <i class="fas fa-check mr-2"></i>Use This Location
                        </button>
                        <button onclick="document.body.removeChild(this.closest('.fixed'))" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Load user's workplaces
            loadWorkplacesForManualEntry();
        }

        function loadWorkplacesForManualEntry() {
            fetch('/api/user/workplaces')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const select = document.getElementById('manual-workplace-select');
                    const setButton = document.getElementById('set-workplace-btn');

                    if (data.workplaces && data.workplaces.length > 0) {
                        select.innerHTML = '<option value="">-- Select a workplace --</option>';
                        data.workplaces.forEach(workplace => {
                            const option = document.createElement('option');
                            option.value = workplace.id;
                            option.textContent = workplace.name;
                            option.dataset.lat = workplace.latitude;
                            option.dataset.lng = workplace.longitude;
                            option.dataset.address = workplace.address;
                            option.dataset.radius = workplace.radius;
                            select.appendChild(option);
                        });

                        // Enable button when workplace is selected
                        select.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const infoDiv = document.getElementById('selected-workplace-info');

                            if (selectedOption.value) {
                                setButton.disabled = false;
                                infoDiv.classList.remove('hidden');
                                document.getElementById('info-address').textContent = selectedOption.dataset
                                    .address;
                                document.getElementById('info-coords').textContent =
                                    `${parseFloat(selectedOption.dataset.lat).toFixed(6)}, ${parseFloat(selectedOption.dataset.lng).toFixed(6)}`;
                                document.getElementById('info-radius').textContent =
                                    `${selectedOption.dataset.radius} meters`;
                            } else {
                                setButton.disabled = true;
                                infoDiv.classList.add('hidden');
                            }
                        });
                    } else {
                        select.innerHTML = '<option value="">No workplaces assigned - Contact admin</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading workplaces:', error);
                    const select = document.getElementById('manual-workplace-select');
                    select.innerHTML = '<option value="">Error loading workplaces - Please try again</option>';
                    showSimpleNotification('Failed to load workplaces. Please refresh and try again.', 'error');
                });
        }

        function setManualWorkplaceLocation() {
            const select = document.getElementById('manual-workplace-select');
            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption.value) {
                alert('Please select a workplace');
                return;
            }

            const lat = parseFloat(selectedOption.dataset.lat);
            const lng = parseFloat(selectedOption.dataset.lng);
            const workplaceName = selectedOption.textContent;

            // Create a manual location object
            const manualLocation = createLocationObject(lat, lng, 50, 'manual');

            // Store as test location
            localStorage.setItem('testLocation', JSON.stringify({
                lat: lat,
                lng: lng,
                accuracy: 50,
                timestamp: Date.now(),
                workplace: workplaceName
            }));

            // Set as current location
            userLocation = manualLocation;
            hasLocationPermission = true;

            // Update UI
            updateLocationStatus('warning', manualLocation, `Manual location set: ${workplaceName}`);
            updateCurrentLocationDisplay(manualLocation);
            updateGeofenceStatus(manualLocation);

            showSimpleNotification(`Location set to ${workplaceName}`, 'success');
        }

        // Enhanced Error Messaging System
        function showContextualError(errorType, errorCode, browserAgent) {
            const errorMessages = {
                PERMISSION_DENIED: {
                    title: 'Location Access Denied',
                    message: 'Please enable location access to use attendance tracking',
                    solutions: [
                        'Click the location icon in your browser address bar',
                        'Select "Allow" or "Always Allow" for location access',
                        'Refresh the page after granting permission',
                        'Check your browser settings if the icon is not visible'
                    ],
                    icon: 'fas fa-shield-alt',
                    color: 'red'
                },
                POSITION_UNAVAILABLE: {
                    title: 'Location Currently Unavailable',
                    message: 'Your device cannot determine your location right now',
                    solutions: [
                        'Make sure location services are enabled on your device',
                        'Try moving to an area with better signal reception',
                        'Check if GPS is working in other apps',
                        'Restart your browser and try again'
                    ],
                    icon: 'fas fa-satellite-dish',
                    color: 'orange'
                },
                TIMEOUT: {
                    title: 'Location Request Timed Out',
                    message: 'It took too long to get your location',
                    solutions: [
                        'Check your internet connection',
                        'Make sure GPS is enabled on your device',
                        'Try again in a few moments',
                        'Move to an area with better connectivity'
                    ],
                    icon: 'fas fa-clock',
                    color: 'yellow'
                },
                GEOLOCATION_NOT_SUPPORTED: {
                    title: 'Geolocation Not Supported',
                    message: 'Your browser doesn\'t support location services',
                    solutions: [
                        'Update your browser to the latest version',
                        'Use a modern browser (Chrome, Firefox, Edge, Safari)',
                        'Contact IT support if using a managed device',
                        'Use manual location entry as an alternative'
                    ],
                    icon: 'fas fa-exclamation-triangle',
                    color: 'red'
                }
            };

            const errorInfo = errorMessages[errorType] || errorMessages.GEOLOCATION_NOT_SUPPORTED;

            // Create enhanced error modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 flex items-center justify-center px-4 modal-blur';
            modal.style.zIndex = '9999';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-${errorInfo.color}-100 flex items-center justify-center mr-4">
                            <i class="${errorInfo.icon} text-${errorInfo.color}-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">${errorInfo.title}</h3>
                            <p class="text-sm text-gray-600">${errorInfo.message}</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900 mb-2">How to fix this:</h4>
                        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                            ${errorInfo.solutions.map(solution => `<li>${solution}</li>`).join('')}
                        </ol>
                    </div>
                    
                    ${getBrowserSpecificHelp(browserAgent)}
                    
                    <div class="bg-blue-50 rounded-lg p-3 mb-4">
                        <h5 class="font-medium text-blue-900 mb-1 flex items-center">
                            <i class="fas fa-lightbulb text-blue-600 mr-2"></i>Alternative Options
                        </h5>
                        <div class="text-sm text-blue-800 space-y-1">
                            <div>• Use manual location entry for testing</div>
                            <div>• Ask your administrator to enable testing mode</div>
                            <div>• Try accessing from a different device or browser</div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="retryLocationAccess(); document.body.removeChild(this.closest('.fixed'))" 
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-medium">
                            <i class="fas fa-redo mr-2"></i>Try Again
                        </button>
                        <button onclick="showManualLocationEntry(); document.body.removeChild(this.closest('.fixed'))" 
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded font-medium">
                            <i class="fas fa-map-pin mr-2"></i>Manual Entry
                        </button>
                        <button onclick="document.body.removeChild(this.closest('.fixed'))" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                            Close
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
        }

        function getBrowserSpecificHelp(userAgent = navigator.userAgent) {
            let browserName = 'your browser';
            let specificSteps = [];

            if (userAgent.includes('Chrome') && !userAgent.includes('Edge')) {
                browserName = 'Chrome';
                specificSteps = [
                    'Look for the location icon (🗺️) in the address bar',
                    'Click it and select "Always allow on this site"',
                    'If no icon: Go to Settings → Privacy and Security → Site Settings → Location',
                    'Add this site to "Allowed to access your location"'
                ];
            } else if (userAgent.includes('Edge')) {
                browserName = 'Microsoft Edge';
                specificSteps = [
                    'Click the location icon in the address bar',
                    'Choose "Allow for this site"',
                    'Or go to Settings → Cookies and site permissions → Location',
                    'Add this website to the allowed list'
                ];
            } else if (userAgent.includes('Firefox')) {
                browserName = 'Firefox';
                specificSteps = [
                    'Click the shield icon or "i" icon in the address bar',
                    'Select "Allow Location Access"',
                    'Or go to Preferences → Privacy & Security → Permissions',
                    'Click Settings next to Location and add this site'
                ];
            } else if (userAgent.includes('Safari')) {
                browserName = 'Safari';
                specificSteps = [
                    'Go to Safari → Preferences → Websites → Location',
                    'Find this website in the list',
                    'Change the setting to "Allow"',
                    'Refresh the page'
                ];
            }

            if (specificSteps.length > 0) {
                return `
                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <h5 class="font-medium text-gray-900 mb-1 flex items-center">
                            <i class="fab fa-${browserName.toLowerCase().replace(' ', '-')} text-gray-600 mr-2"></i>
                            For ${browserName} Users:
                        </h5>
                        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                            ${specificSteps.map(step => `<li>${step}</li>`).join('')}
                        </ol>
                    </div>
                `;
            }

            return '';
        }

        // Enhanced notification system
        function showEnhancedNotification(message, type = 'info', duration = 5000, actionButton = null) {
            const colors = {
                success: 'bg-green-500 border-green-600',
                error: 'bg-red-500 border-red-600',
                warning: 'bg-yellow-500 border-yellow-600',
                info: 'bg-blue-500 border-blue-600'
            };

            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const notification = document.createElement('div');
            notification.className =
                `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg max-w-sm border-l-4`;
            notification.style.zIndex = '10000';

            let actionHtml = '';
            if (actionButton) {
                actionHtml = `
                    <div class="mt-3">
                        <button onclick="${actionButton.action}" 
                                class="px-3 py-1 bg-white bg-opacity-20 hover:bg-opacity-30 text-black rounded text-sm">
                            ${actionButton.text}
                        </button>
                    </div>
                `;
            }

            notification.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${icons[type]} mr-3"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">${message}</p>
                        ${actionHtml}
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            class="ml-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, duration);
        }

        // Update existing error handlers to use contextual errors
        function handleLocationError(error, context = 'general') {
            let errorType;
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorType = 'PERMISSION_DENIED';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorType = 'POSITION_UNAVAILABLE';
                    break;
                case error.TIMEOUT:
                    errorType = 'TIMEOUT';
                    break;
                default:
                    errorType = 'GEOLOCATION_NOT_SUPPORTED';
            }

            // Show contextual error with solutions
            showContextualError(errorType, error.code, navigator.userAgent);

            // Also update the status display
            updateLocationStatus('error', null, `${errorType.replace('_', ' ').toLowerCase()}`);
        }

        // Testing Mode Functions (Admin Only)
        function toggleTestingMode() {
            const toggle = document.getElementById('testing-mode-toggle');
            const content = document.getElementById('testing-mode-content');
            const status = document.getElementById('testing-mode-status');

            if (toggle.checked) {
                content.classList.remove('hidden');
                localStorage.setItem('testingModeEnabled', 'true');
                status.textContent = 'Testing mode enabled - Ready to set test location';
                showSimpleNotification('Testing mode enabled. You can now set simulated locations.', 'info');
            } else {
                content.classList.add('hidden');
                localStorage.removeItem('testingModeEnabled');
                localStorage.removeItem('testLocation');
                status.textContent = 'Testing mode disabled';

                // Reset to actual GPS location
                clearTestLocation();
                showSimpleNotification('Testing mode disabled. Returning to GPS location.', 'info');
            }
        }

        function setPresetLocation(lat, lng, name) {
            const testLocation = {
                lat: lat,
                lng: lng,
                accuracy: 10,
                timestamp: Date.now(),
                name: name
            };

            localStorage.setItem('testLocation', JSON.stringify(testLocation));

            // Apply immediately
            const locationObj = createLocationObject(lat, lng, 10, 'preset');
            userLocation = locationObj;
            hasLocationPermission = true;

            updateLocationStatus('warning', locationObj, `Test location: ${name}`);
            updateCurrentLocationDisplay(locationObj);
            updateGeofenceStatus(locationObj);

            document.getElementById('testing-mode-status').textContent =
                `Active: ${name} (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            showSimpleNotification(`Test location set to ${name}`, 'success');
        }

        function setCustomTestLocation() {
            const lat = parseFloat(document.getElementById('admin-test-lat').value);
            const lng = parseFloat(document.getElementById('admin-test-lng').value);

            if (isNaN(lat) || isNaN(lng)) {
                alert('Please enter valid latitude and longitude values');
                return;
            }

            const testLocation = {
                lat: lat,
                lng: lng,
                accuracy: 10,
                timestamp: Date.now(),
                name: 'Custom Test Location'
            };

            localStorage.setItem('testLocation', JSON.stringify(testLocation));

            // Apply immediately
            const locationObj = createLocationObject(lat, lng, 10, 'custom');
            userLocation = locationObj;
            hasLocationPermission = true;

            updateLocationStatus('warning', locationObj, 'Custom test location');
            updateCurrentLocationDisplay(locationObj);
            updateGeofenceStatus(locationObj);

            document.getElementById('testing-mode-status').textContent =
                `Active: Custom (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            showSimpleNotification(`Custom test location set: ${lat.toFixed(4)}, ${lng.toFixed(4)}`, 'success');
        }

        function clearTestLocation() {
            localStorage.removeItem('testLocation');
            document.getElementById('testing-mode-status').textContent = 'Test location cleared - Using GPS';

            // Reset location inputs
            document.getElementById('admin-test-lat').value = '';
            document.getElementById('admin-test-lng').value = '';

            // Try to get real GPS location
            if (navigator.geolocation) {
                updateLocationStatus('loading', null, 'Returning to GPS location...');
                startLocationTracking().then(() => {
                    showSimpleNotification('Returned to GPS location', 'success');
                }).catch(error => {
                    console.warn('Could not get GPS location:', error);
                    updateLocationStatus('error', null, 'GPS location unavailable');
                    showSimpleNotification('GPS location not available. You may need to enable location access.',
                        'warning');
                });
            } else {
                updateLocationStatus('error', null, 'GPS not supported');
            }
        }

        // Initialize testing mode state on page load
        function initializeTestingMode() {
            const isTestingEnabled = localStorage.getItem('testingModeEnabled') === 'true';
            const toggle = document.getElementById('testing-mode-toggle');
            const content = document.getElementById('testing-mode-content');

            if (toggle && isTestingEnabled) {
                toggle.checked = true;
                content.classList.remove('hidden');

                // Check if there's an active test location
                const testLoc = getTestLocation();
                if (testLoc) {
                    const statusEl = document.getElementById('testing-mode-status');
                    if (statusEl) {
                        statusEl.textContent =
                            `Active: ${testLoc.name || 'Test Location'} (${testLoc.lat.toFixed(4)}, ${testLoc.lng.toFixed(4)})`;
                    }
                }
            }
        }

        // Proactive location status monitoring
        function startLocationHealthMonitoring() {
            // Check location health every 30 seconds
            setInterval(() => {
                if (!navigator.geolocation) return;

                // Check if permission has been revoked
                if (navigator.permissions) {
                    navigator.permissions.query({
                        name: 'geolocation'
                    }).then(permission => {
                        if (permission.state === 'denied' && hasLocationPermission) {
                            hasLocationPermission = false;
                            showEnhancedNotification(
                                'Location permission was revoked. Attendance tracking may not work properly.',
                                'warning',
                                10000, {
                                    text: 'Fix Now',
                                    action: 'showContextualError("PERMISSION_DENIED", 1, navigator.userAgent)'
                                }
                            );
                        }
                    });
                }

                // Warn if location accuracy is very poor
                if (userLocation && userLocation.coords.accuracy > 1000) {
                    showEnhancedNotification(
                        'Location accuracy is poor (±' + Math.round(userLocation.coords.accuracy) +
                        'm). This may affect attendance tracking.',
                        'warning',
                        8000, {
                            text: 'Improve',
                            action: 'retryLocationAccess()'
                        }
                    );
                }

                // Check if we haven't had a location update in a while
                if (userLocation && Date.now() - userLocation.timestamp > 10 * 60 * 1000) { // 10 minutes
                    showEnhancedNotification(
                        'Location data is outdated. Getting fresh location...',
                        'info',
                        5000
                    );

                    // Try to refresh location quietly
                    getQuickLocation().then(position => {
                        userLocation = position;
                        updateLocationStatus('success', position);
                        updateCurrentLocationDisplay(position);
                    }).catch(() => {
                        // Ignore errors from background refresh
                    });
                }
            }, 30000); // 30 seconds
        }

        // Default and stored work locations
        let workLocations = {
            mainOffice: {
                lat: 14.2785,
                lng: 120.8677,
                name: 'DepEd Cavite Main Office',
                address: 'Luciano, Trece Martires, Cavite',
                radius: 100
            }
        };

        // Load saved workplace data
        function loadWorkplaceData() {
            const currentUserId = getCurrentUserId();
            if (!currentUserId) {
                console.log('No valid user ID, skipping localStorage workplace load');
                return false;
            }

            const userSpecificKey = `${STORAGE_KEYS.workplace}_user_${currentUserId}`;
            const saved = localStorage.getItem(userSpecificKey);
            if (saved) {
                try {
                    const workplace = JSON.parse(saved);
                    workLocations.mainOffice = workplace;
                    console.log('Loaded workplace data for user:', currentUserId);
                    return true;
                } catch (e) {
                    console.error('Failed to load workplace data:', e);
                    localStorage.removeItem(userSpecificKey);
                }
            }
            return false;
        }

        // Save workplace data
        function saveWorkplaceData(workplace) {
            const currentUserId = getCurrentUserId();
            if (!currentUserId) {
                console.log('No valid user ID, skipping workplace save to localStorage');
                return false;
            }

            try {
                const userSpecificKey = `${STORAGE_KEYS.workplace}_user_${currentUserId}`;
                localStorage.setItem(userSpecificKey, JSON.stringify(workplace));
                workLocations.mainOffice = workplace;
                console.log('Saved workplace data for user:', currentUserId);
                return true;
            } catch (e) {
                console.error('Failed to save workplace data:', e);
                return false;
            }
        }

        // Location permission and tracking
        function checkLocationPermission() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported by your browser'));
                    return;
                }

                // Check if we have a cached location first
                const cachedLocation = getCachedLocation();
                if (cachedLocation && isCachedLocationValid(cachedLocation)) {
                    console.log('Using cached location while getting fresh location...');
                    userLocation = cachedLocation;
                    hasLocationPermission = true;
                    resolve(true);

                    // Still get fresh location in background for accuracy
                    getOptimizedLocation().catch(error => {
                        console.warn('Background location update failed:', error);
                    });
                    return;
                }

                // Try quick location check with relaxed accuracy first
                getQuickLocation().then(position => {
                    hasLocationPermission = true;
                    resolve(true);
                }).catch(error => {
                    hasLocationPermission = false;
                    let errorMsg = 'Unable to get location';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg =
                                'Location access denied. Please allow location access for attendance tracking.';
                            showLocationPermissionRequest();
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg =
                                'Location information unavailable. Please check your device settings.';
                            showLocationPermissionRequest();
                            break;
                        case error.TIMEOUT:
                            errorMsg = 'Location request timed out. Trying with cached location...';
                            // Try with any cached location as fallback
                            const oldCached = getCachedLocation();
                            if (oldCached) {
                                console.log('Using older cached location as fallback');
                                userLocation = oldCached;
                                hasLocationPermission = true;
                                resolve(true);
                                return;
                            }
                            showLocationPermissionRequest();
                            break;
                    }

                    reject(new Error(errorMsg));
                });
            });
        }

        // Fast location acquisition with progressive enhancement
        function getQuickLocation() {
            return new Promise((resolve, reject) => {
                // First try: Quick location with lower accuracy
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        console.log('Quick location obtained:', position.coords.accuracy + 'm accuracy');
                        userLocation = position;
                        cacheLocation(position);
                        resolve(position);

                        // Background improvement: Get more accurate location
                        setTimeout(() => {
                            getOptimizedLocation().then(accuratePosition => {
                                console.log('Improved location accuracy:', accuratePosition
                                    .coords.accuracy + 'm');
                                userLocation = accuratePosition;
                                cacheLocation(accuratePosition);

                                // Update maps if they exist
                                if (checkinMap && mapInitializationState.checkinMap) {
                                    refreshCheckinMapData();
                                }
                            }).catch(error => {
                                console.log(
                                    'Accuracy improvement failed, keeping quick location');
                            });
                        }, 1000);
                    },
                    function(error) {
                        reject(error);
                    }, {
                        enableHighAccuracy: true, // High accuracy for attendance tracking
                        timeout: 10000, // 10-second timeout for GPS lock
                        maximumAge: 60000 // Accept 1-minute old location only
                    }
                );
            });
        }

        // More accurate location (used as background improvement)
        function getOptimizedLocation() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLocation = position;
                        cacheLocation(position);
                        resolve(position);
                    },
                    function(error) {
                        reject(error);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000, // Longer timeout for accuracy
                        maximumAge: 60000 // Accept 1-minute old accurate location
                    }
                );
            });
        }

        function showLocationPermissionRequest() {
            // Show enhanced permission request with clear workplace context
            const locationRequest = document.getElementById('location-permission-request');
            if (locationRequest) {
                locationRequest.classList.remove('hidden');
                updateStepStatus('step1-status', 'pending', 'Needs Permission');

                // Add workplace context to the permission request
                showLocationPermissionModal();
            }
        }

        function showLocationPermissionModal() {
            // Create a modal with clear explanation
            const modal = document.createElement('div');
            modal.id = 'location-permission-modal';
            modal.className = 'fixed inset-0 flex items-center justify-center modal-blur';
            modal.style.zIndex = '9999';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl p-8 m-4 max-w-md w-full">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-map-marker-alt text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Location Access Required</h3>
                        <p class="text-gray-600 text-sm">This attendance system needs your location to:</p>
                    </div>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-700">Verify you're at your workplace when checking in</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-shield-alt text-green-500 mr-3"></i>
                            <span class="text-gray-700">Ensure accurate attendance tracking</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-building text-green-500 mr-3"></i>
                            <span class="text-gray-700">Show your distance from workplace</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-lock text-green-500 mr-3"></i>
                            <span class="text-gray-700">Location is used only for work verification</span>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                            <div class="text-xs text-blue-800">
                                <p class="font-medium mb-1">Privacy Notice:</p>
                                <p>Your location is only accessed during work hours and is used solely for attendance verification. No tracking occurs outside of work activities.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button onclick="requestLocationPermission()" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            <i class="fas fa-location-arrow mr-2"></i>Allow Location
                        </button>
                        <button onclick="closeLocationModal()" class="px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            Later
                        </button>
                    </div>
                    
                    <p class="text-xs text-gray-500 text-center mt-4">
                        You can change this permission anytime in your browser settings
                    </p>
                </div>
            `;

            document.body.appendChild(modal);
        }

        function closeLocationModal() {
            const modal = document.getElementById('location-permission-modal');
            if (modal) {
                modal.remove();
            }
        }

        function requestLocationPermission() {
            closeLocationModal();
            startLocationTracking();
        }

        function hideLocationPermissionRequest() {
            document.getElementById('location-permission-request').classList.add('hidden');
            updateStepStatus('step1-status', 'success', 'Enabled');
        }

        function updateStepStatus(elementId, status, text) {
            const element = document.getElementById(elementId);
            const colors = {
                pending: 'bg-gray-100 text-gray-600',
                success: 'bg-green-100 text-green-700',
                error: 'bg-red-100 text-red-700'
            };
            element.className = `px-3 py-1 rounded-full text-xs ${colors[status] || colors.pending}`;
            element.textContent = text;
        }

        function startLocationTracking() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    updateLocationStatus('error', null, 'Geolocation not supported by your browser');
                    reject(new Error('Geolocation not supported'));
                    return;
                }

                console.log('Starting optimized location tracking...');
                updateLocationStatus('loading', null, 'Getting your location...');

                // Use the optimized quick location method
                getQuickLocation().then(position => {
                    userLocation = position;
                    hasLocationPermission = true;
                    hideLocationPermissionRequest();

                    console.log('Location tracking started successfully', position);

                    // Update all location-related UI elements
                    updateLocationStatus('success', position);

                    // Small delay to ensure DOM elements are ready
                    setTimeout(() => {
                        updateCurrentLocationDisplay(position);
                        updateGeofenceStatus(position);

                        // Update maps if initialized
                        if (checkinMap) {
                            updateUserLocationOnMaps(position);
                        }

                        // Refresh any section-specific data
                        const currentSection = document.querySelector(
                            '.section-content:not(.hidden)');
                        if (currentSection && currentSection.id === 'gps-checkin-section') {
                            // Refresh check-in section data
                            fetchCurrentStatus();
                        }
                    }, 100);

                    // Start optimized watching for location changes
                    startOptimizedLocationWatch();

                    resolve(position);
                }).catch(error => {
                    console.error('Location tracking failed:', error);
                    hasLocationPermission = false;

                    // Use enhanced error handling for better user experience
                    handleLocationError(error, 'tracking');

                    let errorMsg = 'Unable to get location';
                    let showPermissionRequest = true;

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg =
                                'Location access was denied. Please allow location access for attendance tracking.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg =
                                'Location is currently unavailable. Please check your device settings.';
                            break;
                        case error.TIMEOUT:
                            errorMsg =
                                'Location request timed out. Please try again or check your connection.';
                            break;
                        default:
                            errorMsg = 'Location error: ' + error.message;
                    }

                    updateLocationStatus('error', null, errorMsg);

                    reject(new Error(errorMsg));
                });
            });
        }

        function startOptimizedLocationWatch() {
            // Clear existing watch if any
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }

            // Start watching with optimized settings
            watchId = navigator.geolocation.watchPosition(
                function(pos) {
                    // Update location and cache it
                    userLocation = pos;
                    cacheLocation(pos);

                    // Update UI elements
                    updateUserLocationOnMaps(pos);
                    updateGeofenceStatus(pos);
                    updateCurrentLocationDisplay(pos);

                    // Refresh map data if visible
                    const gpsSection = document.getElementById('gps-checkin-section');
                    if (gpsSection && !gpsSection.classList.contains('hidden') && checkinMap) {
                        refreshCheckinMapData();
                    }

                    console.log('Location updated:', pos.coords.accuracy + 'm accuracy');
                },
                function(error) {
                    console.warn('Location watch error:', error.message);

                    // Don't show errors for watch failures unless it's permission denied
                    if (error.code === error.PERMISSION_DENIED) {
                        updateLocationStatus('error', null, 'Location permission was revoked');
                        hasLocationPermission = false;
                        showLocationPermissionRequest();
                    }
                    // For other errors, keep using cached location and try again later
                }, {
                    enableHighAccuracy: true, // High accuracy for attendance validation
                    timeout: 20000, // 20-second timeout for watch
                    maximumAge: 60000 // Accept 1-minute old locations only
                }
            );

            console.log('Optimized location watching started');
        }

        function updateLocationStatus(status, position, message = null) {
            const badge = document.getElementById('location-badge');
            const location = document.getElementById('current-location');
            const sidebarStatus = document.getElementById('location-status');

            if (status === 'loading') {
                if (badge) {
                    badge.className = 'px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium animate-pulse';
                    badge.textContent = 'Getting Location...';
                }
                if (location) {
                    location.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>' +
                        (message || 'Requesting your location...');
                }
                if (sidebarStatus) {
                    sidebarStatus.textContent = 'Getting Location...';
                }
            } else if (status === 'success' && position) {
                const accuracy = Math.round(position.coords.accuracy);
                const accuracyColor = accuracy <= 20 ? 'text-green-600' : accuracy <= 100 ? 'text-yellow-600' :
                    'text-orange-600';
                const accuracyIcon = accuracy <= 20 ? 'fa-check-circle' : accuracy <= 100 ? 'fa-exclamation-circle' :
                    'fa-question-circle';

                if (badge) {
                    badge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
                    badge.textContent = 'Location Active';
                }
                if (location) {
                    location.innerHTML = `<i class="fas fa-map-marker-alt text-green-600 mr-2"></i>` +
                        `<span class="font-medium">Location:</span> ` +
                        `${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)} ` +
                        `<span class="text-xs ${accuracyColor}">(<i class="fas ${accuracyIcon}"></i> ±${accuracy}m)</span>`;
                }
                if (sidebarStatus) {
                    sidebarStatus.textContent = 'Location Active';
                }

                // Update current location display elements
                updateCurrentLocationDisplay(position);

                // Update geofence status and UI
                updateGeofenceStatus(position);

                // Update maps if they're initialized
                if (checkinMap) {
                    updateUserLocationOnMaps(position);
                }

                // Cache the successful location
                cacheLocation(position);

            } else {
                if (badge) {
                    badge.className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium';
                    badge.textContent = 'Location Error';
                }
                if (location) {
                    location.innerHTML = '<i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>' +
                        (message || 'Unable to get location');
                }
                if (sidebarStatus) {
                    sidebarStatus.textContent = 'Location Error';
                }
            }
        }

        function updateCurrentLocationDisplay(position) {
            if (!position) return;

            // Update coordinate displays in the workplace setup
            const currentLat = document.getElementById('current-lat');
            const currentLng = document.getElementById('current-lng');
            const currentAccuracy = document.getElementById('current-accuracy');

            if (currentLat) currentLat.textContent = position.coords.latitude.toFixed(6);
            if (currentLng) currentLng.textContent = position.coords.longitude.toFixed(6);
            if (currentAccuracy) currentAccuracy.textContent = Math.round(position.coords.accuracy);

            // Enable the "Use Current Location" button
            const useLocationBtn = document.getElementById('use-current-location');
            if (useLocationBtn) {
                useLocationBtn.disabled = false;
                useLocationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                useLocationBtn.classList.add('hover:bg-blue-700');
            }

            // Detect which workplace user is currently at (for regular check-in)
            detectCurrentWorkplace(position);
            
            // Detect which workplace user is currently at (for special check-in)
            detectSpecialCurrentWorkplace(position);
        }

        // Detect which workplace the user is currently at based on GPS
        async function detectCurrentWorkplace(position) {
            if (!position) return;

            try {
                // Get all workplaces (user's assigned + system workplaces)
                const userId = getCurrentUserId();
                const response = await fetch(`/api/user-workplaces/${userId}`);
                
                if (!response.ok) return;
                
                const data = await response.json();
                const allWorkplaces = data.workplaces || [];
                
                // Also get all system workplaces
                const allWorkplacesResponse = await fetch('/api/all-workplaces');
                if (allWorkplacesResponse.ok) {
                    const allWorkplacesData = await allWorkplacesResponse.json();
                    if (allWorkplacesData.workplaces) {
                        // Merge, avoiding duplicates
                        const existingIds = allWorkplaces.map(w => w.id);
                        allWorkplacesData.workplaces.forEach(w => {
                            if (!existingIds.includes(w.id)) {
                                allWorkplaces.push(w);
                            }
                        });
                    }
                }

                // Find which workplace user is currently at
                let currentWorkplace = null;
                let minDistance = Infinity;

                for (const workplace of allWorkplaces) {
                    const distance = calculateDistance(
                        position.coords.latitude,
                        position.coords.longitude,
                        workplace.latitude,
                        workplace.longitude
                    );

                    if (distance <= workplace.radius && distance < minDistance) {
                        currentWorkplace = workplace;
                        minDistance = distance;
                    }
                }

                // Update the Current Workplace display
                const currentWorkplaceDisplay = document.getElementById('current-workplace-display');
                const currentWorkplaceAddress = document.getElementById('current-workplace-address');
                const currentDistance = document.getElementById('current-distance');

                if (currentWorkplace) {
                    if (currentWorkplaceDisplay) {
                        currentWorkplaceDisplay.textContent = currentWorkplace.name;
                        currentWorkplaceDisplay.className = 'text-sm font-semibold text-green-700 block';
                    }
                    if (currentWorkplaceAddress) {
                        currentWorkplaceAddress.textContent = currentWorkplace.address || 'No address';
                    }
                    if (currentDistance) {
                        currentDistance.textContent = Math.round(minDistance) + 'm';
                        currentDistance.className = 'text-green-600 font-semibold text-xs';
                    }
                } else {
                    if (currentWorkplaceDisplay) {
                        currentWorkplaceDisplay.textContent = 'Not at any workplace';
                        currentWorkplaceDisplay.className = 'text-sm font-semibold text-gray-500 block';
                    }
                    if (currentWorkplaceAddress) {
                        currentWorkplaceAddress.textContent = 'You are outside all workplace areas';
                    }
                    if (currentDistance) {
                        currentDistance.textContent = '--';
                        currentDistance.className = 'text-gray-400 text-xs';
                    }
                }

                // Update Primary Workplace display
                const primaryWorkplace = data.primary_workplace;
                const primaryWorkplaceDisplay = document.getElementById('primary-workplace-display');
                const primaryDistance = document.getElementById('primary-distance');

                if (primaryWorkplace) {
                    if (primaryWorkplaceDisplay) {
                        primaryWorkplaceDisplay.textContent = primaryWorkplace.name;
                    }
                    if (primaryDistance) {
                        const dist = calculateDistance(
                            position.coords.latitude,
                            position.coords.longitude,
                            primaryWorkplace.latitude,
                            primaryWorkplace.longitude
                        );
                        primaryDistance.textContent = Math.round(dist) + 'm';
                    }
                } else {
                    if (primaryWorkplaceDisplay) {
                        primaryWorkplaceDisplay.textContent = 'Not set';
                    }
                }

            } catch (error) {
                console.error('Error detecting current workplace:', error);
            }
        }

        // Detect current workplace for special check-in
        async function detectSpecialCurrentWorkplace(position) {
            if (!position) return;

            try {
                // Get all workplaces (user's assigned + system workplaces)
                const userId = getCurrentUserId();
                const response = await fetch(`/api/user-workplaces/${userId}`);
                
                if (!response.ok) return;
                
                const data = await response.json();
                const allWorkplaces = data.workplaces || [];
                
                // Also get all system workplaces
                const allWorkplacesResponse = await fetch('/api/all-workplaces');
                if (allWorkplacesResponse.ok) {
                    const allWorkplacesData = await allWorkplacesResponse.json();
                    if (allWorkplacesData.workplaces) {
                        // Merge, avoiding duplicates
                        const existingIds = allWorkplaces.map(w => w.id);
                        allWorkplacesData.workplaces.forEach(w => {
                            if (!existingIds.includes(w.id)) {
                                allWorkplaces.push(w);
                            }
                        });
                    }
                }

                // Find which workplace user is currently at
                let currentWorkplace = null;
                let minDistance = Infinity;

                for (const workplace of allWorkplaces) {
                    const distance = calculateDistance(
                        position.coords.latitude,
                        position.coords.longitude,
                        workplace.latitude,
                        workplace.longitude
                    );

                    if (distance <= workplace.radius && distance < minDistance) {
                        currentWorkplace = workplace;
                        minDistance = distance;
                    }
                }

                // Store the detected workplace ID globally for special check-in
                selectedSpecialLocationId = currentWorkplace ? currentWorkplace.id : null;

                // Update the Current Workplace display for special check-in
                const currentWorkplaceDisplay = document.getElementById('special-current-workplace-display');
                const currentWorkplaceAddress = document.getElementById('special-current-workplace-address');
                const currentDistance = document.getElementById('special-current-distance');

                if (currentWorkplace) {
                    if (currentWorkplaceDisplay) {
                        currentWorkplaceDisplay.textContent = currentWorkplace.name;
                        currentWorkplaceDisplay.className = 'text-sm font-semibold text-yellow-700 block';
                    }
                    if (currentWorkplaceAddress) {
                        currentWorkplaceAddress.textContent = currentWorkplace.address || 'No address';
                    }
                    if (currentDistance) {
                        currentDistance.textContent = Math.round(minDistance) + 'm';
                        currentDistance.className = 'text-yellow-600 font-semibold text-xs';
                    }
                } else {
                    if (currentWorkplaceDisplay) {
                        currentWorkplaceDisplay.textContent = 'Not at any workplace';
                        currentWorkplaceDisplay.className = 'text-sm font-semibold text-gray-500 block';
                    }
                    if (currentWorkplaceAddress) {
                        currentWorkplaceAddress.textContent = 'You are outside all workplace areas';
                    }
                    if (currentDistance) {
                        currentDistance.textContent = '--';
                        currentDistance.className = 'text-gray-400 text-xs';
                    }
                }

                // Update Primary Workplace display for special check-in
                const primaryWorkplace = data.primary_workplace;
                const primaryWorkplaceDisplay = document.getElementById('special-primary-workplace-display');
                const primaryDistance = document.getElementById('special-primary-distance');

                if (primaryWorkplace) {
                    if (primaryWorkplaceDisplay) {
                        primaryWorkplaceDisplay.textContent = primaryWorkplace.name;
                    }
                    if (primaryDistance) {
                        const dist = calculateDistance(
                            position.coords.latitude,
                            position.coords.longitude,
                            primaryWorkplace.latitude,
                            primaryWorkplace.longitude
                        );
                        primaryDistance.textContent = Math.round(dist) + 'm';
                    }
                } else {
                    if (primaryWorkplaceDisplay) {
                        primaryWorkplaceDisplay.textContent = 'Not set';
                    }
                }

                // Update special check-in button state
                const checkinBtn = document.getElementById('special-checkin-btn');
                const locationBadge = document.getElementById('special-location-badge');
                const currentLocation = document.getElementById('special-current-location');

                if (checkinBtn) {
                    const accuracy = Math.round(position.coords.accuracy);
                    
                    // Update location display
                    if (currentLocation) {
                        currentLocation.innerHTML = `<i class="fas fa-map-marker-alt text-yellow-600 mr-2"></i>` +
                            `Location: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)} ` +
                            `<span class="text-xs text-gray-600">(±${accuracy}m)</span>`;
                    }

                    if (currentWorkplace) {
                        // Enable button - user is at a workplace
                        if (locationBadge) {
                            locationBadge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
                            locationBadge.textContent = 'In Range';
                        }
                        
                        checkinBtn.className = 'w-full py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl font-bold text-lg transition-colors shadow-sm mb-4';
                        checkinBtn.innerHTML = '<i class="fas fa-star mr-2"></i>Special Check In/Out';
                        checkinBtn.disabled = false;
                        checkinBtn.onclick = performSpecialCheckin;
                    } else {
                        // Disable button - user is not at any workplace
                        if (locationBadge) {
                            locationBadge.className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium';
                            locationBadge.textContent = 'Not in Range';
                        }
                        
                        checkinBtn.className = 'w-full py-4 bg-red-500 text-white rounded-xl font-bold text-lg cursor-not-allowed shadow-sm mb-4';
                        checkinBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Outside All Workplace Areas';
                        checkinBtn.disabled = true;
                    }
                }

            } catch (error) {
                console.error('Error detecting special current workplace:', error);
            }
        }

        // Calculate distance between two coordinates (Haversine formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // Earth's radius in meters
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c; // Distance in meters
        }

        // Update workplace display in check-in section
        function updateWorkplaceDisplay() {
            const workplace = workLocations.mainOffice;
            const nameDisplay = document.getElementById('workplace-name-display');
            const addressDisplay = document.getElementById('workplace-address-display');

            if (nameDisplay && addressDisplay) {
                if (workplace && workplace.name) {
                    nameDisplay.textContent = workplace.name;
                    addressDisplay.textContent = workplace.address || 'No address provided';
                } else {
                    nameDisplay.textContent = 'Not configured';
                    addressDisplay.textContent = 'Please setup your workplace first';
                }
            }
        }

        // Update geofence status and distances
        function updateGeofenceStatus(position) {
            if (!position) return;

            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;

            // Load workplace from storage first
            loadWorkplaceData();
            const workplace = workLocations.mainOffice;

            if (!workplace || !workplace.lat || !workplace.lng) {
                // No workplace configured
                const checkinBtn = document.getElementById('checkin-btn');
                if (checkinBtn) {
                    checkinBtn.className =
                        'w-full py-4 bg-gray-400 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
                    checkinBtn.innerHTML = '<i class="fas fa-cog mr-2"></i>Select Workplace First';
                    checkinBtn.disabled = true;
                    checkinBtn.onclick = () => switchToSection('my-workplace');
                }

                // Update workplace display to show not configured
                updateWorkplaceDisplay();
                return;
            }

            // Calculate distance to workplace
            const workplaceDistance = calculateDistance(
                userLat, userLng,
                workplace.lat, workplace.lng
            );

            // Update distance displays
            const officeDistanceEl = document.getElementById('office-distance');
            if (officeDistanceEl) {
                officeDistanceEl.textContent = Math.round(workplaceDistance) + 'm';
            }

            // Update workplace info display
            updateWorkplaceDisplay();

            // Check if user is within geofence
            const inWorkplaceGeofence = workplaceDistance <= workplace.radius;

            // Update geofence status display
            const geofenceStatus = document.getElementById('geofence-status');
            if (geofenceStatus) {
                if (inWorkplaceGeofence) {
                    geofenceStatus.className = 'flex items-center text-green-700 bg-green-100 px-3 py-2 rounded-lg';
                    geofenceStatus.innerHTML = '<i class="fas fa-check-circle mr-2"></i>You are within the work area';
                } else {
                    geofenceStatus.className = 'flex items-center text-yellow-700 bg-yellow-100 px-3 py-2 rounded-lg';
                    geofenceStatus.innerHTML =
                        `<i class="fas fa-info-circle mr-2"></i>You are ${Math.round(workplaceDistance)}m away from primary workplace`;
                }
            }

            // Always fetch current status to set correct button (backend will validate location)
            const checkinBtn = document.getElementById('checkin-btn');
            if (checkinBtn) {
                // Always enable the button - backend will handle validation
                fetchCurrentStatus();
            }

            // Update location badge color based on geofence status
            const badge = document.getElementById('location-badge');
            if (badge) {
                if (inWorkplaceGeofence) {
                    badge.className = 'status-badge bg-green-100 text-green-800';
                    badge.textContent = 'At Primary Workplace';
                } else {
                    badge.className = 'status-badge bg-blue-100 text-blue-800';
                    badge.textContent = 'Location Active';
                }
            }
        }

        // Check-in functionality
        function performCheckin() {
            performActionAPI(); // Use the unified action API
        }

        function performCheckout() {
            const checkinBtn = document.getElementById('checkin-btn');
            checkinBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Checking Out...';
            checkinBtn.disabled = true;

            setTimeout(() => {
                checkinBtn.className =
                    'w-full py-4 bg-blue-600 text-white rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors duration-200';
                checkinBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Check In Now';
                checkinBtn.onclick = performCheckin;
                checkinBtn.disabled = false;

                showNotification('Check-out successful!', 'success');
                updateTodaysActivity('checkout');
            }, 2000);
        }

        function updateTodaysActivity(type = 'checkin') {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            // You can implement more sophisticated activity tracking here
            console.log(`${type} recorded at ${timeString}`);
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 max-w-sm ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-start">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2 mt-0.5 flex-shrink-0"></i>
                    <span class="text-sm leading-tight break-words">${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            // Remove after 6 seconds (longer for mobile users to read)
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 6000);
        }

        // Auto-refresh functionality (simplified for user dashboard)
        function startAutoRefresh() {
            // Update timestamp every 30 seconds
            setInterval(() => {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                // Update any time displays if needed
                console.log('Dashboard refreshed at:', timeString);
            }, 30000); // 30 seconds
        } // Map state tracking
        let mapInitializationState = {
            checkinMap: false,
            setupMap: false,
            checkinMapLoading: false,
            setupMapLoading: false
        };

        // Initialize maps only when needed (lazy loading)
        function initializeMaps() {
            // Don't initialize maps immediately, wait for section switch
            console.log('Maps initialization deferred until needed');
        }

        // Initialize GPS Check-in Map with Leaflet (optimized)
        function initializeCheckinMap() {
            const mapContainer = document.getElementById('checkin-map');
            if (!mapContainer) {
                console.warn('Check-in map container not found');
                return;
            }

            // Prevent multiple simultaneous initializations
            if (mapInitializationState.checkinMapLoading) {
                console.log('Check-in map already loading, skipping...');
                return;
            }

            // If map already exists and has content, don't recreate
            if (checkinMap && mapInitializationState.checkinMap) {
                console.log('Check-in map already initialized, refreshing data only...');
                refreshCheckinMapData();
                return;
            }

            mapInitializationState.checkinMapLoading = true;

            // Show loading state
            showMapLoadingState('checkin-map');

            // Initialize with fallback location if user location not available
            let lat = 14.5995; // Default Manila coordinates
            let lng = 120.9842;
            let hasUserLocation = false;

            if (userLocation && userLocation.coords) {
                lat = userLocation.coords.latitude;
                lng = userLocation.coords.longitude;
                hasUserLocation = true;
            }

            try {
                // Remove existing map if present
                if (checkinMap) {
                    checkinMap.remove();
                    checkinMap = null;
                }

                // Initialize Leaflet map with optimized settings
                checkinMap = L.map('checkin-map', {
                    zoomControl: true,
                    attributionControl: false, // Remove to reduce clutter
                    maxZoom: 18,
                    minZoom: 10,
                    preferCanvas: true, // Better performance for markers
                    fadeAnimation: false, // Disable animations for faster loading
                    zoomAnimation: false,
                    markerZoomAnimation: false
                }).setView([lat, lng], hasUserLocation ? 16 : 12);

                // Add optimized tile layer with loading options
                const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 18,
                    tileSize: 256,
                    crossOrigin: true,
                    // Performance optimizations
                    keepBuffer: 2, // Keep fewer tiles in memory
                    updateWhenZooming: false, // Don't update tiles while zooming
                    updateWhenIdle: true, // Update only when map is idle
                    bounds: [
                        [lat - 0.05, lng - 0.05], // Limit tile loading area
                        [lat + 0.05, lng + 0.05]
                    ]
                });

                // Add loading event handlers
                tileLayer.on('loading', () => {
                    console.log('Map tiles loading...');
                });

                tileLayer.on('load', () => {
                    console.log('Map tiles loaded successfully');
                    hideMapLoadingState('checkin-map');
                    mapInitializationState.checkinMap = true;
                    mapInitializationState.checkinMapLoading = false;
                });

                tileLayer.on('tileerror', (e) => {
                    console.warn('Tile loading error:', e);
                    // Continue anyway, don't block the map
                });

                tileLayer.addTo(checkinMap);

                // Add markers and overlays
                addCheckinMapMarkers(lat, lng, hasUserLocation);

                // Set timeout fallback in case tiles don't load
                setTimeout(() => {
                    if (mapInitializationState.checkinMapLoading) {
                        console.log('Map loading timeout, hiding loading state');
                        hideMapLoadingState('checkin-map');
                        mapInitializationState.checkinMap = true;
                        mapInitializationState.checkinMapLoading = false;
                    }
                }, 5000); // 5 second timeout

            } catch (error) {
                console.error('Error initializing check-in map:', error);
                showMapError('checkin-map', 'Failed to load map. Please try refreshing.');
                mapInitializationState.checkinMapLoading = false;
            }
        }

        // Separate function to add markers (for better organization)
        function addCheckinMapMarkers(lat, lng, hasUserLocation) {
            if (!checkinMap) return;

            // Add user location marker only if we have real location
            if (hasUserLocation && userLocation) {
                const userMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<div style="background: #3b82f6; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(checkinMap);

                userMarker.bindPopup('Your Current Location');
            }

            // Add workplace location marker and geofence circle
            const workplace = workLocations.mainOffice;
            if (workplace && workplace.lat && workplace.lng) {
                // Add workplace marker
                const workMarker = L.marker([workplace.lat, workplace.lng], {
                    icon: L.divIcon({
                        className: 'workplace-marker',
                        html: '<div style="background: #10b981; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(checkinMap);

                workMarker.bindPopup(`<b>${workplace.name}</b><br>${workplace.address || 'Workplace Location'}`);

                // Add geofence circle
                L.circle([workplace.lat, workplace.lng], {
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.1,
                    radius: workplace.radius || 100,
                    weight: 2,
                    dashArray: '5, 5'
                }).addTo(checkinMap);

                // If we have both user and workplace, fit bounds to show both
                if (hasUserLocation) {
                    const group = L.featureGroup([
                        L.marker([lat, lng]),
                        L.marker([workplace.lat, workplace.lng])
                    ]);
                    checkinMap.fitBounds(group.getBounds().pad(0.1));
                }
            }
        }

        // Refresh map data without reinitializing the entire map
        function refreshCheckinMapData() {
            if (!checkinMap) return;

            console.log('Refreshing check-in map data...');

            // Remove existing markers but keep the map
            checkinMap.eachLayer((layer) => {
                if (layer instanceof L.Marker || layer instanceof L.Circle) {
                    checkinMap.removeLayer(layer);
                }
            });

            // Re-add markers with current data
            const lat = userLocation ? userLocation.coords.latitude : 14.5995;
            const lng = userLocation ? userLocation.coords.longitude : 120.9842;
            const hasUserLocation = userLocation && userLocation.coords;

            addCheckinMapMarkers(lat, lng, hasUserLocation);
        }

        // Show loading state for map
        function showMapLoadingState(mapId) {
            const mapContainer = document.getElementById(mapId);
            if (!mapContainer) return;

            const existingLoader = mapContainer.querySelector('.map-loader');
            if (existingLoader) return; // Already showing

            const loader = document.createElement('div');
            loader.className = 'map-loader absolute inset-0 bg-gray-100 flex items-center justify-center z-50';
            loader.innerHTML = `
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-3"></div>
                    <p class="text-gray-600 text-sm">Loading map...</p>
                    <p class="text-gray-500 text-xs mt-1">This may take a few seconds</p>
                </div>
            `;
            mapContainer.appendChild(loader);
        }

        // Hide loading state for map
        function hideMapLoadingState(mapId) {
            const mapContainer = document.getElementById(mapId);
            if (!mapContainer) return;

            const loader = mapContainer.querySelector('.map-loader');
            if (loader) {
                loader.remove();
            }
        }

        // Show map error state
        function showMapError(mapId, message) {
            const mapContainer = document.getElementById(mapId);
            if (!mapContainer) return;

            hideMapLoadingState(mapId);

            const errorDiv = document.createElement('div');
            errorDiv.className = 'absolute inset-0 bg-red-50 flex items-center justify-center z-50';
            errorDiv.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"></i>
                    <p class="text-red-700 font-medium">${message}</p>
                    <button onclick="retryMapLoading('${mapId}')" class="mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm">
                        <i class="fas fa-redo mr-2"></i>Try Again
                    </button>
                </div>
            `;
            mapContainer.appendChild(errorDiv);
        }

        // Retry map loading
        function retryMapLoading(mapId) {
            const mapContainer = document.getElementById(mapId);
            if (!mapContainer) return;

            // Remove error state
            const errorDiv = mapContainer.querySelector('.absolute.inset-0.bg-red-50');
            if (errorDiv) {
                errorDiv.remove();
            }

            // Reset state and retry
            if (mapId === 'checkin-map') {
                mapInitializationState.checkinMap = false;
                mapInitializationState.checkinMapLoading = false;
                initializeCheckinMap();
            } else if (mapId === 'setup-map') {
                mapInitializationState.setupMap = false;
                mapInitializationState.setupMapLoading = false;
                initializeSetupMap();
            }
        }

        // Initialize Setup Map for workplace registration (optimized)
        function initializeSetupMap() {
            const mapContainer = document.getElementById('setup-map');
            if (!mapContainer) {
                console.warn('Setup map container not found');
                return;
            }

            // Prevent multiple simultaneous initializations
            if (mapInitializationState.setupMapLoading) {
                console.log('Setup map already loading, skipping...');
                return;
            }

            // If map already exists, don't recreate
            if (setupMap && mapInitializationState.setupMap) {
                console.log('Setup map already initialized');
                return;
            }

            mapInitializationState.setupMapLoading = true;
            showMapLoadingState('setup-map');

            // Use fallback location if user location not available
            let lat = 14.5995;
            let lng = 120.9842;
            let hasUserLocation = false;

            if (userLocation && userLocation.coords) {
                lat = userLocation.coords.latitude;
                lng = userLocation.coords.longitude;
                hasUserLocation = true;
            }

            try {
                // Remove existing map if present
                if (setupMap) {
                    setupMap.remove();
                    setupMap = null;
                }

                // Initialize Leaflet map with optimized settings
                setupMap = L.map('setup-map', {
                    zoomControl: true,
                    attributionControl: false,
                    maxZoom: 18,
                    minZoom: 10,
                    preferCanvas: true,
                    fadeAnimation: false,
                    zoomAnimation: false,
                    markerZoomAnimation: false
                }).setView([lat, lng], 15);

                // Add optimized tile layer
                const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap',
                    maxZoom: 18,
                    tileSize: 256,
                    crossOrigin: true,
                    keepBuffer: 2,
                    updateWhenZooming: false,
                    updateWhenIdle: true
                });

                tileLayer.on('load', () => {
                    console.log('Setup map tiles loaded');
                    hideMapLoadingState('setup-map');
                    mapInitializationState.setupMap = true;
                    mapInitializationState.setupMapLoading = false;
                });

                tileLayer.on('tileerror', (e) => {
                    console.warn('Setup map tile error:', e);
                });

                tileLayer.addTo(setupMap);

                // Add user location marker if available
                if (hasUserLocation) {
                    const userMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'user-location-marker',
                            html: '<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        })
                    }).addTo(setupMap);

                    userMarker.bindPopup('Your Current Location');
                }

                // Handle map clicks for workplace selection
                setupMap.on('click', function(e) {
                    setWorkplaceLocation(e.latlng.lat, e.latlng.lng);
                });

                // Load existing workplace if available
                const workplace = workLocations.mainOffice;
                if (workplace && workplace.lat && workplace.lng) {
                    setWorkplaceLocation(workplace.lat, workplace.lng, false);
                    populateWorkplaceForm(workplace);
                }

                // Set timeout fallback
                setTimeout(() => {
                    if (mapInitializationState.setupMapLoading) {
                        console.log('Setup map loading timeout');
                        hideMapLoadingState('setup-map');
                        mapInitializationState.setupMap = true;
                        mapInitializationState.setupMapLoading = false;
                    }
                }, 5000);

            } catch (error) {
                console.error('Error initializing setup map:', error);
                showMapError('setup-map', 'Failed to load workplace setup map.');
                mapInitializationState.setupMapLoading = false;
            }
        }

        // Workplace setup functions
        function setWorkplaceLocation(lat, lng, updateForm = true) {
            if (!setupMap) return;

            // Remove existing workplace marker and circle
            if (workplaceMarker) {
                setupMap.removeLayer(workplaceMarker);
            }
            if (workplaceCircle) {
                setupMap.removeLayer(workplaceCircle);
            }

            // Get current radius
            const radius = parseInt(document.getElementById('workplace-radius').value) || 100;

            // Add new workplace marker
            workplaceMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'workplace-marker',
                    html: '<div style="background: #10b981; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(setupMap);

            workplaceMarker.bindPopup('Workplace Location');

            // Add geofence circle
            workplaceCircle = L.circle([lat, lng], {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.1,
                radius: radius,
                weight: 2,
                dashArray: '5, 5'
            }).addTo(setupMap);

            // Update form if requested
            if (updateForm) {
                // Reverse geocoding would go here (optional)
                updateStepStatus('step2-status', 'success', 'Location Set');
                document.getElementById('save-workplace').disabled = false;
            }
        }

        function populateWorkplaceForm(workplace) {
            document.getElementById('workplace-name').value = workplace.name || '';
            document.getElementById('workplace-address').value = workplace.address || '';
            document.getElementById('workplace-radius').value = workplace.radius || 100;
        }

        function resetWorkplaceSetup() {
            // Clear form
            document.getElementById('workplace-name').value = '';
            document.getElementById('workplace-address').value = '';
            document.getElementById('workplace-radius').value = '100';

            // Reset status
            updateStepStatus('step2-status', 'pending', 'Pending');
            updateStepStatus('step3-status', 'pending', 'Pending');

            // Remove markers
            if (workplaceMarker && setupMap) {
                setupMap.removeLayer(workplaceMarker);
                workplaceMarker = null;
            }
            if (workplaceCircle && setupMap) {
                setupMap.removeLayer(workplaceCircle);
                workplaceCircle = null;
            }

            document.getElementById('save-workplace').disabled = true;
        }

        function saveWorkplace() {
            if (!workplaceMarker) {
                alert('Please select a workplace location on the map first.');
                return;
            }

            const name = document.getElementById('workplace-name').value.trim();
            const address = document.getElementById('workplace-address').value.trim();
            const radius = parseInt(document.getElementById('workplace-radius').value);

            if (!name) {
                alert('Please enter a workplace name.');
                return;
            }

            const saveBtn = document.getElementById('save-workplace');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            saveBtn.disabled = true;

            const workplace = {
                user_id: getCurrentUserId(),
                name: name,
                address: address,
                latitude: workplaceMarker.getLatLng().lat,
                longitude: workplaceMarker.getLatLng().lng,
                radius: radius
            };

            // Save to database via API
            fetch('/api/save-workplace', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(workplace)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Also save to localStorage for backward compatibility
                        saveWorkplaceData({
                            lat: workplace.latitude,
                            lng: workplace.longitude,
                            name: workplace.name,
                            address: workplace.address,
                            radius: workplace.radius
                        });

                        updateStepStatus('step3-status', 'success', 'Completed');
                        showNotification('Workplace saved successfully to database!', 'success');

                        // Update workplace display
                        updateWorkplaceDisplay();

                        // Refresh checkin map if it exists
                        if (checkinMap) {
                            initializeCheckinMap();
                        }

                        // Update geofence status
                        if (userLocation) {
                            updateGeofenceStatus(userLocation);
                        }
                    } else {
                        showNotification(data.error || 'Failed to save workplace to database.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving workplace:', error);
                    showNotification('Failed to save workplace: ' + error.message, 'error');
                })
                .finally(() => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                });
        }

        function updateUserLocationOnMaps(position) {
            if (!position) return;

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Update check-in map if it exists
            if (checkinMap) {
                checkinMap.setView([lat, lng], checkinMap.getZoom());

                // Update user marker position
                checkinMap.eachLayer(function(layer) {
                    if (layer.options && layer.options.icon &&
                        layer.options.icon.options.className === 'user-location-marker') {
                        layer.setLatLng([lat, lng]);
                    }
                });
            }

            // Update setup map if it exists
            if (setupMap) {
                setupMap.eachLayer(function(layer) {
                    if (layer.options && layer.options.icon &&
                        layer.options.icon.options.className === 'user-location-marker') {
                        layer.setLatLng([lat, lng]);
                    }
                });
            }
        }

        // Force refresh location status and UI
        function refreshLocationStatus() {
            console.log('Force refreshing location status...');

            if (userLocation && hasLocationPermission) {
                updateLocationStatus('success', userLocation);
                updateGeofenceStatus(userLocation);
                if (checkinMap) {
                    updateUserLocationOnMaps(userLocation);
                }
                fetchCurrentStatus();
            } else {
                // Try to get fresh location
                initializeSmartLocation();
            }
        }

        // Workplace selection and management functions
        function selectWorkplace(id, name, address, latitude, longitude, radius, isPrimary) {
            // Show and scroll to details card
            const detailsCard = document.getElementById('workplace-details-card');
            if (detailsCard) {
                detailsCard.style.display = 'block';
                detailsCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // Update selected workplace details
            const detailsContainer = document.getElementById('selected-workplace-details');
            const mapContainer = document.getElementById('workplace-map-container');

            if (detailsContainer) {
                detailsContainer.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-4 border border-indigo-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-building text-indigo-600 mr-2"></i>
                                <span class="text-xs font-semibold text-gray-600 uppercase">Workplace</span>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900">${name}</h4>
                            ${isPrimary ? '<span class="inline-block mt-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-medium">Primary</span>' : ''}
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-4 border border-blue-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-circle-notch text-blue-600 mr-2"></i>
                                <span class="text-xs font-semibold text-gray-600 uppercase">Check-in Radius</span>
                            </div>
                            <h4 class="text-2xl font-bold text-gray-900">${radius}<span class="text-sm text-gray-600 ml-1">meters</span></h4>
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-map-marker-alt text-green-600 mr-3 mt-1 flex-shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Address</p>
                                <p class="text-sm text-gray-900">${address || 'No address provided'}</p>
                            </div>
                        </div>
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-crosshairs text-blue-600 mr-3 mt-1 flex-shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Coordinates</p>
                                <p class="text-sm text-gray-900 font-mono">${latitude.toFixed(6)}, ${longitude.toFixed(6)}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-2">
                        ${!isPrimary ? `
                            <button onclick="setPrimaryWorkplace(${id}, \`${name}\`)" 
                                class="flex-1 px-4 py-2.5 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-md transition-all font-semibold text-sm">
                                <i class="fas fa-star mr-2"></i>Set as Primary
                            </button>
                        ` : ''}
                        <button onclick="checkInAtWorkplace(${id}, \`${name}\`)" 
                            class="flex-1 px-4 py-2.5 gradient-success text-white rounded-lg hover:shadow-md transition-all font-semibold text-sm">
                            <i class="fas fa-map-pin mr-2"></i>Check In Here
                        </button>
                    </div>
                `;
            }

            // Show map container and initialize workplace map
            if (mapContainer) {
                mapContainer.style.display = 'block';
                initializeWorkplaceMap(latitude, longitude, radius, name, address);
            }

            // Highlight selected workplace
            document.querySelectorAll('.workplace-item').forEach(item => {
                item.classList.remove('ring-2', 'ring-indigo-500');
            });
            const selectedItem = document.querySelector(`[data-workplace-id="${id}"]`);
            if (selectedItem) {
                selectedItem.classList.add('ring-2', 'ring-indigo-500');
            }
        }

        function initializeWorkplaceMap(latitude, longitude, radius, name, address) {
            const mapContainer = document.getElementById('workplace-map');
            if (!mapContainer) return;

            // Remove existing map if any
            if (window.workplaceViewMap) {
                window.workplaceViewMap.remove();
            }

            // Initialize new map
            window.workplaceViewMap = L.map('workplace-map').setView([latitude, longitude], 16);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(window.workplaceViewMap);

            // Add workplace marker
            const workplaceMarker = L.marker([latitude, longitude], {
                icon: L.divIcon({
                    className: 'workplace-marker',
                    html: '<div style="background: #10b981; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(window.workplaceViewMap);

            workplaceMarker.bindPopup(`<b>${name}</b><br>${address || 'Workplace Location'}`);

            // Add geofence circle
            L.circle([latitude, longitude], {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.1,
                radius: radius,
                weight: 2,
                dashArray: '5, 5'
            }).addTo(window.workplaceViewMap);

            // Add user location if available
            if (userLocation) {
                const userMarker = L.marker([userLocation.coords.latitude, userLocation.coords.longitude], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(window.workplaceViewMap);

                userMarker.bindPopup('Your Current Location');

                // Calculate and show distance
                const distance = calculateDistance(
                    userLocation.coords.latitude,
                    userLocation.coords.longitude,
                    latitude,
                    longitude
                );

                // Add distance info to the details
                const detailsContainer = document.getElementById('selected-workplace-details');
                if (detailsContainer) {
                    const distanceInfo = document.createElement('div');
                    distanceInfo.innerHTML = `
                        <div class="flex items-start">
                            <i class="fas fa-route text-gray-500 mr-3 mt-1"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Distance from You</p>
                                <p class="text-sm ${distance <= radius ? 'text-green-600' : 'text-red-600'}">
                                    ${Math.round(distance)}m away 
                                    ${distance <= radius ? '(Within check-in range)' : '(Outside check-in range)'}
                                </p>
                            </div>
                        </div>
                    `;
                    detailsContainer.querySelector('.space-y-3').appendChild(distanceInfo);
                }
            }
        }

        async function setPrimaryWorkplace(workplaceId, workplaceName) {
            try {
                const response = await fetch('/api/set-primary-workplace', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || ''
                    },
                    body: JSON.stringify({
                        user_id: getCurrentUserId(),
                        workplace_id: workplaceId
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification(`${workplaceName} set as primary workplace`, 'success');

                    // Refresh workplace list and other data
                    fetchUserWorkplaces();
                    fetchUserWorkplace();

                    // Update check-in maps if they exist and are initialized
                    if (checkinMap && mapInitializationState.checkinMap) {
                        refreshCheckinMapData();
                    }
                } else {
                    showNotification(result.error || 'Failed to set primary workplace', 'error');
                }
            } catch (error) {
                console.error('Error setting primary workplace:', error);
                showNotification('Failed to set primary workplace: ' + error.message, 'error');
            }
        }

        async function checkInAtWorkplace(workplaceId, workplaceName) {
            try {
                // Get current location first
                if (!userLocation) {
                    showNotification('Getting your location...', 'info');
                    await startLocationTracking();
                }

                showNotification(`Switching to ${workplaceName} for check-in`, 'info');

                // Refresh workplace and location data
                await fetchUserWorkplaces();
                await fetchUserWorkplace();

                // Update check-in maps if they exist and are initialized
                if (checkinMap && mapInitializationState.checkinMap) {
                    refreshCheckinMapData();
                }

                // Switch to GPS check-in section
                switchToSection('gps-checkin');

                // Show info about the workplace
                setTimeout(() => {
                    showNotification(
                        `Now you can check in at ${workplaceName}. Click the check-in button when you're ready.`,
                        'success'
                    );
                }, 500);

            } catch (error) {
                console.error('Error switching to check-in:', error);
                showNotification('Failed to switch to check-in section: ' + error.message, 'error');
            }
        }

        function updatePrimaryWorkplaceInfo(primaryWorkplace) {
            console.log('updatePrimaryWorkplaceInfo called with:', primaryWorkplace);
            
            const primaryWorkplaceName = document.getElementById('primary-workplace-name');
            const primaryWorkplaceSubtitle = document.getElementById('primary-workplace-subtitle');
            
            console.log('Elements found:', {
                primaryWorkplaceName: !!primaryWorkplaceName,
                primaryWorkplaceSubtitle: !!primaryWorkplaceSubtitle
            });

            // Update stat card with actual workplace name
            if (primaryWorkplaceName) {
                if (primaryWorkplace && primaryWorkplace.name) {
                    console.log('Setting primary workplace name to:', primaryWorkplace.name);
                    primaryWorkplaceName.textContent = primaryWorkplace.name;
                    if (primaryWorkplaceSubtitle) {
                        primaryWorkplaceSubtitle.textContent = 'Default location';
                        primaryWorkplaceSubtitle.classList.remove('text-gray-500');
                        primaryWorkplaceSubtitle.classList.add('text-yellow-600');
                    }
                } else {
                    console.log('No primary workplace found, setting to "-"');
                    primaryWorkplaceName.textContent = '-';
                    if (primaryWorkplaceSubtitle) {
                        primaryWorkplaceSubtitle.textContent = 'Not set';
                        primaryWorkplaceSubtitle.classList.remove('text-yellow-600');
                        primaryWorkplaceSubtitle.classList.add('text-gray-500');
                    }
                }
            }

            // Show detailed primary workplace info if container exists
            const primaryInfoContainer = document.getElementById('primary-workplace-info');
            if (!primaryInfoContainer) return;

            if (primaryWorkplace) {
                primaryInfoContainer.innerHTML = `
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border border-green-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-star text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${primaryWorkplace.name}</h4>
                                    <p class="text-sm text-gray-600">Your Primary Workplace</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full font-medium">Active</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Address</p>
                                    <p class="text-sm text-gray-600">${primaryWorkplace.address || 'No address provided'}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-circle-notch text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Check-in Radius</p>
                                    <p class="text-sm text-gray-600">${primaryWorkplace.radius} meters</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-crosshairs text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Coordinates</p>
                                    <p class="text-sm text-gray-600">${primaryWorkplace.latitude.toFixed(6)}, ${primaryWorkplace.longitude.toFixed(6)}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-user-tag text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Role</p>
                                    <p class="text-sm text-gray-600 capitalize">${primaryWorkplace.role || 'Employee'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <button onclick="selectWorkplace(${primaryWorkplace.id}, '${primaryWorkplace.name.replace(/'/g, "\\'")}', '${(primaryWorkplace.address || '').replace(/'/g, "\\'")}', ${primaryWorkplace.latitude}, ${primaryWorkplace.longitude}, ${primaryWorkplace.radius}, true)" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </button>
                            <button onclick="switchToSection('gps-checkin')" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-map-pin mr-2"></i>Go to Check-in
                            </button>
                        </div>
                    </div>
                `;
            } else {
                primaryInfoContainer.innerHTML = `
                    <div class="text-center p-6 text-gray-500">
                        <i class="fas fa-star text-3xl mb-3 text-gray-300"></i>
                        <h4 class="text-lg font-medium text-gray-800 mb-2">No Primary Workplace Set</h4>
                        <p class="text-gray-600 mb-4">Select one of your assigned workplaces as primary to enable check-in functionality.</p>
                    </div>
                `;
            }
        }

        function refreshWorkplaces() {
            // Show loading state in both table and cards
            const tableBody = document.getElementById('workplaces-table-body');
            const cardsContainer = document.getElementById('workplaces-cards');
            const noWorkplacesMessage = document.getElementById('no-workplaces-message');

            const loadingHTML = `
                <div class="flex items-center justify-center p-8 text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-gray-300"></i>
                        <p class="text-sm font-medium">Refreshing your workplaces...</p>
                    </div>
                </div>
            `;

            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="5">${loadingHTML}</td></tr>`;
            }

            if (cardsContainer) {
                cardsContainer.innerHTML = loadingHTML;
            }

            if (noWorkplacesMessage) {
                noWorkplacesMessage.classList.add('hidden');
            }

            // Hide workplace details card
            const detailsCard = document.getElementById('workplace-details-card');
            if (detailsCard) {
                detailsCard.style.display = 'none';
            }

            // Reset to "My Workplaces" view if viewing all
            if (viewingAllWorkplaces) {
                viewingAllWorkplaces = false;
                const viewAllBtn = document.getElementById('view-all-btn');
                if (viewAllBtn) {
                    viewAllBtn.innerHTML = '<i class="fas fa-globe"></i><span>View All</span>';
                    viewAllBtn.classList.remove('bg-yellow-100', 'text-yellow-700', 'hover:bg-yellow-200');
                    viewAllBtn.classList.add('bg-indigo-100', 'text-indigo-700', 'hover:bg-indigo-200');
                }
            }

            // Fetch updated data
            fetchUserWorkplaces();
        }

        // Absence History Functions
        async function fetchAbsenceRecords(userId = null) {
            userId = userId || getCurrentUserId();

            try {
                const filter = document.getElementById('absence-month-filter')?.value || 'current';
                let startDate, endDate;
                const today = new Date();

                switch (filter) {
                    case 'current':
                        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        break;
                    case 'last30':
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setDate(startDate.getDate() - 30);
                        break;
                    case 'last90':
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setDate(startDate.getDate() - 90);
                        break;
                }

                const startDateStr = startDate.toISOString().split('T')[0];
                const endDateStr = endDate.toISOString().split('T')[0];

                const response = await fetch(
                    `/api/absence-records/${userId}?start_date=${startDateStr}&end_date=${endDateStr}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    });

                if (!response.ok) {
                    throw new Error('Failed to fetch absence records');
                }

                const data = await response.json();

                if (data.success) {
                    displayAbsenceRecords(data.absences);
                    updateAbsenceStats(data.stats);
                }
            } catch (error) {
                console.error('Error fetching absence records:', error);
                const tbody = document.getElementById('absence-history-tbody');
                const mobileContainer = document.getElementById('absence-history-mobile');

                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-red-500">
                                <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                                <p>Failed to load absence records. Please try again.</p>
                            </td>
                        </tr>
                    `;
                }

                if (mobileContainer) {
                    mobileContainer.innerHTML = `
                        <div class="bg-white rounded-lg border border-red-200 p-4 text-center text-red-500">
                            <i class="fas fa-exclamation-circle text-xl mb-2"></i>
                            <p class="text-sm font-medium">Failed to load absence records. Please try again.</p>
                        </div>
                    `;
                }
            }
        }

        function displayAbsenceRecords(absences) {
            allAbsenceRecords = absences || [];
            const tbody = document.getElementById('absence-history-tbody');
            const mobileContainer = document.getElementById('absence-history-mobile');
            const emptyState = document.getElementById('absence-empty-state');
            const paginationContainer = document.getElementById('absence-records-pagination');

            if (!tbody) return;

            if (absences.length === 0) {
                tbody.innerHTML = '';
                if (mobileContainer) {
                    mobileContainer.innerHTML = '';
                }
                if (emptyState) {
                    emptyState.classList.remove('hidden');
                }
                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
                return;
            }

            if (emptyState) {
                emptyState.classList.add('hidden');
            }

            // Calculate pagination
            const totalPages = Math.ceil(allAbsenceRecords.length / recordsPerPage);
            const start = (currentRecordsPage - 1) * recordsPerPage;
            const end = start + recordsPerPage;
            const paginatedRecords = allAbsenceRecords.slice(start, end);

            // Desktop table view
            tbody.innerHTML = paginatedRecords.map(absence => `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">${absence.formatted_date}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-700">${absence.day_of_week}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${absence.status_class}">
                            <i class="fas ${absence.status === 'excused' ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                            ${absence.status_label}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-700">${absence.reason || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600">
                            ${absence.admin_comment || '-'}
                        </div>
                    </td>
                </tr>
            `).join('');

            // Mobile card view
            if (mobileContainer) {
                mobileContainer.innerHTML = paginatedRecords.map(absence => `
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="text-sm font-bold text-gray-900 mb-1">${absence.formatted_date}</div>
                                <div class="text-xs text-gray-600">${absence.day_of_week}</div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${absence.status_class}">
                                <i class="fas ${absence.status === 'excused' ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                                ${absence.status_label}
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Reason:</span>
                                <span class="text-gray-600">${absence.reason || 'N/A'}</span>
                            </div>
                            ${absence.admin_comment ? `
                                    <div>
                                        <span class="font-medium text-gray-700">Notes:</span>
                                        <span class="text-gray-600">${absence.admin_comment}</span>
                                    </div>
                                ` : ''}
                        </div>
                    </div>
                `).join('');
            }

            // Update pagination controls
            updateRecordsPagination(totalPages);
        }

        // Update records pagination controls
        function updateRecordsPagination(totalPages) {
            const paginationContainer = document.getElementById('absence-records-pagination');
            const prevBtn = document.getElementById('records-prev-btn');
            const nextBtn = document.getElementById('records-next-btn');
            const pageNumbers = document.getElementById('records-page-numbers');
            const paginationInfo = document.getElementById('records-pagination-info');

            if (totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'flex';

            // Update info
            const start = (currentRecordsPage - 1) * recordsPerPage + 1;
            const end = Math.min(currentRecordsPage * recordsPerPage, allAbsenceRecords.length);
            paginationInfo.textContent = `Showing ${start}-${end} of ${allAbsenceRecords.length} records`;

            // Update buttons
            prevBtn.disabled = currentRecordsPage === 1;
            nextBtn.disabled = currentRecordsPage === totalPages;

            // Generate page numbers
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentRecordsPage - 1 && i <= currentRecordsPage + 1)) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = i === currentRecordsPage ?
                        'px-3 py-1 bg-indigo-600 text-white rounded-lg text-xs sm:text-sm font-medium' :
                        'px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50';
                    btn.onclick = () => goToRecordsPage(i);
                    pageNumbers.appendChild(btn);
                } else if (i === currentRecordsPage - 2 || i === currentRecordsPage + 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.textContent = '...';
                    ellipsis.className = 'px-2 text-gray-500';
                    pageNumbers.appendChild(ellipsis);
                }
            }
        }

        // Change records page
        function changeRecordsPage(delta) {
            const totalPages = Math.ceil(allAbsenceRecords.length / recordsPerPage);
            const newPage = currentRecordsPage + delta;
            if (newPage >= 1 && newPage <= totalPages) {
                currentRecordsPage = newPage;
                displayAbsenceRecords(allAbsenceRecords);
            }
        }

        // Go to specific records page
        function goToRecordsPage(page) {
            currentRecordsPage = page;
            displayAbsenceRecords(allAbsenceRecords);
        }

        function updateAbsenceStats(stats) {
            // Update statistics cards if they exist
            const weekAbsences = document.getElementById('week-absences');
            const monthAbsences = document.getElementById('month-absences');
            const excusedAbsences = document.getElementById('excused-absences');
            const unexcusedAbsences = document.getElementById('unexcused-absences');

            if (monthAbsences) monthAbsences.textContent = stats.total || 0;
            if (excusedAbsences) excusedAbsences.textContent = stats.excused || 0;
            if (unexcusedAbsences) unexcusedAbsences.textContent = stats.unexcused || 0;
        }

        async function fetchWeeklyAbsenceSummary(userId = null) {
            userId = userId || getCurrentUserId();

            try {
                const response = await fetch(`/api/weekly-absence-summary/${userId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch weekly absence summary');
                }

                const data = await response.json();

                if (data.success) {
                    const weekAbsences = document.getElementById('week-absences');
                    if (weekAbsences) {
                        weekAbsences.textContent = data.total_absences || 0;
                    }
                }
            } catch (error) {
                console.error('Error fetching weekly absence summary:', error);
            }
        }

        async function fetchMonthlyAbsenceSummary(userId = null) {
            userId = userId || getCurrentUserId();

            try {
                const response = await fetch(`/api/monthly-absence-summary/${userId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch monthly absence summary');
                }

                const data = await response.json();

                if (data.success) {
                    const attendanceRate = document.getElementById('attendance-rate-absence');
                    if (attendanceRate) {
                        attendanceRate.textContent = data.attendance_rate + '%';
                    }
                }
            } catch (error) {
                console.error('Error fetching monthly absence summary:', error);
            }
        }

        // Absence Request Functions
        function toggleAbsenceRequestForm() {
            const form = document.getElementById('absence-request-form');
            const btn = document.getElementById('toggle-absence-form-btn');

            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-times mr-2"></i>Cancel';
                // Set minimum date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('absence-start-date').setAttribute('min', today);
                document.getElementById('absence-end-date').setAttribute('min', today);
            } else {
                form.classList.add('hidden');
                btn.innerHTML = '<i class="fas fa-plus mr-2"></i>New Request';
                document.getElementById('absence-request-form-element').reset();
            }
        }

        // Character count for reason textarea
        document.addEventListener('DOMContentLoaded', function() {
            const reasonTextarea = document.getElementById('absence-reason');
            const charCount = document.getElementById('reason-char-count');

            if (reasonTextarea && charCount) {
                reasonTextarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // Update end date minimum when start date changes
            const startDateInput = document.getElementById('absence-start-date');
            const endDateInput = document.getElementById('absence-end-date');

            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    endDateInput.setAttribute('min', this.value);
                    if (endDateInput.value && endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                });
            }
        });

        // Submit absence request with comprehensive validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('absence-request-form-element');
            if (form) {
                const startDateInput = document.getElementById('absence-start-date');
                const endDateInput = document.getElementById('absence-end-date');
                const reasonInput = document.getElementById('absence-reason');

                // Real-time character count
                if (reasonInput) {
                    reasonInput.addEventListener('input', function() {
                        const charCount = document.getElementById('reason-char-count');
                        if (charCount) {
                            charCount.textContent = this.value.length;
                        }
                        ValidationUtils.clearError(this);
                    });

                    // Validate on blur
                    reasonInput.addEventListener('blur', function() {
                        if (this.value.trim()) {
                            const result = ValidationUtils.validateTextArea(
                                this.value, 
                                ValidationUtils.lengths.reason.min, 
                                ValidationUtils.lengths.reason.max,
                                'Reason'
                            );
                            if (!result.valid) {
                                ValidationUtils.showError(this, result.errors[0]);
                            }
                        }
                    });
                }

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // Clear all errors
                    ValidationUtils.clearError(startDateInput);
                    ValidationUtils.clearError(endDateInput);
                    ValidationUtils.clearError(reasonInput);

                    let hasErrors = false;
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    const reason = reasonInput.value;

                    // Validate dates
                    if (!startDate) {
                        ValidationUtils.showError(startDateInput, 'Start date is required');
                        hasErrors = true;
                    }

                    if (!endDate) {
                        ValidationUtils.showError(endDateInput, 'End date is required');
                        hasErrors = true;
                    }

                    // Validate date range
                    if (startDate && endDate) {
                        const start = new Date(startDate);
                        const end = new Date(endDate);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);

                        if (start < today) {
                            ValidationUtils.showError(startDateInput, 'Start date cannot be in the past');
                            hasErrors = true;
                        }

                        if (end < start) {
                            ValidationUtils.showError(endDateInput, 'End date must be after start date');
                            hasErrors = true;
                        }

                        // Check for excessive date range (e.g., more than 30 days)
                        const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                        if (daysDiff > 30) {
                            ValidationUtils.showError(endDateInput, 'Leave request cannot exceed 30 days. Please contact admin for longer periods.');
                            hasErrors = true;
                        }
                    }

                    // Validate reason
                    const reasonResult = ValidationUtils.validateTextArea(
                        reason,
                        ValidationUtils.lengths.reason.min,
                        ValidationUtils.lengths.reason.max,
                        'Reason for leave'
                    );

                    if (!reasonResult.valid) {
                        ValidationUtils.showError(reasonInput, reasonResult.errors[0]);
                        hasErrors = true;
                    }

                    if (hasErrors) {
                        const firstError = form.querySelector('.border-red-500');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                        return;
                    }

                    // Check rate limiting
                    const rateCheck = ValidationUtils.rateLimiter.canSubmit('absence-request-form', 3, 300000); // 3 requests per 5 minutes
                    if (!rateCheck.allowed) {
                        ValidationUtils.showToast(rateCheck.message, 'warning');
                        return;
                    }

                    const formData = {
                        start_date: startDate,
                        end_date: endDate,
                        reason: reasonResult.sanitized
                    };

                    // Disable submit button
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalHTML = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';

                    try {
                        const response = await fetch('/api/absence-requests', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            showSimpleNotification(data.message, 'success');
                            ValidationUtils.rateLimiter.reset('absence-request-form');
                            toggleAbsenceRequestForm();
                            fetchAbsenceRequests();
                            form.reset();
                            document.getElementById('reason-char-count').textContent = '0';
                        } else {
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat().join(', ');
                                showSimpleNotification(errorMessages, 'error');
                            } else {
                                showSimpleNotification(data.error || 'Failed to submit request',
                                    'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error submitting absence request:', error);
                        showSimpleNotification('An error occurred. Please try again.', 'error');
                    } finally {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalHTML;
                    }
                });
            }
        });

        // Fetch user's absence requests
        async function fetchAbsenceRequests() {
            try {
                const response = await fetch('/api/absence-requests?my_requests_only=true', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    displayAbsenceRequests(data.requests);
                }
            } catch (error) {
                console.error('Error fetching absence requests:', error);
            }
        }

        // Pagination variables for absence requests
        let allAbsenceRequests = [];
        let currentRequestsPage = 1;
        const requestsPerPage = 5;

        // Pagination variables for absence records
        let allAbsenceRecords = [];
        let currentRecordsPage = 1;
        const recordsPerPage = 5; // Show 5 records per page

        // Display absence requests with pagination
        function displayAbsenceRequests(requests) {
            allAbsenceRequests = requests || [];
            const container = document.getElementById('absence-requests-list');
            const paginationContainer = document.getElementById('absence-requests-pagination');

            if (!requests || requests.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No leave requests yet.</p>
                        <p class="text-sm">Submit a request above to inform your administrator about planned leave.</p>
                    </div>
                `;
                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
                return;
            }

            // Calculate pagination
            const totalPages = Math.ceil(allAbsenceRequests.length / requestsPerPage);
            const start = (currentRequestsPage - 1) * requestsPerPage;
            const end = start + requestsPerPage;
            const paginatedRequests = allAbsenceRequests.slice(start, end);

            const statusConfig = {
                pending: {
                    icon: 'fa-clock',
                    color: 'yellow',
                    bgColor: 'bg-yellow-100',
                    textColor: 'text-yellow-800',
                    label: 'Pending'
                },
                approved: {
                    icon: 'fa-check-circle',
                    color: 'green',
                    bgColor: 'bg-green-100',
                    textColor: 'text-green-800',
                    label: 'Approved'
                },
                rejected: {
                    icon: 'fa-times-circle',
                    color: 'red',
                    bgColor: 'bg-red-100',
                    textColor: 'text-red-800',
                    label: 'Rejected'
                }
            };

            container.innerHTML = paginatedRequests.map(request => {
                const status = statusConfig[request.status];
                const startDate = new Date(request.start_date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                const endDate = new Date(request.end_date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });

                return `
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${status.bgColor} ${status.textColor}">
                                        <i class="fas ${status.icon} mr-1"></i>${status.label}
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        <i class="fas fa-calendar mr-1"></i>${startDate} - ${endDate}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 mb-2"><strong>Reason:</strong> ${request.reason}</p>
                                ${request.admin_comment ? `
                                        <div class="bg-gray-50 border-l-4 border-${status.color}-400 p-2 mt-2">
                                            <p class="text-xs text-gray-600"><strong>Admin Response:</strong> ${request.admin_comment}</p>
                                            ${request.reviewed_at ? `<p class="text-xs text-gray-500 mt-1">Reviewed: ${new Date(request.reviewed_at).toLocaleString()}</p>` : ''}
                                        </div>
                                    ` : ''}
                            </div>
                            ${request.status === 'pending' ? `
                                    <button onclick="deleteAbsenceRequest(${request.id})" class="ml-4 text-red-600 hover:text-red-800" title="Delete request">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            // Update pagination controls
            updateRequestsPagination(totalPages);
        }

        // Update requests pagination controls
        function updateRequestsPagination(totalPages) {
            const paginationContainer = document.getElementById('absence-requests-pagination');
            const prevBtn = document.getElementById('requests-prev-btn');
            const nextBtn = document.getElementById('requests-next-btn');
            const pageNumbers = document.getElementById('requests-page-numbers');
            const paginationInfo = document.getElementById('requests-pagination-info');

            if (totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'flex';

            // Update info
            const start = (currentRequestsPage - 1) * requestsPerPage + 1;
            const end = Math.min(currentRequestsPage * requestsPerPage, allAbsenceRequests.length);
            paginationInfo.textContent = `Showing ${start}-${end} of ${allAbsenceRequests.length} requests`;

            // Update buttons
            prevBtn.disabled = currentRequestsPage === 1;
            nextBtn.disabled = currentRequestsPage === totalPages;

            // Generate page numbers
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentRequestsPage - 1 && i <= currentRequestsPage + 1)) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = i === currentRequestsPage ?
                        'px-3 py-1 bg-indigo-600 text-white rounded-lg text-xs sm:text-sm font-medium' :
                        'px-3 py-1 border border-gray-300 rounded-lg text-xs sm:text-sm hover:bg-gray-50';
                    btn.onclick = () => goToRequestsPage(i);
                    pageNumbers.appendChild(btn);
                } else if (i === currentRequestsPage - 2 || i === currentRequestsPage + 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.textContent = '...';
                    ellipsis.className = 'px-2 text-gray-500';
                    pageNumbers.appendChild(ellipsis);
                }
            }
        }

        // Change requests page
        function changeRequestsPage(delta) {
            const totalPages = Math.ceil(allAbsenceRequests.length / requestsPerPage);
            const newPage = currentRequestsPage + delta;
            if (newPage >= 1 && newPage <= totalPages) {
                currentRequestsPage = newPage;
                displayAbsenceRequests(allAbsenceRequests);
            }
        }

        // Go to specific requests page
        function goToRequestsPage(page) {
            currentRequestsPage = page;
            displayAbsenceRequests(allAbsenceRequests);
        }

        // Display absence requests (old version - removed)
        // Delete absence request
        async function deleteAbsenceRequest(requestId) {
            if (!confirm('Are you sure you want to delete this absence request?')) {
                return;
            }

            try {
                const response = await fetch(`/api/absence-requests/${requestId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showSimpleNotification(data.message, 'success');
                    fetchAbsenceRequests();
                } else {
                    showSimpleNotification(data.error || 'Failed to delete request', 'error');
                }
            } catch (error) {
                console.error('Error deleting absence request:', error);
                showSimpleNotification('An error occurred. Please try again.', 'error');
            }
        }

        function refreshAbsenceData() {
            fetchAbsenceRecords();
            fetchWeeklyAbsenceSummary();
            fetchMonthlyAbsenceSummary();

            showSimpleNotification('Absence data refreshed', 'success');
        }

        // Add event listener for absence month filter
        document.addEventListener('DOMContentLoaded', function() {
            const absenceFilter = document.getElementById('absence-month-filter');
            if (absenceFilter) {
                absenceFilter.addEventListener('change', function() {
                    fetchAbsenceRecords();
                });
            }

            // Update clock in dashboard hero section
            function updateClock() {
                const now = new Date();
                const timeEl = document.getElementById('current-time');
                const dateEl = document.getElementById('current-date');

                if (timeEl) {
                    const hours = now.getHours();
                    const minutes = now.getMinutes();
                    const seconds = now.getSeconds();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    const displayHours = hours % 12 || 12;
                    timeEl.textContent =
                        `${displayHours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} ${ampm}`;
                }

                if (dateEl) {
                    const options = {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric'
                    };
                    dateEl.textContent = now.toLocaleDateString('en-US', options);
                }
            }

            // Update clock immediately and then every second
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>

    <script>
        // Simple script to get user's current location and log it
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    console.log("Latitude:", position.coords.latitude);
                    console.log("Longitude:", position.coords.longitude);
                    // You can send these values to your server if needed
                },
                function(error) {
                    console.error("Geolocation error:", error.message);
                }
            );
        } else {
            console.error("Geolocation not supported by this browser.");
        }

        // Profile Settings Form Handler with Enhanced Validation
        const profileForm = document.getElementById('profile-form');
        if (profileForm) {
            const nameInput = document.getElementById('profile-name');
            const phoneInput = document.getElementById('profile-phone');
            const passwordInput = document.getElementById('profile-password');

            // Real-time validation for name
            nameInput.addEventListener('blur', function() {
                const result = ValidationUtils.validateName(this.value, 'Full Name');
                if (!result.valid) {
                    ValidationUtils.showError(this, result.errors[0]);
                } else {
                    ValidationUtils.clearError(this);
                }
            });

            nameInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    ValidationUtils.clearError(this);
                }
            });

            // Real-time validation for phone
            phoneInput.addEventListener('blur', function() {
                if (this.value.trim() !== '') {
                    const result = ValidationUtils.validatePhone(this.value, false);
                    if (!result.valid) {
                        ValidationUtils.showError(this, result.errors[0]);
                    } else {
                        ValidationUtils.clearError(this);
                    }
                }
            });

            phoneInput.addEventListener('input', function() {
                ValidationUtils.clearError(this);
            });

            // Real-time validation for password
            passwordInput.addEventListener('input', function() {
                ValidationUtils.clearError(this);
                
                if (this.value.length > 0 && this.value.length < ValidationUtils.lengths.password.min) {
                    ValidationUtils.showError(this, `Password must be at least ${ValidationUtils.lengths.password.min} characters`);
                } else {
                    ValidationUtils.clearError(this);
                }
            });

            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Clear all errors
                ValidationUtils.clearError(nameInput);
                ValidationUtils.clearError(phoneInput);
                ValidationUtils.clearError(passwordInput);

                let hasErrors = false;
                const name = nameInput.value.trim();
                const phone = phoneInput.value.trim();
                const password = passwordInput.value;

                // Validate name
                const nameResult = ValidationUtils.validateName(name, 'Full Name');
                if (!nameResult.valid) {
                    ValidationUtils.showError(nameInput, nameResult.errors[0]);
                    hasErrors = true;
                }

                // Validate phone if provided
                if (phone) {
                    const phoneResult = ValidationUtils.validatePhone(phone, false);
                    if (!phoneResult.valid) {
                        ValidationUtils.showError(phoneInput, phoneResult.errors[0]);
                        hasErrors = true;
                    }
                }

                // Validate password if provided
                if (password) {
                    const passwordResult = ValidationUtils.validatePassword(password);
                    if (!passwordResult.valid) {
                        ValidationUtils.showError(passwordInput, passwordResult.errors[0]);
                        hasErrors = true;
                    }
                }

                if (hasErrors) {
                    const firstError = profileForm.querySelector('.border-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                    return;
                }

                // Check rate limiting (3 attempts allowed, blocked on 4th)
                const rateCheck = ValidationUtils.rateLimiter.canSubmit('profile-form', 3, 120000);
                if (!rateCheck.allowed) {
                    ValidationUtils.showToast(rateCheck.message, 'warning');
                    return;
                }

                // Prepare data with sanitized values
                const data = {
                    name: nameResult.sanitized,
                    phone_number: phone ? ValidationUtils.sanitize(phone) : ''
                };
                
                if (password) {
                    data.password = password;
                }
                
                // Disable form during submission
                const submitButton = profileForm.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

                fetch('/api/update-profile', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';

                    if (result.success) {
                        showNotification('Profile updated successfully!', 'success');
                        ValidationUtils.rateLimiter.reset('profile-form');
                        passwordInput.value = '';
                        
                        // Update the header name if changed
                        const headerName = document.querySelector('.text-2xl.font-bold.text-gray-900');
                        if (headerName) {
                            headerName.textContent = 'Welcome, ' + nameResult.sanitized;
                        }
                    } else {
                        showNotification(result.message || 'Failed to update profile', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';
                    showNotification('An error occurred while updating profile', 'error');
                });
            });
        }
        
        function resetProfileForm() {
            document.getElementById('profile-name').value = '{{ Auth::user()->name }}';
            document.getElementById('profile-phone').value = '{{ Auth::user()->phone_number ?? '' }}';
            document.getElementById('profile-password').value = '';
        }
    </script>

</body>

</html>
