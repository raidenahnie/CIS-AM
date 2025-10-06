<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CID-AMS | Reset Password</title>
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

    <!-- Reset Password Card -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-slate-500 shadow-2xl p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-indigo-600">CIS-AM</h1>
            <p class="text-gray-500">Reset your password</p>
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Please create a new secure password for your account.
                        </p>
                    </div>
                </div>
            </div>
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
                        <h3 class="text-sm font-medium">Please correct the following errors:</h3>
                        <div class="mt-1 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" readonly
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-gray-50 text-gray-600 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">This field cannot be changed.</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @else border-gray-300 @enderror"
                       minlength="8">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters required.</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300"
                       minlength="8">
                <p class="mt-1 text-xs text-gray-500">Re-enter your new password.</p>
            </div>

            <!-- Password Requirements -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Password Requirements:</h4>
                <ul class="text-xs text-gray-600 space-y-1">
                    <li class="flex items-center">
                        <svg class="h-3 w-3 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        At least 8 characters long
                    </li>
                    <li class="flex items-center">
                        <svg class="h-3 w-3 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Mix of letters, numbers, and symbols recommended
                    </li>
                    <li class="flex items-center">
                        <svg class="h-3 w-3 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Different from your previous password
                    </li>
                </ul>
            </div>

            <button type="submit" 
                    class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                Reset Password
            </button>
        </form>

        <!-- Back to Login -->
        <p class="mt-6 text-center text-sm text-gray-600">
            Remember your password? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Back to login</a>
        </p>
    </div>

    <script>
        // Client-side password validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');
            
            function validatePasswordMatch() {
                if (passwordConfirm.value && password.value !== passwordConfirm.value) {
                    passwordConfirm.setCustomValidity('Passwords do not match');
                } else {
                    passwordConfirm.setCustomValidity('');
                }
            }
            
            password.addEventListener('input', validatePasswordMatch);
            passwordConfirm.addEventListener('input', validatePasswordMatch);
        });
    </script>

</body>
</html>