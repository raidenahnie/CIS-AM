<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::user()->id ?? 1 }}">
    <title>CID-AMS | Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-slate-100 to-blue-50 min-h-screen flex">

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
                <i class="fas fa-home w-5"></i>
                <span>My Dashboard</span>
            </a>
            <a href="#workplace-setup" class="sidebar-link" data-section="workplace-setup">
                <i class="fas fa-map-marked-alt w-5"></i>
                <span>Workplace Setup</span>
            </a>
            <a href="#gps-checkin" class="sidebar-link" data-section="gps-checkin">
                <i class="fas fa-map-pin w-5"></i>
                <span>Check In/Out</span>
            </a>
            <a href="#attendance-history" class="sidebar-link" data-section="attendance-history">
                <i class="fas fa-history w-5"></i>
                <span>My Attendance History</span>
            </a>
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
                    <h1 class="text-3xl font-bold text-gray-800" id="page-title">My Dashboard</h1>
                    <p class="text-gray-600 mt-1" id="page-subtitle">Welcome to your attendance management portal</p>
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
                <!-- Personal Stats Cards -->
                <div class="grid lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-green-600" id="my-checkins">
                                    <i class="fas fa-spinner fa-spin text-lg"></i>
                                </h3>
                                <p class="text-gray-600 font-medium">Days Present This Month</p>
                                <p class="text-sm text-green-600 mt-1" id="attendance-rate">Loading...</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-blue-600" id="avg-checkin">
                                    <i class="fas fa-spinner fa-spin text-lg"></i>
                                </h3>
                                <p class="text-gray-600 font-medium">Average Check-in Time</p>
                                <p class="text-sm text-blue-600 mt-1" id="checkin-trend">Loading...</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-3xl font-bold text-indigo-600" id="hours-worked">
                                    <i class="fas fa-spinner fa-spin text-lg"></i>
                                </h3>
                                <p class="text-gray-600 font-medium">Today's Work Hours</p>
                                <p class="text-sm text-indigo-600 mt-1" id="work-status">Loading...</p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-hourglass-half text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-map-marker-alt text-3xl mr-4"></i>
                            <h2 class="text-xl font-bold">Check In/Out</h2>
                        </div>
                        <p class="mb-6 opacity-90">Quick access to check in or out with GPS location verification.</p>
                        <button class="w-full px-6 py-3 bg-white text-green-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200" onclick="switchToSection('gps-checkin')">
                            Go to Check In/Out
                        </button>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-history text-3xl mr-4"></i>
                            <h2 class="text-xl font-bold">Attendance History</h2>
                        </div>
                        <p class="mb-6 opacity-90">View your detailed attendance records and work hour summaries.</p>
                        <button class="w-full px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200" onclick="switchToSection('attendance-history')">
                            View My History
                        </button>
                    </div>
                </div>

                <!-- Today's Workflow -->
                <div class="bg-white rounded-xl shadow-lg p-6" id="todays-schedule-section">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-tasks text-indigo-600 mr-2"></i>
                        Today's Workflow
                    </h3>
                    <div class="space-y-3" id="schedule-content">
                        <!-- Loading state -->
                        <div class="flex items-center justify-center p-8 text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin text-2xl mb-3 text-gray-300"></i>
                                <p>Loading schedule...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workplace Setup Section -->
            <div id="workplace-setup-section" class="section-content hidden">
                <div class="mb-6">
                    <!-- Location Permission Request -->
                    <div id="location-permission-request" class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6 rounded-lg hidden">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-lg font-medium text-yellow-800 mb-2">Location Access Required</h3>
                                <p class="text-yellow-700 mb-4">To use the attendance system, we need access to your device's location. Please click "Allow" when prompted by your browser.</p>
                                <button id="request-location-btn" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                                    <i class="fas fa-location-dot mr-2"></i>Enable Location Access
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Setup Steps -->
                    <div class="grid lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-location-dot text-2xl text-blue-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Step 1: Enable Location</h3>
                                <p class="text-gray-600 text-sm">Allow your browser to access your current location</p>
                                <div class="mt-3">
                                    <span id="step1-status" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Pending</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-map-marker-alt text-2xl text-green-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Step 2: Set Work Location</h3>
                                <p class="text-gray-600 text-sm">Mark your workplace on the map</p>
                                <div class="mt-3">
                                    <span id="step2-status" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Pending</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check-circle text-2xl text-indigo-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Step 3: Complete Setup</h3>
                                <p class="text-gray-600 text-sm">Save your workplace settings</p>
                                <div class="mt-3">
                                    <span id="step3-status" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Pending</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Workplace Configuration -->
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-building text-indigo-600 mr-3"></i>
                            Workplace Configuration
                        </h2>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Workplace Name</label>
                                <input type="text" id="workplace-name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="e.g., Main Office, Home Office">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea id="workplace-address" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" rows="3" placeholder="Enter your workplace address"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Radius (meters)</label>
                                <select id="workplace-radius" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="50">50 meters (Small office)</option>
                                    <option value="100" selected>100 meters (Medium building)</option>
                                    <option value="200">200 meters (Large complex)</option>
                                    <option value="500">500 meters (Campus/Remote work)</option>
                                </select>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Location</h3>
                                <div id="current-location-display" class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-600 text-sm mb-2">Latitude: <span id="current-lat">--</span></p>
                                    <p class="text-gray-600 text-sm mb-2">Longitude: <span id="current-lng">--</span></p>
                                    <p class="text-gray-600 text-sm">Accuracy: <span id="current-accuracy">--</span> meters</p>
                                </div>
                                
                                <button id="use-current-location" class="mt-4 w-full py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors" disabled>
                                    <i class="fas fa-crosshairs mr-2"></i>Use Current Location as Workplace
                                </button>
                            </div>
                            
                            <div class="flex space-x-4 pt-6">
                                <button id="save-workplace" class="flex-1 py-3 px-6 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors" disabled>
                                    <i class="fas fa-save mr-2"></i>Save Workplace
                                </button>
                                <button id="reset-workplace" class="py-3 px-6 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-redo mr-2"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Interactive Map -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-map text-indigo-600 mr-2"></i>
                            Select Workplace Location
                        </h3>
                        <div id="setup-map" class="w-full h-96 bg-gray-200 rounded-lg relative overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-location-dot text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500">Click to enable location access</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Instructions -->
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Instructions:</h4>
                            <ul class="text-xs text-blue-700 space-y-1">
                                <li>• Click anywhere on the map to set your workplace location</li>
                                <li>• The circle shows your check-in radius</li>
                                <li>• Use current location button for quick setup</li>
                                <li>• Adjust radius based on your workplace size</li>
                            </ul>
                        </div>
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
                                <h3 class="font-semibold text-blue-800 mb-2">Your Workplace:</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-building text-blue-600 mr-2"></i>
                                        <span class="text-gray-700" id="workplace-name-display">Not configured</span>
                                        <span class="ml-auto text-blue-600 font-medium" id="office-distance">-- meters</span>
                                    </div>
                                    <div class="text-xs text-gray-600" id="workplace-address-display">
                                        Please setup your workplace first
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
                            <div class="space-y-3" id="todays-activity">
                                <div class="flex items-center justify-center p-8 text-gray-500">
                                    <div class="text-center">
                                        <i class="fas fa-calendar-day text-3xl mb-3 text-gray-300"></i>
                                        <p>No activity recorded today</p>
                                        <p class="text-sm text-gray-400 mt-1">Check in to start tracking</p>
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

            <!-- Attendance History Section -->
            <div id="attendance-history-section" class="section-content hidden">
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-history text-indigo-600 mr-3"></i>
                            My Attendance History
                        </h2>
                        <div class="flex space-x-2">
                            <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="thisweek">This Week</option>
                                <option value="lastweek">Last Week</option>
                                <option value="thismonth">This Month</option>
                                <option value="lastmonth">Last Month</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Weekly Summary -->
                    <div class="grid lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-green-600" id="weekly-hours">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-green-700 font-medium">Total Hours</p>
                                <p class="text-sm text-green-600">This Week</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-blue-600" id="weekly-days">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-blue-700 font-medium">Days Present</p>
                                <p class="text-sm text-blue-600" id="weekly-days-total">Out of 0</p>
                            </div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-yellow-600" id="weekly-avg-checkin">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-yellow-700 font-medium">Avg Check-in</p>
                                <p class="text-sm text-yellow-600" id="weekly-checkin-trend">Loading...</p>
                            </div>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-indigo-600" id="weekly-attendance">
                                    <i class="fas fa-spinner fa-spin text-sm"></i>
                                </h3>
                                <p class="text-indigo-700 font-medium">Attendance</p>
                                <p class="text-sm text-indigo-600" id="weekly-performance">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Records -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="attendance-history-tbody">
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-spinner fa-spin text-2xl mb-3"></i>
                                            <p>Loading attendance history...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Showing recent 5 records
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
        
        /* Map container styling */
        #checkin-map, #setup-map {
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
        .user-location-marker, .workplace-marker {
            position: relative;
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
                'dashboard': ['My Dashboard', 'Welcome to your attendance management portal'],
                'workplace-setup': ['Workplace Setup', 'Configure your workplace location for attendance tracking'],
                'gps-checkin': ['Check In/Out', 'GPS location verification for attendance tracking'],
                'attendance-history': ['My Attendance History', 'View your detailed attendance records and summaries']
            };
            
            if (titles[sectionName]) {
                document.getElementById('page-title').textContent = titles[sectionName][0];
                document.getElementById('page-subtitle').textContent = titles[sectionName][1];
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
                
                // Update attendance rate display
                const attendanceRateEl = document.getElementById('attendance-rate');
                if (attendanceRateEl) {
                    if (data.attendance_rate > 0) {
                        attendanceRateEl.textContent = data.attendance_rate + '% attendance rate';
                    } else {
                        attendanceRateEl.textContent = 'No attendance data yet';
                    }
                }
                
                // Update check-in trend display
                const checkinTrendEl = document.getElementById('checkin-trend');
                if (checkinTrendEl) {
                    if (data.days_present_this_month > 0) {
                        checkinTrendEl.textContent = 'Based on ' + data.days_present_this_month + ' day(s)';
                    } else {
                        checkinTrendEl.textContent = 'No check-ins yet';
                    }
                }
                
                // Update status text
                const workStatusEl = document.getElementById('work-status');
                if (workStatusEl) {
                    workStatusEl.textContent = data.current_status || 'Not checked in';
                }
                
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
                const response = await fetch(`/api/attendance-history/${userId}`);
                const data = await response.json();
                
                // Update attendance history table
                const tbody = document.getElementById('attendance-history-tbody');
                if (tbody) {
                    if (data && data.length > 0) {
                        tbody.innerHTML = '';
                        
                        data.forEach(attendance => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gray-50';
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${attendance.date_raw === new Date().toISOString().split('T')[0] ? 'Today' : attendance.date}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attendance.check_in || '--'}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${attendance.check_out === 'Still working' ? '<span class="text-blue-600 font-medium">Still working</span>' : (attendance.check_out || '--')}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attendance.total_hours}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${attendance.location}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${attendance.status_class}">
                                        ${attendance.status}
                                    </span>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                        
                        // Update weekly summary based on data
                        updateWeeklySummary(data);
                    } else {
                        // Show empty state
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
                        
                        // Update weekly summary with zeros
                        updateWeeklySummary([]);
                    }
                }
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
        
        function updateWeeklySummary(attendanceData) {
            // Calculate weekly stats based on real data
            const weeklyHours = document.getElementById('weekly-hours');
            const weeklyDays = document.getElementById('weekly-days');
            const weeklyAvgCheckin = document.getElementById('weekly-avg-checkin');
            const weeklyAttendance = document.getElementById('weekly-attendance');
            const weeklyDaysTotal = document.getElementById('weekly-days-total');
            const weeklyCheckinTrend = document.getElementById('weekly-checkin-trend');
            const weeklyPerformance = document.getElementById('weekly-performance');
            
            if (attendanceData.length > 0) {
                // Calculate actual totals from data
                let totalHours = 0;
                let totalCheckins = 0;
                let checkinTimes = [];
                
                attendanceData.forEach(record => {
                    // Parse hours from "X.X hrs" format
                    const hoursMatch = record.total_hours.match(/(\d+\.?\d*)/);
                    if (hoursMatch) {
                        totalHours += parseFloat(hoursMatch[1]);
                    }
                    
                    // Collect check-in times for average calculation
                    if (record.check_in) {
                        totalCheckins++;
                        checkinTimes.push(record.check_in);
                    }
                });
                
                const presentDays = attendanceData.length;
                const avgHours = totalHours / presentDays;
                
                // Calculate average check-in time
                let avgCheckinDisplay = 'N/A';
                if (checkinTimes.length > 0) {
                    // For now, just show first check-in time as example
                    avgCheckinDisplay = checkinTimes[0];
                }
                
                if (weeklyHours) weeklyHours.textContent = Math.round(totalHours);
                if (weeklyDays) weeklyDays.textContent = presentDays;
                if (weeklyAttendance) weeklyAttendance.textContent = '100%'; // Assuming all records are present
                if (weeklyAvgCheckin) weeklyAvgCheckin.textContent = avgCheckinDisplay;
                if (weeklyDaysTotal) weeklyDaysTotal.textContent = `Out of ${presentDays}`;
                if (weeklyCheckinTrend) weeklyCheckinTrend.textContent = avgHours >= 8 ? 'Good hours' : 'Needs improvement';
                if (weeklyPerformance) weeklyPerformance.textContent = totalHours > 0 ? 'Active week!' : 'Start tracking!';
            } else {
                // Empty states
                if (weeklyHours) weeklyHours.textContent = '0';
                if (weeklyDays) weeklyDays.textContent = '0';
                if (weeklyAttendance) weeklyAttendance.textContent = 'N/A';
                if (weeklyAvgCheckin) weeklyAvgCheckin.textContent = 'N/A';
                if (weeklyDaysTotal) weeklyDaysTotal.textContent = 'Out of 0';
                if (weeklyCheckinTrend) weeklyCheckinTrend.textContent = 'No data';
                if (weeklyPerformance) weeklyPerformance.textContent = 'Start tracking!';
            }
        }
        
        async function fetchUserWorkplace(userId = null) {
            userId = userId || getCurrentUserId();
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
                    
                    // Refresh maps if they exist
                    if (checkinMap) {
                        initializeCheckinMap();
                    }
                    if (setupMap) {
                        populateWorkplaceForm(workLocations.mainOffice);
                        if (workLocations.mainOffice.lat && workLocations.mainOffice.lng) {
                            setWorkplaceLocation(workLocations.mainOffice.lat, workLocations.mainOffice.lng, false);
                        }
                    }
                } else {
                    console.log('No workplace configured in database, using localStorage or defaults');
                }
            } catch (error) {
                console.error('Failed to fetch workplace:', error);
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                    
                    // Handle specific error cases
                    if (result.redirect === 'workplace-setup') {
                        setTimeout(() => {
                            switchToSection('workplace-setup');
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
                    
                    // Refresh all data
                    fetchCurrentStatus();
                    fetchUserStats();
                    fetchAttendanceHistory();
                    fetchTodaysActivity();
                    fetchTodaysSchedule();
                } else {
                    showNotification(result.error || 'Action failed', 'error');
                    
                    // Handle specific error cases
                    if (result.redirect === 'workplace-setup') {
                        setTimeout(() => {
                            switchToSection('workplace-setup');
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
                
                // Update button based on current status
                const checkinBtn = document.getElementById('checkin-btn');
                if (checkinBtn) {
                    if (data.can_perform_action) {
                        const colorClasses = {
                            'green': 'bg-green-600 hover:bg-green-700',
                            'yellow': 'bg-yellow-600 hover:bg-yellow-700',
                            'blue': 'bg-blue-600 hover:bg-blue-700',
                            'red': 'bg-red-600 hover:bg-red-700'
                        };
                        
                        const colorClass = colorClasses[data.button_color] || 'bg-gray-600';
                        checkinBtn.className = `w-full py-4 ${colorClass} text-white rounded-lg font-semibold text-lg transition-colors duration-200`;
                        checkinBtn.innerHTML = `<i class="fas fa-clock mr-2"></i>${data.button_text}`;
                        checkinBtn.disabled = false;
                        checkinBtn.onclick = performCheckin;
                    } else {
                        checkinBtn.className = 'w-full py-4 bg-gray-500 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
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
            if (!activityContainer || !logs || logs.length === 0) {
                // Show empty state
                if (activityContainer) {
                    activityContainer.innerHTML = `
                        <div class="flex items-center justify-center p-8 text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-calendar-day text-3xl mb-3 text-gray-300"></i>
                                <p>No activity recorded today</p>
                                <p class="text-sm text-gray-400 mt-1">Check in to start tracking</p>
                            </div>
                        </div>
                    `;
                }
                return;
            }
            
            let html = '';
            
            logs.forEach((log, index) => {
                const actionIcons = {
                    'check_in': 'fa-sign-in-alt',
                    'break_start': 'fa-utensils',
                    'break_end': 'fa-play',
                    'check_out': 'fa-sign-out-alt'
                };
                
                const actionColors = {
                    'check_in': { bg: 'bg-green-50', text: 'text-green-800', dot: 'bg-green-500', icon: 'text-green-600' },
                    'break_start': { bg: 'bg-yellow-50', text: 'text-yellow-800', dot: 'bg-yellow-500', icon: 'text-yellow-600' },
                    'break_end': { bg: 'bg-blue-50', text: 'text-blue-800', dot: 'bg-blue-500', icon: 'text-blue-600' },
                    'check_out': { bg: 'bg-red-50', text: 'text-red-800', dot: 'bg-red-500', icon: 'text-red-600' }
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
                    <div class="flex items-center p-3 ${colors.bg} rounded-lg">
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
                        check_in_time: todaysAttendance.check_in ? `${today}T${todaysAttendance.check_in}:00` : null,
                        check_out_time: todaysAttendance.check_out && todaysAttendance.check_out !== 'Still working' ? 
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
        
        async function fetchTodaysSchedule(userId = null) {
            userId = userId || getCurrentUserId();
            try {
                // Get current status from API to show workflow progress
                const statusResponse = await fetch(`/api/current-status/${userId}`);
                const statusData = await statusResponse.json();
                
                // Check if user has a workplace configured
                const workplace = await fetch(`/api/user-workplace/${userId}`);
                
                const scheduleContent = document.getElementById('schedule-content');
                const scheduleSection = document.getElementById('todays-schedule-section');
                
                if (workplace.ok && !statusData.error) {
                    let scheduleHtml = '';
                    
                    // Show workflow progress based on current status
                    const steps = [
                        { action: 'check_in', label: 'Check In', icon: '🟢', description: 'Start your work day' },
                        { action: 'break_start', label: 'Start Lunch', icon: '🟡', description: 'Begin lunch break' },
                        { action: 'break_end', label: 'End Lunch', icon: '🔵', description: 'Resume afternoon work' },
                        { action: 'check_out', label: 'Check Out', icon: '🔴', description: 'End your work day' }
                    ];
                    
                    steps.forEach((step, index) => {
                        const isCompleted = index < statusData.current_logs_count;
                        const isCurrent = index === statusData.current_logs_count && !statusData.completed_today;
                        const isPending = index > statusData.current_logs_count;
                        
                        let statusText = '';
                        let statusColor = '';
                        let bgColor = '';
                        let dotColor = '';
                        
                        if (isCompleted) {
                            const log = statusData.logs[index];
                            statusText = log ? log.timestamp : 'Completed';
                            statusColor = 'text-green-600';
                            bgColor = 'bg-green-50';
                            dotColor = 'bg-green-500';
                        } else if (isCurrent) {
                            statusText = 'Ready';
                            statusColor = 'text-blue-600';
                            bgColor = 'bg-blue-50';
                            dotColor = 'bg-blue-500';
                        } else {
                            statusText = 'Pending';
                            statusColor = 'text-gray-500';
                            bgColor = 'bg-gray-50';
                            dotColor = 'bg-gray-400';
                        }
                        
                        scheduleHtml += `
                            <div class="flex items-center p-3 ${bgColor} rounded-lg ${isPending ? 'opacity-60' : ''}">
                                <div class="w-3 h-3 ${dotColor} rounded-full mr-4"></div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">${step.icon} ${step.label}</p>
                                    <p class="text-sm text-gray-600">${step.description}</p>
                                </div>
                                <div class="${statusColor} text-sm font-medium">
                                    ${statusText}
                                </div>
                            </div>
                        `;
                    });
                    
                    // Add completion message if all steps are done
                    if (statusData.completed_today) {
                        scheduleHtml += `
                            <div class="flex items-center p-3 bg-purple-50 rounded-lg border-2 border-purple-200">
                                <div class="w-3 h-3 bg-purple-500 rounded-full mr-4"></div>
                                <div class="flex-1">
                                    <p class="font-medium text-purple-800">🎉 Work Day Complete!</p>
                                    <p class="text-sm text-purple-600">All tasks completed for today</p>
                                </div>
                                <div class="text-purple-600 text-sm font-medium">
                                    Done
                                </div>
                            </div>
                        `;
                    }
                    
                    scheduleContent.innerHTML = scheduleHtml;
                } else {
                    // No workplace configured or error - show setup prompt
                    scheduleContent.innerHTML = `
                        <div class="flex items-center justify-center p-8 text-gray-500">
                            <div class="text-center">
                                <i class="fas fa-map-marker-alt text-3xl mb-3 text-gray-300"></i>
                                <h4 class="font-medium text-gray-800 mb-2">No Workflow Available</h4>
                                <p class="text-gray-500 mb-4">Set up your workplace first to see your daily workflow</p>
                                <button onclick="switchToSection('workplace-setup')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    Setup Workplace
                                </button>
                            </div>
                        </div>
                    `;
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
                }
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
            
            // Load saved workplace data and fetch from API
            loadWorkplaceData();
            fetchUserWorkplace(); // New: Fetch from database
            fetchUserStats(); // New: Fetch real stats
            fetchAttendanceHistory(); // New: Fetch real history
            fetchTodaysActivity(); // New: Fetch today's activity
            fetchTodaysSchedule(); // New: Fetch today's schedule
            fetchCurrentStatus(); // New: Fetch current work status
            updateWorkplaceDisplay();
            
            // Initialize location permission check with better error handling
            checkLocationPermission().then((hasPermission) => {
                if (hasPermission && !userLocation) {
                    // If permission is granted but no location yet, start tracking
                    return startLocationTracking();
                }
            }).catch(error => {
                console.warn('Location permission issue:', error.message);
                // Show permission request UI but don't block the rest of the app
                updateLocationStatus('error', null, error.message);
            });
            
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
        
        // Get current user ID from meta tag
        function getCurrentUserId() {
            return document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 1;
        }
        
        // Storage keys for workplace data
        const STORAGE_KEYS = {
            workplace: 'cid_ams_workplace_data',
            locationPermission: 'cid_ams_location_permission'
        };
        
        // Default and stored work locations
        let workLocations = {
            mainOffice: {
                lat: 14.5995,
                lng: 120.9842,
                name: 'Main Office',
                address: '123 Business St.',
                radius: 100
            }
        };
        
        // Load saved workplace data
        function loadWorkplaceData() {
            const saved = localStorage.getItem(STORAGE_KEYS.workplace);
            if (saved) {
                try {
                    const workplace = JSON.parse(saved);
                    workLocations.mainOffice = workplace;
                    return true;
                } catch (e) {
                    console.error('Failed to load workplace data:', e);
                }
            }
            return false;
        }
        
        // Save workplace data
        function saveWorkplaceData(workplace) {
            try {
                localStorage.setItem(STORAGE_KEYS.workplace, JSON.stringify(workplace));
                workLocations.mainOffice = workplace;
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
                    reject(new Error('Geolocation not supported'));
                    return;
                }
                
                // Try to get location immediately to test permission status
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Permission granted and location obtained
                        hasLocationPermission = true;
                        resolve(true);
                    },
                    function(error) {
                        hasLocationPermission = false;
                        let errorMsg = 'Unable to get location';
                        
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = 'Location access denied by user';
                                showLocationPermissionRequest();
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = 'Location information unavailable';
                                break;
                            case error.TIMEOUT:
                                errorMsg = 'Location request timed out - please try again';
                                break;
                        }
                        
                        // For timeout or unavailable, still show permission request as it might help
                        if (error.code !== error.PERMISSION_DENIED) {
                            showLocationPermissionRequest();
                        }
                        
                        reject(new Error(errorMsg));
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000, // 10 second timeout
                        maximumAge: 60000 // Accept 1 minute old location
                    }
                );
            });
        }
        
        function showLocationPermissionRequest() {
            document.getElementById('location-permission-request').classList.remove('hidden');
            updateStepStatus('step1-status', 'pending', 'Needs Permission');
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
                    updateLocationStatus('error', null, 'Geolocation not supported');
                    reject(new Error('Geolocation not supported'));
                    return;
                }
                
                const options = {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 60000
                };
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLocation = position;
                        hasLocationPermission = true;
                        hideLocationPermissionRequest();
                        updateLocationStatus('success', position);
                        updateCurrentLocationDisplay(position);
                        
                        // Initialize maps after getting location
                        initializeMaps();
                        updateGeofenceStatus(position);
                        
                        // Start watching for location changes with better error handling
                        if (watchId) {
                            navigator.geolocation.clearWatch(watchId);
                        }
                        
                        watchId = navigator.geolocation.watchPosition(
                            function(pos) {
                                userLocation = pos;
                                updateUserLocationOnMaps(pos);
                                updateGeofenceStatus(pos);
                                updateCurrentLocationDisplay(pos);
                            },
                            function(error) {
                                console.warn('Location watch error:', error);
                                // Don't show error for watch failures - keep using last known location
                                // Only log the error for debugging
                            },
                            {
                                enableHighAccuracy: false, // Less strict for continuous tracking
                                timeout: 30000, // Longer timeout for watch
                                maximumAge: 120000 // Accept older locations for watch
                            }
                        );
                        
                        resolve(position);
                    },
                    function(error) {
                        hasLocationPermission = false;
                        let errorMsg = 'Unable to get location';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = 'Location access denied by user';
                                showLocationPermissionRequest();
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = 'Location information unavailable';
                                break;
                            case error.TIMEOUT:
                                errorMsg = 'Location request timed out';
                                break;
                        }
                        updateLocationStatus('error', null, errorMsg);
                        reject(new Error(errorMsg));
                    },
                    options
                );
            });
        }
        
        function updateCurrentLocationDisplay(position) {
            if (!position) return;
            
            document.getElementById('current-lat').textContent = position.coords.latitude.toFixed(6);
            document.getElementById('current-lng').textContent = position.coords.longitude.toFixed(6);
            document.getElementById('current-accuracy').textContent = Math.round(position.coords.accuracy);
            
            // Enable the "Use Current Location" button
            document.getElementById('use-current-location').disabled = false;
        }
        
        function updateLocationStatus(status, position, errorMessage = null) {
            const badge = document.getElementById('location-badge');
            const location = document.getElementById('current-location');
            const sidebarStatus = document.getElementById('location-status');
            
            if (status === 'success' && position) {
                badge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
                badge.textContent = 'Location Found';
                location.innerHTML = '<i class="fas fa-map-marker-alt text-green-600 mr-2"></i>Location: ' + 
                                   position.coords.latitude.toFixed(6) + ', ' + position.coords.longitude.toFixed(6);
                sidebarStatus.textContent = 'Location Active';
            } else {
                badge.className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium';
                badge.textContent = 'Location Error';
                location.innerHTML = '<i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>' + 
                                   (errorMessage || 'Unable to get location');
                sidebarStatus.textContent = 'Location Unavailable';
            }
        }
        
        // Calculate distance between two coordinates (Haversine formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // Earth's radius in meters
            const φ1 = lat1 * Math.PI/180;
            const φ2 = lat2 * Math.PI/180;
            const Δφ = (lat2-lat1) * Math.PI/180;
            const Δλ = (lon2-lon1) * Math.PI/180;
            
            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            
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
            const workplace = workLocations.mainOffice;
            
            if (!workplace || !workplace.lat || !workplace.lng) {
                // No workplace configured
                const checkinBtn = document.getElementById('checkin-btn');
                if (checkinBtn) {
                    checkinBtn.className = 'w-full py-4 bg-gray-400 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
                    checkinBtn.innerHTML = '<i class="fas fa-cog mr-2"></i>Setup Workplace First';
                    checkinBtn.disabled = true;
                    checkinBtn.onclick = () => switchToSection('workplace-setup');
                }
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
            
            // Check if user is within geofence
            const inWorkplaceGeofence = workplaceDistance <= workplace.radius;
            
            // Update check-in button - only if user is outside geofence
            const checkinBtn = document.getElementById('checkin-btn');
            if (checkinBtn && !inWorkplaceGeofence) {
                checkinBtn.className = 'w-full py-4 bg-red-500 text-white rounded-lg font-semibold text-lg cursor-not-allowed';
                checkinBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Outside Work Area';
                checkinBtn.disabled = true;
                checkinBtn.onclick = null;
            } else if (checkinBtn && inWorkplaceGeofence) {
                // If in geofence, fetch current status to set correct button
                fetchCurrentStatus();
            }
            
            // Update location badge color based on geofence status
            const badge = document.getElementById('location-badge');
            if (badge) {
                if (inWorkplaceGeofence) {
                    badge.className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium';
                    badge.textContent = 'In Work Area';
                } else {
                    badge.className = 'px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium';
                    badge.textContent = 'Outside Work Area';
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
                checkinBtn.className = 'w-full py-4 bg-blue-600 text-white rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors duration-200';
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
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
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
        }        // Initialize maps
        function initializeMaps() {
            initializeCheckinMap();
            initializeSetupMap();
        }
        
        // Initialize GPS Check-in Map with Leaflet
        function initializeCheckinMap() {
            if (!userLocation) return;
            
            const lat = userLocation.coords.latitude;
            const lng = userLocation.coords.longitude;
            
            // Initialize Leaflet map centered on user location
            if (checkinMap) {
                checkinMap.remove();
            }
            
            checkinMap = L.map('checkin-map').setView([lat, lng], 16);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(checkinMap);
            
            // Add user location marker
            const userMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'user-location-marker',
                    html: '<div style="background: #3b82f6; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(checkinMap);
            
            userMarker.bindPopup('Your Current Location').openPopup();
            
            // Add workplace location marker and geofence circle
            const workplace = workLocations.mainOffice;
            if (workplace) {
                // Add workplace marker
                const workMarker = L.marker([workplace.lat, workplace.lng], {
                    icon: L.divIcon({
                        className: 'workplace-marker',
                        html: '<div style="background: #10b981; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(checkinMap);
                
                workMarker.bindPopup(`<b>${workplace.name}</b><br>${workplace.address}`);
                
                // Add geofence circle
                L.circle([workplace.lat, workplace.lng], {
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.1,
                    radius: workplace.radius,
                    weight: 2,
                    dashArray: '5, 5'
                }).addTo(checkinMap);
            }
        }
        
        // Initialize Setup Map for workplace registration
        function initializeSetupMap() {
            if (!userLocation) return;
            
            const lat = userLocation.coords.latitude;
            const lng = userLocation.coords.longitude;
            
            // Initialize Leaflet map for setup
            if (setupMap) {
                setupMap.remove();
            }
            
            setupMap = L.map('setup-map').setView([lat, lng], 15);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(setupMap);
            
            // Add user location marker
            const userMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'user-location-marker',
                    html: '<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                })
            }).addTo(setupMap);
            
            userMarker.bindPopup('Your Current Location');
            
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
    </script>

</body>
</html>
