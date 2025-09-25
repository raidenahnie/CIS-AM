<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CID-AMS | Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-100 to-blue-50 min-h-screen flex")

    <!-- Enhanced Sidebar -->
    <aside class="w-72 bg-white shadow-xl h-screen fixed border-r border-gray-200">
        <div class="p-6 border-b border-gray-100">
            <div class="text-indigo-600 text-3xl font-bold flex items-center">
                <i class="fas fa-map-marker-alt mr-3"></i>
                CID-AMS
            </div>
            <p class="text-gray-500 text-sm mt-1">Attendance Management System</p>
        </div>
        <nav class="mt-8 space-y-1 px-4">
            <a href="#dashboard" class="sidebar-link active" data-section="dashboard">
                <i class="fas fa-chart-line w-5"></i>
                <span>Dashboard Overview</span>
            </a>
            <a href="#gps-checkin" class="sidebar-link" data-section="gps-checkin">
                <i class="fas fa-map-pin w-5"></i>
                <span>GPS Check-In</span>
            </a>
            <a href="#realtime-map" class="sidebar-link" data-section="realtime-map">
                <i class="fas fa-globe w-5"></i>
                <span>Realtime Map</span>
            </a>
            <a href="#reports" class="sidebar-link" data-section="reports">
                <i class="fas fa-file-chart-column w-5"></i>
                <span>Reports & Analytics</span>
            </a>
            <div class="border-t border-gray-200 mt-6 pt-6">
                <a href="#settings" class="sidebar-link" data-section="settings">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
        
        <!-- Location Status Indicator -->
        <div class="absolute bottom-6 left-4 right-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></div>
                    <span class="text-green-700 text-sm font-medium" id="location-status">Location Active</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-72 flex-1">
        <!-- Enhanced Topbar -->
        <div class="bg-white shadow-sm border-b border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800" id="page-title">Dashboard Overview</h1>
                    <p class="text-gray-600 mt-1" id="page-subtitle">Monitor attendance and track employee locations</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Welcome back,</p>
                        <p class="font-semibold text-gray-800">{{ Auth::user()->name ?? 'User' }}</p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="border-l border-gray-200 pl-4">
                        <a href="{{ route('logout.get') }}" class="px-6 py-2 bg-red-500 text-white rounded-lg shadow hover:bg-red-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
            <!-- Dashboard Overview Section -->
            <div id="dashboard-section" class="section-content">
                <!-- Real-time Stats Cards -->
                <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-green-600" id="checked-in-count">34</h3>
                                <p class="text-gray-600 font-medium">Checked In Today</p>
                                <p class="text-sm text-green-600 mt-1">↑ 5.2% from yesterday</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-check text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-yellow-600" id="late-count">5</h3>
                                <p class="text-gray-600 font-medium">Late Arrivals</p>
                                <p class="text-sm text-yellow-600 mt-1">↓ 2.1% improvement</p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-red-600" id="absent-count">12</h3>
                                <p class="text-gray-600 font-medium">Absent Today</p>
                                <p class="text-sm text-red-600 mt-1">↑ 1.5% from yesterday</p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-times text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-blue-600" id="total-employees">51</h3>
                                <p class="text-gray-600 font-medium">Total Employees</p>
                                <p class="text-sm text-blue-600 mt-1">Active workforce</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Enhanced -->
                <div class="grid lg:grid-cols-3 gap-8 mb-8">
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-map-marker-alt text-3xl mr-4"></i>
                            <h2 class="text-xl font-bold">GPS Tracking</h2>
                        </div>
                        <p class="mb-6 opacity-90">Verify location and check-in within your designated geofence area.</p>
                        <button class="w-full px-6 py-3 bg-white text-green-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200" onclick="switchToSection('gps-checkin')">
                            Start Check-In
                        </button>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-globe text-3xl mr-4"></i>
                            <h2 class="text-xl font-bold">Realtime Dashboard</h2>
                        </div>
                        <p class="mb-6 opacity-90">Monitor live attendance with interactive maps and real-time updates.</p>
                        <button class="w-full px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200" onclick="switchToSection('realtime-map')">
                            View Live Map
                        </button>
                    </div>
                    
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-chart-bar text-3xl mr-4"></i>
                            <h2 class="text-xl font-bold">Reports & Analytics</h2>
                        </div>
                        <p class="mb-6 opacity-90">Generate comprehensive attendance reports and analytics insights.</p>
                        <button class="w-full px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200" onclick="switchToSection('reports')">
                            Generate Reports
                        </button>
                    </div>
                </div>

                <!-- Analytics Charts -->
                <div class="grid lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 flex items-center">
                            <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                            Weekly Attendance Trend
                        </h3>
                        <canvas id="weeklyChart" width="400" style="max-height: 200px;"></canvas>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 flex items-center">
                            <i class="fas fa-chart-pie text-indigo-600 mr-2"></i>
                            Today's Status Distribution
                        </h3>
                        <canvas id="statusChart" width="400" style="max-height: 200px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- GPS Check-in Section -->
            <div id="gps-checkin-section" class="section-content hidden">
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Check-in Interface -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-map-marker-alt text-3xl text-green-600"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">GPS Check-In</h2>
                                <p class="text-gray-600">Verify your location and check-in to start your work day</p>
                            </div>
                            
                            <!-- Location Status -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Location Status:</span>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium" id="location-badge">
                                        Checking...
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600" id="current-location">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Getting your location...
                                </div>
                            </div>
                            
                            <!-- Geofence Status -->
                            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h3 class="font-semibold text-blue-800 mb-2">Allowed Check-in Areas:</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-building text-blue-600 mr-2"></i>
                                        <span class="text-gray-700">Main Office - 123 Business St.</span>
                                        <span class="ml-auto text-blue-600 font-medium" id="office-distance">-- meters</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-home text-blue-600 mr-2"></i>
                                        <span class="text-gray-700">Remote Work Zone</span>
                                        <span class="ml-auto text-blue-600 font-medium" id="remote-distance">-- meters</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Check-in Button -->
                            <button id="checkin-btn" class="w-full py-4 bg-gray-400 text-white rounded-lg font-semibold text-lg cursor-not-allowed" disabled>
                                <i class="fas fa-location-crosshairs mr-2"></i>
                                Waiting for Location...
                            </button>
                            
                            <!-- Manual Override (Admin only) -->
                            <div class="mt-4 text-center">
                                <button class="text-sm text-gray-500 hover:text-gray-700 underline">
                                    Request manual check-in override
                                </button>
                            </div>
                        </div>
                        
                        <!-- Today's Check-in History -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Today's Activity</h3>
                            <div class="space-y-3">
                                <div class="flex items-center p-3 bg-green-50 rounded-lg">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-sign-in-alt text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-green-800">Check-in</p>
                                        <p class="text-sm text-green-600">Main Office • 8:30 AM</p>
                                    </div>
                                    <div class="text-green-600">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg opacity-50">
                                    <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-sign-out-alt text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-600">Check-out</p>
                                        <p class="text-sm text-gray-500">Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location Map -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-map text-indigo-600 mr-2"></i>
                            Location Verification
                        </h3>
                        <div id="checkin-map" class="w-full h-96 bg-gray-200 rounded-lg relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500">Loading map...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Legend -->
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Map Legend:</h4>
                            <div class="flex flex-wrap gap-4 text-xs">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-1"></div>
                                    <span>Your Location</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                                    <span>Allowed Area</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 border-2 border-dashed border-red-500 rounded-full mr-1"></div>
                                    <span>Geofence Boundary</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Realtime Map Section -->
            <div id="realtime-map-section" class="section-content hidden">
                <div class="mb-6">
                    <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6 mb-8">
                        <!-- Live Stats -->
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold text-green-600" id="live-online">24</h3>
                                    <p class="text-gray-600 font-medium">Online Now</p>
                                </div>
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold text-blue-600" id="live-field">18</h3>
                                    <p class="text-gray-600 font-medium">In Field</p>
                                </div>
                                <i class="fas fa-map-marked-alt text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold text-orange-600" id="live-break">3</h3>
                                    <p class="text-gray-600 font-medium">On Break</p>
                                </div>
                                <i class="fas fa-coffee text-orange-600 text-xl"></i>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold text-red-600" id="live-offline">9</h3>
                                    <p class="text-gray-600 font-medium">Offline</p>
                                </div>
                                <i class="fas fa-user-slash text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Interactive Map -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-globe text-indigo-600 mr-2"></i>
                                Live Employee Tracking
                            </h2>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-sm hover:bg-indigo-200" onclick="refreshMap()">
                                    <i class="fas fa-refresh mr-1"></i>Refresh
                                </button>
                                <select class="px-3 py-1 border border-gray-300 rounded-lg text-sm" id="map-filter">
                                    <option value="all">All Employees</option>
                                    <option value="online">Online Only</option>
                                    <option value="field">Field Workers</option>
                                    <option value="office">Office Workers</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="realtime-map" class="w-full h-96 bg-gray-200 rounded-lg relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500">Loading live map...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Controls -->
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex space-x-4 text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span>Online (24)</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                    <span>Break (3)</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                    <span>Offline (9)</span>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500">
                                Last updated: <span id="last-update">Just now</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employee List & Details -->
                    <div class="space-y-6">
                        <!-- Search & Filter -->
                        <div class="bg-white rounded-xl shadow-lg p-4">
                            <div class="relative">
                                <input type="text" placeholder="Search employees..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <!-- Employee Status List -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Employee Status</h3>
                            <div class="space-y-3 max-h-64 overflow-y-auto" id="employee-list">
                                <!-- Online employees -->
                                <div class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 cursor-pointer" onclick="focusOnEmployee('john-doe')">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-semibold mr-3">
                                        JD
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800">John Doe</p>
                                        <p class="text-sm text-green-600">Online • Main Office</p>
                                    </div>
                                    <div class="text-green-500">
                                        <i class="fas fa-circle text-xs"></i>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 cursor-pointer" onclick="focusOnEmployee('jane-smith')">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold mr-3">
                                        JS
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800">Jane Smith</p>
                                        <p class="text-sm text-blue-600">Field Work • Client Site A</p>
                                    </div>
                                    <div class="text-blue-500">
                                        <i class="fas fa-circle text-xs"></i>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 cursor-pointer">
                                    <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-semibold mr-3">
                                        MB
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800">Mike Brown</p>
                                        <p class="text-sm text-yellow-600">Break • Lunch</p>
                                    </div>
                                    <div class="text-yellow-500">
                                        <i class="fas fa-circle text-xs"></i>
                                    </div>
                                </div>
                                
                                <div class="flex items-center p-3 bg-red-50 rounded-lg hover:bg-red-100 cursor-pointer">
                                    <div class="w-10 h-10 bg-red-400 rounded-full flex items-center justify-center text-white text-sm font-semibold mr-3">
                                        SA
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800">Sarah Adams</p>
                                        <p class="text-sm text-red-600">Offline • 2 hours ago</p>
                                    </div>
                                    <div class="text-red-400">
                                        <i class="fas fa-circle text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                                    <i class="fas fa-bell mr-2"></i>
                                    Send Notification
                                </button>
                                <button class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                    <i class="fas fa-download mr-2"></i>
                                    Export Locations
                                </button>
                                <button class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <i class="fas fa-cog mr-2"></i>
                                    Geofence Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports & Analytics Section -->
            <div id="reports-section" class="section-content hidden">
                <div class="grid lg:grid-cols-4 gap-6 mb-8">
                    <!-- Report Generation -->
                    <div class="lg:col-span-3 bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-chart-bar text-indigo-600 mr-3"></i>
                            Generate Reports
                        </h2>
                        
                        <div class="grid md:grid-cols-3 gap-6 mb-8">
                            <!-- Date Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="daily">Daily Report</option>
                                    <option value="weekly">Weekly Summary</option>
                                    <option value="monthly">Monthly Analytics</option>
                                    <option value="custom">Custom Period</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" value="2024-01-01">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" value="2024-01-31">
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="all">All Departments</option>
                                    <option value="it">IT Department</option>
                                    <option value="sales">Sales Team</option>
                                    <option value="hr">Human Resources</option>
                                    <option value="marketing">Marketing</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="pdf">PDF Report</option>
                                    <option value="excel">Excel Spreadsheet</option>
                                    <option value="csv">CSV Data</option>
                                    <option value="json">JSON Data</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex space-x-4">
                            <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-chart-line mr-2"></i>
                                Generate Report
                            </button>
                            <button class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-download mr-2"></i>
                                Quick Export
                            </button>
                            <button class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-email mr-2"></i>
                                Email Report
                            </button>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">This Month</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Avg. Attendance</span>
                                    <span class="font-bold text-green-600">94.5%</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Late Arrivals</span>
                                    <span class="font-bold text-yellow-600">12</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Absent Days</span>
                                    <span class="font-bold text-red-600">8</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Overtime Hours</span>
                                    <span class="font-bold text-blue-600">156</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Trending</h3>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <i class="fas fa-trending-up text-green-500 mr-2"></i>
                                    <span class="text-sm text-gray-600">Punctuality improved 15%</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-trending-down text-red-500 mr-2"></i>
                                    <span class="text-sm text-gray-600">Remote work increased 8%</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-trending-up text-blue-500 mr-2"></i>
                                    <span class="text-sm text-gray-600">Team productivity up 12%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Analytics Charts -->
                <div class="grid lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 flex items-center">
                            <i class="fas fa-chart-area text-indigo-600 mr-2"></i>
                            Monthly Attendance Trend
                        </h3>
                        <canvas id="monthlyTrendChart" width="400" height="250"></canvas>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4 flex items-center">
                            <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
                            Department Comparison
                        </h3>
                        <canvas id="departmentChart" width="400" height="250"></canvas>
                    </div>
                </div>
                
                <!-- Detailed Analytics -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-table text-indigo-600 mr-2"></i>
                            Detailed Analytics
                        </h3>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                                Filter
                            </button>
                            <button class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-sm hover:bg-indigo-200">
                                Export Table
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Check-in</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">JD</div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">John Doe</div>
                                                <div class="text-sm text-gray-500">john.doe@company.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">IT Department</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">98.5%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">8:15 AM</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">JS</div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Jane Smith</div>
                                                <div class="text-sm text-gray-500">jane.smith@company.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Sales</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">94.2%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">8:28 AM</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Good</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">MB</div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Mike Brown</div>
                                                <div class="text-sm text-gray-500">mike.brown@company.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Marketing</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">91.8%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">8:35 AM</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">8</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Needs Improvement</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Showing 1-3 of 51 employees
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Previous</button>
                            <button class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">1</button>
                            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">2</button>
                            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Next</button>
                        </div>
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
        }
        
        .sidebar-link:hover {
            background-color: #f8fafc;
            color: #4f46e5;
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
    </style>

    <script>
        // Section switching functionality
        function switchToSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
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
                'dashboard': ['Dashboard Overview', 'Monitor attendance and track employee locations'],
                'gps-checkin': ['GPS Check-In', 'Verify your location and check-in to work'],
                'realtime-map': ['Realtime Map', 'Live tracking of all employee locations'],
                'reports': ['Reports & Analytics', 'Generate comprehensive attendance reports']
            };
            
            if (titles[sectionName]) {
                document.getElementById('page-title').textContent = titles[sectionName][0];
                document.getElementById('page-subtitle').textContent = titles[sectionName][1];
            }
        }
        
        // Initialize sidebar click handlers
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    if (section) {
                        switchToSection(section);
                    }
                });
            });
            
            // Initialize charts placeholder
            initializeCharts();
            
            // Start location tracking (placeholder)
            startLocationTracking();
        });
        
        // Chart initialization
        function initializeCharts() {
            // Weekly Attendance Chart
            const weeklyCtx = document.getElementById('weeklyChart');
            if (weeklyCtx) {
                new Chart(weeklyCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Attendance',
                            data: [45, 48, 42, 49, 46, 28, 15],
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Late', 'Absent'],
                        datasets: [{
                            data: [34, 5, 12],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        }
        
        // Location tracking
        function startLocationTracking() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        updateLocationStatus('success', position);
                    },
                    function(error) {
                        updateLocationStatus('error', null);
                    }
                );
            }
        }
        
        function updateLocationStatus(status, position) {
            const badge = document.getElementById('location-badge');
            const location = document.getElementById('current-location');
            
            if (status === 'success' && position) {
                badge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
                badge.textContent = 'Location Found';
                location.innerHTML = '<i class="fas fa-map-marker-alt text-green-600 mr-2"></i>Location: ' + 
                                   position.coords.latitude.toFixed(4) + ', ' + position.coords.longitude.toFixed(4);
            } else {
                badge.className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium';
                badge.textContent = 'Location Error';
                location.innerHTML = '<i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Unable to get location';
            }
        }
        
        // Placeholder functions
        function refreshMap() {
            console.log('Refreshing map...');
        }
        
        function focusOnEmployee(employeeId) {
            console.log('Focusing on employee:', employeeId);
        }
    </script>

</body>
</html>
