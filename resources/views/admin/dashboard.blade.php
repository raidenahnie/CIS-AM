<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CISAM | Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .sidebar-transition { transition: all 0.3s ease-in-out; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Fix Leaflet map z-index issues with modals */
        .leaflet-container { z-index: 1 !important; }
        .leaflet-control-container { z-index: 2 !important; }
        
        /* Fix dropdown overflow in tables */
        .table-container { overflow: visible !important; }
        .table-wrapper { overflow-x: auto; overflow-y: visible; }
        .dropdown-cell { position: relative; }
        .dropdown-menu {
            position: absolute !important;
            z-index: 9999 !important;
            right: 0;
            min-width: 12rem;
        }
        
        .admin-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.125rem 0;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #64748b;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border-right: 4px solid transparent;
        }
        
        .admin-nav-link:hover {
            background-color: #f8fafc;
            color: #4f46e5;
        }
        
        .admin-nav-link.active {
            background-color: #eef2ff;
            color: #4f46e5;
            font-weight: 600;
            border-right-color: #4f46e5;
        }
        
        .admin-section {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        
        .admin-section.hidden {
            display: none;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">

    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg sidebar-transition z-30">
        <div class="flex items-center justify-between p-5 pb-6 border-b-2 border-indigo-500">
            <h2 class="text-xl font-bold text-indigo-600">
                <i class="fas fa-user-shield mr-2"></i>
                CISAM Admin
            </h2>
            <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <nav class="mt-6">
            <div class="px-4 mb-6">
                <div class="flex items-center p-3 bg-indigo-50 rounded-lg">
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-600">Administrator</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-1 px-2">
                <a href="#" onclick="switchAdminSection('dashboard')" class="admin-nav-link active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="#" onclick="switchAdminSection('workplaces')" class="admin-nav-link" data-section="workplaces">
                    <i class="fas fa-building w-5 h-5 mr-3"></i>
                    Workplaces
                </a>
                <a href="#" onclick="switchAdminSection('users')" class="admin-nav-link" data-section="users">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Users
                </a>
                <a href="#" onclick="switchAdminSection('attendance')" class="admin-nav-link" data-section="attendance">
                    <i class="fas fa-clock w-5 h-5 mr-3"></i>
                    Attendance
                </a>
                <a href="#" onclick="switchAdminSection('reports')" class="admin-nav-link" data-section="reports">
                    <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                    Reports
                </a>
                <a href="#" onclick="switchAdminSection('settings')" class="admin-nav-link" data-section="settings">
                    <i class="fas fa-cog w-5 h-5 mr-3"></i>
                    Settings
                </a>
            </div>
            
            <div class="absolute bottom-4 left-0 right-0 px-4">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors rounded-lg">
                    <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                    <span class="text-sm">User Dashboard</span>
                </a>
                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="flex items-center px-4 py-2 mt-2 text-red-600 hover:bg-red-50 transition-colors rounded-lg">
                    <i class="fas fa-sign-out-alt w-4 h-4 mr-2"></i>
                    <span class="text-sm">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </nav>
    </div>

    <!-- Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Header -->
    <header class="bg-white shadow-sm ml-0 lg:ml-64 transition-all duration-300">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Breadcrumbs -->
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-home text-gray-400"></i>
                                    <span class="ml-2 text-sm font-medium text-gray-500">Admin</span>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                                    <span class="text-sm font-medium text-gray-900" id="current-section">Dashboard</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="hidden sm:block text-sm text-gray-600">
                        {{ now()->format('F j, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="ml-0 lg:ml-64 transition-all duration-300">
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="admin-section">
                <!-- Page Title -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
                    <p class="mt-2 text-sm text-gray-600">Welcome back! Here's what's happening in your system today.</p>
                </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-xl card-hover transition-all duration-300 fade-in">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->count() }}</p>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    Active system users
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl card-hover transition-all duration-300 fade-in" style="animation-delay: 0.1s">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user-shield text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Admin Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('role', 'admin')->count() }}</p>
                                <p class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    System administrators
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl card-hover transition-all duration-300 fade-in" style="animation-delay: 0.2s">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Regular Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('role', 'user')->count() }}</p>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Employee accounts
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl card-hover transition-all duration-300 fade-in" style="animation-delay: 0.3s">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-building text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Workplaces</p>
                                <p class="text-2xl font-bold text-gray-900">{{ isset($workplaces) ? $workplaces->count() : 0 }}</p>
                                <p class="text-xs text-blue-600 mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    Active locations
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <a href="#" onclick="switchAdminSection('workplaces'); return false;" 
                   class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-green-500">
                    <div class="flex items-center">
                        <i class="fas fa-building text-2xl text-green-500 mr-4"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Manage Workplaces</h3>
                            <p class="text-sm text-gray-600">Add & edit locations</p>
                        </div>
                    </div>
                </a>
                
                <a href="#" onclick="switchAdminSection('users'); return false;"
                   class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <i class="fas fa-user-group text-2xl text-blue-500 mr-4"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Manage Users</h3>
                            <p class="text-sm text-gray-600">Create, edit and monitor user accounts</p>
                        </div>
                    </div>
                </a>
                
                <a href="#" onclick="switchAdminSection('reports'); return false;"
                   class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-2xl text-purple-500 mr-4"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">View Reports</h3>
                            <p class="text-sm text-gray-600">Attendance analytics</p>
                        </div>
                    </div>
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-indigo-500">
                    <div class="flex items-center">
                        <i class="fas fa-arrow-left text-2xl text-indigo-500 mr-4"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">User Dashboard</h3>
                            <p class="text-sm text-gray-600">Switch to user view</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recent Activity Summary -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Recent Activity</h3>
                    <p class="mt-1 text-sm text-gray-600">Latest system activity overview</p>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                            <div class="flex items-center">
                                <i class="fas fa-user-plus text-green-500 mr-3 text-xl"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">New Users Today</p>
                                    <p class="text-xs text-gray-600">{{ $users->where('created_at', '>=', now()->startOfDay())->count() }} new registrations</p>
                                </div>
                            </div>
                            <span class="text-2xl font-bold text-green-600">{{ $users->where('created_at', '>=', now()->startOfDay())->count() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <i class="fas fa-building text-blue-500 mr-3 text-xl"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Active Workplaces</p>
                                    <p class="text-xs text-gray-600">{{ $workplaces->where('is_active', true)->count() }} locations available</p>
                                </div>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">{{ $workplaces->where('is_active', true)->count() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-purple-500 mr-3 text-xl"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Users Online</p>
                                    <p class="text-xs text-gray-600">Active in last 5 minutes</p>
                                </div>
                            </div>
                            <span class="text-2xl font-bold text-purple-600">{{ $users->filter(function($user) { return $user->isOnline(); })->count() }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                            <div class="flex items-center">
                                <i class="fas fa-user-check text-yellow-500 mr-3 text-xl"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Active Today</p>
                                    <p class="text-xs text-gray-600">Users with activity today</p>
                                </div>
                            </div>
                            <span class="text-2xl font-bold text-yellow-600">{{ $users->filter(function($user) { return $user->last_activity && $user->last_activity->isToday(); })->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Workplaces Section -->
            <div id="workplaces-section" class="admin-section hidden">
                <!-- Page Title -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Workplace Management</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage workplace locations and user assignments.</p>
                </div>

                <!-- Workplace Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-green-400 to-green-600 rounded-xl p-3 shadow-lg">
                                <i class="fas fa-building text-white text-2xl"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500 uppercase">Total Workplaces</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $workplaces->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl p-3 shadow-lg">
                                <i class="fas fa-check-circle text-white text-2xl"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500 uppercase">Active</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $workplaces->where('is_active', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl p-3 shadow-lg">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500 uppercase">Total Assignments</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $users->sum(function($u) { return $u->workplaces->count(); }) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-gradient-to-br from-red-400 to-red-600 rounded-xl p-3 shadow-lg">
                                <i class="fas fa-times-circle text-white text-2xl"></i>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500 uppercase">Inactive</p>
                                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $workplaces->where('is_active', false)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions for Workplaces -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <button onclick="openWorkplaceModal()" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-green-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-plus text-2xl text-green-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Add New Workplace</h3>
                                <p class="text-sm text-gray-600">Create a new location</p>
                            </div>
                        </div>
                    </button>
                    
                    <button onclick="scrollToAllWorkplaces()" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-blue-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-list text-2xl text-blue-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">View All Workplaces</h3>
                                <p class="text-sm text-gray-600">Browse all locations</p>
                            </div>
                        </div>
                    </button>
                    
                    <button onclick="scrollToAssignments()" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-purple-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-user-cog text-2xl text-purple-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Manage Assignments</h3>
                                <p class="text-sm text-gray-600">User-workplace links</p>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- All Workplaces Table -->
                <div id="all-workplaces-table" class="bg-white shadow-xl rounded-xl overflow-hidden mb-8">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">All Workplaces</h3>
                                <p class="mt-1 text-sm text-gray-600">Manage all workplace locations</p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex space-x-3">
                                <div class="relative">
                                    <input type="text" id="workplaceSearchMain" placeholder="Search workplaces..." 
                                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                </div>
                                <button onclick="openWorkplaceModal()" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Workplace
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="min-w-full divide-y divide-gray-200" id="workplacesTableMain">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workplace</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Radius</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="workplacesTableBodyMain">
                                @foreach($workplaces as $workplace)
                                <tr class="hover:bg-gray-50 transition-colors workplace-row-main">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-12">
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                                                    <i class="fas fa-building text-white text-xl"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900 workplace-name">{{ $workplace->name }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ $workplace->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 workplace-address">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                            {{ Str::limit($workplace->address, 50) }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ number_format($workplace->latitude, 6) }}, {{ number_format($workplace->longitude, 6) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <i class="fas fa-circle-notch mr-1"></i>
                                            {{ $workplace->radius }}m
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $workplace->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <div class="w-1.5 h-1.5 {{ $workplace->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-1.5"></div>
                                            {{ $workplace->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                            <i class="fas fa-users mr-1"></i>
                                            {{ $workplace->users_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            <!-- Main actions: View/Edit -->
                                            <button onclick="viewWorkplace({{ $workplace->id }})" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-200 transition-colors"
                                                    title="View Details">
                                                <i class="fas fa-eye mr-1"></i>
                                                View
                                            </button>
                                            <button onclick="editWorkplace({{ $workplace->id }})" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-lg hover:bg-indigo-200 transition-colors"
                                                    title="Edit Workplace">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </button>
                                            
                                            <!-- More button -->
                                            <button id="more-btn-wp-{{ $workplace->id }}" 
                                                    onclick="toggleMoreActionsWorkplace({{ $workplace->id }})" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                                    title="More Actions">
                                                <i class="fas fa-ellipsis-h mr-1"></i>
                                                More
                                            </button>
                                            
                                            <!-- Expanded actions (hidden by default) -->
                                            <div id="more-actions-wp-{{ $workplace->id }}" style="display: none;" class="flex items-center space-x-2">
                                                <button onclick="manageUsers({{ $workplace->id }})" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 text-xs font-medium rounded-lg hover:bg-green-200 transition-colors"
                                                        title="Manage Users">
                                                    <i class="fas fa-users-cog mr-1"></i>
                                                    Users
                                                </button>
                                                <button onclick="deleteWorkplace({{ $workplace->id }})" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors"
                                                        title="Delete Workplace">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Delete
                                                </button>
                                                <button onclick="toggleMoreActionsWorkplace({{ $workplace->id }})" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                                        title="Show Less">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Back
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold">{{ $workplaces->count() }}</span> of <span class="font-semibold">{{ $workplaces->count() }}</span> workplaces
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    <i class="fas fa-chevron-left mr-1"></i>
                                    Previous
                                </button>
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    Next
                                    <i class="fas fa-chevron-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Assignments Section -->
                <div id="assignments-section" class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">User Workplace Assignments</h3>
                                <p class="mt-1 text-sm text-gray-600">Assign users to workplaces and manage their roles</p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex space-x-3">
                                <div class="relative">
                                    <input type="text" id="assignmentSearch" placeholder="Search assignments..." 
                                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                </div>
                                <button onclick="openAssignmentModal()" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Assign User
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workplaces</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Primary</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="assignments-tbody">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors assignment-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-12">
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                    <span class="text-white font-semibold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900 user-name">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-600 user-email">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @forelse($user->workplaces as $workplace)
                                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-building mr-1"></i>
                                                    {{ $workplace->name }}
                                                    @if($workplace->pivot->is_primary)
                                                        <i class="fas fa-star ml-1 text-yellow-500" title="Primary workplace"></i>
                                                    @endif
                                                </span>
                                            @empty
                                                <span class="text-gray-400 text-sm italic">No workplaces assigned</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php $primaryWorkplace = $user->primaryWorkplace(); @endphp
                                        @if($primaryWorkplace)
                                            <div class="flex items-center">
                                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                                <span class="text-sm font-medium text-gray-900">{{ $primaryWorkplace->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-sm italic">None set</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                    
                    <!-- Pagination -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold">{{ $users->count() }}</span> users with assignments
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    <i class="fas fa-chevron-left mr-1"></i>
                                    Previous
                                </button>
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    Next
                                    <i class="fas fa-chevron-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage system users and their permissions.</p>
                </div>

                <!-- User Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Regular Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('role', 'user')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user-shield text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Administrators</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('role', 'admin')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-user-check text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Active Today</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $users->filter(function($user) { return $user->last_activity && $user->last_activity->isToday(); })->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions for Users -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <button onclick="scrollToAllUsers()" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-green-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-users text-2xl text-green-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">View All Users</h3>
                                <p class="text-sm text-gray-600">Browse and manage all users</p>
                            </div>
                        </div>
                    </button>
                    
                    <button onclick="scrollToAllUsers(); setTimeout(() => { document.getElementById('userSearchMain').focus(); animateSearchBar(); }, 300);" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-blue-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-search text-2xl text-blue-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Search Users</h3>
                                <p class="text-sm text-gray-600">Find and manage users</p>
                            </div>
                        </div>
                    </button>
                    
                    <button onclick="openBulkOperationsModal()" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-purple-500 text-left relative">
                        <div class="flex items-center">
                            <i class="fas fa-tasks text-2xl text-purple-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Bulk Operations</h3>
                                <p class="text-sm text-gray-600">Mass user management</p>
                            </div>
                        </div>
                        <!-- Selection count badge -->
                        <span id="bulkSelectionBadge" class="hidden absolute -top-2 -right-2 bg-purple-600 text-white text-xs font-bold rounded-full h-7 w-7 flex items-center justify-center shadow-lg animate-pulse">0</span>
                    </button>
                </div>

                <!-- Employee Location Map -->
                <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-8">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Employee Locations</h3>
                                <p class="mt-1 text-sm text-gray-600">Real-time employee check-in/out locations</p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex space-x-3">
                                <div class="relative">
                                    <input type="text" 
                                           id="employeeLocationSearch"
                                           placeholder="Search employees..." 
                                           class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                                <button onclick="refreshEmployeeMap()" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Refresh
                                </button>
                                <button onclick="toggleMapView()" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-map mr-2"></i>
                                    <span id="mapToggleText">Show Map</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="mapContainer" class="hidden">
                        <div id="employeeMap" class="w-full h-96 bg-gray-100 border-t">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <i class="fas fa-map-marker-alt text-gray-300 text-6xl mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Employee Location Map</h3>
                                    <p class="text-gray-500 mb-4">Interactive map showing employee check-in/out locations</p>
                                    <button onclick="initializeMap()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                        <i class="fas fa-play mr-2"></i>
                                        Initialize Map
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Legend -->
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Map Legend</h4>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">Checked In</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">Checked Out</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">Workplace Location</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                    <span class="text-gray-700">On Break</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employee Location Table (when map is hidden) -->
                    <div id="locationCards" class="table-wrapper">
                        <table class="min-w-full divide-y divide-gray-200" id="employeeLocationTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Location</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workplace</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Online Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="employeeLocationTableBody">
                                @foreach($users->where('role', 'user') as $user)
                                <tr class="hover:bg-gray-50 transition-colors employee-location-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-600">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                                            {{ isset($latestAttendance[$user->id]) ? (
                                                $latestAttendance[$user->id]['action'] === 'check_in' ? 'bg-green-100 text-green-800' : 
                                                ($latestAttendance[$user->id]['action'] === 'check_out' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')
                                            ) : 'bg-gray-100 text-gray-800' }}" id="user-status-{{ $user->id }}">
                                            <i class="fas {{ isset($latestAttendance[$user->id]) ? (
                                                $latestAttendance[$user->id]['action'] === 'check_in' ? 'fa-sign-in-alt' : 
                                                ($latestAttendance[$user->id]['action'] === 'check_out' ? 'fa-sign-out-alt' : 'fa-pause')
                                            ) : 'fa-minus' }} mr-1"></i>
                                            @if(isset($latestAttendance[$user->id]))
                                                {{ ucwords(str_replace('_', ' ', $latestAttendance[$user->id]['action'])) }}
                                            @else
                                                No activity today
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900" id="user-location-{{ $user->id }}">
                                            @if(isset($latestAttendance[$user->id]))
                                                <div class="flex items-center">
                                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                                    <span>{{ Str::limit($latestAttendance[$user->id]['address'] ?: 'Coordinates: ' . $latestAttendance[$user->id]['latitude'] . ', ' . $latestAttendance[$user->id]['longitude'], 40) }}</span>
                                                </div>
                                            @else
                                                <div class="flex items-center text-gray-500">
                                                    <i class="fas fa-question-circle text-gray-400 mr-2"></i>
                                                    <span>Unknown</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($user->workplaces->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($user->workplaces->take(1) as $workplace)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <i class="fas fa-building mr-1"></i>
                                                        {{ $workplace->name }}{{ $workplace->pivot->is_primary ? ' (Primary)' : '' }}
                                                    </span>
                                                @endforeach
                                                @if($user->workplaces->count() > 1)
                                                    <span class="text-xs text-gray-500">+{{ $user->workplaces->count() - 1 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                No assignments
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $isOnline = $user->isOnline();
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            <div class="w-2 h-2 {{ $isOnline ? 'bg-green-500' : 'bg-gray-500' }} rounded-full mr-2"></div>
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                        @if($isOnline && $user->last_activity)
                                            <div class="text-xs text-gray-500 mt-1">Last: {{ $user->last_activity->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <button onclick="showUserLocationDetails({{ $user->id }})" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                                <i class="fas fa-history"></i>
                                                <span>History</span>
                                            </button>
                                            <button onclick="centerMapOnUser({{ $user->id }})" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>Map</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Employee Location Pagination -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ $users->where('role', 'user')->count() }}</span> employees
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    Previous
                                </button>
                                <button class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-md">
                                    1
                                </button>
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Full Users Table -->
                <div id="all-users-table" class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">All Users</h3>
                                <p class="mt-1 text-sm text-gray-600">Manage all users in the system</p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex space-x-3">
                                <div class="relative">
                                    <input type="text" id="userSearchMain" placeholder="Search users..." 
                                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                </div>
                                <button onclick="addUser()" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add User
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="min-w-full divide-y divide-gray-200" id="usersTableMain">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="selectAllMain" class="rounded border-gray-300">
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Online Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workplaces</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="usersTableBodyMain">
                                @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors user-row-main">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="user-checkbox-main rounded border-gray-300" value="{{ $user->id }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-12">
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                    <span class="text-white font-semibold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900 user-name">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-600 user-email">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            <i class="fas {{ $user->role === 'admin' ? 'fa-shield-alt' : 'fa-user' }} mr-1"></i>
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $isOnline = $user->isOnline();
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $isOnline ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            <div class="w-2 h-2 {{ $isOnline ? 'bg-green-500' : 'bg-gray-500' }} rounded-full mr-2"></div>
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                        @if($isOnline && $user->last_activity)
                                            <div class="text-xs text-gray-500 mt-1">Last active: {{ $user->last_activity->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($user->workplaces->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($user->workplaces->take(2) as $workplace)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        {{ $workplace->name }}{{ $workplace->pivot->is_primary ? ' (Primary)' : '' }}
                                                    </span>
                                                @endforeach
                                                @if($user->workplaces->count() > 2)
                                                    <span class="text-xs text-gray-500">+{{ $user->workplaces->count() - 2 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No assignments</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                            {{ $user->created_at->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <button onclick="viewUser({{ $user->id }})" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                                                <i class="fas fa-eye"></i>
                                                <span>View</span>
                                            </button>
                                            <button onclick="editUser({{ $user->id }})" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                                <i class="fas fa-edit"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button onclick="toggleMoreActions({{ $user->id }})" 
                                                    id="more-btn-{{ $user->id }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                                                <i class="fas fa-ellipsis-h"></i>
                                                <span>More</span>
                                            </button>
                                            <div id="more-actions-{{ $user->id }}" style="display: none;" class="flex items-center gap-2">
                                                <button onclick="resetUserPassword({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')" 
                                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors">
                                                    <i class="fas fa-key"></i>
                                                    <span>Reset</span>
                                                </button>
                                                @if($user->id !== auth()->id())
                                                <button onclick="deleteUser({{ $user->id }})" 
                                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <span>Delete</span>
                                                </button>
                                                @endif
                                                <button onclick="toggleMoreActions({{ $user->id }})" 
                                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors border border-gray-300">
                                                    <i class="fas fa-chevron-left"></i>
                                                    <span>Back</span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ $users->count() }}</span> users
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    Previous
                                </button>
                                <button class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-md">
                                    1
                                </button>
                                <button class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Section -->
            <div id="attendance-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Attendance Overview</h1>
                    <p class="mt-2 text-sm text-gray-600">Monitor and analyze attendance data.</p>
                </div>
                
                <!-- Attendance Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-check text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Today's Check-ins</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                                <p class="text-xs text-green-600 mt-1">Active employees</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-clock text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Average Hours</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                                <p class="text-xs text-blue-600 mt-1">Per employee today</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Late Arrivals</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                                <p class="text-xs text-red-600 mt-1">Today</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-clock text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Attendance Reports</h3>
                    <p class="text-gray-500">Detailed attendance analytics and logs will be available here.</p>
                </div>
            </div>

            <!-- Reports Section -->
            <div id="reports-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
                    <p class="mt-2 text-sm text-gray-600">Generate comprehensive reports and insights.</p>
                </div>
                
                <!-- Report Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-calendar-week text-blue-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Weekly Report</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Weekly attendance summary for all employees</p>
                        <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors" onclick="generateReport('weekly')">
                            Generate Report
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-calendar-alt text-green-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Monthly Report</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Monthly attendance and performance metrics</p>
                        <button class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors" onclick="generateReport('monthly')">
                            Generate Report
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-user-clock text-purple-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Employee Report</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Individual employee attendance details</p>
                        <button class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors" onclick="generateReport('employee')">
                            Generate Report
                        </button>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-chart-bar text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Analytics Dashboard</h3>
                    <p class="text-gray-500">Advanced reporting and data visualization tools will be available here.</p>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
                    <p class="mt-2 text-sm text-gray-600">Configure system preferences and security settings.</p>
                </div>
                
                <!-- Settings Categories -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-shield-alt text-indigo-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Security Settings</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Manage password policies and security requirements</p>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Require password change every 90 days</span>
                                <input type="checkbox" class="toggle-switch" checked disabled>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Two-factor authentication</span>
                                <input type="checkbox" class="toggle-switch" disabled>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Session timeout (30 mins)</span>
                                <input type="checkbox" class="toggle-switch" checked disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-map-marker-alt text-green-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Location Settings</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Configure GPS accuracy and location services</p>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">High accuracy GPS</span>
                                <input type="checkbox" class="toggle-switch" checked disabled>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Allow manual location entry</span>
                                <input type="checkbox" class="toggle-switch" disabled>
                            </div>
                            <div class="mb-2">
                                <label class="block text-sm text-gray-700">Default radius (meters)</label>
                                <input type="number" value="100" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded text-sm" disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-bell text-yellow-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Notification Settings</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Configure system notifications and alerts</p>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Late arrival notifications</span>
                                <input type="checkbox" class="toggle-switch" checked disabled>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Daily attendance reports</span>
                                <input type="checkbox" class="toggle-switch" disabled>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">System maintenance alerts</span>
                                <input type="checkbox" class="toggle-switch" checked disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-database text-blue-500 text-2xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Data Management</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Backup and data retention settings</p>
                        <div class="space-y-3">
                            <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm" onclick="showNotification('Backup feature coming soon', 'info')">
                                Create Backup
                            </button>
                            <button class="w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors text-sm" onclick="showNotification('Export feature coming soon', 'info')">
                                Export Data
                            </button>
                            <div class="text-xs text-gray-500">
                                Last backup: Never
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-cog text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Advanced Configuration</h3>
                    <p class="text-gray-500">Additional system configuration options will be available here.</p>
                </div>
            </div>

        </div>
    </main>

    <!-- Workplace Modal -->
    <div id="workplaceModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <h3 class="text-lg font-semibold text-black mb-0" id="modalTitle">Add New Workplace</h3>
                </div>
                <div class="px-6 py-4">
                <form id="workplaceForm">
                    <input type="hidden" id="workplaceId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Name</label>
                        <input type="text" id="workplaceName" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Address</label>
                        <textarea id="workplaceAddress" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" rows="2" required></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-black mb-2">Latitude</label>
                            <input type="number" id="workplaceLatitude" step="any" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black mb-2">Longitude</label>
                            <input type="number" id="workplaceLongitude" step="any" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Radius (meters)</label>
                        <input type="number" id="workplaceRadius" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" required>
                    </div>
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="workplaceActive" class="rounded border-2 border-gray-300 text-indigo-600 focus:ring-indigo-500 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm">
                            <span class="ml-2 text-sm text-black">Active</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end space-x-3">
                    <button type="button" onclick="closeWorkplaceModal()" class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <h3 class="text-lg font-semibold text-black mb-0">Assign User to Workplace</h3>
                </div>
                <div class="px-6 py-4">
                <form id="assignmentForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">User</label>
                        <select id="assignmentUser" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black" required>
                            <option value="">Select a user...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Workplace</label>
                        <select id="assignmentWorkplace" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black" required>
                            <option value="">Select a workplace...</option>
                            @foreach($workplaces as $workplace)
                            <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Role</label>
                        <input type="text" id="assignmentRole" value="employee" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600">
                    </div>
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="assignmentPrimary" class="rounded border-2 border-gray-300 text-indigo-600 focus:ring-indigo-500 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm">
                            <span class="ml-2 text-sm text-black">Set as Primary Workplace</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end space-x-3">
                    <button type="button" onclick="closeAssignmentModal()" class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30">Assign</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <h3 class="text-lg font-semibold text-black mb-0" id="userModalTitle">Add New User</h3>
                </div>
                <div class="px-6 py-4">
                <form id="userForm">
                    <input type="hidden" id="userId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Name</label>
                        <input type="text" id="userName" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black mb-2">Email</label>
                        <input type="email" id="userEmail" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600" required>
                    </div>
                    <div class="mb-4" id="userPasswordField">
                        <label class="block text-sm font-medium text-black mb-2">Password</label>
                        <input type="password" id="userPassword" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600">
                    </div>
                    <div class="mb-4" id="userPasswordConfirmField">
                        <label class="block text-sm font-medium text-black mb-2">Confirm Password</label>
                        <input type="password" id="userPasswordConfirm" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black placeholder-gray-600">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-black mb-2">Role</label>
                        <select id="userRole" class="w-full px-3 py-2 bg-white bg-opacity-30 backdrop-filter backdrop-blur-sm border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-black" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end space-x-3">
                    <button type="button" onclick="closeUserModal()" class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30">Save</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- User Details Glass Modal -->
    <div id="userDetailsModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-black" id="userDetailsTitle">User Details</h3>
                        <button onclick="closeUserDetailsModal()" class="text-black hover:text-gray-300 transition-colors">
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
    <div id="locationHistoryModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-4xl shadow-lg rounded-2xl">
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-black" id="locationHistoryTitle">Location History</h3>
                        <button onclick="closeLocationHistoryModal()" class="text-black hover:text-gray-300 transition-colors">
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
    <div id="bulkOperationsModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 border-0 w-96 shadow-lg rounded-2xl">
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
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
                        <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-key text-blue-600 mr-2"></i>
                                Send Password Reset Email
                            </h4>
                            <p class="text-xs text-gray-700 mb-3">Send password reset links to selected users via email</p>
                            <button onclick="executeBulkPasswordReset()" class="w-full px-4 py-2 bg-blue-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30 text-sm">
                                <i class="fas fa-envelope mr-1"></i>
                                Send Reset Emails
                            </button>
                        </div>
                        
                        <!-- Bulk Change Role -->
                        <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-user-tag text-purple-600 mr-2"></i>
                                Change Role
                            </h4>
                            <select id="bulkRoleSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 bg-white bg-opacity-80 text-black mb-2">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                            <button onclick="executeBulkRoleChange()" class="w-full px-4 py-2 bg-purple-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30 text-sm">
                                <i class="fas fa-check mr-1"></i>
                                Update Role
                            </button>
                        </div>
                        
                        <!-- Bulk Delete -->
                        <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-trash-alt text-red-600 mr-2"></i>
                                Delete Users
                            </h4>
                            <p class="text-xs text-gray-700 mb-2">This action cannot be undone</p>
                            <button onclick="executeBulkDelete()" class="w-full px-4 py-2 bg-red-500 bg-opacity-30 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-40 transition-all duration-200 border border-white border-opacity-30 text-sm">
                                <i class="fas fa-trash mr-1"></i>
                                Delete Selected Users
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-white border-opacity-20 flex justify-end">
                    <button type="button" onclick="closeBulkOperationsModal()" class="px-4 py-2 bg-gray-300 bg-opacity-20 backdrop-filter backdrop-blur-sm text-black rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Workplace User Management Modal -->
    <div id="workplaceUsersModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-4xl shadow-lg rounded-2xl">
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-black mb-0" id="workplaceUsersTitle">Manage Workplace Users</h3>
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
                        <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-users text-green-600 mr-2"></i>
                                Assigned Users (<span id="assignedUsersCount">0</span>)
                            </h4>
                            <div class="mb-3">
                                <input type="text" id="assignedUsersSearch" placeholder="Search assigned users..." 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 bg-white bg-opacity-80">
                            </div>
                            <div id="assignedUsersList" class="space-y-2 max-h-96 overflow-y-auto">
                                <!-- Dynamically filled -->
                            </div>
                        </div>
                        
                        <!-- Available Users (Right) -->
                        <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <h4 class="text-sm font-semibold text-black mb-3 flex items-center">
                                <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                                Available Users (<span id="availableUsersCount">0</span>)
                            </h4>
                            <div class="mb-3">
                                <input type="text" id="availableUsersSearch" placeholder="Search available users..." 
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
    <div id="userWorkplaceSettingsModal" class="fixed inset-0 bg-black/80 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-11/12 max-w-3xl shadow-lg rounded-2xl">
            <div class="relative bg-white bg-opacity-20 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-6 py-4 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-black mb-0" id="userWorkplaceSettingsTitle">User Workplace Settings</h3>
                            <p class="text-sm text-gray-700 mt-1">Manage workplace assignments and set primary location</p>
                        </div>
                        <button onclick="closeUserWorkplaceSettingsModal()" class="text-black hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- User Info (Left - 1/3) -->
                        <div class="bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
                            <div class="flex flex-col items-center text-center">
                                <div id="userAvatarSettings" class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg mb-3">
                                    <span class="text-white font-semibold text-2xl">U</span>
                                </div>
                                <h4 id="userNameSettings" class="text-base font-semibold text-black mb-1">User Name</h4>
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
                                        <div id="userPrimaryWorkplace" class="text-sm font-semibold text-black">None set</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Workplace Assignments (Right - 2/3) -->
                        <div class="md:col-span-2 bg-white bg-opacity-40 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-40">
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
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth < 1024) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
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

            const employeeLocationSearch = document.getElementById('employeeLocationSearch');
            if (employeeLocationSearch) {
                employeeLocationSearch.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('.employee-location-row');
                    
                    rows.forEach(row => {
                        const nameEl = row.querySelector('.text-sm.font-semibold.text-gray-900');
                        const emailEl = row.querySelector('.text-sm.text-gray-600');
                        const name = nameEl ? nameEl.textContent.toLowerCase() : '';
                        const email = emailEl ? emailEl.textContent.toLowerCase() : '';
                        
                        if (name.includes(searchTerm) || email.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
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
                        const address = row.querySelector('.workplace-address').textContent.toLowerCase();
                        
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
                            userSelect.innerHTML += `<option value="${user.id}">${user.name} (${user.email})</option>`;
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
                            workplaceSelect.innerHTML += `<option value="${workplace.id}">${workplace.name}</option>`;
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
                    const formData = {
                        name: document.getElementById('workplaceName').value,
                        address: document.getElementById('workplaceAddress').value,
                        latitude: document.getElementById('workplaceLatitude').value,
                        longitude: document.getElementById('workplaceLongitude').value,
                        radius: document.getElementById('workplaceRadius').value,
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
                            showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
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
                            showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
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
                    const password = document.getElementById('userPassword').value;
                    const passwordConfirm = document.getElementById('userPasswordConfirm').value;
                    
                    // Validate passwords match for new users or when password is being changed
                    if ((!userId || password) && password !== passwordConfirm) {
                        showNotification('Passwords do not match', 'error');
                        return;
                    }
                    
                    const formData = {
                        name: document.getElementById('userName').value,
                        email: document.getElementById('userEmail').value,
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
                                showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
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
            avatar.innerHTML = `<span class="text-white font-semibold text-2xl">${user.name.charAt(0).toUpperCase()}</span>`;
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

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 text-white transition-all duration-300 transform translate-x-full`;
            
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
                        
                        // Add workplace markers
                        if (data.workplaces) {
                            data.workplaces.forEach(workplace => {
                                const workplaceMarker = L.circleMarker([workplace.latitude, workplace.longitude], {
                                    color: '#3b82f6',
                                    fillColor: '#3b82f6',
                                    fillOpacity: 0.3,
                                    radius: workplace.radius / 10 // Scale down for visibility
                                }).addTo(employeeMap);
                                
                                workplaceMarker.bindPopup(`
                                    <strong>${workplace.name}</strong><br>
                                    ${workplace.address}<br>
                                    <small>Workplace Boundary</small>
                                `);
                                
                                employeeMarkers.push(workplaceMarker);
                            });
                        }
                        
                        // Add employee location markers
                        if (data.employeeLocations) {
                            data.employeeLocations.forEach(location => {
                                const color = getStatusColor(location.action);
                                const employeeMarker = L.marker([location.latitude, location.longitude], {
                                    icon: L.divIcon({
                                        className: 'custom-div-icon',
                                        html: `<div style="background-color: ${color}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                                        iconSize: [20, 20],
                                        iconAnchor: [10, 10]
                                    })
                                }).addTo(employeeMap);
                                
                                employeeMarker.bindPopup(`
                                    <strong>${location.user_name}</strong><br>
                                    Status: ${location.action.replace('_', ' ').toUpperCase()}<br>
                                    Time: ${new Date(location.timestamp).toLocaleString()}<br>
                                    <small>${location.address || `Coordinates: ${parseFloat(location.latitude).toFixed(4)}, ${parseFloat(location.longitude).toFixed(4)}`}</small>
                                `);
                                
                                employeeMarkers.push(employeeMarker);
                                
                                // Update location cards
                                updateLocationCard(location.user_id, location);
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
            switch(action) {
                case 'check_in': return '#10b981'; // green
                case 'check_out': return '#ef4444'; // red
                case 'break_start': return '#f59e0b'; // yellow
                case 'break_end': return '#10b981'; // green
                default: return '#6b7280'; // gray
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
                
                statusElement.className = `inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ${bgColorClass} ${textColorClass}`;
                statusElement.innerHTML = `<i class="fas ${iconClass} mr-1"></i>${actionText}`;
            }
            
            if (locationElement && location) {
                // Try to show address first, then coordinates as fallback, then 'Unknown'
                let displayText = 'Unknown';
                let iconClass = 'fa-question-circle';
                let containerClass = 'flex items-center text-gray-500';
                
                if (location.address && location.address !== 'Location not available') {
                    displayText = location.address.length > 40 ? location.address.substring(0, 40) + '...' : location.address;
                    iconClass = 'fa-map-marker-alt';
                    containerClass = 'flex items-center';
                } else if (location.latitude && location.longitude) {
                    displayText = `Coordinates: ${parseFloat(location.latitude).toFixed(4)}, ${parseFloat(location.longitude).toFixed(4)}`;
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
            switch(action) {
                case 'check_in': return 'green-600';
                case 'check_out': return 'red-600';
                case 'break_start': return 'yellow-600';
                case 'break_end': return 'green-600';
                default: return 'gray-600';
            }
        }
        
        function getStatusBgClass(action) {
            switch(action) {
                case 'check_in': return 'bg-green-100';
                case 'check_out': return 'bg-red-100';
                case 'break_start': return 'bg-yellow-100';
                case 'break_end': return 'bg-green-100';
                default: return 'bg-gray-100';
            }
        }
        
        function getStatusTextClass(action) {
            switch(action) {
                case 'check_in': return 'text-green-800';
                case 'check_out': return 'text-red-800';
                case 'break_start': return 'text-yellow-800';
                case 'break_end': return 'text-green-800';
                default: return 'text-gray-800';
            }
        }
        
        function getStatusIconClass(action) {
            switch(action) {
                case 'check_in': return 'fa-sign-in-alt';
                case 'check_out': return 'fa-sign-out-alt';
                case 'break_start': return 'fa-pause';
                case 'break_end': return 'fa-play';
                default: return 'fa-minus';
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
                                    ${location.address}
                                </div>
                                ${location.workplace_name ? `
                                <div class="text-gray-800">
                                    <i class="fas fa-building mr-2"></i>
                                    ${location.workplace_name}
                                </div>
                                ` : ''}
                                <div class="text-gray-800 text-xs">
                                    Coordinates: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}
                                </div>
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
            
            // Check current state
            const isExpanded = moreActions.style.display === 'flex';
            
            if (!isExpanded) {
                // Collapse all other rows first
                document.querySelectorAll('[id^="more-actions-"]').forEach(actions => {
                    if (actions.id !== 'more-actions-' + userId) {
                        actions.style.display = 'none';
                        const otherId = actions.id.replace('more-actions-', '');
                        const otherBtn = document.getElementById('more-btn-' + otherId);
                        if (otherBtn) {
                            otherBtn.style.display = 'inline-flex';
                        }
                    }
                });
                
                // Expand this row - hide More button, show additional actions
                moreBtn.style.display = 'none';
                moreActions.style.display = 'flex';
            } else {
                // Collapse this row - show More button, hide additional actions
                moreBtn.style.display = 'inline-flex';
                moreActions.style.display = 'none';
            }
        }

        // Toggle more actions inline (for Workplaces)
        function toggleMoreActionsWorkplace(workplaceId) {
            const moreBtn = document.getElementById('more-btn-wp-' + workplaceId);
            const moreActions = document.getElementById('more-actions-wp-' + workplaceId);
            
            // Check current state
            const isExpanded = moreActions.style.display === 'flex';
            
            if (!isExpanded) {
                // Collapse all other rows first
                document.querySelectorAll('[id^="more-actions-wp-"]').forEach(actions => {
                    if (actions.id !== 'more-actions-wp-' + workplaceId) {
                        actions.style.display = 'none';
                        const otherId = actions.id.replace('more-actions-wp-', '');
                        const otherBtn = document.getElementById('more-btn-wp-' + otherId);
                        if (otherBtn) {
                            otherBtn.style.display = 'inline-flex';
                        }
                    }
                });
                
                // Expand this row - hide More button, show additional actions
                moreBtn.style.display = 'none';
                moreActions.style.display = 'flex';
            } else {
                // Collapse this row - show More button, hide additional actions
                moreBtn.style.display = 'inline-flex';
                moreActions.style.display = 'none';
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
            
            if (!confirm(`⚠️ WARNING: Delete ${userIds.length} user(s)?\n\nThis action CANNOT be undone!\n\nType "DELETE" to confirm.`)) {
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
    </script>

</body>
</html>