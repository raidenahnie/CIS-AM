<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CIS-AM | Login</title> {{-- Changed title from original login.blade --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Using the CDN for live preview --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased">

    <div class="flex min-h-screen">

        {{-- This is the Left Column (Visuals) from welcome.blade.php --}}
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

        {{-- This is the Right Column (Content) adapted for the Login Form --}}
        <div class="w-full lg:w-3/5 xl:w-1/2 bg-white flex items-center justify-center p-8 sm:p-12 relative overflow-hidden">

            <div class="absolute -top-24 -left-24 w-64 h-64 bg-gray-100 rounded-full z-0"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-white rounded-2xl z-0 transform rotate-45"></div>

            {{-- All content from the original login card is placed inside this wrapper --}}
            <div class="relative z-10 max-w-md w-full">

                {{-- Header (Combined from welcome.blade and login.blade) --}}
                <div class="flex justify-center mb-4 sm:mb-6">
                    <img src="{{ asset('img/CIDLogo.png') }}" alt=" CID DepEd Cavite Logo"
                        class="w-24 h-auto object-contain 
                               transition-transform duration-300 ease-in-out 
                               hover:scale-110 active:scale-95">
                </div>
                <div class="text-center mb-4 sm:mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Sign In to CIS-AM</h1>
                    <p class="text-sm sm:text-base text-gray-500">Welcome back! Please enter your details.</p>
                </div>

                {{-- Error Messages (from login.blade.php) --}}
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium">Login Failed</h3>
                                <div class="mt-1 text-sm">
                                    Please check your credentials and try again.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium">Session Expired</h3>
                                <div class="mt-1 text-sm">
                                    {{ session('error') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Success Messages (from login.blade.php) --}}
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium">Success</h3>
                                <div class="mt-1 text-sm">
                                    {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Form (from login.blade.php) --}}
                <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-300 @else border-gray-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                   class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @else border-gray-300 @enderror">
                            <button type="button" onclick="togglePasswordVisibility()"
                                    class="absolute inset-y-0 right-0 mt-1 pr-3 flex items-center text-gray-600 cursor-pointer hover:text-gray-800">
                                <i class="fas fa-eye" id="togglePassword"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" onclick="showForgotPasswordModal()" class="text-sm text-indigo-600 hover:underline">Forgot password?</a>
                    </div>

                    <button type="submit" 
                            class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition-all duration-300 transform hover:scale-105">
                        Login
                    </button>
                </form>

                {{-- Data Privacy Notice Summary --}}
                <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4" x-data="privacyModal">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-xs font-semibold text-green-800 mb-1">Privacy Notice</h4>
                            <p class="text-xs text-green-700 leading-relaxed">
                                By logging in, you consent to the collection of your work data (name, email, location, attendance records) in compliance with 
                                <button @click="openModal()" type="button" class="font-semibold underline hover:text-green-900 focus:outline-none">
                                    RA 10173
                                </button>.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Footer Links (from login.blade.php) --}}
                <p class="mt-6 text-center text-xs sm:text-sm text-gray-600 px-2">
                    Need access? Contact your system administrator.
                </p>
                <p class="mt-2 text-center text-xs sm:text-sm text-gray-600 px-2">
                   Return to <a href="{{ route('landing') }}" class="text-indigo-600 hover:underline">home page</a>
                </p>

            </div>
        </div>

    </div>

    {{-- Privacy Notice Modal (Full Version) --}}
    <div x-data="privacyModal" x-show="showPrivacyModal" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden" 
             @click.stop
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h2 class="text-2xl font-bold">Data Privacy Notice</h2>
                    </div>
                    <button @click="closeModal()" 
                            class="rounded-full p-2 transition-colors hover:bg-white/20 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div class="space-y-4 text-zinc-700">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm font-semibold text-yellow-800">
                                Please review this privacy information before proceeding.
                            </p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-zinc-900">CIS-AM Attendance Monitoring System</h3>
                    
                    <p class="leading-relaxed">
                        By accessing and using this system, you acknowledge and agree to the collection, processing, and storage of your personal data in accordance with the 
                        <a href="https://www.officialgazette.gov.ph/2012/08/15/republic-act-no-10173/" target="_blank" class="text-green-600 font-semibold hover:underline">
                            Data Privacy Act of 2012 (Republic Act No. 10173)
                        </a>.
                    </p>

                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <h4 class="font-bold text-zinc-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Data We Collect
                        </h4>
                        <ul class="list-disc list-inside space-y-1 text-sm ml-7">
                            <li>Personal identification information (name, ID number, email)</li>
                            <li>GPS location data for attendance verification</li>
                            <li>Attendance records (check-in/check-out times)</li>
                            <li>Device information and IP addresses</li>
                            <li>Workplace assignment details</li>
                        </ul>
                    </div>

                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <h4 class="font-bold text-zinc-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            Purpose of Data Collection
                        </h4>
                        <ul class="list-disc list-inside space-y-1 text-sm ml-7">
                            <li>Accurate attendance tracking and monitoring</li>
                            <li>Workplace compliance and verification</li>
                            <li>Generation of attendance reports</li>
                            <li>System security and audit purposes</li>
                            <li>Administrative and operational requirements</li>
                        </ul>
                    </div>

                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <h4 class="font-bold text-zinc-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Your Rights
                        </h4>
                        <ul class="list-disc list-inside space-y-1 text-sm ml-7">
                            <li>Right to be informed about data collection</li>
                            <li>Right to access your personal data</li>
                            <li>Right to correct inaccurate information</li>
                            <li>Right to object to data processing</li>
                            <li>Right to data portability</li>
                            <li>Right to file a complaint with the National Privacy Commission</li>
                        </ul>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-zinc-700">
                            <strong class="text-green-800">Data Security:</strong> We implement appropriate technical and organizational measures to protect your personal data from unauthorized access, disclosure, alteration, or destruction.
                        </p>
                    </div>

                    <p class="text-sm text-zinc-600 italic">
                        For inquiries or concerns regarding your data privacy, please contact your system administrator or the Data Protection Officer.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-zinc-50 p-6 border-t border-zinc-200">
                <button @click="closeModal()" type="button"
                        class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>


    {{-- Forgot Password Modal (from login.blade.php) --}}
    <div id="forgotPasswordModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 p-4">
        <div class="relative top-4 sm:top-20 mx-auto p-0 border-0 w-full max-w-md shadow-lg rounded-2xl">
            <div class="relative bg-white bg-opacity-90 backdrop-filter backdrop-blur-lg rounded-2xl border border-white border-opacity-30 shadow-xl">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">Forgot Password?</h3>
                        <button onclick="closeForgotPasswordModal()" class="text-gray-400 hover:text-gray-600 text-2xl sm:text-xl">
                            &times;
                        </button>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-3 sm:py-4">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-indigo-100 mb-3 sm:mb-4">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Password Reset Required</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4 px-2">
                            For security reasons, password resets must be handled by your system administrator.
                        </p>
                        <div class="bg-indigo-50 p-3 sm:p-4 rounded-lg border border-indigo-200">
                            <p class="text-xs sm:text-sm text-indigo-800 font-medium mb-2">Contact Information:</p>
                            <p class="text-xs sm:text-sm text-indigo-700">
                                📧 Email: admin@cisdepedcavite.org<br>
                                    <span class="font-bold text-zinc-800">CIS Administrator</span>
                            </p>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 sm:mt-3 px-2">
                            Please include your name and email address when contacting the administrator.
                        </p>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 flex justify-center">
                    <button onclick="closeForgotPasswordModal()" class="px-4 sm:px-6 py-2 bg-indigo-600 text-white text-sm sm:text-base font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        Understood
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Control Script (from login.blade.php) --}}
    <script>
        // Privacy Modal Alpine Store (shared across all components)
        document.addEventListener('alpine:init', () => {
            // Create a shared store for privacy modal state
            Alpine.store('privacy', {
                showModal: false,

                openModal() {
                    this.showModal = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    this.showModal = false;
                    document.body.style.overflow = '';
                }
            });

            // Component for elements that need to access the store
            Alpine.data('privacyModal', () => ({
                get showPrivacyModal() {
                    return Alpine.store('privacy').showModal;
                },
                openModal() {
                    Alpine.store('privacy').openModal();
                },
                closeModal() {
                    Alpine.store('privacy').closeModal();
                }
            }));
        });

        function showForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
        }

        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Close modal when clicking outside
        document.getElementById('forgotPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeForgotPasswordModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeForgotPasswordModal();
            }
        });
    </script>

</body>
</html>