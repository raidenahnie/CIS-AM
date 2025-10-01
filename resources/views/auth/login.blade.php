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
<body class="min-h-screen flex items-center justify-center animated-gradient">

    <!-- Login Card -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-slate-500 shadow-2xl p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-indigo-600">CIS-AM</h1>
            <p class="text-gray-500">Sign in to your account</p>
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
                <a href="#" class="text-sm text-indigo-600 hover:underline">Forgot password?</a>
            </div>

            <button type="submit" 
                    class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700">
                Login
            </button>

        <!-- Contact Admin for Access -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Need access? Contact your system administrator.
        </p>
        <p class="mt-2 text-center text-sm text-gray-600">
           Return to <a href="{{ route('landing') }}" class="text-indigo-600 hover:underline">home page</a>
        </p>
    </div>

</body>
</html>
