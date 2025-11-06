<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CIS-AM | Login</title> {{-- Changed title from original login.blade --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Using the CDN for live preview --}}
    <script src="https://cdn.tailwindcss.com"></script>
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