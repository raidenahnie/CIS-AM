<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CIS-AM | Attendance Monitoring System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Using the CDN for live preview --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f4f5;
            /* Tailwind's zinc-100 */
        }

        ::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="antialiased">

    <div class="flex min-h-screen">

        <div class="hidden lg:flex w-full lg:w-2/5 xl:w-1/2 p-12 flex-col justify-center relative overflow-hidden"
            style="background-color: #f7f1e6;">

            <div class="absolute -top-40 -left-40 w-96 h-96 bg-white/60 rounded-full z-0"></div>
            <div
                class="absolute -bottom-52 -right-32 w-[500px] h-[500px] bg-white/60 rounded-lg z-0 transform rotate-45">
            </div>

            <img src="{{ url('https://static.vecteezy.com/system/resources/thumbnails/021/515/849/small/green-indian-almond-leaves-and-branches-on-transparent-background-file-png.png') }}"
                alt="Leaves decorative banner" class="absolute top-0 left-0 w-80 max-w-md z-20">
            <img src="{{ url('https://static.vecteezy.com/system/resources/thumbnails/047/309/343/small/tree-branches-covered-in-vibrant-leaves-cutout-transparent-backgrounds-3d-render-png.png') }}"
                alt="Leaves decorative banner" class="absolute bottom-0 right-0 rotate-180 w-80 max-w-md z-20">

            <div class="relative z-10" x-data="{ activeSlide: 1, totalSlides: 7 }">
                <div class="relative z-5 w-full aspect-[16/10] rounded-xl overflow-hidden shadow-2xl">
                    <div x-show="activeSlide === 1" class="w-full h-full">
                        <img src="{{ asset('img/pic-1.jpg') }}" alt="Carousel Image 1"
                            class="w-full h-full object-cover">
                    </div>
                    <div x-show="activeSlide === 2" class="w-full h-full" style="display: none;">
                        <img src="{{ asset('img/pic-2.jpg') }}" alt="Carousel Image 2"
                            class="w-full h-full object-cover">
                    </div>
                    <div x-show="activeSlide === 3" class="w-full h-full" style="display: none;">
                        <img src="{{ asset('img/pic-3.jpg') }}" alt="Carousel Image 3"
                            class="w-full h-full object-cover">
                    </div>
                    <div x-show="activeSlide === 4" class="w-full h-full" style="display: none;">
                        <img src="{{ asset('img/pic-5.jpg') }}" alt="Carousel Image 5"
                            class="w-full h-full object-cover">
                    </div>
                    <div x-show="activeSlide === 5" class="w-full h-full" style="display: none;">
                        <img src="{{ asset('img/pic-6.jpg') }}" alt="Carousel Image 6"
                            class="w-full h-full object-cover">
                    </div>
                    <div x-show="activeSlide === 6" class="w-full h-full" style="display: none;">
                        <img src="{{ asset('img/pic-7.jpg') }}" alt="Carousel Image 7"
                            class="w-full h-full object-cover">
                    </div>
                    <div x-show="activeSlide === 7" class="w-full h-full" style="display: none;">
                        <img src="{{ asset('img/pic-8.jpg') }}" alt="Carousel Image 8"
                            class="w-full h-full object-cover">
                    </div>

                    <button @click="activeSlide = (activeSlide === 1) ? totalSlides : activeSlide - 1"
                        class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/10 hover:bg-white/60 rounded-full p-2 shadow-md z-10 transition focus:outline-none">
                        <svg class="w-5 h-5 text-zinc-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button @click="activeSlide = (activeSlide === totalSlides) ? 1 : activeSlide + 1"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/10 hover:bg-white/60 rounded-full p-2 shadow-md z-10 transition focus:outline-none">
                        <svg class="w-5 h-5 text-zinc-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div x-init="setInterval(() => { activeSlide = (activeSlide % totalSlides) + 1 }, 5000)" class="hidden"></div>
            </div>

        </div>

        <div class="w-full lg:w-3/5 xl:w-1/2 bg-white flex items-center justify-center p-8 relative overflow-hidden">

            <div class="absolute -top-24 -left-24 w-64 h-64 bg-gray-100 rounded-full z-0"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-white rounded-2xl z-0 transform rotate-45"></div>


            <div class="relative z-10 max-w-md w-full text-center">

                <div class="flex justify-center mb-6">
                    <img src="{{ asset('img/CIDLogo.png') }}" alt=" CID DepEd Cavite Logo"
                        class="w-40 h-auto object-contain 
                               transition-transform duration-300 ease-in-out 
                               hover:scale-110 active:scale-95">
                </div>

                <h1 class="text-4xl sm:text-5xl font-extrabold text-black leading-tight mb-4">
                    Curriculum Implementation System
                </h1>
                
                <p class="text-lg font-semibold text-black my-5">
                    <span class="bg-yellow-200 border border-gray-300 rounded-full px-4 py-1">
                        Attendance Monitoring
                    </span>
                </p>

                <p class="text-base text-zinc-600 mb-10 max-w-xl mx-auto leading-relaxed">
                    Secure, GPS-powered attendance tracking designed for modern organizations.
                    Streamline your workforce management with precision and ease.
                </p>

                <div class="mb-12">
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center justify-center w-full max-w-xs px-10 py-4 text-lg bg-red-600 text-white font-semibold rounded-lg shadow-lg hover:bg-red-700 transform hover:scale-105 transition-all duration-300 group">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Access System
                    </a>
                </div>

                <div class="mt-12 text-center">
                    <span class="font-bold text-green-500">Data Privacy Notice</span>
                    <p class="text-xs text-zinc-500">
                        By accessing this system, you agree to the processing
                        of your personal data in accordance with the
                        <a href="https://www.officialgazette.gov.ph/2012/08/15/republic-act-no-10173/" target="_blank"
                            class="font-medium text-zinc-700 hover:text-black underline">
                            Data Privacy Act of 2012 (RA 10173)
                        </a>.
                    </p>
                </div>

            </div>
        </div>

    </div>


    <script>
        // --- All your original JS is here, it does not need to be changed ---

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

            return {
                browser,
                icon,
                steps
            };
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

        // NOTE: I've updated this function to use the new text colors
        function testLocationAccess() {
            // This function needs a DOM element #location-status to write to.
            // Your original HTML didn't seem to have one outside the modal.
            // If this function is only called *from* the modal, it will need 
            // a 'statusDiv' inside the modal.
            // For now, I will assume it's part of a different view or was
            // intended to be inside the modal.

            // Let's create a temporary status message in the modal if it's not there
            let statusDiv = document.getElementById('modal-location-status');
            if (!statusDiv) {
                statusDiv = document.createElement('div');
                statusDiv.id = 'modal-location-status';
                statusDiv.className = 'mt-4 text-center';
                // Insert it before the buttons
                let modal = document.getElementById('location-help-modal').querySelector(
                    '.flex.flex-col.sm\\:flex-row.gap-3');
                if (modal) {
                    modal.parentNode.insertBefore(statusDiv, modal);
                }
            }

            statusDiv.innerHTML =
                '<div class="text-blue-500 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i>Testing location access...</div>';

            if (!navigator.geolocation) {
                statusDiv.innerHTML =
                    '<div class="text-red-500 text-sm"><i class="fas fa-times-circle mr-2"></i>Geolocation not supported</div>';
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
                        <div class="text-green-500 text-sm">
                            <i class="fas fa-check-circle mr-2"></i>
                            Location access granted! 
                            <br>
                            <span class="text-xs">Accuracy: ±${Math.round(position.coords.accuracy)}m</span>
                        </div>
                    `;
                },
                function(error) {
                    let message = '';
                    // The help button is already visible (the modal is open)
                    // so we don't need to add another one.

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Location access denied. Please check browser settings.';
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
                        <div class="text-red-500 text-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${message}
                        </div>
                    `;
                },
                options
            );
        }

        // NOTE: I've updated this function to use the new text colors
        function setTestLocation() {
            const lat = parseFloat(document.getElementById('test-lat').value);
            const lng = parseFloat(document.getElementById('test-lng').value);

            if (isNaN(lat) || isNaN(lng)) {
                alert('Please enter valid latitude and longitude values');
                return;
            }

            localStorage.setItem('testLocation', JSON.stringify({
                lat: lat,
                lng: lng,
                accuracy: 10,
                timestamp: Date.now()
            }));

            // Find or create the status div
            let statusDiv = document.getElementById('modal-location-status');
            if (!statusDiv) {
                statusDiv = document.createElement('div');
                statusDiv.id = 'modal-location-status';
                statusDiv.className = 'mt-4 text-center';
                let modal = document.getElementById('location-help-modal').querySelector(
                    '.flex.flex-col.sm\\:flex-row.gap-3');
                if (modal) {
                    modal.parentNode.insertBefore(statusDiv, modal);
                }
            }

            statusDiv.innerHTML = `
                <div class="text-orange-500 text-sm">
                    <i class="fas fa-cog mr-2"></i>
                    Test location set: ${lat.toFixed(4)}, ${lng.toFixed(4)}
                    <br>
                    <span class="text-xs">This will be used for testing purposes</span>
                </div>
            `;

            // Hide the modal after setting
            setTimeout(hideLocationHelp, 1500);
        }

        // Your original DOMContentLoaded (unchanged)
        document.addEventListener('DOMContentLoaded', function() {
            const lastLocationTest = localStorage.getItem('locationTestSuccess');
            if (lastLocationTest && Date.now() - parseInt(lastLocationTest) < 24 * 60 * 60 * 1000) {
                // This notice doesn't exist in the new HTML,
                // but I'll leave the logic in case you add it back
                const notice = document.getElementById('location-notice');
                if (notice) {
                    notice.style.opacity = '0.5';
                }
            }
        });
    </script>

</body>

</html>
