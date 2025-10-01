<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CIS-AM | Attendance Monitoring System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .animated-gradient {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe, #00f2fe);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="min-h-screen animated-gradient overflow-x-hidden flex flex-col">
    
    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="max-w-4xl mx-auto text-center">
            
            <!-- Floating Logo/Icon -->
            <div class="mb-8 float-animation">
                <img src="https://depedcavite.com.ph/wp-content/uploads/2023/06/SDO-Cavite-Province-Logo-Transparent-300x200.png" 
                     alt="DepEd Cavite Logo" 
                     class="w-48 h-32 mx-auto object-contain">
            </div>

            <!-- Main Heading -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                <span class="block sm:inline">Curriculum Implementation</span>
                <span class="block sm:inline"> System</span>
            </h1>
            
            <!-- Subheading -->
            <h2 class="text-xl md:text-2xl text-white/90 font-light mb-4">
                Attendance Monitoring
            </h2>
            
            <!-- Description -->
            <p class="text-lg md:text-xl text-white/80 mb-12 max-w-2xl mx-auto leading-relaxed">
                Secure, GPS-powered attendance tracking designed for modern organizations. 
                Streamline your workforce management with precision and ease.
            </p>
            
            <!-- CTA Button -->
            <div class="mb-16">
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center px-8 py-4 bg-white text-gray-800 font-semibold rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 group">
                    <svg class="w-5 h-5 mr-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Access System
                </a>
            </div>
            
            <!-- About Section (Compact) -->
            <div class="glass-effect rounded-2xl p-8 shadow-2xl max-w-2xl mx-auto">
                <h3 class="text-2xl font-semibold text-white mb-4">About the System</h3>
                <p class="text-white/80 leading-relaxed">
                    CIS-AM utilizes cutting-edge GPS technology to ensure accurate attendance tracking. 
                    Built for educational institutions and workplaces that demand reliability, security, and ease of use.
                </p>
            </div>
            
        </div>
    </div>

    </div>

    <!-- Location Help Modal -->
    <div id="location-help-modal" class="hidden fixed inset-0 px-4 z-50" style="display: none; justify-content: center; align-items: center; background-color: rgba(0, 0, 0, 0.9); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
        <div class="glass-effect rounded-2xl p-6 max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Enable Location Access</h3>
                <button onclick="hideLocationHelp()" class="text-white/70 hover:text-white text-2xl">×</button>
            </div>
            
            <!-- Browser Detection -->
            <div id="browser-instructions" class="mb-6">
                <div class="bg-white/10 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-white mb-2 flex items-center">
                        <i id="browser-icon" class="mr-2"></i>
                        <span id="browser-name">Browser</span> Instructions
                    </h4>
                    <ol id="browser-steps" class="text-white/80 text-sm space-y-1 list-decimal list-inside">
                        <!-- Steps will be populated by JavaScript -->
                    </ol>
                </div>
            </div>

            <!-- Common Issues -->
            <div class="bg-white/10 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-white mb-2 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-300 mr-2"></i>
                    Common Issues
                </h4>
                <ul class="text-white/80 text-sm space-y-1">
                    <li>• Location services disabled on device</li>
                    <li>• Browser blocking location access</li>
                    <li>• Insecure connection (HTTP instead of HTTPS)</li>
                    <li>• Privacy settings preventing location sharing</li>
                </ul>
            </div>

            <!-- Alternative Methods -->
            <div class="bg-white/10 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-white mb-2 flex items-center">
                    <i class="fas fa-cog text-blue-300 mr-2"></i>
                    Alternative for Testing
                </h4>
                <p class="text-white/80 text-sm mb-3">
                    If you're testing in an office environment, administrators can enable "Testing Mode" which allows manual location entry.
                </p>
                <button onclick="showTestingOptions()" 
                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm">
                    Show Testing Options
                </button>
            </div>

            <!-- Testing Options (Hidden by default) -->
            <div id="testing-options" class="hidden bg-white/10 rounded-lg p-4 mb-6">
                <h5 class="font-medium text-white mb-3">Manual Location Entry (Testing Only)</h5>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-white/80 text-sm mb-1">Latitude</label>
                        <input type="number" id="test-lat" step="any" placeholder="14.5995" 
                               class="w-full px-3 py-2 bg-white/20 text-white rounded text-sm border border-white/30 placeholder-white/50">
                    </div>
                    <div>
                        <label class="block text-white/80 text-sm mb-1">Longitude</label>
                        <input type="number" id="test-lng" step="any" placeholder="120.9842" 
                               class="w-full px-3 py-2 bg-white/20 text-white rounded text-sm border border-white/30 placeholder-white/50">
                    </div>
                </div>
                <button onclick="setTestLocation()" 
                        class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm">
                    Set Test Location
                </button>
                <p class="text-white/60 text-xs mt-2">
                    Note: This simulates GPS for testing. Use your workplace coordinates.
                </p>
            </div>

            <div class="flex gap-3">
                <button onclick="testLocationAccess()" 
                        class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded">
                    Test Again
                </button>
                <button onclick="hideLocationHelp()" 
                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="w-full py-6 text-center mt-auto">
        <p class="text-white/70 text-sm px-4">
            © {{ date('Y') }} DepEd Cavite. All Rights Reserved.
        </p>
    </footer>

    <!-- Add FontAwesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <script>
        // Detect browser and show appropriate instructions
        function detectBrowser() {
            const userAgent = navigator.userAgent;
            let browser = 'Unknown';
            let icon = 'fas fa-globe';
            let steps = [];

            if (userAgent.includes('Chrome') && !userAgent.includes('Edge')) {
                browser = 'Google Chrome';
                icon = 'fab fa-chrome';
                steps = [
                    'Click the location icon (🗺️) in the address bar',
                    'Select "Always allow on this site"',
                    'Refresh the page',
                    'If no icon appears, go to Settings → Privacy and Security → Site Settings → Location',
                    'Add this site to "Allowed to access your location"'
                ];
            } else if (userAgent.includes('Edge')) {
                browser = 'Microsoft Edge';
                icon = 'fab fa-edge';
                steps = [
                    'Click the location icon in the address bar',
                    'Select "Allow for this site"',
                    'Refresh the page',
                    'Or go to Settings → Cookies and site permissions → Location',
                    'Add this site to allowed locations'
                ];
            } else if (userAgent.includes('Firefox')) {
                browser = 'Mozilla Firefox';
                icon = 'fab fa-firefox';
                steps = [
                    'Click the shield icon in the address bar',
                    'Select "Allow Location Access"',
                    'Or go to Preferences → Privacy & Security',
                    'Under Permissions, click Settings next to Location',
                    'Add this site as allowed'
                ];
            } else if (userAgent.includes('Safari')) {
                browser = 'Safari';
                icon = 'fab fa-safari';
                steps = [
                    'Go to Safari → Preferences → Websites → Location',
                    'Find this website in the list',
                    'Change setting to "Allow"',
                    'Refresh the page'
                ];
            } else {
                browser = 'Your Browser';
                icon = 'fas fa-globe';
                steps = [
                    'Look for a location icon in the address bar',
                    'Allow location access when prompted',
                    'Check browser settings for location permissions',
                    'Add this site to allowed locations'
                ];
            }

            return { browser, icon, steps };
        }

        function showLocationHelp() {
            const modal = document.getElementById('location-help-modal');
            const browserInfo = detectBrowser();
            
            // Update browser-specific content
            document.getElementById('browser-name').textContent = browserInfo.browser;
            document.getElementById('browser-icon').className = browserInfo.icon;
            
            const stepsList = document.getElementById('browser-steps');
            stepsList.innerHTML = '';
            browserInfo.steps.forEach(step => {
                const li = document.createElement('li');
                li.textContent = step;
                stepsList.appendChild(li);
            });
            
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        function hideLocationHelp() {
            const modal = document.getElementById('location-help-modal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
            document.getElementById('testing-options').classList.add('hidden');
        }

        function showTestingOptions() {
            document.getElementById('testing-options').classList.remove('hidden');
        }

        function testLocationAccess() {
            const statusDiv = document.getElementById('location-status');
            statusDiv.innerHTML = '<div class="text-blue-300 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Testing location access...</div>';

            if (!navigator.geolocation) {
                statusDiv.innerHTML = '<div class="text-red-300 text-sm"><i class="fas fa-times-circle mr-2"></i>Geolocation not supported</div>';
                return;
            }

            const options = {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 60000
            };

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    statusDiv.innerHTML = `
                        <div class="text-green-300 text-sm">
                            <i class="fas fa-check-circle mr-2"></i>
                            Location access granted! 
                            <br>
                            <span class="text-xs">Accuracy: ±${Math.round(position.coords.accuracy)}m</span>
                        </div>
                    `;
                    
                    // Hide the notice after successful test
                    setTimeout(() => {
                        document.getElementById('location-notice').style.opacity = '0.5';
                    }, 2000);
                },
                function(error) {
                    let message = '';
                    let helpButton = '';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Location access denied';
                            helpButton = '<button onclick="showLocationHelp()" class="ml-2 px-2 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs">Get Help</button>';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Location unavailable';
                            break;
                        case error.TIMEOUT:
                            message = 'Location request timed out';
                            break;
                        default:
                            message = 'Unknown location error';
                    }
                    
                    statusDiv.innerHTML = `
                        <div class="text-red-300 text-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${message}
                            ${helpButton}
                        </div>
                    `;
                },
                options
            );
        }

        function setTestLocation() {
            const lat = parseFloat(document.getElementById('test-lat').value);
            const lng = parseFloat(document.getElementById('test-lng').value);
            
            if (isNaN(lat) || isNaN(lng)) {
                alert('Please enter valid latitude and longitude values');
                return;
            }
            
            // Store test location for later use
            localStorage.setItem('testLocation', JSON.stringify({
                lat: lat,
                lng: lng,
                accuracy: 10,
                timestamp: Date.now()
            }));
            
            const statusDiv = document.getElementById('location-status');
            statusDiv.innerHTML = `
                <div class="text-orange-300 text-sm">
                    <i class="fas fa-cog mr-2"></i>
                    Test location set: ${lat.toFixed(4)}, ${lng.toFixed(4)}
                    <br>
                    <span class="text-xs">This will be used for testing purposes</span>
                </div>
            `;
            
            hideLocationHelp();
        }

        // Auto-test location on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check if location was previously tested successfully
            const lastLocationTest = localStorage.getItem('locationTestSuccess');
            if (lastLocationTest && Date.now() - parseInt(lastLocationTest) < 24 * 60 * 60 * 1000) {
                // Hide notice if location was tested successfully in last 24 hours
                document.getElementById('location-notice').style.opacity = '0.5';
            }
        });
    </script>

</body>

</html>
