<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CID-AMS | Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .animated-gradient {
            background: linear-gradient(-45deg, #e879f9, #f9a8d4, #a5b4fc, #7dd3fc, #67e8f9);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center animated-gradient p-4">

    <!-- Login Card -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-slate-500 shadow-2xl p-6 sm:p-8 mx-4">
        <div class="text-center mb-4 sm:mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-indigo-600">CIS-AM</h1>
            <p class="text-sm sm:text-base text-gray-500">Sign in to your account</p>
        </div>

        <!-- Error Messages -->
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

        <!-- Success Messages -->
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

        <!-- Form -->
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
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @else border-gray-300 @enderror">
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
                    class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                Login
            </button>

        <!-- Contact Admin for Access -->
        <p class="mt-4 sm:mt-6 text-center text-xs sm:text-sm text-gray-600 px-2">
            Need access? Contact your system administrator.
        </p>
        <p class="mt-2 text-center text-xs sm:text-sm text-gray-600 px-2">
           Return to <a href="{{ route('landing') }}" class="text-indigo-600 hover:underline">home page</a>
        </p>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 p-4">
        <div class="relative top-4 sm:top-20 mx-auto p-0 border-0 w-full max-w-md shadow-lg rounded-2xl">
            <!-- Glassmorphism container -->
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
                                📧 Email: admin@company.com<br>
                                📞 Phone: +1 (555) 123-4567<br>
                                💬 Or contact your CIS administrator
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

    <script>
        function showForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
        }

        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
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
