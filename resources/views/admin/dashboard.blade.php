<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CISAM | Admin Dashboard</title>
    
    <!-- Preconnect to external resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Critical CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/x-icon" href="/img/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
        }

        .sidebar-transition {
            transition: all 0.3s ease-in-out;
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

        .stat-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
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

        .fade-in {
            animation: fadeIn 0.5s ease-in;
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

        /* Highlight pulse animation for search results */
        .highlight-pulse {
            animation: highlightPulse 1.5s ease-in-out;
            transition: all 0.3s ease;
        }

        @keyframes highlightPulse {
            0% {
                background-color: transparent;
                transform: scale(1);
            }
            25% {
                background-color: rgba(59, 130, 246, 0.1);
                transform: scale(1.01);
            }
            50% {
                background-color: rgba(59, 130, 246, 0.15);
            }
            75% {
                background-color: rgba(59, 130, 246, 0.1);
                transform: scale(1.01);
            }
            100% {
                background-color: transparent;
                transform: scale(1);
            }
        }

        /* Map marker bounce animation */
        @keyframes markerBounce {
            0%, 100% {
                transform: translateY(0);
            }
            25% {
                transform: translateY(-8px);
            }
            50% {
                transform: translateY(-4px);
            }
            75% {
                transform: translateY(-6px);
            }
        }

        @keyframes markerPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.15);
                opacity: 0.9;
            }
        }

        /* Fix Leaflet map z-index issues with modals */
        .leaflet-container {
            z-index: 1 !important;
        }

        .leaflet-control-container {
            z-index: 2 !important;
        }

        /* Fix dropdown overflow in tables */
        .table-container {
            overflow: visible !important;
        }

        .table-wrapper {
            overflow-x: auto;
            overflow-y: visible;
        }

        .dropdown-cell {
            position: relative;
        }

        .dropdown-menu {
            position: absolute !important;
            z-index: 9999 !important;
            right: 0;
            min-width: 12rem;
        }

        .admin-nav-link {
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

        .admin-nav-link:hover {
            background: #f1f5f9;
            color: #6366f1;
        }

        .admin-nav-link.active {
            background: #eef2ff;
            color: #6366f1;
            font-weight: 600;
        }

        .admin-nav-link.active::before {
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

        .admin-section {
            display: block;
            animation: fadeIn 0.4s ease-in-out;
        }

        .admin-section.hidden {
            display: none;
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

        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.025em;
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

        /* Mobile responsive table wrapper */
        @media (max-width: 768px) {
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-wrapper table {
                min-width: 600px;
            }

            /* Better touch targets for mobile buttons */
            .table-wrapper button,
            .table-wrapper a {
                min-height: 36px;
                min-width: 36px;
            }

            /* Card view styling for mobile */
            .workplace-row-main,
            .assignment-row {
                transition: background-color 0.2s ease;
            }

            .workplace-row-main:active,
            .assignment-row:active {
                background-color: #f8fafc;
            }
        }

        /* Extra small screens */
        @media (max-width: 640px) {

            /* Make tables fully scrollable */
            .table-wrapper {
                margin: 0 -1rem;
                padding: 0 1rem;
            }

            /* Compact table cells */
            .table-wrapper td,
            .table-wrapper th {
                padding: 0.5rem !important;
                font-size: 0.75rem;
            }

            /* Hide less critical columns on very small screens */
            .mobile-hide {
                display: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">

    <!-- Sidebar -->
    <div id="sidebar"
        class="fixed left-0 top-0 h-full w-72 bg-white transition-transform duration-300 z-[50] transform -translate-x-full lg:translate-x-0 border-r border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 gradient-primary rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">CISAM</h2>
                        <p class="text-xs text-gray-500 font-medium">Admin Panel</p>
                    </div>
                </div>
                <button type="button" onclick="toggleMobileSidebar()"
                    class="lg:hidden text-gray-600 hover:text-indigo-600 transition-colors" aria-label="Close Sidebar">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <nav class="mt-6 space-y-1 px-2">
            <div class="px-4 mb-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Navigation</p>
            </div>

            <div class="space-y-1">
                <a href="javascript:void(0)" onclick="switchAdminSection('dashboard')" class="admin-nav-link active"
                    data-section="dashboard">
                    <i class="fas fa-home w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="javascript:void(0)" onclick="switchAdminSection('workplaces')" class="admin-nav-link"
                    data-section="workplaces">
                    <i class="fas fa-building w-5"></i>
                    <span class="ml-3">Workplaces</span>
                </a>
                <a href="javascript:void(0)" onclick="switchAdminSection('users')" class="admin-nav-link"
                    data-section="users">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">Users</span>
                </a>
                <a href="javascript:void(0)" onclick="switchAdminSection('attendance')" class="admin-nav-link"
                    data-section="attendance">
                    <i class="fas fa-clipboard-check w-5"></i>
                    <span class="ml-3">Attendance</span>
                </a>
                <a href="javascript:void(0)" onclick="switchAdminSection('absence-requests')" class="admin-nav-link"
                    data-section="absence-requests">
                    <i class="fas fa-calendar-times w-5"></i>
                    <span class="ml-3">Leave Requests</span>
                    <span id="pending-absence-badge"
                        class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full hidden">0</span>
                </a>
                <a href="javascript:void(0)" onclick="switchAdminSection('reports')" class="admin-nav-link"
                    data-section="reports">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="ml-3">Reports</span>
                </a>
                <a href="javascript:void(0)" onclick="switchAdminSection('settings')" class="admin-nav-link"
                    data-section="settings">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">Settings</span>
                </a>
            </div>

            <div class="absolute bottom-4 left-0 right-0 px-4">
                <div
                    class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 shadow-sm border border-green-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full pulse-dot"></div>
                        <div class="flex-1">
                            <p class="text-xs text-green-700 font-semibold">System Status</p>
                            <p class="text-sm font-bold text-green-800">All Systems Online</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- Header -->
    <header class="topbar ml-0 lg:ml-72 transition-all duration-300 sticky top-0.5 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4 lg:py-6">
                <div class="flex items-center flex-1">
                    <button type="button" onclick="toggleMobileSidebar()"
                        class="lg:hidden text-gray-600 hover:text-indigo-600 mr-3 transition-colors"
                        aria-label="Toggle Sidebar">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <div class="flex-1">
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 leading-none">Admin Dashboard</h1>
                        <p class="text-sm text-gray-600 mt-0.5 hidden sm:block leading-none">Manage your system and
                            monitor activities</p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="text-right hidden md:block">
                        <p class="text-xs text-gray-500 font-medium">Welcome back,</p>
                        <p class="text-sm font-bold text-gray-900">{{ Auth::user()->name ?? 'Admin' }}</p>
                    </div>
                    <div
                        class="w-11 h-11 gradient-primary rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-md">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                        <a href="{{ route('dashboard') }}"
                            class="px-4 py-2 gradient-info text-white rounded-lg shadow-sm hover:shadow-md transition-all text-sm font-semibold btn-modern">
                            <i class="fas fa-user mr-2"></i>
                            <span class="hidden sm:inline">User View</span>
                        </a>
                        <a href="{{ route('logout.get') }}"
                            class="px-4 py-2 gradient-danger text-white rounded-lg shadow-sm hover:shadow-md transition-all text-sm font-semibold btn-modern">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="ml-0 lg:ml-72 transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="admin-section">
                <!-- Compact Stats - Mobile Optimized -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">System Overview
                    </h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                        <!-- Total Users -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-14 lg:h-14 gradient-primary rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-users text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1">
                                    {{ $users->count() }}</h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">Total
                                    Users</p>
                                <p class="text-[9px] lg:text-xs text-indigo-600 font-medium hidden lg:block">
                                    <i class="fas fa-arrow-up mr-1"></i>Active system users
                                </p>
                            </div>
                        </div>

                        <!-- Workplaces -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-14 lg:h-14 gradient-success rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-building text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1">
                                    {{ isset($workplaces) ? $workplaces->count() : 0 }}</h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">
                                    Workplaces</p>
                                <p class="text-[9px] lg:text-xs text-green-600 font-medium hidden lg:block">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Active locations
                                </p>
                            </div>
                        </div>

                        <!-- Today's Check-ins -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-14 lg:h-14 gradient-info rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-clock text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1"
                                    id="dashboard-stat-checkins">
                                    <i class="fas fa-spinner fa-spin text-lg"></i>
                                </h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">
                                    Today's Check-ins</p>
                                <p class="text-[9px] lg:text-xs text-blue-600 font-medium hidden lg:block">
                                    <i class="fas fa-users mr-1"></i>Active today
                                </p>
                            </div>
                        </div>

                        <!-- Pending Leave Requests -->
                        <div class="card-modern rounded-xl lg:rounded-2xl p-3 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-14 lg:h-14 gradient-warning rounded-lg lg:rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-calendar-times text-white text-sm lg:text-xl"></i>
                                </div>
                                <h3 class="text-xl lg:text-3xl font-bold text-gray-900 mb-0.5 lg:mb-1">
                                    <span id="dashboard-pending-count">0</span>
                                </h3>
                                <p class="text-[10px] lg:text-sm text-gray-600 font-semibold mb-1 leading-tight">
                                    Pending Leave</p>
                                <p class="text-[9px] lg:text-xs text-yellow-600 font-medium hidden lg:block">
                                    <i class="fas fa-hourglass-half mr-1"></i>Needs review
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Grid -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                        <!-- Manage Workplaces -->
                        <div class="action-card gradient-success text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer"
                            onclick="switchAdminSection('workplaces')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-building text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">Workplaces</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Manage
                                    locations</p>
                            </div>
                        </div>

                        <!-- Manage Users -->
                        <div class="action-card gradient-info text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer"
                            onclick="switchAdminSection('users')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-users text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">Users</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Manage
                                    accounts</p>
                            </div>
                        </div>

                        <!-- View Reports -->
                        <div class="action-card bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer"
                            onclick="switchAdminSection('reports')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-chart-bar text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">Reports</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Analytics
                                </p>
                            </div>
                        </div>

                        <!-- Attendance -->
                        <div class="action-card gradient-primary text-white p-4 lg:p-8 rounded-xl shadow-md cursor-pointer"
                            onclick="switchAdminSection('attendance')">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-clipboard-check text-2xl lg:text-3xl mb-2 lg:mb-4 opacity-90"></i>
                                <h3 class="text-sm lg:text-xl font-bold mb-1 lg:mb-2">Attendance</h3>
                                <p class="text-[10px] lg:text-sm opacity-90 leading-tight hidden lg:block">Monitor
                                    activity</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Summary -->
                <div class="card-modern rounded-2xl shadow-sm p-6 lg:p-8">
                    <div class="section-header">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-primary rounded-xl flex items-center justify-center mr-3 lg:mr-4">
                                    <i class="fas fa-chart-line text-white text-base lg:text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg lg:text-2xl font-bold text-gray-900">Recent Activity</h3>
                                    <p class="text-xs lg:text-sm text-gray-600">Latest system overview</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 shadow-sm">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-plus text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">New Users Today</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $users->where('created_at', '>=', now()->startOfDay())->count() }} new
                                        registrations</p>
                                </div>
                            </div>
                            <span
                                class="text-2xl font-bold text-green-600">{{ $users->where('created_at', '>=', now()->startOfDay())->count() }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border border-blue-200 shadow-sm">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-building text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Active Workplaces</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $workplaces->where('is_active', true)->count() }} locations available</p>
                                </div>
                            </div>
                            <span
                                class="text-2xl font-bold text-blue-600">{{ $workplaces->where('is_active', true)->count() }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl border border-purple-200 shadow-sm">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Users Online</p>
                                    <p class="text-xs text-gray-600">Active in last 5 minutes</p>
                                </div>
                            </div>
                            <span
                                class="text-2xl font-bold text-purple-600">{{ $users->filter(function ($user) {return $user->isOnline();})->count() }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border border-yellow-200 shadow-sm">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-check text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Active Today</p>
                                    <p class="text-xs text-gray-600">Users with activity today</p>
                                </div>
                            </div>
                            <span
                                class="text-2xl font-bold text-yellow-600">{{ $users->filter(function ($user) {return $user->last_activity && $user->last_activity->isToday();})->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workplaces Section -->
            <div id="workplaces-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-building text-green-600 mr-3"></i>
                        Workplace Management
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Manage workplace locations and user assignments</p>
                </div>

                <!-- Workplace Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Workplace
                        Statistics</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-success rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-building text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">
                                    {{ $workplaces->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Total Workplaces</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-info rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-check-circle text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">
                                    {{ $workplaces->where('is_active', true)->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Active</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-warning rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-users text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">
                                    {{ $users->sum(function ($u) {return $u->workplaces->count();}) }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Assignments</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-danger rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-times-circle text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">
                                    {{ $workplaces->where('is_active', false)->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Inactive</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions for Workplaces -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6">
                        <button onclick="openWorkplaceModal()"
                            class="action-card gradient-success text-white p-4 lg:p-6 rounded-xl shadow-md cursor-pointer">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-plus-circle text-xl lg:text-2xl mb-2 lg:mb-3 opacity-90"></i>
                                <h3 class="text-sm lg:text-base font-bold mb-1">Add Workplace</h3>
                                <p class="text-xs opacity-90 leading-tight hidden lg:block">Create new location</p>
                            </div>
                        </button>

                        <button onclick="openAssignmentModal()"
                            class="action-card gradient-info text-white p-4 lg:p-6 rounded-xl shadow-md cursor-pointer">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-user-plus text-xl lg:text-2xl mb-2 lg:mb-3 opacity-90"></i>
                                <h3 class="text-sm lg:text-base font-bold mb-1">Assign Users</h3>
                                <p class="text-xs opacity-90 leading-tight hidden lg:block">Link users to workplaces
                                </p>
                            </div>
                        </button>

                        <button onclick="scrollToAllWorkplaces()"
                            class="action-card bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-4 lg:p-6 rounded-xl shadow-md cursor-pointer col-span-2 lg:col-span-1">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-list text-xl lg:text-2xl mb-2 lg:mb-3 opacity-90"></i>
                                <h3 class="text-sm lg:text-base font-bold mb-1">View All</h3>
                                <p class="text-xs opacity-90 leading-tight hidden lg:block">Browse all workplaces</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div id="all-workplaces-table" class="card-modern rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div>
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">All Workplaces</h3>
                                <p class="text-xs text-gray-600 mt-0.5 hidden sm:block">Manage all workplace locations
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1 sm:flex-none">
                                    <input type="text" id="workplaceSearchMain" placeholder="Search..."
                                        class="w-full sm:w-auto pl-8 pr-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                </div>
                                <button onclick="openWorkplaceModal()"
                                    class="px-3 py-1.5 gradient-success text-white text-xs sm:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern whitespace-nowrap">
                                    <i class="fas fa-plus sm:mr-2"></i><span class="hidden sm:inline">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="workplacesTableMain">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Workplace</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Info
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="workplacesTableBodyMain">
                                @foreach ($workplaces as $workplace)
                                    <tr class="hover:bg-gray-50 transition-colors workplace-row-main">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div
                                                        class="h-10 w-10 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow">
                                                        <i class="fas fa-building text-white text-sm"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-semibold text-gray-900 workplace-name">
                                                        {{ $workplace->name }}</div>
                                                    <div class="text-xs text-gray-500">ID: {{ $workplace->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs text-gray-900 workplace-address">
                                                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                                {{ Str::limit($workplace->address, 35) }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ number_format($workplace->latitude, 4) }},
                                                {{ number_format($workplace->longitude, 4) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="space-y-1">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fas fa-circle-notch mr-1"></i>{{ $workplace->radius }}m
                                                </span>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $workplace->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    <div
                                                        class="w-1.5 h-1.5 {{ $workplace->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-1">
                                                    </div>
                                                    {{ $workplace->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                                    <i class="fas fa-users mr-1"></i>{{ $workplace->users_count }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1">
                                                <button onclick="viewWorkplace({{ $workplace->id }})"
                                                    class="inline-flex items-center p-1.5 bg-blue-100 text-blue-700 text-xs rounded-lg hover:bg-blue-200 transition-colors"
                                                    title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editWorkplace({{ $workplace->id }})"
                                                    class="inline-flex items-center p-1.5 bg-indigo-100 text-indigo-700 text-xs rounded-lg hover:bg-indigo-200 transition-colors"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button id="more-btn-wp-{{ $workplace->id }}"
                                                    onclick="toggleMoreActionsWorkplace({{ $workplace->id }})"
                                                    class="inline-flex items-center p-1.5 bg-gray-100 text-gray-700 text-xs rounded-lg hover:bg-gray-200 transition-colors"
                                                    title="More">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <div id="more-actions-wp-{{ $workplace->id }}" style="display: none;"
                                                    class="flex items-center gap-1">
                                                    <button onclick="manageUsers({{ $workplace->id }})"
                                                        class="inline-flex items-center p-1.5 bg-green-100 text-green-700 text-xs rounded-lg hover:bg-green-200 transition-colors"
                                                        title="Users">
                                                        <i class="fas fa-users-cog"></i>
                                                    </button>
                                                    <button onclick="deleteWorkplace({{ $workplace->id }})"
                                                        class="inline-flex items-center p-1.5 bg-red-100 text-red-700 text-xs rounded-lg hover:bg-red-200 transition-colors"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button onclick="toggleMoreActionsWorkplace({{ $workplace->id }})"
                                                        class="inline-flex items-center p-1.5 bg-gray-100 text-gray-700 text-xs rounded-lg hover:bg-gray-200 transition-colors"
                                                        title="Back">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" id="workplacesCardsMain">
                        @foreach ($workplaces as $workplace)
                            <div class="p-3 hover:bg-gray-50 transition-colors workplace-row-main">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-12 w-12 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow">
                                            <i class="fas fa-building text-white text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1 min-w-0">
                                                <h4
                                                    class="text-sm font-semibold text-gray-900 truncate workplace-name">
                                                    {{ $workplace->name }}</h4>
                                                <p class="text-xs text-gray-500">ID: {{ $workplace->id }}</p>
                                            </div>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $workplace->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                <div
                                                    class="w-1.5 h-1.5 {{ $workplace->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-1">
                                                </div>
                                                {{ $workplace->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>

                                        <div class="text-xs text-gray-600 mb-2 workplace-address">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                            {{ Str::limit($workplace->address, 50) }}
                                        </div>

                                        <div class="flex items-center gap-2 mb-2">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <i class="fas fa-circle-notch mr-1"></i>{{ $workplace->radius }}m
                                            </span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                                <i class="fas fa-users mr-1"></i>{{ $workplace->users_count }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <button onclick="viewWorkplace({{ $workplace->id }})"
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200 transition-colors">
                                                <i class="fas fa-eye"></i><span>View</span>
                                            </button>
                                            <button onclick="editWorkplace({{ $workplace->id }})"
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-lg hover:bg-indigo-200 transition-colors">
                                                <i class="fas fa-edit"></i><span>Edit</span>
                                            </button>
                                            <button id="more-btn-wp-mobile-{{ $workplace->id }}"
                                                onclick="toggleMoreActionsWorkplace({{ $workplace->id }})"
                                                class="inline-flex items-center justify-center p-1.5 bg-gray-100 text-gray-700 text-xs rounded-lg hover:bg-gray-200 transition-colors">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                        </div>

                                        <div id="more-actions-wp-mobile-{{ $workplace->id }}" style="display: none;"
                                            class="flex items-center gap-1.5 mt-2">
                                            <button onclick="manageUsers({{ $workplace->id }})"
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded-lg hover:bg-green-200 transition-colors">
                                                <i class="fas fa-users-cog"></i><span>Users</span>
                                            </button>
                                            <button onclick="deleteWorkplace({{ $workplace->id }})"
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors">
                                                <i class="fas fa-trash"></i><span>Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-3 sm:px-4 py-3 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                            <div id="workplace-pagination-info" class="text-xs sm:text-sm text-gray-600"></div>
                            <div id="workplace-pagination-controls" class="flex items-center space-x-1"></div>
                        </div>
                    </div>
                </div>

                <!-- User Assignments Section -->
                <div id="assignments-section" class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-3 sm:px-4 lg:px-5 py-3 sm:py-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900">User Assignments</h3>
                                <p class="mt-0.5 text-xs text-gray-600 hidden sm:block">Assign users to workplaces</p>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1 sm:flex-none">
                                    <input type="text" id="assignmentSearch" placeholder="Search..."
                                        class="w-full sm:w-auto pl-8 pr-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                </div>
                                <button onclick="openAssignmentModal()"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                                    <i class="fas fa-user-plus sm:mr-2"></i>
                                    <span class="hidden sm:inline">Assign</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Workplaces</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Primary
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="assignments-tbody">
                                @foreach ($users as $user)
                                    <tr class="hover:bg-gray-50 transition-colors assignment-row">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div
                                                        class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                                        <span
                                                            class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-semibold text-gray-900 user-name">
                                                        {{ $user->name }}</div>
                                                    <div class="text-xs text-gray-600 user-email">{{ $user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($user->workplaces->count() > 0)
                                                <div class="group relative">
                                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 cursor-help">
                                                        <i class="fas fa-building mr-1.5"></i>
                                                        {{ $user->workplaces->count() }} workplace{{ $user->workplaces->count() > 1 ? 's' : '' }}
                                                        @if($user->workplaces->where('pivot.is_primary', true)->count() > 0)
                                                            <i class="fas fa-star ml-1.5 text-yellow-500" title="Has primary"></i>
                                                        @endif
                                                    </span>
                                                    <!-- Tooltip on hover -->
                                                    <div class="hidden group-hover:block absolute z-50 w-64 p-2 bg-gray-900 text-white text-xs rounded-lg shadow-xl -top-2 left-0 transform -translate-y-full">
                                                        <div class="space-y-1">
                                                            @foreach($user->workplaces as $workplace)
                                                                <div class="flex items-center gap-1">
                                                                    <i class="fas fa-building text-green-400"></i>
                                                                    <span>{{ $workplace->name }}</span>
                                                                    @if($workplace->pivot->is_primary)
                                                                        <i class="fas fa-star text-yellow-400" title="Primary"></i>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="absolute bottom-0 left-4 transform translate-y-1/2 rotate-45 w-2 h-2 bg-gray-900"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs italic">None</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @php $primaryWorkplace = $user->primaryWorkplace(); @endphp
                                            @if ($primaryWorkplace)
                                                <div class="flex items-center">
                                                    <i class="fas fa-star text-yellow-500 mr-1.5"></i>
                                                    <span
                                                        class="text-xs font-medium text-gray-900">{{ $primaryWorkplace->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs italic">None</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <button onclick="manageUserWorkplaces({{ $user->id }})"
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200 transition-colors">
                                                <i class="fas fa-cog mr-1"></i>
                                                Manage
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($users as $user)
                            <div class="p-3 hover:bg-gray-50 transition-colors assignment-row">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                            <span
                                                class="text-white font-semibold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="mb-2">
                                            <h4 class="text-sm font-semibold text-gray-900 truncate user-name">
                                                {{ $user->name }}</h4>
                                            <p class="text-xs text-gray-600 truncate user-email">{{ $user->email }}
                                            </p>
                                        </div>

                                        @if($user->workplaces->count() > 0)
                                            <div class="mb-2 space-y-1">
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-building mr-1"></i>
                                                        {{ $user->workplaces->count() }} workplace{{ $user->workplaces->count() > 1 ? 's' : '' }}
                                                    </span>
                                                </div>
                                                @php $primaryWorkplace = $user->primaryWorkplace(); @endphp
                                                @if ($primaryWorkplace)
                                                    <div>
                                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-star mr-1"></i>
                                                            {{ Str::limit($primaryWorkplace->name, 25) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="mb-2">
                                                <span class="text-gray-400 text-xs italic">No workplaces assigned</span>
                                            </div>
                                        @endif

                                        <button onclick="manageUserWorkplaces({{ $user->id }})"
                                            class="w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-cog"></i>
                                            <span>Manage Workplaces</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-3 sm:px-4 py-3 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                            <div class="text-xs sm:text-sm text-gray-600">
                                <span class="font-semibold">{{ $users->count() }}</span> users
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    class="px-2 py-1 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button
                                    class="px-2 py-1 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-users text-blue-600 mr-3"></i>
                        User Management
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Manage system users and their permissions</p>
                </div>

                <!-- User Statistics -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">User Statistics</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                        <!-- Total Users Card -->
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-info rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-users text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">{{ $users->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Total Users</p>
                            </div>
                        </div>

                        <!-- Regular Users Card -->
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-success rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-user text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">{{ $users->where('role', 'user')->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Regular Users</p>
                            </div>
                        </div>

                        <!-- Administrators Card -->
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-danger rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-user-shield text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">{{ $users->where('role', 'admin')->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Administrators</p>
                            </div>
                        </div>

                        <!-- Active Today Card -->
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-warning rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-user-check text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">{{ $users->filter(function ($user) {return $user->last_activity && $user->last_activity->isToday();})->count() }}</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Active Today</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 lg:gap-4">
                        <!-- View All Users -->
                        <button onclick="scrollToAllUsers()" class="action-card gradient-success rounded-xl p-4 lg:p-5 text-left transition-all duration-200 hover:shadow-lg group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-users text-white text-base lg:text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm lg:text-base font-bold text-white mb-0.5">View All Users</h3>
                                    <p class="text-xs text-white/90">Browse and manage all users</p>
                                </div>
                            </div>
                        </button>

                        <!-- Search Users -->
                        <button onclick="scrollToAllUsers(); setTimeout(() => { document.getElementById('userSearchMain').focus(); animateSearchBar(); }, 300);" class="action-card gradient-info rounded-xl p-4 lg:p-5 text-left transition-all duration-200 hover:shadow-lg group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-search text-white text-base lg:text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm lg:text-base font-bold text-white mb-0.5">Search Users</h3>
                                    <p class="text-xs text-white/90">Find and manage users</p>
                                </div>
                            </div>
                        </button>

                        <!-- Bulk Operations -->
                        <button onclick="openBulkOperationsModal()" class=" action-card bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-4 lg:p-5 text-left transition-all duration-200 hover:shadow-lg group relative">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-tasks text-white text-base lg:text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm lg:text-base font-bold text-white mb-0.5">Bulk Operations</h3>
                                    <p class="text-xs text-white/90">Mass user management</p>
                                </div>
                            </div>
                            <!-- Selection count badge -->
                            <span id="bulkSelectionBadge" style="display: none;" class="absolute -top-2 -right-2 bg-white text-purple-600 text-xs font-bold rounded-full h-6 w-6 lg:h-7 lg:w-7 flex items-center justify-center shadow-lg">0</span>
                        </button>
                    </div>
                </div>

                <!-- Employee Location Map & Activity -->
                <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-6">
                    <!-- Header -->
                    <div class="px-4 py-4 bg-gray-600">
                        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-map-marked-alt text-white text-xl"></i>
                                <h3 class="text-lg font-bold text-white">Employee Checked In Locations</h3>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 lg:gap-3">
                                <div class="flex items-center gap-2 text-xs text-gray-300">
                                    <span class="hidden sm:inline">Status:</span>
                                    <select id="statusFilter" class="bg-gray-700 text-white border-gray-600 rounded px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500" onchange="filterEmployeeLocations()">
                                        <option value="all">All Users</option>
                                        <option value="checked_in">Checked In</option>
                                        <option value="checked_out">Checked Out</option>
                                        <option value="no_activity">No Activity</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <input type="text" id="employeeLocationSearch" placeholder="Find User..." onkeyup="filterEmployeeLocations()"
                                        class="bg-gray-700 text-white placeholder-gray-400 border-gray-600 rounded-lg pl-8 pr-3 py-1.5 text-xs w-32 sm:w-auto focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                </div>
                                <button onclick="refreshEmployeeMap()" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors" title="Refresh">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="hidden sm:inline ml-1">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="relative">
                        <div id="employeeMap" class="w-full h-96 bg-gray-900">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <i class="fas fa-map-marker-alt text-gray-600 text-6xl mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-400 mb-2">Employee Location Map</h3>
                                    <p class="text-gray-500 mb-4">Interactive map showing checked in/out employee locations</p>
                                    <button onclick="initializeMap()"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-play mr-2"></i>
                                        Initialize Map
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity Log Table -->
                <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-6">
                    <div class="px-4 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">User Activity Log</h3>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="employeeLocationTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Check-in Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="employeeLocationTableBody">
                                @foreach ($users as $user)
                                    @if(isset($latestAttendance[$user->id]) && isset($latestAttendance[$user->id]['action']))
                                    <tr class="hover:bg-gray-50 transition-colors employee-location-row" 
                                        data-user-name="{{ strtolower($user->name) }}" 
                                        data-user-email="{{ strtolower($user->email) }}" 
                                        data-user-id="{{ $user->id }}" 
                                        data-action="{{ $latestAttendance[$user->id]['action'] }}" 
                                        data-timestamp="{{ $latestAttendance[$user->id]['timestamp'] ?? '' }}">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $action = $latestAttendance[$user->id]['action'];
                                                $statusClass = 'bg-gray-100 text-gray-700';
                                                $statusText = 'Unknown';
                                                
                                                if ($action === 'check_in' || $action === 'break_end') {
                                                    $statusClass = 'bg-green-100 text-green-700';
                                                    $statusText = 'Checked In';
                                                } elseif ($action === 'check_out') {
                                                    $statusClass = 'bg-red-100 text-red-700';
                                                    $statusText = 'Checked Out';
                                                } elseif ($action === 'break_start') {
                                                    $statusClass = 'bg-yellow-100 text-yellow-700';
                                                    $statusText = 'On Break';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md {{ $statusClass }}">
                                                <i class="fas fa-circle text-xs"></i>
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900">
                                                @if (isset($latestAttendance[$user->id]) && isset($latestAttendance[$user->id]['timestamp']))
                                                    {{ \Carbon\Carbon::parse($latestAttendance[$user->id]['timestamp'])->format('h:i A') }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                @if (isset($latestAttendance[$user->id]) && isset($latestAttendance[$user->id]['timestamp']))
                                                    {{ \Carbon\Carbon::parse($latestAttendance[$user->id]['timestamp'])->diffForHumans() }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs text-gray-700 max-w-xs">
                                                @if (isset($latestAttendance[$user->id]))
                                                    @php
                                                        $addressDisplay = 'Location not available';
                                                        if ($latestAttendance[$user->id]['workplace_address']) {
                                                            $addressDisplay = $latestAttendance[$user->id]['workplace_address'];
                                                        } elseif ($latestAttendance[$user->id]['address']) {
                                                            $addressDisplay = $latestAttendance[$user->id]['address'];
                                                        } elseif (isset($latestAttendance[$user->id]['latitude']) && isset($latestAttendance[$user->id]['longitude'])) {
                                                            $addressDisplay = 'Lat: ' . number_format($latestAttendance[$user->id]['latitude'], 6) . ', Long: ' . number_format($latestAttendance[$user->id]['longitude'], 6);
                                                        }
                                                    @endphp
                                                    <div class="flex items-start gap-1.5">
                                                        <i class="fas fa-map-marker-alt text-red-500 mt-0.5"></i>
                                                        <div class="flex-1">
                                                            <div class="line-clamp-2">{{ $addressDisplay }}</div>
                                                            @if($latestAttendance[$user->id]['workplace_name'])
                                                                <div class="text-xs text-gray-500 mt-0.5">
                                                                    <i class="fas fa-building mr-1"></i>{{ $latestAttendance[$user->id]['workplace_name'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">Unknown</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $device = $latestAttendance[$user->id]['device'] ?? 'Unknown';
                                                $deviceIcon = 'fa-question-circle';
                                                if (stripos($device, 'mobile') !== false || stripos($device, 'android') !== false || stripos($device, 'iphone') !== false) {
                                                    $deviceIcon = 'fa-mobile-alt';
                                                } elseif (stripos($device, 'desktop') !== false || stripos($device, 'windows') !== false || stripos($device, 'mac') !== false || stripos($device, 'linux') !== false) {
                                                    $deviceIcon = 'fa-desktop';
                                                } elseif (stripos($device, 'tablet') !== false || stripos($device, 'ipad') !== false) {
                                                    $deviceIcon = 'fa-tablet-alt';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                                <i class="fas {{ $deviceIcon }}"></i>
                                                {{ ucfirst($device) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button onclick="showUserLocationDetails({{ $user->id }})" class="p-2 text-green-600 hover:text-gray-600 transition-colors" title="History">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($users as $user)
                            @if(isset($latestAttendance[$user->id]) && isset($latestAttendance[$user->id]['action']))
                            <div class="p-4 hover:bg-gray-50 transition-colors employee-location-row" 
                                 data-user-name="{{ strtolower($user->name) }}" 
                                 data-user-email="{{ strtolower($user->email) }}" 
                                 data-user-id="{{ $user->id }}" 
                                 data-action="{{ $latestAttendance[$user->id]['action'] }}" 
                                 data-timestamp="{{ $latestAttendance[$user->id]['timestamp'] ?? '' }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow flex-shrink-0">
                                            <span class="text-white font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                    <button onclick="showUserLocationDetails({{ $user->id }})" class="ml-2 p-2 text-gray-400 hover:text-gray-600 rounded transition-colors">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $action = $latestAttendance[$user->id]['action'];
                                            $statusClass = 'bg-gray-100 text-gray-700';
                                            $statusText = 'Unknown';
                                            
                                            if ($action === 'check_in' || $action === 'break_end') {
                                                $statusClass = 'bg-green-100 text-green-700';
                                                $statusText = 'Checked In';
                                            } elseif ($action === 'check_out') {
                                                $statusClass = 'bg-red-100 text-red-700';
                                                $statusText = 'Checked Out';
                                            } elseif ($action === 'break_start') {
                                                $statusClass = 'bg-yellow-100 text-yellow-700';
                                                $statusText = 'On Break';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md {{ $statusClass }}">
                                            <i class="fas fa-circle text-xs"></i>
                                            {{ $statusText }}
                                        </span>
                                    </div>

                                    @if(isset($latestAttendance[$user->id]['timestamp']))
                                    <div class="text-xs">
                                        <div class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($latestAttendance[$user->id]['timestamp'])->format('h:i A') }}</div>
                                        <div class="text-gray-500">{{ \Carbon\Carbon::parse($latestAttendance[$user->id]['timestamp'])->diffForHumans() }}</div>
                                    </div>
                                    @endif

                                    @php
                                        $addressDisplay = 'Location not available';
                                        if ($latestAttendance[$user->id]['workplace_address']) {
                                            $addressDisplay = $latestAttendance[$user->id]['workplace_address'];
                                        } elseif ($latestAttendance[$user->id]['address']) {
                                            $addressDisplay = $latestAttendance[$user->id]['address'];
                                        } elseif (isset($latestAttendance[$user->id]['latitude']) && isset($latestAttendance[$user->id]['longitude'])) {
                                            $addressDisplay = 'Lat: ' . number_format($latestAttendance[$user->id]['latitude'], 6) . ', Long: ' . number_format($latestAttendance[$user->id]['longitude'], 6);
                                        }
                                    @endphp
                                    <div class="flex items-start gap-1.5 text-xs text-gray-700">
                                        <i class="fas fa-map-marker-alt text-red-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <div class="line-clamp-2">{{ $addressDisplay }}</div>
                                            @if($latestAttendance[$user->id]['workplace_name'])
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    <i class="fas fa-building mr-1"></i>{{ $latestAttendance[$user->id]['workplace_name'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 pt-1">
                                        @php
                                            $device = $latestAttendance[$user->id]['device'] ?? 'Unknown';
                                            $deviceIcon = 'fa-question-circle';
                                            if (stripos($device, 'mobile') !== false || stripos($device, 'android') !== false || stripos($device, 'iphone') !== false) {
                                                $deviceIcon = 'fa-mobile-alt';
                                            } elseif (stripos($device, 'desktop') !== false || stripos($device, 'windows') !== false || stripos($device, 'mac') !== false || stripos($device, 'linux') !== false) {
                                                $deviceIcon = 'fa-desktop';
                                            } elseif (stripos($device, 'tablet') !== false || stripos($device, 'ipad') !== false) {
                                                $deviceIcon = 'fa-tablet-alt';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                            <i class="fas {{ $deviceIcon }}"></i>
                                            {{ ucfirst($device) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                            <div id="activity-pagination-info" class="text-xs sm:text-sm text-gray-600"></div>
                            <div id="activity-pagination-controls" class="flex items-center space-x-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Full Users Table -->
                <div id="all-users-table" class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-3 sm:px-4 lg:px-5 py-3 sm:py-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div>
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">All Users</h3>
                                <p class="text-xs text-gray-600 mt-0.5 hidden sm:block">Manage all users in the system</p>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1 sm:flex-none">
                                    <input type="text" id="userSearchMain" placeholder="Search..."
                                        class="w-full sm:w-auto pl-8 pr-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                                    <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                </div>
                                <button onclick="addUser()"
                                    class="px-3 py-1.5 bg-indigo-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors whitespace-nowrap">
                                    <i class="fas fa-plus sm:mr-2"></i>
                                    <span class="hidden sm:inline">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="usersTableMain">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <input type="checkbox" id="selectAllMain" class="rounded border-gray-300">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Online Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workplaces</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="usersTableBodyMain">
                                @foreach ($users as $user)
                                    <tr class="hover:bg-gray-50 transition-colors user-row-main">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" class="user-checkbox-main rounded border-gray-300"
                                                value="{{ $user->id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-semibold text-gray-900 user-name">{{ $user->name }}</div>
                                                    <div class="text-xs text-gray-600 user-email">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                <i class="fas {{ $user->role === 'admin' ? 'fa-shield-alt' : 'fa-user' }} mr-1"></i>
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php $isOnline = $user->isOnline(); @endphp
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                <div class="w-2 h-2 {{ $isOnline ? 'bg-green-500' : 'bg-gray-500' }} rounded-full mr-2"></div>
                                                {{ $isOnline ? 'Online' : 'Offline' }}
                                            </span>
                                            @if ($isOnline && $user->last_activity)
                                                <div class="text-xs text-gray-500 mt-1">Last active: {{ $user->last_activity->diffForHumans() }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($user->workplaces->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($user->workplaces->take(2) as $workplace)
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                            <i class="fas fa-building mr-1"></i>
                                                            {{ $workplace->name }}{{ $workplace->pivot->is_primary ? ' (Primary)' : '' }}
                                                        </span>
                                                    @endforeach
                                                    @if ($user->workplaces->count() > 2)
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700" title="{{ $user->workplaces->skip(2)->pluck('name')->join(', ') }}">
                                                            <i class="fas fa-plus-circle mr-1"></i>
                                                            {{ $user->workplaces->count() - 2 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 italic">No assignments</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs text-gray-600">
                                                {{ $user->created_at->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5">
                                                <button onclick="viewUser({{ $user->id }})" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editUser({{ $user->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="resetUserPassword({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                @if ($user->id !== auth()->id())
                                                    <button onclick="deleteUser({{ $user->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" id="usersCardsMain">
                        @foreach ($users as $user)
                            <div class="p-3 hover:bg-gray-50 transition-colors user-row-main">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow">
                                            <span class="text-white font-semibold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900 truncate user-name">{{ $user->name }}</h4>
                                                <p class="text-xs text-gray-600 truncate user-email">{{ $user->email }}</p>
                                            </div>
                                            <input type="checkbox" class="user-checkbox-main rounded border-gray-300 ml-2" value="{{ $user->id }}">
                                        </div>

                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                <i class="fas {{ $user->role === 'admin' ? 'fa-shield-alt' : 'fa-user' }} mr-1"></i>
                                                {{ ucfirst($user->role) }}
                                            </span>
                                            @php $isOnline = $user->isOnline(); @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                <div class="w-1.5 h-1.5 {{ $isOnline ? 'bg-green-500' : 'bg-gray-500' }} rounded-full mr-1"></div>
                                                {{ $isOnline ? 'Online' : 'Offline' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                                            <div class="flex items-center gap-2">
                                                @if ($user->workplaces->count() > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        <i class="fas fa-building mr-1"></i>{{ $user->workplaces->count() }}
                                                    </span>
                                                @endif
                                                <span>Joined {{ $user->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <button onclick="viewUser({{ $user->id }})" 
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded-lg hover:bg-green-200 transition-colors">
                                                <i class="fas fa-eye"></i><span>View</span>
                                            </button>
                                            <button onclick="editUser({{ $user->id }})" 
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-lg hover:bg-indigo-200 transition-colors">
                                                <i class="fas fa-edit"></i><span>Edit</span>
                                            </button>
                                            <button onclick="resetUserPassword({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')" 
                                                class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-1.5 bg-orange-100 text-orange-700 text-xs font-medium rounded-lg hover:bg-orange-200 transition-colors">
                                                <i class="fas fa-key"></i><span>Reset</span>
                                            </button>
                                            @if ($user->id !== auth()->id())
                                                <button onclick="deleteUser({{ $user->id }})" 
                                                    class="inline-flex items-center justify-center p-1.5 bg-red-100 text-red-700 text-xs rounded-lg hover:bg-red-200 transition-colors" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-3 sm:px-4 py-3 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                            <div id="user-pagination-info" class="text-xs sm:text-sm text-gray-600"></div>
                            <div id="user-pagination-controls" class="flex items-center space-x-1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Section -->
            <div id="attendance-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-clock text-blue-600 mr-3"></i>
                        Attendance Management
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Monitor and analyze real-time attendance data. Late time threshold: 9:00 AM</p>
                </div>

                <!-- Attendance Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Today's Statistics</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-success rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-check text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="stat-checkins">
                                    <i class="fas fa-spinner fa-spin text-base"></i>
                                </h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Check-ins</p>
                                <p class="text-xs text-green-600 mt-1">Active today</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-info rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-clock text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="stat-avg-hours">
                                    <i class="fas fa-spinner fa-spin text-base"></i>
                                </h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Avg Hours</p>
                                <p class="text-xs text-blue-600 mt-1">Per employee</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-danger rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-exclamation-triangle text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="stat-late">
                                    <i class="fas fa-spinner fa-spin text-base"></i>
                                </h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Late Arrivals</p>
                                <p class="text-xs text-red-600 mt-1">After 9:00 AM</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-warning rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-coffee text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="stat-on-break">
                                    <i class="fas fa-spinner fa-spin text-base"></i>
                                </h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">On Break</p>
                                <p class="text-xs text-yellow-600 mt-1">Currently</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions for Attendance -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Quick Actions</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-2 gap-3 lg:gap-6">
                        <button onclick="openAttendanceLogsModal()"
                            class="action-card bg-gradient-to-br from-orange-500 to-red-600 text-white p-4 lg:p-6 rounded-xl shadow-md cursor-pointer">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-list-alt text-xl lg:text-2xl mb-2 lg:mb-3 opacity-90"></i>
                                <h3 class="text-sm lg:text-base font-bold mb-1">Check-In Logs</h3>
                                <p class="text-xs opacity-90 leading-tight hidden lg:block">View assigned & off-site check-ins</p>
                            </div>
                        </button>

                        <button onclick="switchAdminSection('reports')"
                            class="action-card bg-gradient-to-br from-purple-500 to-indigo-600 text-white p-4 lg:p-6 rounded-xl shadow-md cursor-pointer">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <i class="fas fa-chart-bar text-xl lg:text-2xl mb-2 lg:mb-3 opacity-90"></i>
                                <h3 class="text-sm lg:text-base font-bold mb-1">View Reports</h3>
                                <p class="text-xs opacity-90 leading-tight hidden lg:block">Generate attendance reports</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="card-modern rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div>
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">Today's Attendance</h3>
                                <p class="text-xs text-gray-600 mt-0.5 hidden sm:block">Real-time monitoring of employee attendance</p>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1 sm:flex-none">
                                    <input type="text" id="attendanceSearch" placeholder="Search employee..."
                                        class="w-full sm:w-auto pl-8 pr-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                </div>
                                <button onclick="refreshAttendanceData()"
                                    class="px-3 py-1.5 gradient-info text-white text-xs sm:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern whitespace-nowrap">
                                    <i class="fas fa-sync-alt sm:mr-2"></i><span class="hidden sm:inline">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="attendanceTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Break Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Work Hours</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workplace</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="attendanceTableBody">
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-3"></i>
                                            <p class="text-sm font-medium">Loading attendance data...</p>
                                            <p class="text-xs text-gray-400 mt-1">Please wait</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" id="attendanceCards">
                        <div class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-3"></i>
                                <p class="text-sm font-medium">Loading attendance data...</p>
                                <p class="text-xs text-gray-400 mt-1">Please wait</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="text-xs sm:text-sm text-gray-700">
                                Showing <span class="font-semibold" id="attendance-count">0</span> employee(s)
                            </div>
                            <div class="text-xs text-gray-500">
                                Last updated: <span class="font-medium" id="last-updated">Never</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div id="reports-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-chart-bar text-purple-600 mr-3"></i>
                        Reports & Analytics
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Generate comprehensive attendance reports and insights</p>
                </div>

                <!-- Report Statistics -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Report Statistics</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-info rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-clipboard-list text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="report-total-records">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Total Records</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-success rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-check-circle text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="report-present-count">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Present</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 gradient-warning rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-clock text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="report-late-count">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Late Arrivals</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-map-marker-alt text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="report-non-assigned-count">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Non-Assigned</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div
                                    class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-chart-line text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="report-attendance-rate">0%</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Attendance Rate</p>
                                <p class="text-xs text-purple-600 mt-1" id="attendance-rate-formula">calculating...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="card-modern rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">
                            <i class="fas fa-filter mr-2 text-purple-600"></i>Report Filters
                        </h3>
                    </div>
                    <div class="p-3 sm:p-4 lg:p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-3">
                        <!-- Report Type -->
                        <div class="col-span-2 sm:col-span-1">
                            <label
                                class="block text-xs sm:text-sm lg:text-xs font-medium text-gray-700 mb-1 sm:mb-2 lg:mb-1">Report
                                Type</label>
                            <select id="reportType"
                                class="w-full px-2 sm:px-3 lg:px-3 py-1.5 sm:py-2 text-sm sm:text-base lg:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label
                                class="block text-xs sm:text-sm lg:text-xs font-medium text-gray-700 mb-1 sm:mb-2 lg:mb-1">Start
                                Date</label>
                            <input type="date" id="reportStartDate"
                                class="w-full px-2 sm:px-3 lg:px-3 py-1.5 sm:py-2 text-sm sm:text-base lg:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <!-- End Date -->
                        <div>
                            <label
                                class="block text-xs sm:text-sm lg:text-xs font-medium text-gray-700 mb-1 sm:mb-2 lg:mb-1">End
                                Date</label>
                            <input type="date" id="reportEndDate"
                                class="w-full px-2 sm:px-3 lg:px-3 py-1.5 sm:py-2 text-sm sm:text-base lg:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <!-- Employee Filter -->
                        <div class="relative col-span-2 sm:col-span-1">
                            <label
                                class="block text-xs sm:text-sm lg:text-xs font-medium text-gray-700 mb-1 sm:mb-2 lg:mb-1">Employee</label>
                            <input type="text" id="reportUserSearch" placeholder="Search employee..."
                                autocomplete="off"
                                class="w-full px-2 sm:px-3 lg:px-3 py-1.5 sm:py-2 text-sm sm:text-base lg:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <input type="hidden" id="reportUserFilter" value="">

                            <!-- Dropdown results -->
                            <div id="reportUserResults"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                <!-- Results will be populated here -->
                            </div>
                        </div>

                        <!-- Workplace Filter -->
                        <div class="col-span-2 sm:col-span-1">
                            <label
                                class="block text-xs sm:text-sm lg:text-xs font-medium text-gray-700 mb-1 sm:mb-2 lg:mb-1">Workplace</label>
                            <select id="reportWorkplaceFilter"
                                class="w-full px-2 sm:px-3 lg:px-3 py-1.5 sm:py-2 text-sm sm:text-base lg:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">All Workplaces</option>
                                @foreach ($workplaces as $workplace)
                                    <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3 mt-4">
                            <button onclick="generateAttendanceReport()"
                                class="col-span-2 lg:col-span-1 px-3 lg:px-4 py-2 lg:py-2.5 bg-gradient-to-br from-purple-500 to-indigo-600 text-white text-xs lg:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern">
                                <i class="fas fa-play mr-1 lg:mr-2"></i><span class="hidden sm:inline">Generate </span>Report
                            </button>
                            <button onclick="exportReport('csv')"
                                class="px-3 lg:px-4 py-2 lg:py-2.5 gradient-success text-white text-xs lg:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern">
                                <i class="fas fa-file-csv mr-1 lg:mr-2"></i><span class="hidden sm:inline">Export </span>CSV
                            </button>
                            <button onclick="exportReport('excel')"
                                class="px-3 lg:px-4 py-2 lg:py-2.5 bg-gradient-to-br from-emerald-500 to-green-600 text-white text-xs lg:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern">
                                <i class="fas fa-file-excel mr-1 lg:mr-2"></i><span class="hidden sm:inline">Export </span>Excel
                            </button>
                            <button onclick="resetReportFilters()"
                                class="px-3 lg:px-4 py-2 lg:py-2.5 bg-gray-600 text-white text-xs lg:text-sm font-semibold rounded-lg hover:bg-gray-700 transition-all">
                                <i class="fas fa-redo mr-1 lg:mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Report Results Table -->
                <div class="card-modern rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                            <div>
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">
                                    <i class="fas fa-table mr-2 text-purple-600"></i>Attendance Report
                                </h3>
                                <p class="text-xs text-gray-600 mt-0.5 hidden sm:block">
                                    Showing <span id="report-showing-count">0</span> records from
                                    <span id="report-date-range">-</span>
                                    (<span id="report-working-days-text">0 working days</span>)
                                </p>
                            </div>
                            <div class="relative flex-1 sm:flex-none">
                                <input type="text" id="reportTableSearch" placeholder="Search..."
                                    class="w-full sm:w-auto pl-8 pr-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workplace</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours Worked</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Late</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-sm font-medium">No data available</p>
                                            <p class="text-xs text-gray-400 mt-1">Please select filters and generate a report</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" id="reportCardsContainer">
                        <div class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm font-medium">No data available</p>
                                <p class="text-xs text-gray-400 mt-1">Please select filters and generate a report</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Requests Section -->
            <div id="absence-requests-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-calendar-alt text-orange-600 mr-3"></i>
                        Leave Requests Management
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Review and manage employee leave requests</p>
                </div>

                <!-- Statistics -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Request Statistics</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-inbox text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="total-requests-count">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Total Requests</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-warning rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-clock text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="pending-requests-stat">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Pending</p>
                                <p class="text-xs text-yellow-600 mt-1">Need review</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-success rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-check-circle text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="approved-requests-stat">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Approved</p>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-4 lg:p-6 shadow-sm stat-card">
                            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 gradient-danger rounded-lg flex items-center justify-center mb-2 lg:mb-3 shadow-md">
                                    <i class="fas fa-times-circle text-white text-sm lg:text-lg"></i>
                                </div>
                                <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5" id="rejected-requests-stat">0</h3>
                                <p class="text-xs lg:text-sm text-gray-600 font-semibold">Rejected</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Requests Table -->
                <div class="card-modern rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-3 sm:p-4 lg:p-5 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">Leave Requests</h3>
                                <p class="text-xs text-gray-600 mt-0.5 hidden sm:block">Review and manage all leave requests</p>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1 sm:flex-none">
                                    <input type="text" id="absenceRequestSearch" placeholder="Search..."
                                        class="w-full sm:w-auto pl-8 pr-3 py-1.5 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                                </div>
                                <button onclick="fetchAdminAbsenceRequests()"
                                    class="px-3 py-1.5 bg-gradient-to-br from-orange-500 to-amber-600 text-white text-xs sm:text-sm font-semibold rounded-lg hover:shadow-md transition-all btn-modern whitespace-nowrap">
                                    <i class="fas fa-sync-alt sm:mr-2"></i><span class="hidden sm:inline">Refresh</span>
                                </button>
                            </div>
                        </div>

                        <!-- Filter Tabs -->
                        <div class="flex flex-wrap gap-2">
                            <button onclick="filterAbsenceRequests('all')"
                                class="absence-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-sm"
                                data-status="all">
                                All
                            </button>
                            <button onclick="filterAbsenceRequests('pending')"
                                class="absence-filter-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                data-status="pending">
                                Pending <span id="pending-count-tab"
                                    class="ml-1 bg-red-500 text-white px-1.5 py-0.5 rounded-full text-xs font-semibold">0</span>
                            </button>
                            <button onclick="filterAbsenceRequests('approved')"
                                class="absence-filter-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                data-status="approved">
                                Approved
                            </button>
                            <button onclick="filterAbsenceRequests('rejected')"
                                class="absence-filter-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                data-status="rejected">
                                Rejected
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="absenceRequestsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="absenceRequestsTableBody">
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-spinner fa-spin text-3xl text-orange-500 mb-3"></i>
                                            <p class="text-sm font-medium">Loading leave requests...</p>
                                            <p class="text-xs text-gray-400 mt-1">Please wait</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" id="absenceRequestsCards">
                        <div class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-spinner fa-spin text-3xl text-orange-500 mb-3"></i>
                                <p class="text-sm font-medium">Loading leave requests...</p>
                                <p class="text-xs text-gray-400 mt-1">Please wait</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="text-xs sm:text-sm text-gray-700">
                                Showing <span class="font-semibold" id="requests-count">0</span> request(s)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-cog text-gray-600 mr-3"></i>
                        System Settings
                    </h2>
                    <p class="text-sm lg:text-base text-gray-600">Configure system preferences and security settings</p>
                </div>

                <!-- Settings Categories -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Security & Administration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 lg:gap-6">
                        <div class="card-modern rounded-xl p-6 shadow-sm">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 gradient-danger rounded-lg flex items-center justify-center shadow-md mr-3">
                                    <i class="fas fa-user-shield text-white text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-gray-900">Admin Account</h3>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold text-red-600 bg-red-100 rounded">DANGER ZONE</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Modify admin account credentials and information</p>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                                    <div class="text-xs text-red-700">
                                        <strong>Warning:</strong> Requires password and security phrase for confirmation.
                                    </div>
                                </div>
                            </div>
                            <button
                                class="w-full gradient-danger text-white py-2 px-4 rounded-lg hover:shadow-md transition-all text-sm font-semibold btn-modern"
                                onclick="openAdminAccountModal()">
                                <i class="fas fa-user-cog mr-2"></i>Modify Admin Account
                            </button>
                        </div>

                        <div class="card-modern rounded-xl p-6 shadow-sm">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-md mr-3">
                                    <i class="fas fa-history text-white text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-gray-900">Activity Logs</h3>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Monitor all admin actions and system activities</p>
                            <div class="space-y-3">
                                <div class="text-xs text-gray-700 bg-purple-50 rounded-lg p-3">
                                    Track all administrative actions including user management, workplace modifications, and
                                    system changes.
                                </div>
                                <button
                                    class="w-full bg-gradient-to-br from-purple-500 to-indigo-600 text-white py-2 px-4 rounded-lg hover:shadow-md transition-all text-sm font-semibold btn-modern"
                                    onclick="openActivityLogsModal()">
                                    <i class="fas fa-list mr-2"></i>View Activity Logs
                                </button>
                            </div>
                        </div>

                        <div class="card-modern rounded-xl p-6 shadow-sm">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 gradient-info rounded-lg flex items-center justify-center shadow-md mr-3">
                                    <i class="fas fa-key text-white text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-gray-900">Manual Entry Code</h3>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold text-blue-600 bg-blue-100 rounded">SECURITY</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Manage the access code for manual location entry feature</p>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                    <div class="text-xs text-blue-700">
                                        This code is required to access manual GPS location entry. Only share with
                                        authorized personnel.
                                    </div>
                                </div>

                                <div class="flex items-start mt-2">
                                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                                    <div class="text-xs text-red-700">
                                        Update the code after giving it to authorized personnel to prevent multiple
                                        unauthorized access.
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 bg-blue-50 rounded-lg p-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700 font-medium">Current Code:</span>
                                    <span id="currentManualEntryCode"
                                        class="font-mono font-bold text-gray-900">••••••••</span>
                                </div>
                            </div>
                            <button
                                class="w-full gradient-info text-white py-2 px-4 rounded-lg hover:shadow-md transition-all text-sm font-semibold btn-modern"
                                onclick="openManualEntryCodeModal()">
                                <i class="fas fa-edit mr-2"></i>Update Access Code
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Auto Check-Out & Notification Settings -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">Auto Check-Out & Notifications</h3>
                    <div class="card-modern rounded-xl p-6 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 gradient-success rounded-lg flex items-center justify-center shadow-md mr-3">
                                <i class="fas fa-bell text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-gray-900">Notification Settings</h3>
                                <p class="text-xs text-gray-600 mt-1">Configure auto check-out reminders and notification methods</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                <div class="text-xs text-blue-700">
                                    <strong>Auto Check-Out:</strong> System automatically checks out users at 6:00 PM. Reminders are sent at 4:30 PM.
                                </div>
                            </div>
                        </div>

                        <form id="notificationSettingsForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-paper-plane mr-1 text-indigo-600"></i>
                                    Notification Method
                                </label>
                                <select id="notificationType" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                    <option value="email">Email Only</option>
                                    <option value="sms">SMS Only</option>
                                    <option value="both">Both Email & SMS</option>
                                    <option value="none">None (Disable Notifications)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Choose how users will receive check-out reminders</p>
                            </div>

                            <div id="smsApiUrlField">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-link mr-1 text-green-600"></i>
                                    SMS API URL
                                </label>
                                <input type="url" id="smsApiUrl" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    placeholder="https://sms.example.com/api/send">
                                <p class="text-xs text-gray-500 mt-1">
                                    Must be a complete URL starting with http:// or https://
                                    <br>
                                    Example: <code class="text-green-600 bg-green-50 px-1 rounded">https://sms.cisdepedcavite.org/send-sms.php</code>
                                </p>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">
                                    <i class="fas fa-clock mr-1"></i>Schedule Information
                                </h4>
                                <div class="space-y-2 text-xs text-gray-700">
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center">
                                            <i class="fas fa-bell text-yellow-500 mr-2"></i>Reminder Time:
                                        </span>
                                        <span class="font-semibold">4:30 PM</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center">
                                            <i class="fas fa-sign-out-alt text-red-500 mr-2"></i>Auto Check-Out:
                                        </span>
                                        <span class="font-semibold">6:00 PM</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center">
                                            <i class="fas fa-sync-alt text-blue-500 mr-2"></i>Cron Frequency:
                                        </span>
                                        <span class="font-semibold">Every 15 minutes</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" onclick="saveNotificationSettings()"
                                    class="flex-1 gradient-success text-white py-2.5 px-4 rounded-lg hover:shadow-md transition-all text-sm font-semibold btn-modern">
                                    <i class="fas fa-save mr-2"></i>Save Settings
                                </button>
                                <button type="button" onclick="testNotification()"
                                    class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all text-sm font-semibold">
                                    <i class="fas fa-vial mr-2"></i>Test
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Workplace Modal -->
    <div id="workplaceModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 p-2 sm:p-4">
        <div class="relative top-2 sm:top-4 md:top-20 mx-auto p-0 border-0 w-full max-w-md shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-3 sm:px-4 md:px-5 py-2 sm:py-3 md:py-3.5 border-b border-white border-opacity-20">
                    <h3 class="text-sm sm:text-base md:text-base font-semibold text-black mb-0" id="modalTitle">Add
                        New Workplace</h3>
                </div>
                <div class="px-3 sm:px-4 md:px-5 py-2 sm:py-3 md:py-3.5">
                    <form id="workplaceForm">
                        <input type="hidden" id="workplaceId">
                        <div class="mb-2 sm:mb-3 md:mb-3">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-1.5">Name</label>
                            <input type="text" id="workplaceName"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                required>
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-3">
                            <label
                                class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-1.5">Address</label>
                            <textarea id="workplaceAddress"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                rows="2" required></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3 md:gap-3 mb-2 sm:mb-3 md:mb-3">
                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-1.5">Latitude</label>
                                <input type="number" id="workplaceLatitude" step="any"
                                    class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                    required>
                            </div>
                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-1.5">Longitude</label>
                                <input type="number" id="workplaceLongitude" step="any"
                                    class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                    required>
                            </div>
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-3">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-1.5">Radius
                                (meters)</label>
                            <input type="number" id="workplaceRadius"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                required>
                        </div>
                        <div class="mb-3 sm:mb-4 md:mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="workplaceActive"
                                    class="rounded border-2 border-gray-300 text-indigo-600 focus:ring-indigo-500 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm">
                                <span class="ml-2 text-xs sm:text-sm text-black">Active</span>
                            </label>
                        </div>
                </div>
                <div
                    class="px-3 sm:px-4 md:px-5 py-2 sm:py-3 md:py-3 border-t border-white border-opacity-20 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" onclick="closeWorkplaceModal()"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-indigo-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 p-2 sm:p-4">
        <div class="relative top-2 sm:top-4 md:top-20 mx-auto p-0 border-0 w-full max-w-md shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4 border-b border-white border-opacity-20">
                    <h3 class="text-sm sm:text-base md:text-lg font-semibold text-black mb-0">Assign User to Workplace
                    </h3>
                </div>
                <div class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4">
                    <form id="assignmentForm">
                        <div class="mb-2 sm:mb-3 md:mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">User</label>
                            <select id="assignmentUser"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black"
                                required>
                                <option value="">Select a user...</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}
                                        ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-4">
                            <label
                                class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Workplace</label>
                            <select id="assignmentWorkplace"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black"
                                required>
                                <option value="">Select a workplace...</option>
                                @foreach ($workplaces as $workplace)
                                    <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Role</label>
                            <input type="text" id="assignmentRole" value="employee"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600">
                        </div>
                        <div class="mb-3 sm:mb-4 md:mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" id="assignmentPrimary"
                                    class="rounded border-2 border-gray-300 text-indigo-600 focus:ring-indigo-500 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm">
                                <span class="ml-2 text-xs sm:text-sm text-black">Set as Primary Workplace</span>
                            </label>
                        </div>
                </div>
                <div
                    class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4 border-t border-white border-opacity-20 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" onclick="closeAssignmentModal()"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-blue-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30">Assign</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div id="userModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 p-2 sm:p-4">
        <div class="relative top-2 sm:top-4 md:top-20 mx-auto p-0 border-0 w-full max-w-md shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4 border-b border-white border-opacity-20">
                    <h3 class="text-sm sm:text-base md:text-lg font-semibold text-black mb-0" id="userModalTitle">
                        Add New User</h3>
                </div>
                <div class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4">
                    <form id="userForm">
                        <input type="hidden" id="userId">
                        <div class="mb-2 sm:mb-3 md:mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Name</label>
                            <input type="text" id="userName"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                required>
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Email</label>
                            <input type="email" id="userEmail"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600"
                                required>
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-4" id="userPasswordField">
                            <label
                                class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Password</label>
                            <input type="password" id="userPassword"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600">
                        </div>
                        <div class="mb-2 sm:mb-3 md:mb-4" id="userPasswordConfirmField">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Confirm
                                Password</label>
                            <input type="password" id="userPasswordConfirm"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600">
                        </div>
                        <div class="mb-3 sm:mb-4 md:mb-6">
                            <label class="block text-xs sm:text-sm font-medium text-black mb-1 sm:mb-2">Role</label>
                            <select id="userRole"
                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black"
                                required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                </div>
                <div
                    class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 md:py-4 border-t border-white border-opacity-20 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" onclick="closeUserModal()"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit"
                        class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base bg-green-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Details Glass Modal -->
    <div id="userDetailsModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-black" id="userDetailsTitle">User Details</h3>
                        <button onclick="closeUserDetailsModal()"
                            class="text-black hover:text-gray-300 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-4 text-black">
                    <div class="space-y-4" id="userDetailsContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button onclick="closeUserDetailsModal()"
                        class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Location History Glass Modal -->
    <div id="locationHistoryModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[70]">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-4xl shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-black" id="locationHistoryTitle">Location History</h3>
                        <button onclick="closeLocationHistoryModal()"
                            class="text-black hover:text-gray-300 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div class="space-y-3" id="locationHistoryContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button onclick="closeLocationHistoryModal()"
                        class="px-4 py-1 bg-blue-400  bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-full hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Operations Modal -->
    <div id="bulkOperationsModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <h3 class="text-lg font-semibold text-black mb-0">Bulk Operations</h3>
                    <p class="text-sm text-gray-700 mt-1">Perform actions on multiple users</p>
                </div>
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="selectedCount">0</span> user(s) selected
                        </label>
                    </div>

                    <div class="space-y-3">
                        <!-- Bulk Send Password Reset -->
                        <div
                            class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-key text-blue-600 mr-2"></i>
                                Send Password Reset Email
                            </h4>
                            <p class="text-xs text-gray-700 mb-3">Send password reset links to selected users via
                                email</p>
                            <button onclick="executeBulkPasswordReset()"
                                class="w-full px-4 py-2 bg-blue-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30 text-sm">
                                <i class="fas fa-envelope mr-1"></i>
                                Send Reset Emails
                            </button>
                        </div>

                        <!-- Bulk Change Role -->
                        <div
                            class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-user-tag text-purple-600 mr-2"></i>
                                Change Role
                            </h4>
                            <select id="bulkRoleSelect"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white bg-opacity-80 text-black mb-2">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                            <button onclick="executeBulkRoleChange()"
                                class="w-full px-4 py-2 bg-purple-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30 text-sm">
                                <i class="fas fa-check mr-1"></i>
                                Update Role
                            </button>
                        </div>

                        <!-- Bulk Delete -->
                        <div
                            class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-trash-alt text-red-600 mr-2"></i>
                                Delete Users
                            </h4>
                            <p class="text-xs text-gray-700 mb-2">This action cannot be undone</p>
                            <button onclick="executeBulkDelete()"
                                class="w-full px-4 py-2 bg-red-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30 text-sm">
                                <i class="fas fa-trash mr-1"></i>
                                Delete Selected Users
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button type="button" onclick="closeBulkOperationsModal()"
                        class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Account Modal -->
    <div id="adminAccountModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20 bg-red-50 bg-opacity-50">
                    <h3 class="text-lg font-semibold text-black mb-0 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                        Modify Admin Account
                    </h3>
                    <p class="text-xs text-red-700 mt-1">DANGER ZONE - Requires double confirmation</p>
                </div>
                <div class="px-6 py-4">
                    <form id="adminAccountForm">
                        <div class="bg-red-100 border border-red-300 rounded-lg p-3 mb-4">
                            <p class="text-xs text-red-800">
                                <strong>Security Requirements:</strong><br>
                                1. Enter your current admin password<br>
                                2. Type the security phrase
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">
                                <i class="fas fa-lock mr-1"></i>Current Password *
                            </label>
                            <input type="password" id="adminCurrentPassword"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">
                                <i class="fas fa-shield-alt mr-1"></i>Security Phrase *
                            </label>
                            <input type="text"
                                id="adminSecurityPhrase"class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-black font-mono"
                                required>
                        </div>

                        <hr class="my-4 border-gray-400">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">New Name</label>
                            <input type="text" id="adminNewName" value="{{ Auth::user()->name }}"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">New Email</label>
                            <input type="email" id="adminNewEmail" value="{{ Auth::user()->email }}"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">New Password (leave blank to keep
                                current)</label>
                            <input type="password" id="adminNewPassword"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">Confirm New Password</label>
                            <input type="password" id="adminNewPasswordConfirm"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black">
                        </div>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end space-x-3">
                    <button type="button" onclick="closeAdminAccountModal()"
                        class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 bg-opacity-70 backdrop-filter backdrop-blur-sm text-white rounded-lg hover:bg-opacity-80 transition-all duration-200 border border-white border-opacity-30 font-semibold">
                        <i class="fas fa-save mr-1"></i>Update Admin Account
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manual Entry Code Modal -->
    <div id="manualEntryCodeModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20 bg-blue-50 bg-opacity-50">
                    <h3 class="text-lg font-semibold text-black mb-0 flex items-center">
                        <i class="fas fa-key text-blue-600 mr-2"></i>
                        Update Manual Entry Code
                    </h3>
                    <p class="text-xs text-blue-700 mt-1">Configure access code for manual location entry</p>
                </div>
                <div class="px-6 py-4">
                    <form id="manualEntryCodeForm">
                        <div class="bg-blue-100 border border-blue-300 rounded-lg p-3 mb-4">
                            <p class="text-xs text-blue-800">
                                <strong>Security Note:</strong> This code will be required for users to access manual
                                GPS location entry. Only share with authorized administrators.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">
                                <i class="fas fa-lock mr-1"></i>Your Admin Password *
                            </label>
                            <input type="password" id="codeAdminPassword"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black"
                                required placeholder="Enter your password to confirm">
                            <p class="text-xs text-gray-600 mt-1">Confirmation required for security</p>
                        </div>

                        <hr class="my-4 border-gray-400">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">
                                <i class="fas fa-key mr-1"></i>New Access Code *
                            </label>
                            <input type="text" id="newManualEntryCode"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black font-mono text-lg tracking-wide"
                                required placeholder="e.g., DEPED2025" maxlength="20">
                            <p class="text-xs text-gray-600 mt-1">Use a memorable but secure code (4-20 characters)
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black mb-2">
                                <i class="fas fa-check-double mr-1"></i>Confirm Access Code *
                            </label>
                            <input type="text" id="confirmManualEntryCode"
                                class="w-full px-3 py-2 bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black font-mono text-lg tracking-wide"
                                required placeholder="Re-enter the code" maxlength="20">
                        </div>

                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-lightbulb text-yellow-600 mt-0.5 mr-2"></i>
                                <div class="text-xs text-yellow-800">
                                    <strong>Tip:</strong> Use a code that's easy to share verbally with administrators
                                    but hard for others to guess. Avoid common words or sequential numbers.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end space-x-3">
                    <button type="button" onclick="closeManualEntryCodeModal()"
                        class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="button" onclick="submitManualEntryCode()"
                        class="px-4 py-2 bg-blue-600 bg-opacity-70 backdrop-filter backdrop-blur-sm text-white rounded-lg hover:bg-opacity-80 transition-all duration-200 border border-white border-opacity-30 font-semibold">
                        <i class="fas fa-save mr-1"></i>Update Access Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Logs Modal -->
    <div id="activityLogsModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-6xl shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-black mb-0 flex items-center">
                                <i class="fas fa-history text-purple-600 mr-2"></i>
                                Admin Activity Logs
                            </h3>
                            <p class="text-sm text-gray-700 mt-1">Complete audit trail of all administrative actions
                            </p>
                        </div>
                        <button onclick="closeActivityLogsModal()" class="text-black hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <!-- Filters -->
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="text" id="activitySearchInput" placeholder="Search logs..."
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white bg-opacity-80">
                        <select id="activityActionFilter"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white bg-opacity-80">
                            <option value="">All Actions</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="create_user">Create User</option>
                            <option value="update_user">Update User</option>
                            <option value="delete_user">Delete User</option>
                            <option value="create_workplace">Create Workplace</option>
                            <option value="update_workplace">Update Workplace</option>
                            <option value="delete_workplace">Delete Workplace</option>
                            <option value="assign_user_workplace">Assign User to Workplace</option>
                            <option value="remove_user_workplace">Remove User from Workplace</option>
                            <option value="update_admin_account">Update Admin Account</option>
                            <option value="failed_admin_update">Failed Admin Update</option>
                            <option value="export_attendance_report_csv">Export Attendance Report CSV</option>
                            <option value="export_attendance_report_excel">Export Attendance Report Excel</option>
                        </select>
                        <button onclick="loadActivityLogs()"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh
                        </button>
                    </div>

                    <!-- Logs Table -->
                    <div class="bg-white bg-opacity-60 backdrop-blur-sm rounded-lg overflow-hidden">
                        <div class="overflow-x-auto max-h-96">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-100 bg-opacity-80 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Admin</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Action
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">
                                            Description</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">IP Address
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="activityLogsTableBody"
                                    class="bg-white bg-opacity-40 divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-600">
                                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                            <p>Loading activity logs...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="activityLogsPagination" class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-black">
                            Showing <span id="activityLogsShowing">0</span> entries
                        </div>
                        <div class="flex space-x-2" id="activityLogsPaginationButtons">
                            <!-- Pagination buttons will be inserted here -->
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button type="button" onclick="closeActivityLogsModal()"
                        class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Logs Modal (Assigned vs Non-Assigned Check-ins) -->
    <div id="attendanceLogsModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-6xl shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-black mb-0 flex items-center">
                                <i class="fas fa-map-marker-alt text-orange-600 mr-2"></i>
                                Check-In Location Logs
                            </h3>
                            <p class="text-sm text-gray-700 mt-1">View assigned and off-site workplace check-ins
                            </p>
                        </div>
                        <button onclick="closeAttendanceLogsModal()" class="text-black hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <!-- Filters -->
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                        <input type="text" id="attendanceLogsSearchInput" placeholder="Search by user or workplace..."
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white bg-opacity-80">
                        <select id="attendanceLogsTypeFilter"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white bg-opacity-80">
                            <option value="">All Types</option>
                            <option value="assigned">Assigned Workplaces</option>
                            <option value="non_assigned">Off-Site Check-ins</option>
                        </select>
                        <input type="date" id="attendanceLogsDateFilter"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white bg-opacity-80">
                        <button onclick="loadAttendanceLogs()"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh
                        </button>
                    </div>

                    <!-- Summary Stats -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="bg-white bg-opacity-60 backdrop-blur-sm rounded-lg p-3">
                            <div class="text-xs text-gray-600 mb-1">Total Check-ins</div>
                            <div class="text-2xl font-bold text-gray-900" id="attendanceLogsTotalCount">0</div>
                        </div>
                        <div class="bg-green-50 bg-opacity-60 backdrop-blur-sm rounded-lg p-3">
                            <div class="text-xs text-green-700 mb-1">Assigned Workplaces</div>
                            <div class="text-2xl font-bold text-green-700" id="attendanceLogsAssignedCount">0</div>
                        </div>
                        <div class="bg-orange-50 bg-opacity-60 backdrop-blur-sm rounded-lg p-3">
                            <div class="text-xs text-orange-700 mb-1">Off-Site Check-ins</div>
                            <div class="text-2xl font-bold text-orange-700" id="attendanceLogsNonAssignedCount">0</div>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="bg-white bg-opacity-60 backdrop-blur-sm rounded-lg overflow-hidden">
                        <div class="overflow-x-auto max-h-96">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-100 bg-opacity-80 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Date/Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Workplace</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Location</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceLogsTableBody"
                                    class="bg-white bg-opacity-40 divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-600">
                                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                            <p>Loading attendance logs...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="attendanceLogsPagination" class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-black">
                            Showing <span id="attendanceLogsShowing">0</span> entries
                        </div>
                        <div class="flex space-x-2" id="attendanceLogsPaginationButtons">
                            <!-- Pagination buttons will be inserted here -->
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button type="button" onclick="closeAttendanceLogsModal()"
                        class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Workplace User Management Modal -->
    <div id="workplaceUsersModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-4xl shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-black mb-0" id="workplaceUsersTitle">Manage
                                Workplace Users</h3>
                            <p class="text-sm text-gray-700 mt-1">Assign or remove users from this workplace</p>
                        </div>
                        <button onclick="closeWorkplaceUsersModal()" class="text-black hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Assigned Users (Left) -->
                        <div
                            class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-users text-green-600 mr-2"></i>
                                Assigned Users (<span id="assignedUsersCount">0</span>)
                            </h4>
                            <div class="mb-3">
                                <input type="text" id="assignedUsersSearch"
                                    placeholder="Search assigned users..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 bg-white bg-opacity-80">
                            </div>
                            <div id="assignedUsersList" class="space-y-2 max-h-96 overflow-y-auto">
                                <!-- Dynamically filled -->
                            </div>
                        </div>

                        <!-- Available Users (Right) -->
                        <div
                            class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                                Available Users (<span id="availableUsersCount">0</span>)
                            </h4>
                            <div class="mb-3">
                                <input type="text" id="availableUsersSearch"
                                    placeholder="Search available users..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white bg-opacity-80">
                            </div>
                            <div id="availableUsersList" class="space-y-2 max-h-96 overflow-y-auto">
                                <!-- Dynamically filled -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button onclick="closeWorkplaceUsersModal()"
                        class="px-4 py-2 bg-blue-400 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Workplace Settings Modal -->
    <div id="userWorkplaceSettingsModal"
        class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-3xl shadow-lg rounded-2xl">
            <div
                class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-black mb-0" id="userWorkplaceSettingsTitle">User
                                Workplace Settings</h3>
                            <p class="text-sm text-gray-700 mt-1">Manage workplace assignments and set primary
                                location</p>
                        </div>
                        <button onclick="closeUserWorkplaceSettingsModal()" class="text-black hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- User Info (Left - 1/3) -->
                        <div
                            class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <div class="flex flex-col items-center text-center">
                                <div id="userAvatarSettings"
                                    class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg mb-3">
                                    <span class="text-white font-semibold text-2xl">U</span>
                                </div>
                                <h4 id="userNameSettings" class="text-base font-semibold text-black mb-1">User Name
                                </h4>
                                <p id="userEmailSettings" class="text-xs text-gray-700 mb-4">user@example.com</p>

                                <div class="w-full space-y-2 text-left">
                                    <div class="bg-white bg-opacity-60 rounded-lg p-3">
                                        <div class="flex items-center text-xs text-gray-700 mb-1">
                                            <i class="fas fa-building mr-2 text-indigo-600"></i>
                                            <span>Total Workplaces</span>
                                        </div>
                                        <div id="userTotalWorkplaces" class="text-2xl font-bold text-black">0</div>
                                    </div>

                                    <div class="bg-white bg-opacity-60 rounded-lg p-3">
                                        <div class="flex items-center text-xs text-gray-700 mb-1">
                                            <i class="fas fa-star mr-2 text-yellow-500"></i>
                                            <span>Primary Workplace</span>
                                        </div>
                                        <div id="userPrimaryWorkplace" class="text-sm font-semibold text-black">None
                                            set</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Workplace Assignments (Right - 2/3) -->
                        <div
                            class="md:col-span-2 bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-list-ul text-indigo-600 mr-2"></i>
                                Workplace Assignments (<span id="workplaceAssignmentsCount">0</span>)
                            </h4>

                            <div id="userWorkplacesList" class="space-y-2 max-h-96 overflow-y-auto">
                                <!-- Dynamically filled -->
                            </div>

                            <div id="noWorkplacesMessage" class="hidden text-center py-8 text-gray-600">
                                <i class="fas fa-building text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm">No workplaces assigned</p>
                                <p class="text-xs text-gray-500 mt-1">Use "Assign User" button to add workplaces</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button onclick="closeUserWorkplaceSettingsModal()"
                        class="px-4 py-2 bg-blue-400 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set up CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ?
            document.querySelector('meta[name="csrf-token"]').getAttribute('content') :
            '{{ csrf_token() }}';

        // Admin section switching functionality
        function switchAdminSection(sectionName) {
            // Close sidebar on mobile ONLY if it's currently open
            if (window.innerWidth < 1024) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                    toggleMobileSidebar();
                }
            }

            // Hide all sections
            document.querySelectorAll('.admin-section').forEach(section => {
                section.classList.add('hidden');
            });

            // Show selected section
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }

            // Update sidebar active state
            document.querySelectorAll('.admin-nav-link').forEach(link => {
                link.classList.remove('active', 'bg-indigo-50', 'border-r-4', 'border-indigo-500');
                link.classList.add('hover:bg-gray-50');
            });

            const activeLink = document.querySelector(`[data-section="${sectionName}"]`);
            if (activeLink) {
                activeLink.classList.add('active', 'bg-indigo-50', 'border-r-4', 'border-indigo-500');
                activeLink.classList.remove('hover:bg-gray-50');
            }

            // Update breadcrumb
            const sectionTitles = {
                'dashboard': 'Dashboard',
                'workplaces': 'Workplaces',
                'users': 'Users',
                'attendance': 'Attendance',
                'reports': 'Reports',
                'absence-requests': 'Absence Requests',
                'settings': 'Settings'
            };

            const breadcrumbElement = document.getElementById('current-section');
            if (breadcrumbElement && sectionTitles[sectionName]) {
                breadcrumbElement.textContent = sectionTitles[sectionName];
            }

            // Prevent default link behavior
            return false;
        }

        // Sidebar toggle functionality
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Load dashboard stats immediately on page load (lightweight)
            fetch('/admin/attendance-stats', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update dashboard overview only
                        const dashboardCheckins = document.getElementById('dashboard-stat-checkins');
                        if (dashboardCheckins) {
                            dashboardCheckins.textContent = data.stats.total_checkins;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard stats:', error);
                    const dashboardCheckins = document.getElementById('dashboard-stat-checkins');
                    if (dashboardCheckins) {
                        dashboardCheckins.textContent = '0';
                    }
                });

            // Sync pending count from leave section to dashboard
            const pendingCountTab = document.getElementById('pending-count-tab');
            if (pendingCountTab) {
                const observer = new MutationObserver(function() {
                    const dashboardCount = document.getElementById('dashboard-pending-count');
                    if (dashboardCount) {
                        dashboardCount.textContent = pendingCountTab.textContent.trim();
                    }
                });
                observer.observe(pendingCountTab, {
                    childList: true,
                    characterData: true,
                    subtree: true
                });
            }

            const userSearchMain = document.getElementById('userSearchMain');
            if (userSearchMain) {
                userSearchMain.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('.user-row-main');

                    rows.forEach(row => {
                        const name = row.querySelector('.user-name').textContent.toLowerCase();
                        const email = row.querySelector('.user-email').textContent.toLowerCase();

                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Employee Location filtering with status and time range - FOR MAP MARKERS
            let searchTimeout;
            window.filterEmployeeLocations = function() {
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Wait 500ms after user stops typing
                searchTimeout = setTimeout(() => {
                    performMapFilter();
                }, 500);
            };

            function performMapFilter() {
                const searchTerm = document.getElementById('employeeLocationSearch')?.value.toLowerCase().trim() || '';
                const statusFilter = document.getElementById('statusFilter')?.value || 'all';
                
                console.log('Filtering map - Search:', searchTerm, 'Status:', statusFilter);

                if (!employeeMarkersData || employeeMarkersData.length === 0) {
                    console.log('No marker data available');
                    return;
                }

                let matchedMarkers = [];

                employeeMarkersData.forEach(markerData => {
                    const { marker, user_name, user_email, user_id, action, timestamp } = markerData;
                    
                    // Check search term
                    const matchesSearch = searchTerm === '' || user_name.includes(searchTerm) || user_email.includes(searchTerm);

                    // Check status filter
                    let matchesStatus = true;
                    if (statusFilter !== 'all') {
                        if (statusFilter === 'checked_in') {
                            matchesStatus = action === 'check_in';
                        } else if (statusFilter === 'checked_out') {
                            matchesStatus = action === 'check_out';
                        } else if (statusFilter === 'no_activity') {
                            matchesStatus = !action || action === '';
                        }
                    }

                    // Show/hide marker based on all filters
                    if (matchesSearch && matchesStatus) {
                        // Show marker
                        if (!employeeMap.hasLayer(marker)) {
                            marker.addTo(employeeMap);
                        }
                        
                        // If searching, highlight matched markers
                        if (searchTerm !== '') {
                            matchedMarkers.push(markerData);
                            
                            // Make marker pulse/stand out with larger animated icon
                            const color = getStatusColor(action);
                            marker.setIcon(L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="color: ${color}; font-size: 38px; text-shadow: 0 0 8px #3b82f6, 0 0 15px rgba(59, 130, 246, 0.6), 0 2px 4px rgba(0,0,0,0.3); filter: drop-shadow(0 0 5px rgba(59, 130, 246, 0.8)); animation: markerBounce 0.6s ease-out, markerPulse 1.5s infinite;"><i class="fas fa-map-marker-alt"></i></div>`,
                                iconSize: [38, 38],
                                iconAnchor: [19, 38],
                                popupAnchor: [0, -38]
                            }));
                        } else {
                            // Reset to original icon
                            const color = getStatusColor(action);
                            marker.setIcon(L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="color: ${color}; font-size: 28px; text-shadow: 0 0 3px white, 0 2px 4px rgba(0,0,0,0.3);"><i class="fas fa-map-marker-alt"></i></div>`,
                                iconSize: [28, 28],
                                iconAnchor: [14, 28],
                                popupAnchor: [0, -28]
                            }));
                        }
                    } else {
                        // Hide marker
                        employeeMap.removeLayer(marker);
                    }
                });

                console.log('Matched markers:', matchedMarkers.length);

                // Auto-focus on single match - ONLY OPEN POPUP, NOT FULL MODAL
                if (searchTerm !== '' && matchedMarkers.length === 1) {
                    const markerData = matchedMarkers[0];
                    
                    // Zoom to marker and open popup
                    setTimeout(() => {
                        employeeMap.setView(markerData.marker.getLatLng(), 16, {
                            animate: true,
                            duration: 0.5
                        });
                        
                        // Open popup (small detail box on map)
                        setTimeout(() => {
                            markerData.marker.openPopup();
                        }, 600);
                    }, 300);
                } else if (searchTerm !== '' && matchedMarkers.length > 0 && matchedMarkers.length <= 3) {
                    // Zoom to show all matched markers
                    setTimeout(() => {
                        const group = new L.featureGroup(matchedMarkers.map(m => m.marker));
                        employeeMap.fitBounds(group.getBounds().pad(0.2), {
                            animate: true,
                            duration: 0.5
                        });
                    }, 200);
                } else if (searchTerm !== '' && matchedMarkers.length > 3) {
                    // Show all matches zoomed out
                    const group = new L.featureGroup(matchedMarkers.map(m => m.marker));
                    employeeMap.fitBounds(group.getBounds().pad(0.1));
                }

                // Filter table rows based on same criteria
                filterTableRows(searchTerm, statusFilter);

                // Reset marker sizes after 3 seconds
                if (matchedMarkers.length > 0) {
                    setTimeout(() => {
                        matchedMarkers.forEach(markerData => {
                            const color = getStatusColor(markerData.action);
                            markerData.marker.setIcon(L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="color: ${color}; font-size: 28px; text-shadow: 0 0 3px white, 0 2px 4px rgba(0,0,0,0.3);"><i class="fas fa-map-marker-alt"></i></div>`,
                                iconSize: [28, 28],
                                iconAnchor: [14, 28],
                                popupAnchor: [0, -28]
                            }));
                        });
                    }, 3000);
                }

            }

            // Filter table rows based on search and status
            function filterTableRows(searchTerm, statusFilter) {
                const rows = document.querySelectorAll('.employee-location-row');
                
                rows.forEach(row => {
                    const userName = row.getAttribute('data-user-name') || '';
                    const userEmail = row.getAttribute('data-user-email') || '';
                    const action = row.getAttribute('data-action') || '';
                    
                    // Check search term
                    const matchesSearch = searchTerm === '' || userName.includes(searchTerm) || userEmail.includes(searchTerm);
                    
                    // Check status filter
                    let matchesStatus = true;
                    if (statusFilter !== 'all') {
                        if (statusFilter === 'checked_in') {
                            matchesStatus = action === 'check_in' || action === 'break_end';
                        } else if (statusFilter === 'checked_out') {
                            matchesStatus = action === 'check_out';
                        } else if (statusFilter === 'no_activity') {
                            matchesStatus = !action || action === '';
                        }
                    }
                    
                    // Show/hide row based on filters
                    if (matchesSearch && matchesStatus) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Initialize employee location search on page load
            const employeeLocationSearch = document.getElementById('employeeLocationSearch');
            if (employeeLocationSearch) {
                employeeLocationSearch.addEventListener('input', window.filterEmployeeLocations);
            }

            const assignmentSearch = document.getElementById('assignmentSearch');
            if (assignmentSearch) {
                assignmentSearch.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('.assignment-row');

                    rows.forEach(row => {
                        const name = row.querySelector('.user-name').textContent.toLowerCase();
                        const email = row.querySelector('.user-email').textContent.toLowerCase();

                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Workplace search functionality
            const workplaceSearchMain = document.getElementById('workplaceSearchMain');
            if (workplaceSearchMain) {
                workplaceSearchMain.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('.workplace-row-main');

                    rows.forEach(row => {
                        const name = row.querySelector('.workplace-name').textContent.toLowerCase();
                        const address = row.querySelector('.workplace-address').textContent
                            .toLowerCase();

                        if (name.includes(searchTerm) || address.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Select all functionality for Users section
            const selectAllMain = document.getElementById('selectAllMain');
            if (selectAllMain) {
                selectAllMain.addEventListener('change', function(e) {
                    const checkboxes = document.querySelectorAll('.user-checkbox-main');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = e.target.checked;
                    });
                    updateBulkSelectionBadge();
                });
            }

            // Update badge when individual checkboxes are changed
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('user-checkbox-main')) {
                    updateBulkSelectionBadge();
                }
            });
        });

        // Update bulk selection badge
        function updateBulkSelectionBadge() {
            const selectedCount = document.querySelectorAll('.user-checkbox-main:checked').length;
            const badge = document.getElementById('bulkSelectionBadge');

            if (badge) {
                if (selectedCount > 0) {
                    badge.textContent = selectedCount;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }

        // User action functions
        function addUser() {
            openUserModal();
        }

        function editUser(userId) {
            fetch(`/admin/users/${userId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        document.getElementById('userId').value = userId;
                        document.getElementById('userName').value = user.name;
                        document.getElementById('userEmail').value = user.email;
                        document.getElementById('userRole').value = user.role;
                        document.getElementById('userPasswordField').style.display = 'none';
                        document.getElementById('userPasswordConfirmField').style.display = 'none';
                        document.getElementById('userModalTitle').textContent = 'Edit User';
                        document.getElementById('userModal').classList.remove('hidden');
                    } else {
                        showNotification('Error loading user data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading user data', 'error');
                });
        }

        function viewUser(userId) {
            fetch(`/admin/users/${userId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showUserDetailsModal(data.user);
                    } else {
                        showNotification(data.message || 'Error loading user data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading user data', 'error');
                });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                fetch(`/admin/users/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred while deleting user', 'error');
                    });
            }
        }

        // Reset User Password Function
        function resetUserPassword(userId, userName, userEmail) {
            if (confirm(`Are you sure you want to send a password reset email to ${userName} (${userEmail})?`)) {
                fetch(`/admin/users/${userId}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(`Password reset email sent to ${userEmail}`, 'success');
                        } else {
                            showNotification('Error: ' + (data.message || 'Failed to send reset email'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred while sending reset email', 'error');
                    });
            }
        }

        // Workplace Modal Functions
        function openWorkplaceModal(workplaceId = null) {
            document.getElementById('workplaceModal').classList.remove('hidden');

            if (workplaceId) {
                document.getElementById('modalTitle').textContent = 'Edit Workplace';
                document.getElementById('workplaceId').value = workplaceId;
                // Load workplace data - implement this
            } else {
                document.getElementById('modalTitle').textContent = 'Add New Workplace';
                document.getElementById('workplaceForm').reset();
                document.getElementById('workplaceId').value = '';
                document.getElementById('workplaceActive').checked = true;
            }
        }

        function closeWorkplaceModal() {
            document.getElementById('workplaceModal').classList.add('hidden');
        }

        // Assignment Modal Functions
        function openAssignmentModal() {
            document.getElementById('assignmentModal').classList.remove('hidden');
        }

        function closeAssignmentModal() {
            document.getElementById('assignmentModal').classList.add('hidden');
        }


        // Pagination for All Workplaces table
        document.addEventListener('DOMContentLoaded', function() {
            const itemsPerPage = 5;
            let currentPage = 1;

            const tableBody = document.getElementById('workplacesTableBodyMain');
            const cardsContainer = document.getElementById('workplacesCardsMain');
            const allTableRows = tableBody ? Array.from(tableBody.querySelectorAll('tr.workplace-row-main')) : [];
            const allCards = cardsContainer ? Array.from(cardsContainer.querySelectorAll('.workplace-row-main')) : [];
            const totalItems = Math.max(allTableRows.length, allCards.length);
            const paginationInfo = document.getElementById('workplace-pagination-info');
            const paginationControls = document.getElementById('workplace-pagination-controls');
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            function displayPage(page) {
                currentPage = page;

                // Calculate the start and end index for the current page
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                // Hide all rows and cards first
                allTableRows.forEach(row => row.style.display = 'none');
                allCards.forEach(card => card.style.display = 'none');

                // Show only the items for the current page
                const pageTableRows = allTableRows.slice(startIndex, endIndex);
                const pageCards = allCards.slice(startIndex, endIndex);
                
                pageTableRows.forEach(row => row.style.display = '');
                pageCards.forEach(card => card.style.display = 'block');

                updatePaginationUI();
            }

            function updatePaginationUI() {
                // Clear existing controls
                paginationControls.innerHTML = '';

                // Update "Showing X to Y of Z" text
                const startItem = (currentPage - 1) * itemsPerPage + 1;
                const endItem = Math.min(startItem + itemsPerPage - 1, totalItems);
                paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${totalItems} workplaces`;

                // Add "Previous" button
                const prevButton = createButton('<i class="fas fa-chevron-left"></i>', () => displayPage(
                    currentPage - 1), currentPage === 1);
                paginationControls.appendChild(prevButton);

                // Add page number buttons
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = createButton(i, () => displayPage(i));
                    if (i === currentPage) {
                        pageButton.className =
                            'px-3 py-1 text-sm bg-indigo-600 text-white rounded-lg font-semibold cursor-default';
                    }
                    paginationControls.appendChild(pageButton);
                }

                // Add "Next" button
                const nextButton = createButton('<i class="fas fa-chevron-right"></i>', () => displayPage(
                    currentPage + 1), currentPage === totalPages);
                paginationControls.appendChild(nextButton);
            }

            function createButton(content, onClick, disabled = false) {
                const button = document.createElement('button');
                button.innerHTML = content;
                button.className =
                    'px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed';
                button.disabled = disabled;
                button.addEventListener('click', onClick);
                return button;
            }

            // Initial setup
            if (totalItems > 0) {
                displayPage(1);
            } else {
                paginationInfo.textContent = 'No workplaces found';
            }
        });

        // Pagination for All Users table
        document.addEventListener('DOMContentLoaded', function() {
            const usersPerPage = 5;
            let currentUserPage = 1;

            const userTableBody = document.getElementById('usersTableBodyMain');
            const userCardsContainer = document.getElementById('usersCardsMain');
            const allUserTableRows = userTableBody ? Array.from(userTableBody.querySelectorAll('tr.user-row-main')) : [];
            const allUserCards = userCardsContainer ? Array.from(userCardsContainer.querySelectorAll('.user-row-main')) : [];
            const totalUsers = Math.max(allUserTableRows.length, allUserCards.length);
            const userPaginationInfo = document.getElementById('user-pagination-info');
            const userPaginationControls = document.getElementById('user-pagination-controls');
            const totalUserPages = Math.ceil(totalUsers / usersPerPage);

            function displayUserPage(page) {
                currentUserPage = page;

                // Calculate the start and end index for the current page
                const startIndex = (page - 1) * usersPerPage;
                const endIndex = startIndex + usersPerPage;

                // Hide all rows and cards first
                allUserTableRows.forEach(row => row.style.display = 'none');
                allUserCards.forEach(card => card.style.display = 'none');

                // Show only the items for the current page
                const pageUserTableRows = allUserTableRows.slice(startIndex, endIndex);
                const pageUserCards = allUserCards.slice(startIndex, endIndex);
                
                pageUserTableRows.forEach(row => row.style.display = '');
                pageUserCards.forEach(card => card.style.display = 'block');

                updateUserPaginationUI();
            }

            function updateUserPaginationUI() {
                // Clear existing controls
                userPaginationControls.innerHTML = '';

                // Update "Showing X to Y of Z" text
                const startUser = (currentUserPage - 1) * usersPerPage + 1;
                const endUser = Math.min(startUser + usersPerPage - 1, totalUsers);
                userPaginationInfo.textContent = `Showing ${startUser} to ${endUser} of ${totalUsers} users`;

                // Add "Previous" button
                const prevButton = createUserButton('<i class="fas fa-chevron-left"></i>', () => displayUserPage(
                    currentUserPage - 1), currentUserPage === 1);
                userPaginationControls.appendChild(prevButton);

                // Add page number buttons
                for (let i = 1; i <= totalUserPages; i++) {
                    const pageButton = createUserButton(i, () => displayUserPage(i));
                    if (i === currentUserPage) {
                        pageButton.className =
                            'px-3 py-1 text-sm bg-indigo-600 text-white rounded-lg font-semibold cursor-default';
                    }
                    userPaginationControls.appendChild(pageButton);
                }

                // Add "Next" button
                const nextButton = createUserButton('<i class="fas fa-chevron-right"></i>', () => displayUserPage(
                    currentUserPage + 1), currentUserPage === totalUserPages);
                userPaginationControls.appendChild(nextButton);
            }

            function createUserButton(content, onClick, disabled = false) {
                const button = document.createElement('button');
                button.innerHTML = content;
                button.className =
                    'px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed';
                button.disabled = disabled;
                button.addEventListener('click', onClick);
                return button;
            }

            // Initial setup
            if (totalUsers > 0) {
                displayUserPage(1);
            } else {
                userPaginationInfo.textContent = 'No users found';
            }
        });

        // Pagination for Employee Locations table
        document.addEventListener('DOMContentLoaded', function() {
            const employeesPerPage = 5;
            let currentEmployeePage = 1;

            const employeeTableBody = document.getElementById('employeeLocationTableBody');
            const employeeCardsContainer = document.getElementById('employeeLocationCards');
            const allEmployeeTableRows = employeeTableBody ? Array.from(employeeTableBody.querySelectorAll('tr.employee-location-row')) : [];
            const allEmployeeCards = employeeCardsContainer ? Array.from(employeeCardsContainer.querySelectorAll('.employee-location-row')) : [];
            const totalEmployees = Math.max(allEmployeeTableRows.length, allEmployeeCards.length);
            const employeePaginationInfo = document.getElementById('activity-pagination-info');
            const employeePaginationControls = document.getElementById('activity-pagination-controls');
            const totalEmployeePages = Math.ceil(totalEmployees / employeesPerPage);

            function displayEmployeePage(page) {
                currentEmployeePage = page;

                // Calculate the start and end index for the current page
                const startIndex = (page - 1) * employeesPerPage;
                const endIndex = startIndex + employeesPerPage;

                // Hide all rows and cards first
                allEmployeeTableRows.forEach(row => row.style.display = 'none');
                allEmployeeCards.forEach(card => card.style.display = 'none');

                // Show only the items for the current page
                const pageEmployeeTableRows = allEmployeeTableRows.slice(startIndex, endIndex);
                const pageEmployeeCards = allEmployeeCards.slice(startIndex, endIndex);
                
                pageEmployeeTableRows.forEach(row => row.style.display = '');
                pageEmployeeCards.forEach(card => card.style.display = 'block');

                updateEmployeePaginationUI();
            }

            function updateEmployeePaginationUI() {
                // Clear existing controls
                employeePaginationControls.innerHTML = '';

                // Update "Showing X to Y of Z" text
                const startEmployee = (currentEmployeePage - 1) * employeesPerPage + 1;
                const endEmployee = Math.min(startEmployee + employeesPerPage - 1, totalEmployees);
                employeePaginationInfo.textContent = `Showing ${startEmployee} to ${endEmployee} of ${totalEmployees} employees`;

                // Add "Previous" button
                const prevButton = createEmployeeButton('<i class="fas fa-chevron-left"></i>', () => displayEmployeePage(
                    currentEmployeePage - 1), currentEmployeePage === 1);
                employeePaginationControls.appendChild(prevButton);

                // Add page number buttons
                for (let i = 1; i <= totalEmployeePages; i++) {
                    const pageButton = createEmployeeButton(i, () => displayEmployeePage(i));
                    if (i === currentEmployeePage) {
                        pageButton.className =
                            'px-3 py-1 text-sm bg-indigo-600 text-white rounded-lg font-semibold cursor-default';
                    }
                    employeePaginationControls.appendChild(pageButton);
                }

                // Add "Next" button
                const nextButton = createEmployeeButton('<i class="fas fa-chevron-right"></i>', () => displayEmployeePage(
                    currentEmployeePage + 1), currentEmployeePage === totalEmployeePages);
                employeePaginationControls.appendChild(nextButton);
            }

            function createEmployeeButton(content, onClick, disabled = false) {
                const button = document.createElement('button');
                button.innerHTML = content;
                button.className =
                    'px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed';
                button.disabled = disabled;
                button.addEventListener('click', onClick);
                return button;
            }

            // Initial setup
            if (totalEmployees > 0) {
                displayEmployeePage(1);
            } else {
                employeePaginationInfo.textContent = 'No employees found';
            }
        });

        // User Modal Functions
        function openUserModal() {
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('userModalTitle').textContent = 'Add New User';
            document.getElementById('userPasswordField').style.display = 'block';
            document.getElementById('userPasswordConfirmField').style.display = 'block';
            document.getElementById('userPassword').required = true;
            document.getElementById('userPasswordConfirm').required = true;
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        // Populate assignment modal with users and workplaces
        function populateAssignmentModal() {
            // Populate users dropdown
            fetch('/admin/users')
                .then(response => response.json())
                .then(data => {
                    const userSelect = document.getElementById('assignmentUser');
                    userSelect.innerHTML = '<option value="">Select User</option>';
                    if (data.users) {
                        data.users.forEach(user => {
                            userSelect.innerHTML +=
                                `<option value="${user.id}">${user.name} (${user.email})</option>`;
                        });
                    }
                })
                .catch(error => console.error('Error loading users:', error));

            // Populate workplaces dropdown
            fetch('/admin/workplaces')
                .then(response => response.json())
                .then(data => {
                    const workplaceSelect = document.getElementById('assignmentWorkplace');
                    workplaceSelect.innerHTML = '<option value="">Select Workplace</option>';
                    if (data.workplaces) {
                        data.workplaces.forEach(workplace => {
                            workplaceSelect.innerHTML +=
                                `<option value="${workplace.id}">${workplace.name}</option>`;
                        });
                    }
                })
                .catch(error => console.error('Error loading workplaces:', error));
        }

        // Form Handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize assignment modal
            populateAssignmentModal();

            const workplaceForm = document.getElementById('workplaceForm');
            if (workplaceForm) {
                workplaceForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const workplaceId = document.getElementById('workplaceId').value;
                    const nameInput = document.getElementById('workplaceName');
                    const addressInput = document.getElementById('workplaceAddress');
                    const latInput = document.getElementById('workplaceLatitude');
                    const lngInput = document.getElementById('workplaceLongitude');
                    const radiusInput = document.getElementById('workplaceRadius');

                    // Clear all errors
                    [nameInput, addressInput, latInput, lngInput, radiusInput].forEach(input => {
                        ValidationUtils.clearError(input);
                    });

                    let hasErrors = false;

                    // Validate name
                    const nameResult = ValidationUtils.validateName(nameInput.value, 'Workplace name');
                    if (!nameResult.valid) {
                        ValidationUtils.showError(nameInput, nameResult.errors[0]);
                        hasErrors = true;
                    }

                    // Validate address
                    const addressResult = ValidationUtils.validateTextArea(
                        addressInput.value, 5, 500, 'Address'
                    );
                    if (!addressResult.valid) {
                        ValidationUtils.showError(addressInput, addressResult.errors[0]);
                        hasErrors = true;
                    }

                    // Validate coordinates
                    const coordsResult = ValidationUtils.validateCoordinates(
                        latInput.value, lngInput.value
                    );
                    if (!coordsResult.valid) {
                        coordsResult.errors.forEach(err => {
                            if (err.includes('latitude')) {
                                ValidationUtils.showError(latInput, err);
                            } else {
                                ValidationUtils.showError(lngInput, err);
                            }
                        });
                        hasErrors = true;
                    }

                    // Validate radius
                    const radius = parseFloat(radiusInput.value);
                    if (isNaN(radius) || radius <= 0 || radius > 10000) {
                        ValidationUtils.showError(radiusInput, 'Radius must be between 1 and 10000 meters');
                        hasErrors = true;
                    }

                    if (hasErrors) {
                        const firstError = workplaceForm.querySelector('.border-red-500');
                        if (firstError) firstError.focus();
                        return;
                    }

                    // Check rate limiting (3 attempts allowed, blocked on 4th)
                    const rateCheck = ValidationUtils.rateLimiter.canSubmit('workplace-form', 3, 60000);
                    if (!rateCheck.allowed) {
                        ValidationUtils.showToast(rateCheck.message, 'warning');
                        return;
                    }

                    const formData = {
                        name: nameResult.sanitized,
                        address: addressResult.sanitized,
                        latitude: coordsResult.latitude,
                        longitude: coordsResult.longitude,
                        radius: radius,
                        is_active: document.getElementById('workplaceActive').checked
                    };

                    const url = workplaceId ? `/admin/workplaces/${workplaceId}` : '/admin/workplaces';
                    const method = workplaceId ? 'PUT' : 'POST';

                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                closeWorkplaceModal();
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showNotification('Error: ' + (data.message || 'Unknown error'),
                                'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('An error occurred', 'error');
                        });
                });
            }

            const assignmentForm = document.getElementById('assignmentForm');
            if (assignmentForm) {
                assignmentForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = {
                        user_id: document.getElementById('assignmentUser').value,
                        workplace_id: document.getElementById('assignmentWorkplace').value,
                        role: document.getElementById('assignmentRole').value,
                        is_primary: document.getElementById('assignmentPrimary').checked
                    };

                    fetch('/admin/assign-workplace', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                closeAssignmentModal();
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showNotification('Error: ' + (data.message || 'Unknown error'),
                                'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('An error occurred', 'error');
                        });
                });
            }

            const userForm = document.getElementById('userForm');
            if (userForm) {
                userForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const userId = document.getElementById('userId').value;
                    const nameInput = document.getElementById('userName');
                    const emailInput = document.getElementById('userEmail');
                    const passwordInput = document.getElementById('userPassword');
                    const passwordConfirmInput = document.getElementById('userPasswordConfirm');

                    // Clear all errors
                    [nameInput, emailInput, passwordInput, passwordConfirmInput].forEach(input => {
                        if (input) ValidationUtils.clearError(input);
                    });

                    let hasErrors = false;

                    // Validate name
                    const nameResult = ValidationUtils.validateName(nameInput.value, 'Name');
                    if (!nameResult.valid) {
                        ValidationUtils.showError(nameInput, nameResult.errors[0]);
                        hasErrors = true;
                    }

                    // Validate email
                    const emailResult = ValidationUtils.validateEmail(emailInput.value);
                    if (!emailResult.valid) {
                        ValidationUtils.showError(emailInput, emailResult.errors[0]);
                        hasErrors = true;
                    }

                    // Validate password if provided
                    const password = passwordInput.value;
                    const passwordConfirm = passwordConfirmInput.value;

                    if (password || !userId) {
                        const passwordResult = ValidationUtils.validatePassword(password, passwordConfirm);
                        if (!passwordResult.valid) {
                            ValidationUtils.showError(passwordInput, passwordResult.errors[0]);
                            hasErrors = true;
                        }
                    }

                    if (hasErrors) {
                        const firstError = userForm.querySelector('.border-red-500');
                        if (firstError) firstError.focus();
                        return;
                    }

                    // Check rate limiting (3 attempts allowed, blocked on 4th)
                    const rateCheck = ValidationUtils.rateLimiter.canSubmit('user-form', 3, 60000);
                    if (!rateCheck.allowed) {
                        ValidationUtils.showToast(rateCheck.message, 'warning');
                        return;
                    }

                    const formData = {
                        name: nameResult.sanitized,
                        email: emailResult.sanitized,
                        role: document.getElementById('userRole').value
                    };

                    if (password) {
                        formData.password = password;
                        formData.password_confirmation = passwordConfirm;
                    }

                    const url = userId ? `/admin/users/${userId}` : '/admin/users';
                    const method = userId ? 'PUT' : 'POST';

                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(formData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                closeUserModal();
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                if (data.errors) {
                                    const errorMessages = Object.values(data.errors).flat().join('\n');
                                    showNotification('Validation errors:\n' + errorMessages, 'error');
                                } else {
                                    showNotification('Error: ' + (data.message || 'Unknown error'),
                                        'error');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('An error occurred', 'error');
                        });
                });
            }
        });

        // Workplace Action Functions
        function editWorkplace(id) {
            fetch(`/admin/workplaces/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const workplace = data.workplace;
                        document.getElementById('workplaceId').value = id;
                        document.getElementById('workplaceName').value = workplace.name;
                        document.getElementById('workplaceAddress').value = workplace.address;
                        document.getElementById('workplaceLatitude').value = workplace.latitude;
                        document.getElementById('workplaceLongitude').value = workplace.longitude;
                        document.getElementById('workplaceRadius').value = workplace.radius;
                        document.getElementById('workplaceActive').checked = workplace.is_active;
                        document.getElementById('modalTitle').textContent = 'Edit Workplace';
                        document.getElementById('workplaceModal').classList.remove('hidden');
                    } else {
                        showNotification('Error loading workplace data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading workplace data', 'error');
                });
        }

        function deleteWorkplace(id) {
            if (confirm('Are you sure you want to delete this workplace? This action cannot be undone.')) {
                fetch(`/admin/workplaces/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred', 'error');
                    });
            }
        }

        function manageUsers(workplaceId) {
            fetch(`/admin/workplace-users/${workplaceId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showWorkplaceUsersModal(workplaceId, data);
                    } else {
                        showNotification('Error loading workplace users', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading workplace users', 'error');
                });
        }

        function showWorkplaceUsersModal(workplaceId, data) {
            const modal = document.getElementById('workplaceUsersModal');
            const title = document.getElementById('workplaceUsersTitle');
            const assignedList = document.getElementById('assignedUsersList');
            const availableList = document.getElementById('availableUsersList');
            const assignedCount = document.getElementById('assignedUsersCount');
            const availableCount = document.getElementById('availableUsersCount');

            // Set workplace name in title
            const workplaceName = data.workplace ? data.workplace.name : 'Workplace';
            title.textContent = `Manage Users - ${workplaceName}`;

            // Set counts
            assignedCount.textContent = data.workplaceUsers.length;
            availableCount.textContent = data.availableUsers.length;

            // Render assigned users
            if (data.workplaceUsers.length > 0) {
                assignedList.innerHTML = data.workplaceUsers.map(user => `
                    <div class="assigned-user-item flex items-center justify-between p-3 bg-white bg-opacity-60 rounded-lg hover:bg-opacity-80 transition-all">
                        <div class="flex items-center flex-1">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg mr-3">
                                <span class="text-white font-semibold text-sm">${user.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-black assigned-user-name">${user.name}</div>
                                <div class="text-xs text-gray-700 assigned-user-email">${user.email}</div>
                                <div class="text-xs text-gray-600 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        ${user.pivot.role}
                                    </span>
                                    ${user.pivot.is_primary ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 ml-1"><i class="fas fa-star mr-1"></i>Primary</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <button onclick="removeUserFromWorkplace(${workplaceId}, ${user.id})" 
                                class="ml-3 px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors"
                                title="Remove user">
                            <i class="fas fa-times mr-1"></i>
                            Remove
                        </button>
                    </div>
                `).join('');
            } else {
                assignedList.innerHTML = `
                    <div class="text-center py-8 text-gray-600">
                        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                        <p class="text-sm">No users assigned yet</p>
                    </div>
                `;
            }

            // Render available users
            if (data.availableUsers.length > 0) {
                availableList.innerHTML = data.availableUsers.map(user => `
                    <div class="available-user-item flex items-center justify-between p-3 bg-white bg-opacity-60 rounded-lg hover:bg-opacity-80 transition-all">
                        <div class="flex items-center flex-1">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg mr-3">
                                <span class="text-white font-semibold text-sm">${user.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-black available-user-name">${user.name}</div>
                                <div class="text-xs text-gray-700 available-user-email">${user.email}</div>
                            </div>
                        </div>
                        <button onclick="addUserToWorkplace(${workplaceId}, ${user.id})" 
                                class="ml-3 px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded-lg hover:bg-green-200 transition-colors"
                                title="Assign user">
                            <i class="fas fa-plus mr-1"></i>
                            Assign
                        </button>
                    </div>
                `).join('');
            } else {
                availableList.innerHTML = `
                    <div class="text-center py-8 text-gray-600">
                        <i class="fas fa-check-circle text-4xl text-gray-300 mb-3"></i>
                        <p class="text-sm">All users are assigned</p>
                    </div>
                `;
            }

            // Setup search functionality
            setupWorkplaceUserSearch();

            modal.classList.remove('hidden');
        }

        function closeWorkplaceUsersModal() {
            document.getElementById('workplaceUsersModal').classList.add('hidden');
        }

        function setupWorkplaceUserSearch() {
            const assignedSearch = document.getElementById('assignedUsersSearch');
            const availableSearch = document.getElementById('availableUsersSearch');

            if (assignedSearch) {
                assignedSearch.value = '';
                assignedSearch.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const items = document.querySelectorAll('.assigned-user-item');

                    items.forEach(item => {
                        const name = item.querySelector('.assigned-user-name').textContent.toLowerCase();
                        const email = item.querySelector('.assigned-user-email').textContent.toLowerCase();

                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            if (availableSearch) {
                availableSearch.value = '';
                availableSearch.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const items = document.querySelectorAll('.available-user-item');

                    items.forEach(item => {
                        const name = item.querySelector('.available-user-name').textContent.toLowerCase();
                        const email = item.querySelector('.available-user-email').textContent.toLowerCase();

                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        }

        function addUserToWorkplace(workplaceId, userId) {
            if (!confirm('Assign this user to the workplace?')) {
                return;
            }

            fetch('/admin/assign-workplace', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        workplace_id: workplaceId,
                        role: 'employee',
                        is_primary: false
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('User assigned successfully', 'success');
                        // Refresh the modal
                        manageUsers(workplaceId);
                    } else {
                        showNotification(data.message || 'Error assigning user', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while assigning user', 'error');
                });
        }

        function removeUserFromWorkplace(workplaceId, userId) {
            if (!confirm('Remove this user from the workplace?')) {
                return;
            }

            fetch('/admin/remove-assignment', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        workplace_id: workplaceId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('User removed successfully', 'success');
                        // Refresh the modal
                        manageUsers(workplaceId);
                    } else {
                        showNotification(data.message || 'Error removing user', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while removing user', 'error');
                });
        }

        function manageUserWorkplaces(userId) {
            fetch(`/admin/user-workplaces/${userId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showUserWorkplaceSettingsModal(userId, data);
                    } else {
                        showNotification('Error loading user workplaces', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading user workplaces', 'error');
                });
        }

        function showUserWorkplaceSettingsModal(userId, data) {
            const modal = document.getElementById('userWorkplaceSettingsModal');
            const title = document.getElementById('userWorkplaceSettingsTitle');
            const avatar = document.getElementById('userAvatarSettings');
            const userName = document.getElementById('userNameSettings');
            const userEmail = document.getElementById('userEmailSettings');
            const totalWorkplaces = document.getElementById('userTotalWorkplaces');
            const primaryWorkplace = document.getElementById('userPrimaryWorkplace');
            const workplacesList = document.getElementById('userWorkplacesList');
            const workplacesCount = document.getElementById('workplaceAssignmentsCount');
            const noWorkplacesMsg = document.getElementById('noWorkplacesMessage');

            // Set user info
            const user = data.user;
            title.textContent = `${user.name} - Workplace Settings`;
            avatar.innerHTML =
            `<span class="text-white font-semibold text-2xl">${user.name.charAt(0).toUpperCase()}</span>`;
            userName.textContent = user.name;
            userEmail.textContent = user.email;

            // Set stats
            totalWorkplaces.textContent = data.userWorkplaces.length;
            const primary = data.userWorkplaces.find(w => w.pivot.is_primary);
            primaryWorkplace.textContent = primary ? primary.name : 'None set';
            workplacesCount.textContent = data.userWorkplaces.length;

            // Render workplaces list
            if (data.userWorkplaces.length > 0) {
                noWorkplacesMsg.classList.add('hidden');
                workplacesList.classList.remove('hidden');

                workplacesList.innerHTML = data.userWorkplaces.map(workplace => `
                    <div class="workplace-assignment-item flex items-center justify-between p-3 bg-white bg-opacity-60 rounded-lg hover:bg-opacity-80 transition-all">
                        <div class="flex items-center flex-1">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg mr-3">
                                <i class="fas fa-building text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-black">${workplace.name}</div>
                                <div class="text-xs text-gray-700 mt-1">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                    ${workplace.address.substring(0, 50)}${workplace.address.length > 50 ? '...' : ''}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-user-tag mr-1"></i>
                                        ${workplace.pivot.role}
                                    </span>
                                    ${workplace.pivot.is_primary ? 
                                        '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-star mr-1"></i>Primary</span>' : 
                                        ''}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-3">
                            ${!workplace.pivot.is_primary ? 
                                `<button onclick="setUserPrimaryWorkplace(${userId}, ${workplace.id})" 
                                            class="px-3 py-1.5 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg hover:bg-yellow-200 transition-colors"
                                            title="Set as Primary">
                                        <i class="fas fa-star mr-1"></i>
                                        Set Primary
                                    </button>` : 
                                '<span class="text-xs text-gray-500 italic">Current Primary</span>'
                            }
                            <button onclick="removeUserWorkplaceAssignment(${userId}, ${workplace.id}, '${workplace.name}')" 
                                    class="px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors"
                                    title="Remove Assignment">
                                <i class="fas fa-times mr-1"></i>
                                Remove
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                noWorkplacesMsg.classList.remove('hidden');
                workplacesList.classList.add('hidden');
            }

            modal.classList.remove('hidden');
        }

        function closeUserWorkplaceSettingsModal() {
            document.getElementById('userWorkplaceSettingsModal').classList.add('hidden');
        }

        function setUserPrimaryWorkplace(userId, workplaceId) {
            if (!confirm('Set this workplace as primary for this user?')) {
                return;
            }

            fetch('/admin/set-primary-workplace', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        workplace_id: workplaceId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Primary workplace updated successfully', 'success');
                        // Refresh the modal
                        manageUserWorkplaces(userId);
                    } else {
                        showNotification(data.message || 'Error setting primary workplace', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while setting primary workplace', 'error');
                });
        }

        function removeUserWorkplaceAssignment(userId, workplaceId, workplaceName) {
            if (!confirm(`Remove user from "${workplaceName}"?`)) {
                return;
            }

            fetch('/admin/remove-assignment', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        workplace_id: workplaceId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('User removed from workplace successfully', 'success');
                        // Refresh the modal
                        manageUserWorkplaces(userId);
                        // Also reload the page to update the table
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message || 'Error removing assignment', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while removing assignment', 'error');
                });
        }

        // Report generation function
        function generateReport(type) {
            showNotification(`Generating ${type} report... This feature will be available soon.`, 'info');
        }

        // Attendance monitoring functions
        async function loadAttendanceData() {
            try {
                const response = await fetch('/admin/attendance-stats', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update stats in attendance section
                    document.getElementById('stat-checkins').textContent = data.stats.total_checkins;
                    document.getElementById('stat-avg-hours').textContent = data.stats.average_hours + ' hrs';
                    document.getElementById('stat-late').textContent = data.stats.late_arrivals;

                    // Also update dashboard overview
                    const dashboardCheckins = document.getElementById('dashboard-stat-checkins');
                    if (dashboardCheckins) {
                        dashboardCheckins.textContent = data.stats.total_checkins;
                    }

                    // Count employees on break
                    const onBreak = data.attendance.filter(emp => emp.status === 'On Break').length;
                    document.getElementById('stat-on-break').textContent = onBreak;

                    // Populate table
                    populateAttendanceTable(data.attendance);

                    // Update last updated time
                    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
                    document.getElementById('attendance-count').textContent = data.attendance.length;
                } else {
                    showNotification('Failed to load attendance data: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error loading attendance data:', error);
                showNotification('Error loading attendance data', 'error');
            }
        }

        function populateAttendanceTable(attendanceData) {
            const tbody = document.getElementById('attendanceTableBody');
            const cardsContainer = document.getElementById('attendanceCards');
            tbody.innerHTML = '';
            if (cardsContainer) cardsContainer.innerHTML = '';

            if (attendanceData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm font-medium">No attendance records found</p>
                                <p class="text-xs text-gray-400 mt-1">Check back later or refresh the data</p>
                            </div>
                        </td>
                    </tr>
                `;
                if (cardsContainer) {
                    cardsContainer.innerHTML = `
                        <div class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm font-medium">No attendance records found</p>
                                <p class="text-xs text-gray-400 mt-1">Check back later or refresh the data</p>
                            </div>
                        </div>
                    `;
                }
                return;
            }


            // Normalize each item so the template can accept either a flattened row (from reports/exports)
            // or the legacy attendance object shape used elsewhere. Prefer API-provided row-level
            // check_in_time / check_out_time values when available.
            attendanceData.forEach(item => {
                const empObj = {};

                // User info
                if (item.user) {
                    empObj.user_name = item.user.name ?? (item.user_name ?? 'Unknown');
                    empObj.user_email = item.user.email ?? (item.user_email ?? '');
                } else {
                    empObj.user_name = item.user_name ?? (item.name ?? 'Unknown');
                    empObj.user_email = item.user_email ?? item.email ?? '';
                }

                // Workplace
                empObj.workplace = item.workplace && item.workplace.name ? item.workplace.name : (item.workplace ??
                    'N/A');

                // Determine check-in and check-out times (prefer pair-level fields)
                const rawCheckIn = item.check_in_time ?? item.check_in ?? item.attendance_check_in ?? null;
                const rawCheckOut = item.check_out_time ?? item.check_out ?? item.attendance_check_out ?? null;

                // Format display times using formatTime if we have full datetime strings
                empObj.check_in = rawCheckIn ? (typeof rawCheckIn === 'string' && rawCheckIn.length > 5 ?
                    formatTime(rawCheckIn) : rawCheckIn) : null;
                empObj.check_out = rawCheckOut ? (typeof rawCheckOut === 'string' && rawCheckOut.length > 5 ?
                    formatTime(rawCheckOut) : rawCheckOut) : null;

                // Break fields (keep legacy names)
                empObj.break_start = item.break_start ?? item.break_started_at ?? null;
                empObj.break_end = item.break_end ?? item.break_ended_at ?? null;
                empObj.break_duration = item.break_duration ?? item.break_minutes ?? null;

                // Work hours and status (preserve what caller provided)
                empObj.work_hours = item.total_hours ?? item.work_hours ?? item.hours ?? '0 hrs';
                empObj.status = item.status ?? (item.state ?? 'N/A');

                // Simple late detection data if present
                empObj.is_late = !!item.is_late;
                empObj.late_by = item.late_by ?? '';

                // Render row
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors attendance-row';

                const employeeCell = `
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow">
                                    <span class="text-white font-semibold text-sm">${empObj.user_name.charAt(0).toUpperCase()}</span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-semibold text-gray-900">${empObj.user_name}</div>
                                <div class="text-xs text-gray-600">${empObj.user_email}</div>
                            </div>
                            ${empObj.is_late ? `
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Late ${empObj.late_by}
                                </span>
                            ` : ''}
                        </div>
                    </td>
                `;

                const checkInCell = `
                    <td class="px-4 py-3 text-sm">
                        ${empObj.check_in ? `
                            <div class="flex items-center">
                                <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                                <span class="font-medium ${empObj.is_late ? 'text-red-600' : 'text-green-600'}">${empObj.check_in}</span>
                            </div>
                        ` : '<span class="text-gray-400">--</span>'}
                    </td>
                `;

                const checkOutCell = `
                    <td class="px-4 py-3 text-sm">
                        ${empObj.check_out ? `
                            <div class="flex items-center">
                                <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                                <span class="font-medium text-red-600">${empObj.check_out}</span>
                            </div>
                        ` : '<span class="text-gray-400">--</span>'}
                    </td>
                `;

                const breakCell = `
                    <td class="px-4 py-3 text-sm">
                        ${empObj.break_start ? `
                            <div class="text-xs">
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-pause text-yellow-600 mr-1"></i>
                                    <span>Start: ${empObj.break_start}</span>
                                </div>
                                ${empObj.break_end ? `
                                <div class="flex items-center">
                                    <i class="fas fa-play text-green-600 mr-1"></i>
                                    <span>End: ${empObj.break_end}</span>
                                </div>
                                <div class="mt-1 font-medium text-gray-700">
                                    Duration: ${empObj.break_duration}
                                </div>
                            ` : '<span class="text-yellow-600 italic">On break</span>'}
                            </div>
                        ` : '<span class="text-gray-400">No break</span>'}
                    </td>
                `;

                const hoursCell = `
                    <td class="px-4 py-3 text-sm">
                        <span class="font-bold text-indigo-600">${empObj.work_hours}</span>
                    </td>
                `;

                let statusClass = 'bg-gray-100 text-gray-800';
                let statusIcon = 'fa-minus';
                if (empObj.status === 'Working') {
                    statusClass = 'bg-green-100 text-green-800';
                    statusIcon = 'fa-circle';
                } else if (empObj.status === 'On Break') {
                    statusClass = 'bg-yellow-100 text-yellow-800';
                    statusIcon = 'fa-coffee';
                } else if (empObj.status === 'Completed') {
                    statusClass = 'bg-blue-100 text-blue-800';
                    statusIcon = 'fa-check-circle';
                }

                const statusCell = `
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            <i class="fas ${statusIcon} mr-1"></i>
                            ${empObj.status}
                        </span>
                    </td>
                `;

                const workplaceCell = `
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-building text-gray-400 mr-2"></i>
                            <span>${empObj.workplace}</span>
                        </div>
                    </td>
                `;

                row.innerHTML = employeeCell + checkInCell + checkOutCell + breakCell + hoursCell + statusCell +
                    workplaceCell;
                tbody.appendChild(row);

                // Mobile card view
                if (cardsContainer) {
                    const card = document.createElement('div');
                    card.className = 'attendance-row p-4 hover:bg-gray-50 transition-colors';
                    card.innerHTML = `
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                    <span class="text-white font-semibold text-sm">${empObj.user_name.charAt(0).toUpperCase()}</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-gray-900">${empObj.user_name}</div>
                                    <div class="text-xs text-gray-600">${empObj.user_email}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                                <i class="fas ${statusIcon} mr-1"></i>
                                ${empObj.status}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-gray-500 mb-1">Check In</div>
                                ${empObj.check_in ? `
                                    <div class="flex items-center font-medium ${empObj.is_late ? 'text-red-600' : 'text-green-600'}">
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        ${empObj.check_in}
                                    </div>
                                ` : '<span class="text-gray-400">--</span>'}
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-gray-500 mb-1">Check Out</div>
                                ${empObj.check_out ? `
                                    <div class="flex items-center font-medium text-red-600">
                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                        ${empObj.check_out}
                                    </div>
                                ` : '<span class="text-gray-400">--</span>'}
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-gray-500 mb-1">Work Hours</div>
                                <div class="font-bold text-indigo-600">${empObj.work_hours}</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-gray-500 mb-1">Workplace</div>
                                <div class="flex items-center text-gray-900">
                                    <i class="fas fa-building text-gray-400 mr-1"></i>
                                    <span class="truncate">${empObj.workplace}</span>
                                </div>
                            </div>
                        </div>
                        ${empObj.is_late ? `
                            <div class="mt-2 flex items-center justify-center px-2 py-1 bg-red-50 rounded-lg">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <span class="text-xs font-semibold text-red-800">Late by ${empObj.late_by}</span>
                            </div>
                        ` : ''}
                        ${empObj.break_start ? `
                            <div class="mt-2 bg-yellow-50 rounded-lg p-2">
                                <div class="text-xs text-yellow-700 font-medium mb-1">Break Time</div>
                                <div class="text-xs text-gray-600">
                                    ${empObj.break_end ? 
                                        `Duration: ${empObj.break_duration}` : 
                                        '<span class="text-yellow-600 italic">Currently on break</span>'}
                                </div>
                            </div>
                        ` : ''}
                    `;
                    cardsContainer.appendChild(card);
                }
            });

            // Setup search
            setupAttendanceSearch(attendanceData);
        }

        function setupAttendanceSearch(attendanceData) {
            const searchInput = document.getElementById('attendanceSearch');
            if (!searchInput) return;

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.attendance-row');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update count - only count table rows (not mobile cards)
                const tableRows = document.querySelectorAll('#attendanceTableBody .attendance-row');
                const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none').length;
                document.getElementById('attendance-count').textContent = visibleRows;
            });
        }

        function refreshAttendanceData() {
            const btn = event.target.closest('button');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Refreshing...';
            btn.disabled = true;

            loadAttendanceData().then(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                showNotification('Attendance data refreshed', 'success');
            }).catch(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        }

        // Load attendance data when switching to attendance section
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceLink = document.querySelector('[data-section="attendance"]');
            if (attendanceLink) {
                attendanceLink.addEventListener('click', function() {
                    // Small delay to ensure section is visible
                    setTimeout(() => {
                        loadAttendanceData();
                    }, 100);
                });
            }
        });

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className =
                `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 text-white transition-all duration-300 transform translate-x-full`;

            const bgColors = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'warning': 'bg-yellow-500',
                'info': 'bg-blue-500'
            };

            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };

            notification.className += ` ${bgColors[type]}`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${icons[type]} mr-3"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // Initialize sidebar state on mobile
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.querySelector('[onclick="toggleSidebar()"]');

            if (window.innerWidth < 1024 &&
                !sidebar.contains(e.target) &&
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
                document.getElementById('sidebar-overlay').classList.add('hidden');
            }
        });

        // Employee Location Map Functions
        let employeeMap = null;
        let employeeMarkers = [];
        let employeeMarkersData = []; // Store marker data for filtering

        function toggleMapView() {
            const mapContainer = document.getElementById('mapContainer');
            const locationCards = document.getElementById('locationCards');
            const toggleText = document.getElementById('mapToggleText');

            if (mapContainer.classList.contains('hidden')) {
                mapContainer.classList.remove('hidden');
                locationCards.classList.add('hidden');
                toggleText.textContent = 'Show Cards';
                if (!employeeMap) {
                    initializeMap();
                }
            } else {
                mapContainer.classList.add('hidden');
                locationCards.classList.remove('hidden');
                toggleText.textContent = 'Show Map';
            }
        }

        function initializeMap() {
            // Check if Leaflet is available (you'll need to include it in the head)
            if (typeof L !== 'undefined') {
                // Initialize map centered on a default location
                employeeMap = L.map('employeeMap').setView([14.2785, 120.8677], 11); // DepEd Cavite coordinates as default

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(employeeMap);

                // Load employee locations
                loadEmployeeLocations();
            } else {
                showNotification('Map library not loaded. Please include Leaflet.js', 'error');
            }
        }

        function loadEmployeeLocations() {
            fetch('/admin/employee-locations')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        clearMapMarkers();
                        employeeMarkersData = []; // Clear stored data

                        // Add workplace markers
                        if (data.workplaces) {
                            data.workplaces.forEach(workplace => {
                                const workplaceCircle = L.circle([workplace.latitude, workplace.longitude], {
                                    color: '#10b981',
                                    fillColor: '#10b981',
                                    fillOpacity: 0.1,
                                    radius: workplace.radius, // Use actual radius in meters
                                    weight: 2,
                                    dashArray: '5, 5'
                                }).addTo(employeeMap);

                                workplaceCircle.bindPopup(`
                                    <strong>${workplace.name}</strong><br>
                                    ${workplace.address}<br>
                                    <small>Workplace Boundary (${workplace.radius}m radius)</small>
                                `);

                                employeeMarkers.push(workplaceCircle);
                            });
                        }

                        // Add employee location markers with smart offset for overlapping
                        if (data.employeeLocations) {
                            // Group locations by coordinates to detect overlaps
                            const locationGroups = {};
                            
                            data.employeeLocations.forEach(location => {
                                const key = `${location.latitude.toFixed(6)}_${location.longitude.toFixed(6)}`;
                                if (!locationGroups[key]) {
                                    locationGroups[key] = [];
                                }
                                locationGroups[key].push(location);
                            });

                            // Create markers with offset for overlapping locations
                            Object.values(locationGroups).forEach(group => {
                                const baseLocation = group[0];
                                
                                // If multiple users at same location, arrange in circle
                                if (group.length > 1) {
                                    const radius = 0.00008; // Much smaller offset - keeps markers very close
                                    const angleStep = (2 * Math.PI) / group.length;
                                    
                                    // Draw a subtle circle to show grouping
                                    const groupCircle = L.circle([baseLocation.latitude, baseLocation.longitude], {
                                        radius: 30, // 30 meters - original size
                                        color: '#3b82f6',
                                        fillColor: '#3b82f6',
                                        fillOpacity: 0.1,
                                        weight: 1,
                                        dashArray: '5, 5'
                                    }).addTo(employeeMap);
                                    
                                    employeeMarkers.push(groupCircle);
                                    
                                    // Add center badge showing count
                                    const centerBadge = L.marker([baseLocation.latitude, baseLocation.longitude], {
                                        icon: L.divIcon({
                                            className: 'custom-div-icon',
                                            html: `<div style="background-color: #3b82f6; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); border: 2px solid white;">${group.length}</div>`,
                                            iconSize: [24, 24],
                                            iconAnchor: [12, 12]
                                        }),
                                        zIndexOffset: 1000 // Keep badge on top
                                    }).addTo(employeeMap);
                                    
                                    centerBadge.bindPopup(`<strong>${group.length} employees</strong><br>at this location`);
                                    employeeMarkers.push(centerBadge);
                                }
                                
                                group.forEach((location, index) => {
                                    let lat = location.latitude;
                                    let lng = location.longitude;
                                    
                                    // Offset position if multiple markers at same location
                                    if (group.length > 1) {
                                        const radius = 0.00008; // Tiny offset - just enough to see separate pins
                                        const angle = index * ((2 * Math.PI) / group.length);
                                        lat += radius * Math.cos(angle);
                                        lng += radius * Math.sin(angle);
                                    }
                                    
                                    const color = getStatusColor(location.action);
                                    const employeeMarker = L.marker([lat, lng], {
                                        icon: L.divIcon({
                                            className: 'custom-div-icon',
                                            html: `<div style="color: ${color}; font-size: 28px; text-shadow: 0 0 3px white, 0 2px 4px rgba(0,0,0,0.3); animation: markerBounce 0.6s ease-out; animation-delay: ${index * 0.1}s;"><i class="fas fa-map-marker-alt"></i></div>`,
                                            iconSize: [28, 28],
                                            iconAnchor: [14, 28],
                                            popupAnchor: [0, -28]
                                        })
                                    }).addTo(employeeMap);

                                    const popupContent = `
                                        <strong>${location.user_name}</strong><br>
                                        Status: ${location.action.replace('_', ' ').toUpperCase()}<br>
                                        Time: ${new Date(location.timestamp).toLocaleString()}<br>
                                        <small>${location.address || `Coordinates: ${parseFloat(location.latitude).toFixed(4)}, ${parseFloat(location.longitude).toFixed(4)}`}</small>
                                        ${group.length > 1 ? `<br><small class="text-blue-600"><i class="fas fa-users"></i> ${group.length} at this location</small>` : ''}
                                    `;
                                    
                                    employeeMarker.bindPopup(popupContent);

                                    employeeMarkers.push(employeeMarker);

                                    // Store marker data for filtering (use original coordinates)
                                    employeeMarkersData.push({
                                        marker: employeeMarker,
                                        user_name: location.user_name.toLowerCase(),
                                        user_email: location.user_email ? location.user_email.toLowerCase() : '',
                                        user_id: location.user_id,
                                        action: location.action,
                                        timestamp: location.timestamp,
                                        originalIcon: employeeMarker.getIcon(),
                                        originalLat: location.latitude,
                                        originalLng: location.longitude
                                    });

                                    // Update location cards
                                    updateLocationCard(location.user_id, location);
                                });
                            });
                        }

                        // Auto-fit map to show all markers
                        if (employeeMarkers.length > 0) {
                            const group = new L.featureGroup(employeeMarkers);
                            employeeMap.fitBounds(group.getBounds().pad(0.1));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading employee locations:', error);
                    showNotification('Error loading employee locations', 'error');
                });
        }

        function getStatusColor(action) {
            switch (action) {
                case 'check_in':
                    return '#10b981'; // green
                case 'check_out':
                    return '#ef4444'; // red
                case 'break_start':
                    return '#f59e0b'; // yellow
                case 'break_end':
                    return '#10b981'; // green
                default:
                    return '#6b7280'; // gray
            }
        }

        function clearMapMarkers() {
            employeeMarkers.forEach(marker => {
                employeeMap.removeLayer(marker);
            });
            employeeMarkers = [];
        }

        function refreshEmployeeMap() {
            if (employeeMap) {
                loadEmployeeLocations();
            } else {
                // Refresh location cards data
                fetch('/admin/employee-locations')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.employeeLocations) {
                            data.employeeLocations.forEach(location => {
                                updateLocationCard(location.user_id, location);
                            });
                        }
                    })
                    .catch(error => console.error('Error refreshing data:', error));
            }
        }

        function updateLocationCard(userId, location) {
            const statusElement = document.getElementById(`user-status-${userId}`);
            const locationElement = document.getElementById(`user-location-${userId}`);

            if (statusElement && location) {
                // Update the badge with proper styling for table format
                const actionText = location.action.replace('_', ' ').toUpperCase();
                const bgColorClass = getStatusBgClass(location.action);
                const textColorClass = getStatusTextClass(location.action);
                const iconClass = getStatusIconClass(location.action);

                statusElement.className =
                    `inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ${bgColorClass} ${textColorClass}`;
                statusElement.innerHTML = `<i class="fas ${iconClass} mr-1"></i>${actionText}`;
            }

            if (locationElement && location) {
                // Try to show address first, then coordinates as fallback, then 'Unknown'
                let displayText = 'Unknown';
                let iconClass = 'fa-question-circle';
                let containerClass = 'flex items-center text-gray-500';

                if (location.address && location.address !== 'Location not available') {
                    displayText = location.address.length > 40 ? location.address.substring(0, 40) + '...' : location
                        .address;
                    iconClass = 'fa-map-marker-alt';
                    containerClass = 'flex items-center';
                } else if (location.latitude && location.longitude) {
                    displayText =
                        `Coordinates: ${parseFloat(location.latitude).toFixed(4)}, ${parseFloat(location.longitude).toFixed(4)}`;
                    iconClass = 'fa-map-marker-alt';
                    containerClass = 'flex items-center';
                }

                locationElement.className = `text-sm text-gray-900`;
                locationElement.innerHTML = `
                    <div class="${containerClass}">
                        <i class="fas ${iconClass} text-gray-400 mr-2"></i>
                        <span>${displayText}</span>
                    </div>
                `;
            }
        }

        function getStatusColorClass(action) {
            switch (action) {
                case 'check_in':
                    return 'green-600';
                case 'check_out':
                    return 'red-600';
                case 'break_start':
                    return 'yellow-600';
                case 'break_end':
                    return 'green-600';
                default:
                    return 'gray-600';
            }
        }

        function getStatusBgClass(action) {
            switch (action) {
                case 'check_in':
                    return 'bg-green-100';
                case 'check_out':
                    return 'bg-red-100';
                case 'break_start':
                    return 'bg-yellow-100';
                case 'break_end':
                    return 'bg-green-100';
                default:
                    return 'bg-gray-100';
            }
        }

        function getStatusTextClass(action) {
            switch (action) {
                case 'check_in':
                    return 'text-green-800';
                case 'check_out':
                    return 'text-red-800';
                case 'break_start':
                    return 'text-yellow-800';
                case 'break_end':
                    return 'text-green-800';
                default:
                    return 'text-gray-800';
            }
        }

        function getStatusIconClass(action) {
            switch (action) {
                case 'check_in':
                    return 'fa-sign-in-alt';
                case 'check_out':
                    return 'fa-sign-out-alt';
                case 'break_start':
                    return 'fa-pause';
                case 'break_end':
                    return 'fa-play';
                default:
                    return 'fa-minus';
            }
        }

        function showUserLocationDetails(userId) {
            fetch(`/admin/user-location-details/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.locations) {
                        showLocationHistoryModal(data.user_name, data.locations);
                    } else {
                        showNotification(data.message || 'No location data found for this user', 'info');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error loading user location details', 'error');
                });
        }

        function centerMapOnUser(userId) {
            if (!employeeMap) {
                toggleMapView();
                setTimeout(() => centerMapOnUser(userId), 1000);
                return;
            }

            fetch(`/admin/user-location-details/${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.locations && data.locations.length > 0) {
                        const latestLocation = data.locations[0];
                        employeeMap.setView([latestLocation.latitude, latestLocation.longitude], 16);
                        showNotification(`Centered map on ${data.user_name}'s location`, 'success');
                    } else {
                        showNotification('No location data found for this user', 'info');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error centering map on user', 'error');
                });
        }

        // Glass Modal Functions
        function showUserDetailsModal(user) {
            const modal = document.getElementById('userDetailsModal');
            const title = document.getElementById('userDetailsTitle');
            const content = document.getElementById('userDetailsContent');

            title.textContent = `${user.name} - User Details`;

            let workplacesList = 'No workplaces assigned';
            if (user.workplaces && user.workplaces.length > 0) {
                workplacesList = user.workplaces.map(wp =>
                    `<span class="inline-block bg-white bg-opacity-20 backdrop-filter backdrop-blur-sm px-3 py-1 rounded-full text-sm border border-white border-opacity-30 mr-2 mb-2">
                        ${wp.name}${wp.pivot.is_primary ? ' (Primary)' : ''} - ${wp.pivot.role}
                    </span>`
                ).join('');
            }

            const onlineStatus = user.last_activity ?
                (new Date(user.last_activity) > new Date(Date.now() - 5 * 60000) ? 'Online' : 'Offline') : 'Unknown';

            content.innerHTML = `
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                            <span class="text-white font-semibold text-2xl">${user.name.charAt(0).toUpperCase()}</span>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold">${user.name}</h4>
                            <p class="text-gray-800">${user.email}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Role:</span>
                            <span class="inline-block px-3 py-1 rounded-full text-sm ${user.role === 'admin' ? 'bg-red-500 bg-opacity-30' : 'bg-green-500 bg-opacity-30'} border border-white border-opacity-30">
                                ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Status:</span>
                            <span class="inline-block px-3 py-1 rounded-full text-sm ${onlineStatus === 'Online' ? 'bg-green-500 bg-opacity-30' : 'bg-gray-500 bg-opacity-30'} border border-white border-opacity-30">
                                ${onlineStatus}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Joined:</span>
                            <span>${new Date(user.created_at).toLocaleDateString()}</span>
                        </div>
                        
                        ${user.last_activity ? `
                            <div class="flex justify-between items-center">
                                <span class="text-gray-800">Last Activity:</span>
                                <span>${new Date(user.last_activity).toLocaleString()}</span>
                            </div>
                            ` : ''}
                    </div>
                    
                    <div>
                        <span class="text-gray-800 block mb-2">Workplace Assignments:</span>
                        <div>${workplacesList}</div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        function closeUserDetailsModal() {
            document.getElementById('userDetailsModal').classList.add('hidden');
        }

        function showLocationHistoryModal(userName, locations) {
            const modal = document.getElementById('locationHistoryModal');
            const title = document.getElementById('locationHistoryTitle');
            const content = document.getElementById('locationHistoryContent');

            title.textContent = `Location History - ${userName}`;

            if (locations.length === 0) {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-map-marker-alt text-gray-300 text-4xl mb-4"></i>
                        <p class="text-white text-lg">No location data available for today</p>
                    </div>
                `;
            } else {
                content.innerHTML = locations.map(location => {
                    const actionColor = getStatusColor(location.action);
                    const actionText = location.action.replace('_', ' ').toUpperCase();

                    // Use workplace address if available, otherwise fall back to coordinates
                    let addressDisplay = 'Location not available';
                    if (location.workplace_address) {
                        addressDisplay = location.workplace_address;
                    } else if (location.latitude && location.longitude) {
                        addressDisplay =
                            `Coordinates: ${parseFloat(location.latitude).toFixed(6)}, ${parseFloat(location.longitude).toFixed(6)}`;
                    }

                    return `
                        <div class="bg-white bg-opacity-10 backdrop-filter backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-20">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full" style="background-color: ${actionColor};"></div>
                                    <span class="text-black font-semibold">${actionText}</span>
                                </div>
                                <span class="text-gray-800 text-sm">${new Date(location.timestamp).toLocaleString()}</span>
                            </div>
                            <div class="ml-6 space-y-1 text-sm">
                                <div class="text-gray-800">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    ${addressDisplay}
                                </div>
                                ${location.workplace_name ? `
                                    <div class="text-gray-800">
                                        <i class="fas fa-building mr-2"></i>
                                        ${location.workplace_name}
                                    </div>
                                    ` : ''}
                                ${location.latitude && location.longitude ? `
                                    <div class="text-gray-800 text-xs">
                                        Lat: ${location.latitude.toFixed(6)}, Lng: ${location.longitude.toFixed(6)}
                                    </div>
                                    ` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            modal.classList.remove('hidden');
        }

        function closeLocationHistoryModal() {
            document.getElementById('locationHistoryModal').classList.add('hidden');
        }

        // Toggle more actions inline (for Users)
        function toggleMoreActions(userId) {
            const moreBtn = document.getElementById('more-btn-' + userId);
            const moreActions = document.getElementById('more-actions-' + userId);
            const moreBtnMobile = document.getElementById('more-btn-mobile-' + userId);
            const moreActionsMobile = document.getElementById('more-actions-mobile-' + userId);

            // Check current state (check both desktop and mobile)
            const isExpanded = moreActions?.style.display === 'flex' || moreActionsMobile?.style.display === 'flex';

            if (!isExpanded) {
                // Collapse all other rows first
                document.querySelectorAll('[id^="more-actions-"]').forEach(actions => {
                    if (actions.id !== 'more-actions-' + userId && actions.id !== 'more-actions-mobile-' + userId) {
                        actions.style.display = 'none';
                        const otherId = actions.id.replace('more-actions-', '').replace('more-actions-mobile-', '');
                        const otherBtn = document.getElementById('more-btn-' + otherId);
                        const otherBtnMobile = document.getElementById('more-btn-mobile-' + otherId);
                        if (otherBtn) otherBtn.style.display = 'inline-flex';
                        if (otherBtnMobile) otherBtnMobile.style.display = 'inline-flex';
                    }
                });

                // Expand this row - hide More button, show additional actions
                if (moreBtn) moreBtn.style.display = 'none';
                if (moreActions) moreActions.style.display = 'flex';
                if (moreBtnMobile) moreBtnMobile.style.display = 'none';
                if (moreActionsMobile) moreActionsMobile.style.display = 'flex';
            } else {
                // Collapse this row - show More button, hide additional actions
                if (moreBtn) moreBtn.style.display = 'inline-flex';
                if (moreActions) moreActions.style.display = 'none';
                if (moreBtnMobile) moreBtnMobile.style.display = 'inline-flex';
                if (moreActionsMobile) moreActionsMobile.style.display = 'none';
            }
        }

        // Toggle more actions for mobile users
        function toggleMoreActionsMobile(userId) {
            toggleMoreActions(userId);
        }

        // Toggle more actions inline (for Workplaces)
        function toggleMoreActionsWorkplace(workplaceId) {
            const moreBtn = document.getElementById('more-btn-wp-' + workplaceId);
            const moreActions = document.getElementById('more-actions-wp-' + workplaceId);
            const moreBtnMobile = document.getElementById('more-btn-wp-mobile-' + workplaceId);
            const moreActionsMobile = document.getElementById('more-actions-wp-mobile-' + workplaceId);

            // Check current state (check both desktop and mobile)
            const isExpanded = moreActions?.style.display === 'flex' || moreActionsMobile?.style.display === 'flex';

            if (!isExpanded) {
                // Collapse all other rows first
                document.querySelectorAll('[id^="more-actions-wp-"]').forEach(actions => {
                    if (actions.id !== 'more-actions-wp-' + workplaceId && actions.id !==
                        'more-actions-wp-mobile-' + workplaceId) {
                        actions.style.display = 'none';
                        const otherId = actions.id.replace('more-actions-wp-', '').replace(
                            'more-actions-wp-mobile-', '');
                        const otherBtn = document.getElementById('more-btn-wp-' + otherId);
                        const otherBtnMobile = document.getElementById('more-btn-wp-mobile-' + otherId);
                        if (otherBtn) otherBtn.style.display = 'inline-flex';
                        if (otherBtnMobile) otherBtnMobile.style.display = 'inline-flex';
                    }
                });

                // Expand this row - hide More button, show additional actions
                if (moreBtn) moreBtn.style.display = 'none';
                if (moreActions) moreActions.style.display = 'flex';
                if (moreBtnMobile) moreBtnMobile.style.display = 'none';
                if (moreActionsMobile) moreActionsMobile.style.display = 'flex';
            } else {
                // Collapse this row - show More button, hide additional actions
                if (moreBtn) moreBtn.style.display = 'inline-flex';
                if (moreActions) moreActions.style.display = 'none';
                if (moreBtnMobile) moreBtnMobile.style.display = 'inline-flex';
                if (moreActionsMobile) moreActionsMobile.style.display = 'none';
            }
        }

        // Scroll to All Users table
        function scrollToAllUsers() {
            const allUsersTable = document.getElementById('all-users-table');
            if (allUsersTable) {
                allUsersTable.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Optional: Add a highlight effect
                allUsersTable.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-2');
                setTimeout(() => {
                    allUsersTable.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-2');
                }, 2000);
            }
        }

        // Animate search bar - expand and contract
        function animateSearchBar() {
            const searchBar = document.getElementById('userSearchMain');
            if (searchBar) {
                const originalWidth = searchBar.offsetWidth;
                // Expand
                searchBar.style.width = (originalWidth * 1.15) + 'px';
                // Contract back after 300ms
                setTimeout(() => {
                    searchBar.style.width = originalWidth + 'px';
                }, 300);
            }
        }

        // Scroll to All Workplaces table
        function scrollToAllWorkplaces() {
            const allWorkplacesTable = document.getElementById('all-workplaces-table');
            if (allWorkplacesTable) {
                allWorkplacesTable.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Add a highlight effect
                allWorkplacesTable.classList.add('ring-2', 'ring-green-500', 'ring-offset-2');
                setTimeout(() => {
                    allWorkplacesTable.classList.remove('ring-2', 'ring-green-500', 'ring-offset-2');
                }, 2000);
            }
        }

        // Scroll to Assignments section
        function scrollToAssignments() {
            const assignmentsSection = document.getElementById('assignments-section');
            if (assignmentsSection) {
                assignmentsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Add a highlight effect
                assignmentsSection.classList.add('ring-2', 'ring-purple-500', 'ring-offset-2');
                setTimeout(() => {
                    assignmentsSection.classList.remove('ring-2', 'ring-purple-500', 'ring-offset-2');
                }, 2000);
            }
        }

        // View Workplace Details
        function viewWorkplace(workplaceId) {
            fetch(`/admin/workplaces/${workplaceId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showWorkplaceDetailsModal(data.workplace);
                    } else {
                        showNotification(data.message || 'Error loading workplace data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while loading workplace data', 'error');
                });
        }

        // Show Workplace Details Modal
        function showWorkplaceDetailsModal(workplace) {
            const modal = document.getElementById('userDetailsModal'); // Reuse user details modal
            const title = document.getElementById('userDetailsTitle');
            const content = document.getElementById('userDetailsContent');

            title.textContent = workplace.name + ' - Details';

            content.innerHTML = `
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-building text-green-600 mr-2"></i>
                            <span class="text-sm font-semibold text-black">Workplace Name</span>
                        </div>
                        <p class="text-black ml-6">${workplace.name}</p>
                    </div>
                    
                    <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>
                            <span class="text-sm font-semibold text-black">Address</span>
                        </div>
                        <p class="text-black ml-6">${workplace.address}</p>
                    </div>
                    
                    <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-map text-blue-600 mr-2"></i>
                            <span class="text-sm font-semibold text-black">Coordinates</span>
                        </div>
                        <p class="text-black ml-6">${workplace.latitude}, ${workplace.longitude}</p>
                    </div>
                    
                    <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-circle-notch text-indigo-600 mr-2"></i>
                            <span class="text-sm font-semibold text-black">Check-in Radius</span>
                        </div>
                        <p class="text-black ml-6">${workplace.radius} meters</p>
                    </div>
                    
                    <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas ${workplace.is_active ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600'} mr-2"></i>
                            <span class="text-sm font-semibold text-black">Status</span>
                        </div>
                        <p class="text-black ml-6">${workplace.is_active ? 'Active' : 'Inactive'}</p>
                    </div>
                    
                    <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-users text-purple-600 mr-2"></i>
                            <span class="text-sm font-semibold text-black">Assigned Users</span>
                        </div>
                        <p class="text-black ml-6">${workplace.users_count || 0} users</p>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        // Bulk Operations Functions
        function openBulkOperationsModal() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox-main:checked');

            if (selectedCheckboxes.length === 0) {
                showNotification('Please select at least one user first', 'warning');
                // Scroll to users table and highlight it
                scrollToAllUsers();
                return;
            }

            // Update selected count
            document.getElementById('selectedCount').textContent = selectedCheckboxes.length;

            // Show modal
            document.getElementById('bulkOperationsModal').classList.remove('hidden');
        }

        function closeBulkOperationsModal() {
            document.getElementById('bulkOperationsModal').classList.add('hidden');
            // Reset selections
            document.getElementById('bulkRoleSelect').value = '';
        }

        function getSelectedUserIds() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox-main:checked');
            return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
        }

        function executeBulkPasswordReset() {
            const userIds = getSelectedUserIds();

            if (userIds.length === 0) {
                showNotification('No users selected', 'warning');
                return;
            }

            if (!confirm(`Send password reset email to ${userIds.length} user(s)?`)) {
                return;
            }

            fetch('/admin/bulk-password-reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_ids: userIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Password reset emails sent to ${data.sent_count} user(s)`, 'success');
                        if (data.errors && data.errors.length > 0) {
                            console.log('Errors:', data.errors);
                            showNotification(`${data.errors.length} email(s) failed to send`, 'warning');
                        }
                        closeBulkOperationsModal();
                        // Uncheck all checkboxes
                        document.querySelectorAll('.user-checkbox-main:checked').forEach(cb => cb.checked = false);
                        document.getElementById('selectAllMain').checked = false;
                        updateBulkSelectionBadge();
                    } else {
                        showNotification(data.message || 'Error sending password reset emails', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while sending reset emails', 'error');
                });
        }

        function executeBulkRoleChange() {
            const role = document.getElementById('bulkRoleSelect').value;
            const userIds = getSelectedUserIds();

            if (!role) {
                showNotification('Please select a role', 'warning');
                return;
            }

            if (userIds.length === 0) {
                showNotification('No users selected', 'warning');
                return;
            }

            if (!confirm(`Change role to "${role}" for ${userIds.length} user(s)?`)) {
                return;
            }

            fetch('/admin/bulk-change-role', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_ids: userIds,
                        role: role
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Successfully updated role for ${userIds.length} user(s)`, 'success');
                        closeBulkOperationsModal();
                        // Uncheck all checkboxes
                        document.querySelectorAll('.user-checkbox-main:checked').forEach(cb => cb.checked = false);
                        document.getElementById('selectAllMain').checked = false;
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message || 'Error changing roles', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred during bulk role change', 'error');
                });
        }

        function executeBulkDelete() {
            const userIds = getSelectedUserIds();

            if (userIds.length === 0) {
                showNotification('No users selected', 'warning');
                return;
            }

            if (!confirm(
                    `⚠️ WARNING: Delete ${userIds.length} user(s)?\n\nThis action CANNOT be undone!\n\nType "DELETE" to confirm.`
                    )) {
                return;
            }

            // Additional confirmation for safety
            const confirmation = prompt(`Type "DELETE" in CAPS to confirm deletion of ${userIds.length} user(s):`);
            if (confirmation !== 'DELETE') {
                showNotification('Deletion cancelled - confirmation text did not match', 'info');
                return;
            }

            fetch('/admin/bulk-delete-users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        user_ids: userIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Successfully deleted ${userIds.length} user(s)`, 'success');
                        closeBulkOperationsModal();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification(data.message || 'Error deleting users', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred during bulk deletion', 'error');
                });
        }

        // Dropdown menu functions (for other dropdowns if any)
        function toggleUserDropdown(userId, event) {
            event.stopPropagation();
            const dropdownId = 'more-' + userId;
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            const wasHidden = dropdown.classList.contains('hidden');

            // Close all other dropdowns first (prevents stacking)
            document.querySelectorAll('[id^="more-"]').forEach(dd => {
                if (dd.id !== dropdownId) {
                    dd.classList.add('hidden');
                }
            });

            // Toggle the clicked dropdown
            if (wasHidden) {
                dropdown.classList.remove('hidden');

                // Check if dropdown is cut off on the right edge
                const rect = dropdown.getBoundingClientRect();
                const viewportWidth = window.innerWidth;

                // If dropdown extends beyond viewport, align it to the left instead
                if (rect.right > viewportWidth - 10) {
                    dropdown.style.right = '0';
                    dropdown.style.left = 'auto';
                } else {
                    dropdown.style.right = 'auto';
                    dropdown.style.left = '0';
                }
            } else {
                dropdown.classList.add('hidden');
            }
        }

        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            const wasHidden = dropdown.classList.contains('hidden');

            // Close all other dropdowns
            document.querySelectorAll('[id^="more-"]').forEach(dd => {
                if (dd.id !== dropdownId) {
                    dd.classList.add('hidden');
                }
            });

            // Toggle
            if (wasHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        function hideDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const isDropdownButton = e.target.closest('[onclick*="toggleDropdown"]') ||
                e.target.closest('[onclick*="toggleUserDropdown"]');
            const isDropdownContent = e.target.closest('[id^="more-"]');

            if (!isDropdownButton && !isDropdownContent) {
                document.querySelectorAll('[id^="more-"]').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        // Close modals when clicking outside
        document.getElementById('userDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserDetailsModal();
            }
        });

        document.getElementById('locationHistoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLocationHistoryModal();
            }
        });

        // Admin Account Modal Functions
        // Notification Settings Functions
        function loadNotificationSettings() {
            fetch('/admin/notification-settings', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('notificationType').value = data.notification_type || 'email';
                    document.getElementById('smsApiUrl').value = data.sms_api_url || '';
                    toggleSmsApiField();
                }
            })
            .catch(error => {
                console.error('Error loading notification settings:', error);
                showNotification('Failed to load notification settings', 'error');
            });
        }

        function saveNotificationSettings() {
            const notificationType = document.getElementById('notificationType').value;
            const smsApiUrl = document.getElementById('smsApiUrl').value.trim();

            // Validate SMS API URL if SMS is enabled
            if ((notificationType === 'sms' || notificationType === 'both')) {
                if (!smsApiUrl) {
                    showNotification('SMS API URL is required when SMS notifications are enabled', 'error');
                    return;
                }
                
                // Validate URL format
                if (!smsApiUrl.startsWith('http://') && !smsApiUrl.startsWith('https://')) {
                    showNotification('SMS API URL must start with http:// or https://', 'error');
                    document.getElementById('smsApiUrl').focus();
                    return;
                }

                // Additional URL validation
                try {
                    new URL(smsApiUrl);
                } catch (e) {
                    showNotification('Please enter a valid URL (e.g., https://sms.example.com/send)', 'error');
                    document.getElementById('smsApiUrl').focus();
                    return;
                }
            }

            const data = {
                notification_type: notificationType,
                sms_api_url: smsApiUrl || null
            };

            fetch('/admin/notification-settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Notification settings saved successfully!', 'success');
                } else {
                    showNotification(data.message || 'Failed to save settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving notification settings:', error);
                showNotification('An error occurred while saving settings', 'error');
            });
        }

        function testNotification() {
            const notificationType = document.getElementById('notificationType').value;
            
            if (notificationType === 'none') {
                showNotification('Notifications are disabled. Please select a notification method first.', 'warning');
                return;
            }

            if (!confirm('This will send a test notification to your account. Continue?')) {
                return;
            }

            fetch('/admin/test-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    notification_type: notificationType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Test notification sent successfully! Check your email/SMS.', 'success');
                } else {
                    showNotification(data.message || 'Failed to send test notification', 'error');
                }
            })
            .catch(error => {
                console.error('Error sending test notification:', error);
                showNotification('An error occurred while sending test notification', 'error');
            });
        }

        function toggleSmsApiField() {
            const notificationType = document.getElementById('notificationType').value;
            const smsApiUrlField = document.getElementById('smsApiUrlField');
            
            if (notificationType === 'sms' || notificationType === 'both') {
                smsApiUrlField.style.display = 'block';
            } else {
                smsApiUrlField.style.display = 'none';
            }
        }

        // Listen for notification type changes
        document.addEventListener('DOMContentLoaded', function() {
            const notificationTypeSelect = document.getElementById('notificationType');
            if (notificationTypeSelect) {
                notificationTypeSelect.addEventListener('change', toggleSmsApiField);
                
                // Load settings when settings section is opened
                const settingsNavLink = document.querySelector('[data-section="settings"]');
                if (settingsNavLink) {
                    settingsNavLink.addEventListener('click', function() {
                        setTimeout(loadNotificationSettings, 100);
                    });
                }
            }
        });

        function openAdminAccountModal() {
            document.getElementById('adminAccountForm').reset();
            // Pre-fill current values
            document.getElementById('adminNewName').value = '{{ Auth::user()->name }}';
            document.getElementById('adminNewEmail').value = '{{ Auth::user()->email }}';
            document.getElementById('adminAccountModal').classList.remove('hidden');
        }

        function closeAdminAccountModal() {
            document.getElementById('adminAccountModal').classList.add('hidden');
            document.getElementById('adminAccountForm').reset();
        }

        // Manual Entry Code Modal Functions
        function openManualEntryCodeModal() {
            document.getElementById('manualEntryCodeForm').reset();
            document.getElementById('manualEntryCodeModal').classList.remove('hidden');

            // Fetch and display current code
            fetchCurrentManualEntryCode();
        }

        function closeManualEntryCodeModal() {
            document.getElementById('manualEntryCodeModal').classList.add('hidden');
            document.getElementById('manualEntryCodeForm').reset();
        }

        function fetchCurrentManualEntryCode() {
            fetch('/admin/settings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.settings && data.settings.manual_entry_code) {
                        // Display masked version
                        const code = data.settings.manual_entry_code;
                        document.getElementById('currentManualEntryCode').textContent = code;
                    } else {
                        // Default code
                        document.getElementById('currentManualEntryCode').textContent = 'DEPED2025';
                    }
                })
                .catch(error => {
                    console.error('Error fetching manual entry code:', error);
                    document.getElementById('currentManualEntryCode').textContent = 'DEPED2025';
                });
        }

        function submitManualEntryCode() {
            const adminPassword = document.getElementById('codeAdminPassword').value;
            const newCode = document.getElementById('newManualEntryCode').value.trim();
            const confirmCode = document.getElementById('confirmManualEntryCode').value.trim();

            // Validation
            if (!adminPassword) {
                showNotification('Please enter your admin password', 'error');
                return;
            }

            if (!newCode || newCode.length < 4) {
                showNotification('Access code must be at least 4 characters', 'error');
                return;
            }

            if (newCode.length > 20) {
                showNotification('Access code must be 20 characters or less', 'error');
                return;
            }

            if (newCode !== confirmCode) {
                showNotification('Access codes do not match', 'error');
                return;
            }

            // Confirm action
            if (!confirm(`Are you sure you want to update the manual entry code to "${newCode}"? This will be logged.`)) {
                return;
            }

            const requestData = {
                admin_password: adminPassword,
                key: 'manual_entry_code',
                value: newCode
            };

            fetch('/admin/update-manual-entry-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Manual entry code updated successfully!', 'success');
                        closeManualEntryCodeModal();
                        // Update displayed code
                        document.getElementById('currentManualEntryCode').textContent = newCode;
                    } else {
                        showNotification(data.message || 'Failed to update access code', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating the access code', 'error');
                });
        }

        // Load current code on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchCurrentManualEntryCode();
        });

        document.getElementById('adminAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const currentPassword = document.getElementById('adminCurrentPassword').value;
            const securityPhrase = document.getElementById('adminSecurityPhrase').value;
            const newName = document.getElementById('adminNewName').value;
            const newEmail = document.getElementById('adminNewEmail').value;
            const newPassword = document.getElementById('adminNewPassword').value;
            const newPasswordConfirm = document.getElementById('adminNewPasswordConfirm').value;

            // Validate security phrase
            if (securityPhrase !== 'CONFIRM UPDATE ADMIN') {
                showNotification('Security phrase incorrect!', 'error');
                return;
            }

            // Validate password confirmation
            if (newPassword && newPassword !== newPasswordConfirm) {
                showNotification('New passwords do not match', 'error');
                return;
            }

            // Confirm action
            if (!confirm(
                    'Are you absolutely sure you want to modify the admin account? This action will be logged.')) {
                return;
            }

            const requestData = {
                current_password: currentPassword,
                security_phrase: securityPhrase,
                name: newName,
                email: newEmail
            };

            if (newPassword) {
                requestData.new_password = newPassword;
                requestData.new_password_confirmation = newPasswordConfirm;
            }

            fetch('/admin/update-admin-account', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Admin account updated successfully', 'success');
                        closeAdminAccountModal();

                        // If email or password was changed, might need to re-login
                        if (data.changes.includes('Email changed') || data.changes.includes(
                            'Password changed')) {
                            setTimeout(() => {
                                showNotification('Please log in again with your new credentials',
                                    'info');
                                window.location.href = '/logout';
                            }, 2000);
                        } else {
                            // Just reload the page to update the name
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        showNotification(data.message || 'Error updating admin account', 'error');
                        if (data.errors) {
                            Object.values(data.errors).forEach(errorArray => {
                                errorArray.forEach(error => showNotification(error, 'error'));
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating admin account', 'error');
                });
        });

        // Activity Logs Modal Functions
        let currentActivityPage = 1;
        let activityLogsData = [];

        function openActivityLogsModal() {
            document.getElementById('activityLogsModal').classList.remove('hidden');
            loadActivityLogs();
        }

        function closeActivityLogsModal() {
            document.getElementById('activityLogsModal').classList.add('hidden');
        }

        function loadActivityLogs(page = 1) {
            currentActivityPage = page;
            const searchQuery = document.getElementById('activitySearchInput').value;
            const actionFilter = document.getElementById('activityActionFilter').value;

            const tbody = document.getElementById('activityLogsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-600">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading activity logs...</p>
                    </td>
                </tr>
            `;

            // Build query parameters
            let queryParams = `page=${page}&per_page=50`;
            if (searchQuery) {
                queryParams += `&search=${encodeURIComponent(searchQuery)}`;
            }
            if (actionFilter) {
                queryParams += `&action=${encodeURIComponent(actionFilter)}`;
            }

            fetch(`/admin/activity-logs?${queryParams}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        activityLogsData = data.logs.data;
                        renderActivityLogs(data.logs);
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-red-600">
                                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                    <p>Error loading activity logs</p>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-red-600">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>Error loading activity logs</p>
                            </td>
                        </tr>
                    `;
                });
        }

        function renderActivityLogs(logsData) {
            const tbody = document.getElementById('activityLogsTableBody');

            if (!logsData.data || logsData.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-600">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No activity logs found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = logsData.data.map(log => {
                const date = new Date(log.created_at);
                const actionBadgeColors = {
                    'login': 'bg-blue-100 text-blue-800',
                    'logout': 'bg-gray-100 text-gray-800',
                    'create_user': 'bg-green-100 text-green-800',
                    'update_user': 'bg-yellow-100 text-yellow-800',
                    'delete_user': 'bg-red-100 text-red-800',
                    'create_workplace': 'bg-green-100 text-green-800',
                    'update_workplace': 'bg-yellow-100 text-yellow-800',
                    'delete_workplace': 'bg-red-100 text-red-800',
                    'assign_user_workplace': 'bg-indigo-100 text-indigo-800',
                    'remove_user_workplace': 'bg-orange-100 text-orange-800',
                    'update_admin_account': 'bg-purple-100 text-purple-800',
                    'failed_admin_update': 'bg-red-100 text-red-800',
                    'update_setting': 'bg-indigo-100 text-indigo-800',
                    // Legacy export actions (for old logs only)
                    'export_activity_logs_csv': 'bg-teal-100 text-teal-800',
                    'export_activity_logs_excel': 'bg-cyan-100 text-cyan-800',
                    // Active report export actions
                    'export_attendance_report_csv': 'bg-emerald-100 text-emerald-800',
                    'export_attendance_report_excel': 'bg-lime-100 text-lime-800'
                };
                const badgeColor = actionBadgeColors[log.action] || 'bg-gray-100 text-gray-800';

                return `
                    <tr class="hover:bg-white hover:bg-opacity-20 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-800">
                            ${date.toLocaleString()}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-800">
                            ${log.admin ? log.admin.name : 'Unknown'}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold ${badgeColor}">
                                ${log.action.replace(/_/g, ' ').toUpperCase()}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-800">
                            ${log.description}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            ${log.ip_address || 'N/A'}
                        </td>
                    </tr>
                `;
            }).join('');

            // Update pagination info
            document.getElementById('activityLogsShowing').textContent =
                `${logsData.from || 0} - ${logsData.to || 0} of ${logsData.total || 0}`;

            // Render pagination buttons
            renderActivityLogsPagination(logsData);
        }

        function renderActivityLogsPagination(logsData) {
            const paginationContainer = document.getElementById('activityLogsPaginationButtons');

            if (logsData.last_page <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let buttonsHtml = '';

            // Previous button
            if (logsData.current_page > 1) {
                buttonsHtml += `
                    <button onclick="loadActivityLogs(${logsData.current_page - 1})" 
                            class="px-3 py-1 bg-white bg-opacity-40 text-black rounded hover:bg-opacity-60 text-sm">
                        Previous
                    </button>
                `;
            }

            // Page numbers
            for (let i = 1; i <= logsData.last_page; i++) {
                if (i === logsData.current_page) {
                    buttonsHtml += `
                        <button class="px-3 py-1 bg-purple-600 text-white rounded text-sm font-semibold">
                            ${i}
                        </button>
                    `;
                } else if (i === 1 || i === logsData.last_page || Math.abs(i - logsData.current_page) <= 2) {
                    buttonsHtml += `
                        <button onclick="loadActivityLogs(${i})" 
                                class="px-3 py-1 bg-white bg-opacity-40 text-black rounded hover:bg-opacity-60 text-sm">
                            ${i}
                        </button>
                    `;
                } else if (Math.abs(i - logsData.current_page) === 3) {
                    buttonsHtml += `<span class="px-2 text-black">...</span>`;
                }
            }

            // Next button
            if (logsData.current_page < logsData.last_page) {
                buttonsHtml += `
                    <button onclick="loadActivityLogs(${logsData.current_page + 1})" 
                            class="px-3 py-1 bg-white bg-opacity-40 text-black rounded hover:bg-opacity-60 text-sm">
                        Next
                    </button>
                `;
            }

            paginationContainer.innerHTML = buttonsHtml;
        }

        // Settings functionality
        function saveSetting(key, value) {
            fetch('/admin/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        key: key,
                        value: value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Setting saved:', key, value);
                    } else {
                        showNotification('Error saving setting', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving setting:', error);
                });
        }

        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const settingName = this.id.replace('setting-', '');
                const isEnabled = this.checked;

                // Save to backend
                saveSetting(settingName, isEnabled);

                showNotification(
                    `Setting "${settingName.replace(/-/g, ' ')}" ${isEnabled ? 'enabled' : 'disabled'}`,
                    'success');
            });
        });

        // Default radius change handler
        const defaultRadiusInput = document.getElementById('setting-default-radius');
        if (defaultRadiusInput) {
            let radiusTimeout;
            defaultRadiusInput.addEventListener('input', function() {
                clearTimeout(radiusTimeout);
                const value = this.value;
                radiusTimeout = setTimeout(() => {
                    // Save to backend
                    saveSetting('default_radius', parseInt(value));
                    showNotification(`Default radius updated to ${value} meters`, 'success');
                }, 1000);
            });
        }

        // Activity logs search and filter handlers
        const activitySearchInput = document.getElementById('activitySearchInput');
        if (activitySearchInput) {
            let searchTimeout;
            activitySearchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadActivityLogs(1); // Reset to page 1 when searching
                }, 500); // Debounce for 500ms
            });
        }

        const activityActionFilter = document.getElementById('activityActionFilter');
        if (activityActionFilter) {
            activityActionFilter.addEventListener('change', function() {
                loadActivityLogs(1); // Reset to page 1 when filtering
            });
        }

        // Close admin account modal when clicking outside
        document.getElementById('adminAccountModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAdminAccountModal();
            }
        });

        // Close activity logs modal when clicking outside
        document.getElementById('activityLogsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeActivityLogsModal();
            }
        });

        // ============================================
        // ATTENDANCE LOGS FUNCTIONALITY (Assigned vs Non-Assigned)
        // ============================================

        let currentAttendanceLogsPage = 1;
        let attendanceLogsData = [];

        function openAttendanceLogsModal() {
            document.getElementById('attendanceLogsModal').classList.remove('hidden');
            // Set default date to today
            document.getElementById('attendanceLogsDateFilter').value = new Date().toISOString().split('T')[0];
            loadAttendanceLogs();
        }

        function closeAttendanceLogsModal() {
            document.getElementById('attendanceLogsModal').classList.add('hidden');
        }

        function loadAttendanceLogs(page = 1) {
            currentAttendanceLogsPage = page;
            const searchQuery = document.getElementById('attendanceLogsSearchInput').value;
            const typeFilter = document.getElementById('attendanceLogsTypeFilter').value;
            const dateFilter = document.getElementById('attendanceLogsDateFilter').value;

            const tbody = document.getElementById('attendanceLogsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-600">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading attendance logs...</p>
                    </td>
                </tr>
            `;

            // Build query parameters
            let queryParams = `page=${page}&per_page=50`;
            if (searchQuery) {
                queryParams += `&search=${encodeURIComponent(searchQuery)}`;
            }
            if (typeFilter) {
                queryParams += `&type=${encodeURIComponent(typeFilter)}`;
            }
            if (dateFilter) {
                queryParams += `&date=${encodeURIComponent(dateFilter)}`;
            }

            fetch(`/admin/attendance-logs?${queryParams}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        attendanceLogsData = data.logs.data;
                        renderAttendanceLogs(data.logs, data.stats);
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-red-600">
                                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                    <p>Error loading attendance logs</p>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-red-600">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>Error loading attendance logs</p>
                            </td>
                        </tr>
                    `;
                });
        }

        function renderAttendanceLogs(logsData, stats) {
            const tbody = document.getElementById('attendanceLogsTableBody');

            // Update stats
            document.getElementById('attendanceLogsTotalCount').textContent = stats.total || 0;
            document.getElementById('attendanceLogsAssignedCount').textContent = stats.assigned || 0;
            document.getElementById('attendanceLogsNonAssignedCount').textContent = stats.non_assigned || 0;

            if (!logsData.data || logsData.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-600">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No attendance logs found</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = logsData.data.map(log => {
                const date = new Date(log.check_in_time || log.created_at);
                const isAssigned = log.is_assigned_workplace;
                const typeBadge = isAssigned 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Assigned</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800"><i class="fas fa-map-marker-alt"></i> Off-Site</span>';
                
                const statusBadge = log.status === 'present' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Present</span>'
                    : log.status === 'late'
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Late</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">' + log.status + '</span>';

                const coordinates = log.latitude && log.longitude 
                    ? `${parseFloat(log.latitude).toFixed(6)}, ${parseFloat(log.longitude).toFixed(6)}`
                    : 'N/A';

                return `
                    <tr class="hover:bg-white hover:bg-opacity-20 transition-colors ${!isAssigned ? 'bg-orange-50 bg-opacity-30' : ''}">
                        <td class="px-4 py-3 text-xs text-gray-800">
                            <div>${date.toLocaleDateString()}</div>
                            <div class="text-gray-600">${date.toLocaleTimeString()}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-800">
                            <div class="font-medium">${log.user ? log.user.name : 'Unknown'}</div>
                            <div class="text-gray-600">${log.user ? log.user.email : 'N/A'}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-800">
                            ${log.workplace ? log.workplace.name : 'N/A'}
                        </td>
                        <td class="px-4 py-3">
                            ${typeBadge}
                        </td>
                        <td class="px-4 py-3">
                            ${statusBadge}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            <i class="fas fa-map-pin text-red-500 mr-1"></i>${coordinates}
                        </td>
                    </tr>
                `;
            }).join('');

            // Update pagination info
            document.getElementById('attendanceLogsShowing').textContent =
                `${logsData.from || 0} - ${logsData.to || 0} of ${logsData.total || 0}`;

            // Render pagination buttons
            renderAttendanceLogsPagination(logsData);
        }

        function renderAttendanceLogsPagination(logsData) {
            const paginationContainer = document.getElementById('attendanceLogsPaginationButtons');

            if (logsData.last_page <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let buttonsHtml = '';

            // Previous button
            if (logsData.current_page > 1) {
                buttonsHtml += `
                    <button onclick="loadAttendanceLogs(${logsData.current_page - 1})" 
                        class="px-3 py-1 text-xs bg-white bg-opacity-60 text-black rounded hover:bg-opacity-80 transition-all">
                        Previous
                    </button>
                `;
            }

            // Page numbers (show max 5)
            const startPage = Math.max(1, logsData.current_page - 2);
            const endPage = Math.min(logsData.last_page, logsData.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === logsData.current_page 
                    ? 'bg-orange-600 text-white' 
                    : 'bg-white bg-opacity-60 text-black hover:bg-opacity-80';
                buttonsHtml += `
                    <button onclick="loadAttendanceLogs(${i})" 
                        class="px-3 py-1 text-xs ${activeClass} rounded transition-all">
                        ${i}
                    </button>
                `;
            }

            // Next button
            if (logsData.current_page < logsData.last_page) {
                buttonsHtml += `
                    <button onclick="loadAttendanceLogs(${logsData.current_page + 1})" 
                        class="px-3 py-1 text-xs bg-white bg-opacity-60 text-black rounded hover:bg-opacity-80 transition-all">
                        Next
                    </button>
                `;
            }

            paginationContainer.innerHTML = buttonsHtml;
        }

        // Event listeners for attendance logs filters
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceLogsSearch = document.getElementById('attendanceLogsSearchInput');
            const attendanceLogsType = document.getElementById('attendanceLogsTypeFilter');
            const attendanceLogsDate = document.getElementById('attendanceLogsDateFilter');

            if (attendanceLogsSearch) {
                attendanceLogsSearch.addEventListener('input', debounce(function() {
                    loadAttendanceLogs(1);
                }, 500));
            }

            if (attendanceLogsType) {
                attendanceLogsType.addEventListener('change', function() {
                    loadAttendanceLogs(1);
                });
            }

            if (attendanceLogsDate) {
                attendanceLogsDate.addEventListener('change', function() {
                    loadAttendanceLogs(1);
                });
            }
        });

        // ============================================
        // REPORTS FUNCTIONALITY
        // ============================================

        let currentReportData = null;

        // Initialize report dates based on report type
        function initializeReportDates() {
            const reportType = document.getElementById('reportType').value;
            const today = new Date();
            let startDate, endDate;

            if (reportType === 'weekly') {
                // Get start of week (Monday)
                const day = today.getDay();
                const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                startDate = new Date(today.setDate(diff));
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
            } else if (reportType === 'monthly') {
                // Get start and end of month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else {
                // Custom - default to last 30 days
                endDate = new Date();
                startDate = new Date();
                startDate.setDate(startDate.getDate() - 30);
            }

            document.getElementById('reportStartDate').value = formatDateForInput(startDate);
            document.getElementById('reportEndDate').value = formatDateForInput(endDate);
        }

        function formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Report type change handler
        document.addEventListener('DOMContentLoaded', function() {
            const reportTypeSelect = document.getElementById('reportType');
            if (reportTypeSelect) {
                reportTypeSelect.addEventListener('change', initializeReportDates);
                initializeReportDates(); // Initialize on load
            }

            // Set initial value for employee search
            const reportUserSearch = document.getElementById('reportUserSearch');
            if (reportUserSearch && !reportUserSearch.value) {
                reportUserSearch.value = 'All Employees';
            }

            // Report table search
            const reportTableSearch = document.getElementById('reportTableSearch');
            if (reportTableSearch) {
                reportTableSearch.addEventListener('input', function(e) {
                    filterReportTable(e.target.value);
                });
            }

            // Employee search functionality
            const reportUserResults = document.getElementById('reportUserResults');
            const reportUserFilter = document.getElementById('reportUserFilter');

            if (reportUserSearch) {
                let searchTimeout;

                reportUserSearch.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    const searchTerm = e.target.value.trim();

                    if (searchTerm.length === 0) {
                        reportUserResults.classList.add('hidden');
                        reportUserFilter.value = '';
                        return;
                    }

                    if (searchTerm.length < 2) return;

                    searchTimeout = setTimeout(() => {
                        searchEmployees(searchTerm);
                    }, 300);
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!reportUserSearch.contains(e.target) && !reportUserResults.contains(e.target)) {
                        reportUserResults.classList.add('hidden');
                    }
                });
            }
        });

        // Search employees function
        function searchEmployees(searchTerm) {
            const resultsDiv = document.getElementById('reportUserResults');

            fetch(`/admin/users?search=${encodeURIComponent(searchTerm)}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        resultsDiv.innerHTML = `
                        <div class="py-2">
                            <div onclick="selectEmployee(null, 'All Employees')" 
                                 class="px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700 font-medium border-b border-gray-200">
                                <i class="fas fa-users mr-2 text-indigo-600"></i>All Employees
                            </div>
                            ${data.map(user => `
                                    <div onclick="selectEmployee(${user.id}, '${user.name.replace(/'/g, "\\'")}')" 
                                         class="px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                            <span class="text-indigo-600 font-semibold text-xs">
                                                ${user.name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">${user.name}</div>
                                            <div class="text-xs text-gray-500">${user.email}</div>
                                        </div>
                                    </div>
                                `).join('')}
                        </div>
                    `;
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.innerHTML = `
                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                            No employees found
                        </div>
                    `;
                        resultsDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error searching employees:', error);
                });
        }

        // Select employee from search results
        function selectEmployee(userId, userName) {
            document.getElementById('reportUserFilter').value = userId || '';
            document.getElementById('reportUserSearch').value = userName;
            document.getElementById('reportUserResults').classList.add('hidden');
        }

        // Format minutes to hours and minutes display (e.g., "5hrs 30mins", "45mins", "2hrs")
        function formatHoursMinutes(totalMinutes) {
            if (!totalMinutes || totalMinutes === 0) return '0mins';

            const hours = Math.floor(totalMinutes / 60);
            const minutes = Math.round(totalMinutes % 60);

            if (hours > 0 && minutes > 0) {
                return `${hours}hr${hours > 1 ? 's' : ''} ${minutes}min${minutes > 1 ? 's' : ''}`;
            } else if (hours > 0) {
                return `${hours}hr${hours > 1 ? 's' : ''}`;
            } else {
                return `${minutes}min${minutes > 1 ? 's' : ''}`;
            }
        }

        // Calculate work hours and late minutes from attendance logs
        function calculateAttendanceMetrics(attendance) {
            let workMinutes = 0;
            let lateMinutes = 0;
            let checkInTime = null;
            let checkOutTime = null;
            let status = attendance.status || 'N/A';

            // Extract times from logs if available
            if (attendance.logs && attendance.logs.length > 0) {
                const checkInLog = attendance.logs.find(log => log.action === 'check_in');
                const checkOutLog = attendance.logs.find(log => log.action === 'check_out');

                checkInTime = checkInLog ? checkInLog.timestamp : attendance.check_in_time;
                checkOutTime = checkOutLog ? checkOutLog.timestamp : attendance.check_out_time;
            } else {
                checkInTime = attendance.check_in_time;
                checkOutTime = attendance.check_out_time;
            }

            // Debug: Log the raw times
            console.log('Raw checkInTime:', checkInTime, 'for user:', attendance.user?.name);

            // Helper to parse datetime properly
            function parseDateTime(dateTimeStr) {
                if (!dateTimeStr) return null;

                // Try to parse as ISO format first
                let dateObj = null;

                // If it contains a space, replace with T for ISO format
                if (typeof dateTimeStr === 'string') {
                    if (dateTimeStr.includes(' ')) {
                        const isoStr = dateTimeStr.replace(' ', 'T');
                        dateObj = new Date(isoStr);
                    } else if (dateTimeStr.includes('T')) {
                        dateObj = new Date(dateTimeStr);
                    } else if (dateTimeStr.match(/^\d{2}:\d{2}/)) {
                        // Just a time string like "08:28:33"
                        const date = attendance.date;
                        dateObj = new Date(`${date}T${dateTimeStr}`);
                    } else {
                        dateObj = new Date(dateTimeStr);
                    }
                } else {
                    dateObj = new Date(dateTimeStr);
                }

                console.log('Parsed datetime:', dateTimeStr, '→', dateObj);
                return dateObj;
            }

            // Calculate work hours
            if (checkInTime && checkOutTime) {
                const checkIn = parseDateTime(checkInTime);
                const checkOut = parseDateTime(checkOutTime);

                if (checkIn && checkOut && !isNaN(checkIn) && !isNaN(checkOut)) {
                    workMinutes = (checkOut - checkIn) / (1000 * 60);

                    // Subtract break duration
                    if (attendance.break_duration) {
                        workMinutes -= attendance.break_duration;
                    }

                    workMinutes = Math.max(0, workMinutes);

                    // Calculate late minutes (after 9:00 AM)
                    const checkInHour = checkIn.getHours();
                    const checkInMinute = checkIn.getMinutes();
                    const checkInTotalMinutes = (checkInHour * 60) + checkInMinute;
                    const lateThreshold = 9 * 60; // 9:00 AM

                    console.log('Check-in hour:', checkInHour, 'minute:', checkInMinute, 'total minutes:',
                        checkInTotalMinutes);

                    if (checkInTotalMinutes > lateThreshold) {
                        lateMinutes = checkInTotalMinutes - lateThreshold;
                        if (status !== 'absent') {
                            status = 'late';
                        }
                    } else {
                        if (status !== 'absent') {
                            status = 'present';
                        }
                    }
                }
            } else if (checkInTime && !checkOutTime) {
                // Still working
                const checkIn = parseDateTime(checkInTime);

                if (checkIn && !isNaN(checkIn)) {
                    const now = new Date();
                    workMinutes = (now - checkIn) / (1000 * 60);

                    if (attendance.break_duration) {
                        workMinutes -= attendance.break_duration;
                    }

                    workMinutes = Math.max(0, workMinutes);

                    // Calculate late
                    const checkInHour = checkIn.getHours();
                    const checkInMinute = checkIn.getMinutes();
                    const checkInTotalMinutes = (checkInHour * 60) + checkInMinute;

                    if (checkInTotalMinutes > 540) {
                        lateMinutes = checkInTotalMinutes - 540;
                        status = 'late';
                    }
                }
            }

            return {
                workMinutes: workMinutes,
                lateMinutes: lateMinutes,
                status: status,
                checkInTime: checkInTime,
                checkOutTime: checkOutTime
            };
        }

        // Generate attendance report
        function generateAttendanceReport() {
            const reportType = document.getElementById('reportType').value;
            const startDate = document.getElementById('reportStartDate').value;
            const endDate = document.getElementById('reportEndDate').value;
            const userId = document.getElementById('reportUserFilter').value;
            const workplaceId = document.getElementById('reportWorkplaceFilter').value;

            if (!startDate || !endDate) {
                showNotification('Please select start and end dates', 'error');
                return;
            }

            // Show loading state
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <i class="fas fa-spinner fa-spin text-4xl mb-3 text-indigo-600"></i>
                        <p class="text-gray-500">Generating report...</p>
                    </td>
                </tr>
            `;

            // Build query parameters
            let queryParams = new URLSearchParams({
                report_type: reportType,
                start_date: startDate,
                end_date: endDate
            });

            if (userId) queryParams.append('user_id', userId);
            if (workplaceId) queryParams.append('workplace_id', workplaceId);

            // Fetch report data
            fetch(`/admin/reports/attendance?${queryParams.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => {
                    // Check if response is ok before parsing
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Server response:', text);
                            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Report data received:', data);
                    if (data.success) {
                        currentReportData = data;
                        displayReportData(data);
                        updateReportStats(data.stats);
                        showNotification('Report generated successfully', 'success');
                    } else {
                        const errorMsg = data.message || data.error || 'Failed to generate report';
                        console.error('Report generation failed:', errorMsg);
                        showNotification(errorMsg, 'error');
                        tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-red-500">
                                <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                                <p>${errorMsg}</p>
                            </td>
                        </tr>
                    `;
                    }
                })
                .catch(error => {
                    console.error('Error generating report:', error);
                    showNotification('An error occurred: ' + error.message, 'error');
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                            <p>Error generating report</p>
                            <p class="text-sm mt-2">${error.message}</p>
                        </td>
                    </tr>
                `;
                });
        }

        // Display report data in table
        function displayReportData(reportData) {
            try {
                const tbody = document.getElementById('reportTableBody');
                const cardsContainer = document.getElementById('reportCardsContainer');
                // Prefer server-provided flattened rows (per-pair) when available
                const attendances = (reportData.rows && reportData.rows.length) ? reportData.rows : reportData.data;
                const absences = reportData.absences || [];

                console.log('Displaying report data:', {
                    attendances: attendances.length,
                    absences: absences.length,
                    reportData: reportData
                });

                // Combine attendances and absences into a single array for individual reports
                let allRecords = [];

                if (absences.length > 0) {
                    // This is an individual employee report - merge attendance and absence records
                    // Add attendance records
                    attendances.forEach(attendance => {
                        allRecords.push({
                            type: 'attendance',
                            date: attendance.date,
                            data: attendance
                        });
                    });

                    // Add absence records
                    absences.forEach(absence => {
                        allRecords.push({
                            type: 'absence',
                            date: absence.date,
                            data: absence
                        });
                    });

                    // Sort by date descending
                    allRecords.sort((a, b) => new Date(b.date) - new Date(a.date));
                } else {
                    // All employees report - just attendance records
                    allRecords = attendances.map(att => ({
                        type: 'attendance',
                        date: att.date,
                        data: att
                    }));
                }

                if (allRecords.length === 0) {
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                            <p>No attendance records found for the selected filters</p>
                        </td>
                    </tr>
                `;
                    cardsContainer.innerHTML = `
                        <div class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm font-medium">No attendance records found</p>
                                <p class="text-xs text-gray-400 mt-1">for the selected filters</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                tbody.innerHTML = allRecords.map(record => {
                    if (record.type === 'absence') {
                        // Render absence row
                        const absence = record.data;
                        return `
                        <tr class="hover:bg-gray-50 transition-colors bg-red-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${absence.formatted_date}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-user-times text-red-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-700">${absence.day_of_week}</div>
                                        <div class="text-xs text-gray-500">No attendance</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    ABSENT
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        </tr>
                    `;
                    } else {
                        // Render attendance row
                        const attendance = record.data;
                        const metrics = calculateAttendanceMetrics(attendance);

                        const statusColors = {
                            'present': 'bg-green-100 text-green-800',
                            'late': 'bg-yellow-100 text-yellow-800',
                            'absent': 'bg-red-100 text-red-800',
                            'excused': 'bg-blue-100 text-blue-800'
                        };
                        const statusColor = statusColors[metrics.status] || 'bg-gray-100 text-gray-800';

                        // Add row background color for excused status
                        const rowBgClass = metrics.status === 'excused' ? 'bg-blue-50' : '';

                        return `
                        <tr class="hover:bg-gray-50 transition-colors ${rowBgClass}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${formatDisplayDate(attendance.date)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                        <span class="text-indigo-600 font-semibold text-xs">
                                            ${attendance.user.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-medium">${attendance.user.name}</div>
                                        <div class="text-xs text-gray-500">${attendance.user.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${attendance.workplace ? attendance.workplace.name : 'N/A'}
                                ${attendance.is_assigned_workplace === false ? '<span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-800 text-xs rounded-full" title="Non-assigned workplace"><i class="fas fa-map-marker-alt"></i> Off-site</span>' : ''}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${metrics.checkInTime ? formatTime(metrics.checkInTime) : '-'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${metrics.checkOutTime ? formatTime(metrics.checkOutTime) : '-'}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                    ${metrics.status.toUpperCase()}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium text-indigo-600">
                                ${formatHoursMinutes(metrics.workMinutes)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm ${metrics.lateMinutes > 0 ? 'text-yellow-700 font-medium' : 'text-gray-500'}">
                                ${formatHoursMinutes(metrics.lateMinutes)}
                            </td>
                        </tr>
                    `;
                    }
                }).join('');

                // Populate mobile cards
                cardsContainer.innerHTML = allRecords.map(record => {
                    if (record.type === 'absence') {
                        const absence = record.data;
                        return `
                            <div class="p-4 bg-red-50">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-sm font-semibold text-gray-900">${absence.formatted_date}</div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        ABSENT
                                    </span>
                                </div>
                                <div class="flex items-center mb-3">
                                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-user-times text-red-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-700">${absence.day_of_week}</div>
                                        <div class="text-xs text-gray-500">No attendance recorded</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        const attendance = record.data;
                        const metrics = calculateAttendanceMetrics(attendance);
                        const statusColors = {
                            'present': 'bg-green-100 text-green-800',
                            'late': 'bg-yellow-100 text-yellow-800',
                            'absent': 'bg-red-100 text-red-800',
                            'excused': 'bg-blue-100 text-blue-800'
                        };
                        const statusColor = statusColors[metrics.status] || 'bg-gray-100 text-gray-800';
                        const cardBg = metrics.status === 'excused' ? 'bg-blue-50' : '';
                        
                        return `
                            <div class="p-4 ${cardBg}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-sm font-semibold text-gray-900">${formatDisplayDate(attendance.date)}</div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                        ${metrics.status.toUpperCase()}
                                    </span>
                                </div>
                                
                                <div class="flex items-center mb-3">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center mr-3 shadow">
                                        <span class="text-white font-semibold text-sm">${attendance.user.name.charAt(0).toUpperCase()}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">${attendance.user.name}</div>
                                        <div class="text-xs text-gray-600">${attendance.user.email}</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                                    <div class="bg-white rounded-lg p-2">
                                        <div class="text-gray-500 mb-1">Workplace</div>
                                        <div class="font-medium text-gray-900">
                                            ${attendance.workplace ? attendance.workplace.name : 'N/A'}
                                            ${attendance.is_assigned_workplace === false ? '<span class="block mt-1 px-2 py-0.5 bg-orange-100 text-orange-800 text-xs rounded-full"><i class="fas fa-map-marker-alt"></i> Off-site</span>' : ''}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg p-2">
                                        <div class="text-gray-500 mb-1">Hours Worked</div>
                                        <div class="font-bold text-purple-600">${formatHoursMinutes(metrics.workMinutes)}</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <div class="text-gray-500 mb-1">
                                            <i class="fas fa-sign-in-alt text-green-500 mr-1"></i>Check In
                                        </div>
                                        <div class="font-medium text-gray-900">${metrics.checkInTime ? formatTime(metrics.checkInTime) : '-'}</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-2">
                                        <div class="text-gray-500 mb-1">
                                            <i class="fas fa-sign-out-alt text-red-500 mr-1"></i>Check Out
                                        </div>
                                        <div class="font-medium text-gray-900">${metrics.checkOutTime ? formatTime(metrics.checkOutTime) : '-'}</div>
                                    </div>
                                </div>
                                
                                ${metrics.lateMinutes > 0 ? `
                                    <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="text-xs text-yellow-700">
                                            <i class="fas fa-clock mr-1"></i>Late: <span class="font-semibold">${formatHoursMinutes(metrics.lateMinutes)}</span>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }
                }).join('');

                // Update record count (use number of rows shown)
                document.getElementById('report-showing-count').textContent = allRecords.length;
                document.getElementById('report-date-range').textContent =
                    `${reportData.filters.start_date} to ${reportData.filters.end_date}`;

                // Update working days text
                if (reportData.stats && reportData.stats.date_range && reportData.stats.date_range.working_days) {
                    const workingDays = reportData.stats.date_range.working_days;
                    document.getElementById('report-working-days-text').textContent =
                        `${workingDays} working day${workingDays !== 1 ? 's' : ''} (Mon-Fri)`;
                }

                console.log('Report displayed successfully');
            } catch (error) {
                console.error('Error displaying report data:', error);
                const tbody = document.getElementById('reportTableBody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                            <p>Error displaying report data</p>
                            <p class="text-sm mt-2">${error.message}</p>
                        </td>
                    </tr>
                `;
                throw error; // Re-throw to trigger the outer catch
            }
        }

        // Update report statistics
        function updateReportStats(stats) {
            document.getElementById('report-total-records').textContent = stats.total_records;
            document.getElementById('report-present-count').textContent = stats.present_count;
            document.getElementById('report-late-count').textContent = stats.late_count;
            document.getElementById('report-non-assigned-count').textContent = stats.non_assigned_count || 0;
            document.getElementById('report-attendance-rate').textContent = stats.attendance_rate + '%';

            // Update attendance rate formula display
            if (stats.date_range) {
                const workingDays = stats.date_range.working_days || 0;
                const userCount = stats.date_range.user_count || 1;
                const expectedRecords = stats.date_range.expected_records || 0;

                let formulaText = '';
                if (userCount === 1) {
                    // Individual report
                    formulaText = `${stats.total_records} / ${workingDays} working days`;
                } else {
                    // All employees report
                    formulaText = `${stats.total_records} / (${workingDays} days × ${userCount} users)`;
                }

                document.getElementById('attendance-rate-formula').textContent = formulaText;
            }
        }

        // Filter report table
        function filterReportTable(searchTerm) {
            const tbody = document.getElementById('reportTableBody');
            const rows = tbody.getElementsByTagName('tr');
            let visibleCount = 0;

            searchTerm = searchTerm.toLowerCase();

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();

                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Update showing count
            if (currentReportData && currentReportData.data) {
                document.getElementById('report-showing-count').textContent = visibleCount;
            }
        }

        // Export report
        function exportReport(format) {
            if (!currentReportData || !currentReportData.data || currentReportData.data.length === 0) {
                showNotification('Please generate a report first before exporting', 'warning');
                return;
            }

            const reportType = document.getElementById('reportType').value;
            const startDate = document.getElementById('reportStartDate').value;
            const endDate = document.getElementById('reportEndDate').value;
            const userId = document.getElementById('reportUserFilter').value;
            const workplaceId = document.getElementById('reportWorkplaceFilter').value;

            // Build query parameters
            let queryParams = new URLSearchParams({
                report_type: reportType,
                start_date: startDate,
                end_date: endDate,
                format: format
            });

            if (userId) queryParams.append('user_id', userId);
            if (workplaceId) queryParams.append('workplace_id', workplaceId);

            // Show loading notification
            showNotification(`Preparing ${format.toUpperCase()} export...`, 'info');

            // Trigger download
            window.location.href = `/admin/reports/export?${queryParams.toString()}`;

            // Show success notification after a delay
            setTimeout(() => {
                showNotification(`Report exported as ${format.toUpperCase()}`, 'success');
            }, 1000);
        }

        // Reset report filters
        function resetReportFilters() {
            document.getElementById('reportType').value = 'weekly';
            document.getElementById('reportUserFilter').value = '';
            document.getElementById('reportUserSearch').value = 'All Employees';
            document.getElementById('reportWorkplaceFilter').value = '';
            document.getElementById('reportUserResults').classList.add('hidden');
            initializeReportDates();

            // Clear table
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 text-gray-300"></i>
                        <p>No data available. Please select filters and generate a report.</p>
                    </td>
                </tr>
            `;

            // Reset stats
            document.getElementById('report-total-records').textContent = '0';
            document.getElementById('report-present-count').textContent = '0';
            document.getElementById('report-late-count').textContent = '0';
            document.getElementById('report-non-assigned-count').textContent = '0';
            document.getElementById('report-attendance-rate').textContent = '0%';
            document.getElementById('report-showing-count').textContent = '0';
            document.getElementById('report-date-range').textContent = '-';

            currentReportData = null;
            showNotification('Filters reset', 'info');
        }

        // Helper: Format display date
        function formatDisplayDate(dateString) {
            const date = new Date(dateString);
            const options = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return date.toLocaleDateString('en-US', options);
        }

        // Helper: Format time
        function formatTime(timeString) {
            if (!timeString) return '-';

            // Try parsing with Date first (handles ISO strings and many datetime formats)
            const parsed = new Date(timeString);
            if (!isNaN(parsed)) {
                // Use user's locale time format with hour:minute
                return parsed.toLocaleTimeString([], {
                    hour: 'numeric',
                    minute: '2-digit'
                });
            }

            // Fallback: extract HH:MM using regex
            const m = ('' + timeString).match(/(\d{1,2}:\d{2})/);
            if (m) return m[1];

            return timeString;
        }

        // Load reports when switching to reports section
        const reportsLink = document.querySelector('[data-section="reports"]');
        if (reportsLink) {
            reportsLink.addEventListener('click', function() {
                // Auto-generate weekly report on first load if no data
                if (!currentReportData) {
                    setTimeout(() => {
                        generateAttendanceReport();
                    }, 300);
                }
            });
        }

        // ===== ABSENCE REQUESTS MANAGEMENT =====

        let currentAbsenceFilter = 'all';
        let allAbsenceRequests = [];

        // Fetch absence requests for admin
        async function fetchAdminAbsenceRequests() {
            try {
                const response = await fetch(`/api/absence-requests?status=${currentAbsenceFilter}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    allAbsenceRequests = data.requests;
                    displayAdminAbsenceRequests(allAbsenceRequests);
                    updatePendingCount();
                }
            } catch (error) {
                console.error('Error fetching absence requests:', error);
                showNotification('Failed to load absence requests', 'error');
            }
        }

        // Display absence requests for admin
        function displayAdminAbsenceRequests(requests) {
            const tbody = document.getElementById('absenceRequestsTableBody');
            const cardsContainer = document.getElementById('absenceRequestsCards');
            const requestsCount = document.getElementById('requests-count');
            
            // Update counts
            requestsCount.textContent = requests.length;
            document.getElementById('total-requests-count').textContent = allAbsenceRequests.length;
            document.getElementById('pending-requests-stat').textContent = allAbsenceRequests.filter(r => r.status === 'pending').length;
            document.getElementById('approved-requests-stat').textContent = allAbsenceRequests.filter(r => r.status === 'approved').length;
            document.getElementById('rejected-requests-stat').textContent = allAbsenceRequests.filter(r => r.status === 'rejected').length;

            if (!requests || requests.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm font-medium">No requests found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                `;
                cardsContainer.innerHTML = `
                    <div class="p-6 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                            <p class="text-sm font-medium">No requests found</p>
                            <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                        </div>
                    </div>
                `;
                return;
            }

            const statusConfig = {
                pending: {
                    icon: 'fa-clock',
                    bgColor: 'bg-yellow-100',
                    textColor: 'text-yellow-800',
                    label: 'Pending'
                },
                approved: {
                    icon: 'fa-check-circle',
                    bgColor: 'bg-green-100',
                    textColor: 'text-green-800',
                    label: 'Approved'
                },
                rejected: {
                    icon: 'fa-times-circle',
                    bgColor: 'bg-red-100',
                    textColor: 'text-red-800',
                    label: 'Rejected'
                }
            };

            tbody.innerHTML = '';
            cardsContainer.innerHTML = '';

            requests.forEach(request => {
                const status = statusConfig[request.status];
                const startDate = new Date(request.start_date);
                const endDate = new Date(request.end_date);
                const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                
                const startDateStr = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const endDateStr = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const submittedDate = new Date(request.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                // Desktop table row
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors absence-request-row';
                row.dataset.status = request.status;

                row.innerHTML = `
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow">
                                <span class="text-white font-semibold text-sm">${request.user.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-semibold text-gray-900">${request.user.name}</div>
                                <div class="text-xs text-gray-600">${request.user.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium">
                            <i class="fas fa-umbrella-beach mr-1"></i>${request.leave_type || 'Personal Leave'}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-gray-400 mr-2"></i>
                            <div>
                                <div class="font-medium">${startDateStr}</div>
                                <div class="text-xs text-gray-500">to ${endDateStr}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="font-bold text-orange-600">${daysDiff} ${daysDiff === 1 ? 'day' : 'days'}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full ${status.bgColor} ${status.textColor}">
                            <i class="fas ${status.icon} mr-1"></i>${status.label}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        ${submittedDate}
                    </td>
                    <td class="px-4 py-3">
                        ${request.status === 'pending' ? `
                            <div class="flex items-center gap-2">
                                <button onclick="approveAbsenceRequest(${request.id})" 
                                    class="px-2.5 py-1.5 gradient-success text-white text-xs font-semibold rounded-lg hover:shadow-md transition-all"
                                    title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="openRejectModal(${request.id})" 
                                    class="px-2.5 py-1.5 gradient-danger text-white text-xs font-semibold rounded-lg hover:shadow-md transition-all"
                                    title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button onclick="viewRequestDetails(${request.id})" 
                                    class="px-2.5 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded-lg hover:bg-gray-700 transition-all"
                                    title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        ` : `
                            <button onclick="viewRequestDetails(${request.id})" 
                                class="px-3 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded-lg hover:bg-gray-700 transition-all">
                                <i class="fas fa-eye mr-1"></i>Details
                            </button>
                        `}
                    </td>
                `;
                tbody.appendChild(row);

                // Mobile card
                const card = document.createElement('div');
                card.className = 'absence-request-row p-4 hover:bg-gray-50 transition-colors';
                card.dataset.status = request.status;

                card.innerHTML = `
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow">
                                <span class="text-white font-semibold text-sm">${request.user.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-semibold text-gray-900">${request.user.name}</div>
                                <div class="text-xs text-gray-600">${request.user.email}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full ${status.bgColor} ${status.textColor}">
                            <i class="fas ${status.icon} mr-1"></i>${status.label}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <div class="text-gray-500 mb-1">Leave Type</div>
                            <div class="font-medium text-gray-900">
                                <i class="fas fa-umbrella-beach text-blue-500 mr-1"></i>${request.leave_type || 'Personal'}
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <div class="text-gray-500 mb-1">Duration</div>
                            <div class="font-bold text-orange-600">${daysDiff} ${daysDiff === 1 ? 'day' : 'days'}</div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-2 mb-3 text-xs">
                        <div class="text-gray-600 mb-1">
                            <i class="fas fa-calendar text-blue-500 mr-1"></i>
                            <span class="font-medium">${startDateStr}</span> to <span class="font-medium">${endDateStr}</span>
                        </div>
                        <div class="text-gray-500">
                            <i class="fas fa-clock mr-1"></i>Requested: ${submittedDate}
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-2 mb-3">
                        <div class="text-xs text-gray-500 mb-1">Reason:</div>
                        <div class="text-xs text-gray-700">${request.reason}</div>
                    </div>
                    
                    ${request.status === 'pending' ? `
                        <div class="flex gap-2">
                            <button onclick="approveAbsenceRequest(${request.id})" 
                                class="flex-1 gradient-success text-white py-2 px-3 rounded-lg hover:shadow-md transition-all text-xs font-semibold">
                                <i class="fas fa-check mr-1"></i>Approve
                            </button>
                            <button onclick="openRejectModal(${request.id})" 
                                class="flex-1 gradient-danger text-white py-2 px-3 rounded-lg hover:shadow-md transition-all text-xs font-semibold">
                                <i class="fas fa-times mr-1"></i>Reject
                            </button>
                        </div>
                    ` : `
                        <button onclick="viewRequestDetails(${request.id})" 
                            class="w-full bg-gray-600 text-white py-2 px-3 rounded-lg hover:bg-gray-700 transition-all text-xs font-semibold">
                            <i class="fas fa-eye mr-1"></i>View Details
                        </button>
                    `}
                `;
                cardsContainer.appendChild(card);
            });

            // Setup search
            setupAbsenceRequestSearch();
        }

        // Filter absence requests
        function filterAbsenceRequests(status) {
            currentAbsenceFilter = status;

            // Update button styles
            document.querySelectorAll('.absence-filter-btn').forEach(btn => {
                if (btn.dataset.status === status) {
                    btn.className = 'absence-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-sm';
                } else {
                    btn.className = 'absence-filter-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors';
                }
            });

            // Filter and display
            if (status === 'all') {
                displayAdminAbsenceRequests(allAbsenceRequests);
            } else {
                const filtered = allAbsenceRequests.filter(req => req.status === status);
                displayAdminAbsenceRequests(filtered);
            }
        }

        // Setup search functionality
        function setupAbsenceRequestSearch() {
            const searchInput = document.getElementById('absenceRequestSearch');
            if (!searchInput) return;

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.absence-request-row');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update count
                const tableRows = document.querySelectorAll('#absenceRequestsTableBody .absence-request-row');
                const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none').length;
                document.getElementById('requests-count').textContent = visibleRows;
            });
        }

        // View request details (modal or expanded view)
        function viewRequestDetails(requestId) {
            const request = allAbsenceRequests.find(r => r.id === requestId);
            if (!request) return;

            const status = {
                pending: { bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', icon: 'fa-clock', label: 'Pending' },
                approved: { bgColor: 'bg-green-100', textColor: 'text-green-800', icon: 'fa-check-circle', label: 'Approved' },
                rejected: { bgColor: 'bg-red-100', textColor: 'text-red-800', icon: 'fa-times-circle', label: 'Rejected' }
            }[request.status];

            const startDate = new Date(request.start_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            const endDate = new Date(request.end_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            const submittedDate = new Date(request.created_at).toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });

            const daysDiff = Math.ceil((new Date(request.end_date) - new Date(request.start_date)) / (1000 * 60 * 60 * 24)) + 1;

            const modalContent = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="this.remove()">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold text-gray-900">Leave Request Details</h2>
                                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-times text-2xl"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Employee Info -->
                            <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl">
                                <div class="h-16 w-16 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg">
                                    <span class="text-white font-bold text-2xl">${request.user.name.charAt(0).toUpperCase()}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">${request.user.name}</h3>
                                    <p class="text-sm text-gray-600">${request.user.email}</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-full ${status.bgColor} ${status.textColor}">
                                        <i class="fas ${status.icon} mr-2"></i>${status.label}
                                    </span>
                                </div>
                            </div>

                            <!-- Leave Details -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-blue-50 rounded-xl">
                                    <div class="text-sm text-gray-600 mb-1">Leave Type</div>
                                    <div class="text-lg font-semibold text-gray-900">
                                        <i class="fas fa-umbrella-beach text-blue-500 mr-2"></i>${request.leave_type || 'Personal Leave'}
                                    </div>
                                </div>
                                <div class="p-4 bg-orange-50 rounded-xl">
                                    <div class="text-sm text-gray-600 mb-1">Duration</div>
                                    <div class="text-lg font-bold text-orange-600">${daysDiff} ${daysDiff === 1 ? 'Day' : 'Days'}</div>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="text-sm text-gray-600 mb-2">Leave Period</div>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <div class="text-xs text-gray-500">From</div>
                                        <div class="font-semibold text-gray-900">${startDate}</div>
                                    </div>
                                    <i class="fas fa-arrow-right text-gray-400"></i>
                                    <div class="flex-1">
                                        <div class="text-xs text-gray-500">To</div>
                                        <div class="font-semibold text-gray-900">${endDate}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div>
                                <div class="text-sm font-semibold text-gray-700 mb-2">Reason for Leave</div>
                                <div class="p-4 bg-gray-50 rounded-xl text-gray-700">${request.reason}</div>
                            </div>

                            <!-- Admin Response -->
                            ${request.admin_comment ? `
                                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl">
                                    <div class="text-sm font-semibold text-gray-700 mb-2">Admin Response</div>
                                    <p class="text-gray-700 mb-2">${request.admin_comment}</p>
                                    ${request.reviewed_at ? `
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-user-shield mr-1"></i>Reviewed by ${request.admin ? request.admin.name : 'Admin'} on ${new Date(request.reviewed_at).toLocaleString()}
                                        </p>
                                    ` : ''}
                                </div>
                            ` : ''}

                            <!-- Submitted Date -->
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-clock mr-2"></i>Submitted on ${submittedDate}
                            </div>

                            <!-- Actions -->
                            ${request.status === 'pending' ? `
                                <div class="flex gap-3 pt-4 border-t border-gray-200">
                                    <button onclick="approveAbsenceRequest(${request.id}); this.closest('.fixed').remove();" 
                                        class="flex-1 gradient-success text-white py-3 px-4 rounded-xl hover:shadow-lg transition-all font-semibold">
                                        <i class="fas fa-check mr-2"></i>Approve Request
                                    </button>
                                    <button onclick="openRejectModal(${request.id}); this.closest('.fixed').remove();" 
                                        class="flex-1 gradient-danger text-white py-3 px-4 rounded-xl hover:shadow-lg transition-all font-semibold">
                                        <i class="fas fa-times mr-2"></i>Reject Request
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        // Approve absence request
        async function approveAbsenceRequest(requestId) {
            if (!confirm('Are you sure you want to approve this absence request?')) {
                return;
            }

            try {
                const response = await fetch(`/admin/absence-requests/${requestId}/approve`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    fetchAdminAbsenceRequests();
                } else {
                    showNotification(data.error || 'Failed to approve request', 'error');
                }
            } catch (error) {
                console.error('Error approving absence request:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        }

        // Open reject modal
        function openRejectModal(requestId) {
            const comment = prompt('Please provide a reason for rejection (required):');

            if (comment && comment.trim().length >= 10) {
                rejectAbsenceRequest(requestId, comment.trim());
            } else if (comment !== null) {
                showNotification('Rejection reason must be at least 10 characters long', 'error');
            }
        }

        // Reject absence request
        async function rejectAbsenceRequest(requestId, comment) {
            try {
                const response = await fetch(`/admin/absence-requests/${requestId}/reject`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        comment
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    fetchAdminAbsenceRequests();
                } else {
                    showNotification(data.error || 'Failed to reject request', 'error');
                }
            } catch (error) {
                console.error('Error rejecting absence request:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        }

        // Update pending count badge
        async function updatePendingCount() {
            try {
                const response = await fetch('/api/absence-requests/pending-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const count = data.count;
                    const badge = document.getElementById('pending-absence-badge');
                    const tabBadge = document.getElementById('pending-count-tab');

                    if (badge) {
                        badge.textContent = count;
                        if (count > 0) {
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    if (tabBadge) {
                        tabBadge.textContent = count;
                    }
                }
            } catch (error) {
                console.error('Error fetching pending count:', error);
            }
        }

        // Load absence requests when switching to absence requests section
        const absenceRequestsLink = document.querySelector('[data-section="absence-requests"]');
        if (absenceRequestsLink) {
            absenceRequestsLink.addEventListener('click', function() {
                fetchAdminAbsenceRequests();
            });
        }

        // Update pending count on page load and periodically
        document.addEventListener('DOMContentLoaded', function() {
            updatePendingCount();
            // Update every 2 minutes
            setInterval(updatePendingCount, 120000);
        });
    </script>

</body>

</html>
