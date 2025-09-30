<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CID-AMS | Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <div class="flex items-center justify-between p-4 border-b">
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
                
                <a href="#" 
                   class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 card-hover border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <i class="fas fa-user-plus text-2xl text-blue-500 mr-4"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Add New User</h3>
                            <p class="text-sm text-gray-600">Create user account</p>
                        </div>
                    </div>
                </a>
                
                <a href="#" 
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

            <!-- Users Table -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">All Users</h3>
                            <p class="mt-1 text-sm text-gray-600">Manage all users in the system</p>
                        </div>
                        <div class="mt-4 sm:mt-0 flex space-x-3">
                            <div class="relative">
                                <input type="text" id="userSearch" placeholder="Search users..." 
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
                    <table class="min-w-full divide-y divide-gray-200" id="usersTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="usersTableBody">
                            @foreach($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors user-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="user-checkbox rounded border-gray-300" value="{{ $user->id }}">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                        {{ $user->created_at->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        Active
                                    </span>
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
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">User Management</h3>
                    <p class="text-gray-500">Advanced user management features coming soon.</p>
                </div>
            </div>

            <!-- Attendance Section -->
            <div id="attendance-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Attendance Overview</h1>
                    <p class="mt-2 text-sm text-gray-600">Monitor and analyze attendance data.</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-clock text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Attendance Reports</h3>
                    <p class="text-gray-500">Detailed attendance analytics coming soon.</p>
                </div>
            </div>

            <!-- Reports Section -->
            <div id="reports-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
                    <p class="mt-2 text-sm text-gray-600">Generate comprehensive reports and insights.</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-chart-bar text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Analytics Dashboard</h3>
                    <p class="text-gray-500">Advanced reporting features coming soon.</p>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" class="admin-section hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
                    <p class="mt-2 text-sm text-gray-600">Configure system preferences and security settings.</p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <i class="fas fa-cog text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">System Configuration</h3>
                    <p class="text-gray-500">Advanced settings panel coming soon.</p>
                </div>
            </div>

        </div>
    </main>

    <!-- Workplace Modal -->
    <div id="workplaceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modalTitle">Add New Workplace</h3>
                <form id="workplaceForm">
                    <input type="hidden" id="workplaceId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" id="workplaceName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea id="workplaceAddress" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" rows="2" required></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                            <input type="number" id="workplaceLatitude" step="any" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                            <input type="number" id="workplaceLongitude" step="any" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Radius (meters)</label>
                        <input type="number" id="workplaceRadius" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                    </div>
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="workplaceActive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeWorkplaceModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign User to Workplace</h3>
                <form id="assignmentForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                        <select id="assignmentUser" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                            <option value="">Select a user...</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Workplace</label>
                        <select id="assignmentWorkplace" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                            <option value="">Select a workplace...</option>
                            @foreach($workplaces as $workplace)
                            <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <input type="text" id="assignmentRole" value="employee" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="assignmentPrimary" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Set as Primary Workplace</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAssignmentModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Assign</button>
                    </div>
                </form>
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
            const userSearch = document.getElementById('userSearch');
            if (userSearch) {
                userSearch.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('.user-row');
                    
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

            // Select all functionality
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function(e) {
                    const checkboxes = document.querySelectorAll('.user-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = e.target.checked;
                    });
                });
            }
        });

        // User action functions
        function addUser() {
            alert('Add user functionality to be implemented');
        }

        function editUser(userId) {
            alert(`Edit user functionality for user ID: ${userId} to be implemented`);
        }

        function viewUser(userId) {
            alert(`View user details for user ID: ${userId} to be implemented`);
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                alert(`Delete user functionality for user ID: ${userId} to be implemented`);
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
        
        // Form Handlers
        document.addEventListener('DOMContentLoaded', function() {
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
        });
        
        // Workplace Action Functions
        function editWorkplace(id) {
            openWorkplaceModal(id);
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
            showNotification('Workplace user management feature coming soon', 'info');
        }
        
        function manageUserWorkplaces(userId) {
            showNotification('User workplace management feature coming soon', 'info');
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
    </script>

</body>
</html>