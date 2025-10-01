<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CID-AMS | Admin Dashboard</title>
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
                CIS-AM Admin
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

                <!-- Add Workplace Button -->
                <div class="mb-6">
                    <button onclick="openWorkplaceModal()" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Workplace
                    </button>
                </div>

                <!-- Workplaces Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" id="workplaces-grid">
                    @foreach($workplaces as $workplace)
                    <div class="bg-white rounded-xl shadow-lg card-hover transition-all duration-300 border border-gray-200" data-workplace-id="{{ $workplace->id }}">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <i class="fas fa-building text-white text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $workplace->name }}</h3>
                                        <p class="text-sm text-gray-600">Radius: {{ $workplace->radius }}m</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $workplace->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <div class="w-1.5 h-1.5 {{ $workplace->is_active ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-1"></div>
                                        {{ $workplace->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $workplace->address }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Coordinates: {{ $workplace->latitude }}, {{ $workplace->longitude }}
                                </p>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $workplace->users_count }} users
                                </span>
                                <div class="flex space-x-2">
                                    <button onclick="editWorkplace({{ $workplace->id }})" 
                                            class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Edit workplace">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="manageUsers({{ $workplace->id }})" 
                                            class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-colors"
                                            title="Manage users">
                                        <i class="fas fa-users-cog"></i>
                                    </button>
                                    <button onclick="deleteWorkplace({{ $workplace->id }})" 
                                            class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete workplace">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- User Assignments Section -->
                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
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
                    
                    <div class="overflow-x-auto">
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
                                                class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-cog mr-1"></i>
                                            Manage
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                    <button onclick="addUser()" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-green-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-user-plus text-2xl text-green-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Add New User</h3>
                                <p class="text-sm text-gray-600">Create a new user account</p>
                            </div>
                        </div>
                    </button>
                    
                    <button onclick="switchAdminSection('users'); setTimeout(() => document.getElementById('userSearchMain').focus(), 300);" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-blue-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-search text-2xl text-blue-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Search Users</h3>
                                <p class="text-sm text-gray-600">Find and manage users</p>
                            </div>
                        </div>
                    </button>
                    
                    <button onclick="showNotification('Bulk operations coming soon', 'info')" class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-purple-500 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-tasks text-2xl text-purple-500 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-gray-900">Bulk Operations</h3>
                                <p class="text-sm text-gray-600">Mass user management</p>
                            </div>
                        </div>
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
                                <button onclick="refreshEmployeeMap()" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Refresh Map
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
                    
                    <!-- Employee Status Cards (when map is hidden) -->
                    <div id="locationCards" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="employeeLocationCards">
                            @foreach($users->where('role', 'user') as $user)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $user->isOnline() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <div class="w-2 h-2 {{ $user->isOnline() ? 'bg-green-500' : 'bg-gray-500' }} rounded-full mr-1"></div>
                                        {{ $user->isOnline() ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                                
                                <div class="text-xs text-gray-500 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span>Status:</span>
                                        <span class="font-medium {{ isset($latestAttendance[$user->id]) ? 'text-' . (
                                            $latestAttendance[$user->id]['action'] === 'check_in' ? 'green' : 
                                            ($latestAttendance[$user->id]['action'] === 'check_out' ? 'red' : 'yellow')
                                        ) . '-600' : '' }}" id="user-status-{{ $user->id }}">
                                            @if(isset($latestAttendance[$user->id]))
                                                {{ ucwords(str_replace('_', ' ', $latestAttendance[$user->id]['action'])) }}
                                            @else
                                                No activity today
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Location:</span>
                                        <span class="font-medium" id="user-location-{{ $user->id }}">
                                            @if(isset($latestAttendance[$user->id]))
                                                {{ Str::limit($latestAttendance[$user->id]['address'], 25) }}
                                            @else
                                                Unknown
                                            @endif
                                        </span>
                                    </div>
                                    @if($user->workplaces->count() > 0)
                                    <div class="flex items-center justify-between">
                                        <span>Assigned to:</span>
                                        <span class="font-medium">{{ Str::limit($user->workplaces->first()->name, 20) }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="mt-3 flex justify-between">
                                    <button onclick="showUserLocationDetails({{ $user->id }})" class="text-xs text-indigo-600 hover:text-indigo-800">
                                        View Details
                                    </button>
                                    <button onclick="centerMapOnUser({{ $user->id }})" class="text-xs text-blue-600 hover:text-blue-800">
                                        Show on Map
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Full Users Table -->
                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">All Users</h3>
                                <p class="mt-1 text-sm text-gray-600">Manage all users in the system</p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex space-x-3">
                                <div class="relative">
                                    <input type="text" id="userSearchMain" placeholder="Search users..." 
                                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
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
                    
                    <div class="overflow-x-auto">
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
                                        <div class="flex space-x-2">
                                            <button onclick="editUser({{ $user->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 p-2 hover:bg-indigo-50 rounded-lg transition-colors"
                                                    title="Edit user">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="viewUser({{ $user->id }})" 
                                                    class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition-colors"
                                                    title="View details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="manageUserWorkplaces({{ $user->id }})" 
                                                    class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Manage workplaces">
                                                <i class="fas fa-building"></i>
                                            </button>
                                            @if($user->id !== auth()->id())
                                            <button onclick="deleteUser({{ $user->id }})" 
                                                    class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Delete user">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-gray-50 px-6 py-4 border-t">
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

            // Select all functionality for Users section
            const selectAllMain = document.getElementById('selectAllMain');
            if (selectAllMain) {
                selectAllMain.addEventListener('change', function(e) {
                    const checkboxes = document.querySelectorAll('.user-checkbox-main');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = e.target.checked;
                    });
                });
            }
        });

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
                    let usersList = 'Assigned Users:\n\n';
                    if (data.workplaceUsers.length > 0) {
                        data.workplaceUsers.forEach(user => {
                            usersList += `• ${user.name} (${user.email}) - Role: ${user.pivot.role}${user.pivot.is_primary ? ' (Primary)' : ''}\n`;
                        });
                    } else {
                        usersList += 'No users assigned to this workplace.\n';
                    }
                    
                    usersList += '\nAvailable Users:\n';
                    if (data.availableUsers.length > 0) {
                        data.availableUsers.forEach(user => {
                            usersList += `• ${user.name} (${user.email})\n`;
                        });
                    } else {
                        usersList += 'All users are already assigned.\n';
                    }
                    
                    alert(usersList);
                } else {
                    showNotification('Error loading workplace users', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while loading workplace users', 'error');
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
                    let workplacesList = 'User Workplaces:\n\n';
                    if (data.userWorkplaces.length > 0) {
                        data.userWorkplaces.forEach(workplace => {
                            workplacesList += `• ${workplace.name} - Role: ${workplace.pivot.role}${workplace.pivot.is_primary ? ' (Primary)' : ''}\n`;
                        });
                    } else {
                        workplacesList += 'No workplaces assigned to this user.\n';
                    }
                    
                    workplacesList += '\nAvailable Workplaces:\n';
                    if (data.availableWorkplaces.length > 0) {
                        data.availableWorkplaces.forEach(workplace => {
                            workplacesList += `• ${workplace.name} (${workplace.address})\n`;
                        });
                    } else {
                        workplacesList += 'User is assigned to all workplaces.\n';
                    }
                    
                    alert(workplacesList);
                } else {
                    showNotification('Error loading user workplaces', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while loading user workplaces', 'error');
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
                statusElement.textContent = location.action.replace('_', ' ').toUpperCase();
                statusElement.className = `font-medium text-${getStatusColorClass(location.action)}`;
            }
            
            if (locationElement && location) {
                // Try to show address first, then coordinates as fallback, then 'Unknown'
                let displayText = 'Unknown';
                
                if (location.address && location.address !== 'Location not available') {
                    displayText = location.address.length > 30 ? location.address.substring(0, 30) + '...' : location.address;
                } else if (location.latitude && location.longitude) {
                    displayText = `${parseFloat(location.latitude).toFixed(4)}, ${parseFloat(location.longitude).toFixed(4)}`;
                }
                
                locationElement.textContent = displayText;
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