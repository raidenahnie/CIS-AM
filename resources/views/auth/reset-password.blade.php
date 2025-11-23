<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CIS-AM | Reset Password</title> 
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Scripts and Links from login.blade.php for consistent styling --}}
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

        {{-- This is the Left Column (Visuals) from login.blade.php --}}
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

        {{-- This is the Right Column (Content) --}}
        <div class="w-full lg:w-3/5 xl:w-1/2 bg-white flex items-center justify-center p-8 sm:p-12 relative overflow-hidden">

            <div class="absolute -top-24 -left-24 w-64 h-64 bg-gray-100 rounded-full z-0"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-white rounded-2xl z-0 transform rotate-45"></div>

            {{-- Content from reset-password.blade.php is placed inside this wrapper --}}
            <div class="relative z-10 max-w-md w-full">

                {{-- Header from login.blade.php for consistency --}}
                <div class="flex justify-center mb-4 sm:mb-6">
                    <img src="{{ asset('img/CIDLogo.png') }}" alt=" CID DepEd Cavite Logo"
                        class="w-24 h-auto object-contain 
                               transition-transform duration-300 ease-in-out 
                               hover:scale-110 active:scale-95">
                </div>

                {{-- Header from reset-password.blade.php --}}
                <div class="text-center mb-4 sm:mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Reset Your Password</h1>
                    <p class="text-sm sm:text-base text-gray-500">Create a new, secure password for your account.</p>
                    <div class="mt-3 sm:mt-4 p-3 sm:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs sm:text-sm text-blue-700 text-left">
                                    Please create a new secure password for your account.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Error Messages from reset-password.blade.php --}}
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

                {{-- Form from reset-password.blade.php --}}
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
                        <div class="mt-1">
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                       class="block w-full px-4 py-2 pr-10 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @else border-gray-300 @enderror"
                                       minlength="8">
                                <button type="button" onclick="togglePasswordVisibility('password', 'togglePassword')" tabindex="-1"
                                        class="absolute top-1/2 -translate-y-1/2 right-3 flex items-center text-gray-600 cursor-pointer hover:text-gray-800">
                                    <i class="fas fa-eye" id="togglePassword"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters required.</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <div class="mt-1">
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                       class="block w-full px-4 py-2 pr-10 border rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300"
                                       minlength="8">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirm')" tabindex="-1"
                                        class="absolute top-1/2 -translate-y-1/2 right-3 flex items-center text-gray-600 cursor-pointer hover:text-gray-800">
                                    <i class="fas fa-eye" id="togglePasswordConfirm"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Re-enter your new password.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                        <h4 class="text-xs sm:text-sm font-medium text-gray-700 mb-2">Password Requirements:</h4>
                        <ul class="text-xs text-gray-600 space-y-1">
                            <li class="flex items-center">
                                <svg class="h-3 w-3 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>At least 8 characters long</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-3 w-3 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>Mix of letters, numbers, and symbols recommended</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-3 w-3 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>Different from your previous password</span>
                            </li>
                        </ul>
                    </div>

                    <button type="submit" 
                            class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-700 transition-all duration-300 transform hover:scale-105">
                        Reset Password
                    </button>
                </form>

                <p class="mt-6 text-center text-xs sm:text-sm text-gray-600 px-2">
                    Remember your password? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Back to login</a>
                </p>

            </div>
        </div>

    </div>

    {{-- Validation Utility - Load synchronously --}}
    <script src="{{ asset('js/validation-utils.js') }}"></script>

    {{-- Combined scripts from both files --}}
    <script>
        // Password visibility toggle (from login.blade.php, modified for two fields)
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
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

        // ====== PASSWORD RESET FORM VALIDATION ======
        document.addEventListener('DOMContentLoaded', function() {
            const resetForm = document.querySelector('form[action="{{ route('password.update') }}"]');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const submitButton = resetForm.querySelector('button[type="submit"]');

            // Password strength indicator
            function updatePasswordStrength() {
                const password = passwordInput.value;
                if (password.length === 0) return;

                const result = ValidationUtils.validatePassword(password);
                const strength = result.strength;
                
                // Visual feedback for password strength
                let strengthText = '';
                let strengthColor = '';
                
                if (strength <= 2) {
                    strengthText = 'Weak password';
                    strengthColor = 'text-red-600';
                } else if (strength === 3) {
                    strengthText = 'Good password';
                    strengthColor = 'text-yellow-600';
                } else {
                    strengthText = 'Strong password';
                    strengthColor = 'text-green-600';
                }

                // Show/update strength indicator (add to outer wrapper, not the relative container)
                const outerWrapper = passwordInput.closest('div').parentNode; // Get the mt-1 wrapper
                let strengthIndicator = outerWrapper.querySelector('.password-strength');
                if (!strengthIndicator) {
                    strengthIndicator = document.createElement('p');
                    strengthIndicator.className = 'password-strength text-xs mt-1';
                    outerWrapper.appendChild(strengthIndicator);
                }
                strengthIndicator.className = `password-strength text-xs mt-1 ${strengthColor}`;
                strengthIndicator.textContent = strengthText;
            }

            // Real-time password validation
            passwordInput.addEventListener('input', function() {
                ValidationUtils.clearError(this);
                updatePasswordStrength();
                
                // Check confirmation match if it has value
                if (passwordConfirmInput.value) {
                    validatePasswordMatch();
                }
            });

            function validatePasswordMatch() {
                ValidationUtils.clearError(passwordConfirmInput);
                
                if (passwordConfirmInput.value && passwordInput.value !== passwordConfirmInput.value) {
                    ValidationUtils.showError(passwordConfirmInput, 'Passwords do not match');
                } else if (passwordConfirmInput.value) {
                    ValidationUtils.showSuccess(passwordConfirmInput);
                }
            }

            passwordConfirmInput.addEventListener('input', validatePasswordMatch);

            // Form submission with comprehensive validation
            resetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Clear all errors
                ValidationUtils.clearError(passwordInput);
                ValidationUtils.clearError(passwordConfirmInput);

                let hasErrors = false;

                // Validate password
                const passwordResult = ValidationUtils.validatePassword(
                    passwordInput.value, 
                    passwordConfirmInput.value
                );

                if (!passwordResult.valid) {
                    ValidationUtils.showError(passwordInput, passwordResult.errors[0]);
                    hasErrors = true;
                }

                // Additional confirmation check
                if (!passwordConfirmInput.value || passwordConfirmInput.value.trim() === '') {
                    ValidationUtils.showError(passwordConfirmInput, 'Please confirm your password');
                    hasErrors = true;
                } else if (passwordInput.value !== passwordConfirmInput.value) {
                    ValidationUtils.showError(passwordConfirmInput, 'Passwords do not match');
                    hasErrors = true;
                }

                if (!hasErrors) {
                    // Disable submit button
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Resetting Password...';
                    
                    // Submit form
                    this.submit();
                } else {
                    // Focus first error
                    const firstError = resetForm.querySelector('.border-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        });
    </script>

</body>
</html>