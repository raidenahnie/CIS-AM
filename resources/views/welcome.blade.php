<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CIS-AM | Attendance Monitoring System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Using the CDN for live preview --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="/img/favicon.png">
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

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased">

    <div class="flex min-h-screen">

        <div class="hidden lg:flex w-full lg:w-2/5 xl:w-1/2 p-12 flex-col justify-center relative overflow-hidden"
            style="background-color: #f7f1e6;" x-data="{ loadDecorations: false }" x-init="if (window.innerWidth >= 1024) loadDecorations = true">

            <div class="absolute -top-40 -left-40 w-96 h-96 bg-white/60 rounded-full z-0"></div>
            <div
                class="absolute -bottom-52 -right-32 w-[500px] h-[500px] bg-white/60 rounded-lg z-0 transform rotate-45">
            </div>

            <template x-if="loadDecorations">
                <div>
                    <img loading="lazy"
                        src="{{ url('https://static.vecteezy.com/system/resources/thumbnails/021/515/849/small/green-indian-almond-leaves-and-branches-on-transparent-background-file-png.png') }}"
                        alt="Leaves decorative banner" class="absolute top-0 left-0 w-80 max-w-md z-10">
                    <img loading="lazy"
                        src="{{ url('https://static.vecteezy.com/system/resources/thumbnails/047/309/343/small/tree-branches-covered-in-vibrant-leaves-cutout-transparent-backgrounds-3d-render-png.png') }}"
                        alt="Leaves decorative banner" class="absolute bottom-0 right-0 rotate-180 w-80 max-w-md z-10">
                </div>
            </template>

            <div class="relative z-10" x-data="{
                activeSlide: 1,
                totalSlides: 7,
                imagesLoaded: false,
                init() {
                    // Only load images on larger screens (lg breakpoint = 1024px)
                    if (window.innerWidth >= 1024) {
                        this.imagesLoaded = true;
                    }
                    // Listen for resize events to load images if screen becomes larger
                    window.addEventListener('resize', () => {
                        if (window.innerWidth >= 1024 && !this.imagesLoaded) {
                            this.imagesLoaded = true;
                        }
                    });
                }
            }">
                <div class="relative z-5 w-full aspect-[16/10] rounded-xl overflow-hidden shadow-2xl">
                    <template x-if="imagesLoaded">
                        <div>
                            <div x-show="activeSlide === 1" class="w-full h-full">
                                <img loading="lazy" src="{{ asset('img/pic-1.jpg') }}" alt="Carousel Image 1"
                                    class="w-full h-full object-cover">
                            </div>
                            <div x-show="activeSlide === 2" class="w-full h-full" style="display: none;">
                                <img loading="lazy" src="{{ asset('img/pic-2.jpg') }}" alt="Carousel Image 2"
                                    class="w-full h-full object-cover">
                            </div>
                            <div x-show="activeSlide === 3" class="w-full h-full" style="display: none;">
                                <img loading="lazy" src="{{ asset('img/pic-3.jpg') }}" alt="Carousel Image 3"
                                    class="w-full h-full object-cover">
                            </div>
                            <div x-show="activeSlide === 4" class="w-full h-full" style="display: none;">
                                <img loading="lazy" src="{{ asset('img/pic-5.jpg') }}" alt="Carousel Image 5"
                                    class="w-full h-full object-cover">
                            </div>
                            <div x-show="activeSlide === 5" class="w-full h-full" style="display: none;">
                                <img loading="lazy" src="{{ asset('img/pic-6.jpg') }}" alt="Carousel Image 6"
                                    class="w-full h-full object-cover">
                            </div>
                            <div x-show="activeSlide === 6" class="w-full h-full" style="display: none;">
                                <img loading="lazy" src="{{ asset('img/pic-7.jpg') }}" alt="Carousel Image 7"
                                    class="w-full h-full object-cover">
                            </div>
                            <div x-show="activeSlide === 7" class="w-full h-full" style="display: none;">
                                <img loading="lazy" src="{{ asset('img/pic-8.jpg') }}" alt="Carousel Image 8"
                                    class="w-full h-full object-cover">
                            </div>
                        </div>
                    </template>

                    <template x-if="imagesLoaded">
                        <div>
                            <button @click="activeSlide = (activeSlide === 1) ? totalSlides : activeSlide - 1"
                                class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/10 hover:bg-white/60 rounded-full p-2 shadow-md z-10 transition focus:outline-none">
                                <svg class="w-5 h-5 text-zinc-800" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button @click="activeSlide = (activeSlide === totalSlides) ? 1 : activeSlide + 1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/10 hover:bg-white/60 rounded-full p-2 shadow-md z-10 transition focus:outline-none">
                                <svg class="w-5 h-5 text-zinc-800" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div x-show="imagesLoaded" x-init="$watch('imagesLoaded', value => { if (value) setInterval(() => { activeSlide = (activeSlide % totalSlides) + 1 }, 5000) })" class="hidden"></div>
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

                <div class="mb-12" x-data="privacyModal" x-id="['privacy-modal']">
                    <!-- Direct link if already acknowledged, modal if not -->
                    <template x-if="hasAgreed">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center w-full max-w-xs px-10 py-4 text-lg bg-red-600 text-white font-semibold rounded-lg shadow-lg hover:bg-red-700 transform hover:scale-105 transition-all duration-300 group">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Access System
                        </a>
                    </template>

                    <template x-if="!hasAgreed">
                        <button @click="openModal()" type="button"
                            class="inline-flex items-center justify-center w-full max-w-xs px-10 py-4 text-lg bg-red-600 text-white font-semibold rounded-lg shadow-lg hover:bg-red-700 transform hover:scale-105 transition-all duration-300 group">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Access System
                        </button>
                    </template>

                    <!-- Privacy Notice Modal -->
                    <div x-show="showPrivacyModal" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" @click="if(hasAgreed) closeModal()"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 sm:p-6 overflow-y-auto">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full my-auto max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col"
                            @click.stop x-transition:enter="transition ease-out duration-300 delay-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95">

                            <!-- Header -->
                            <div
                                class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 sm:p-6 flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2 sm:space-x-3">
                                        <svg class="w-6 h-6 sm:w-8 sm:h-8 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <h2 class="text-lg sm:text-2xl font-bold">Data Privacy Notice</h2>
                                    </div>
                                    <!-- Only show close button if user has already acknowledged before -->
                                    <button x-show="hasAgreed" @click="closeModal()"
                                        class="rounded-full p-2 transition-colors hover:bg-white/20 cursor-pointer flex-shrink-0">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-4 sm:p-6 overflow-y-auto flex-1" @scroll="checkScrollPosition($el)">

                                <div class="space-y-4 text-zinc-700">
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-yellow-600 mt-0.5 mr-3 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="text-sm font-semibold text-yellow-800">
                                                Please read this notice carefully before proceeding.
                                            </p>
                                        </div>
                                    </div>

                                    <h3 class="text-base sm:text-lg font-bold text-zinc-900">Welcome to CIS-AM
                                        Attendance Monitoring
                                        System</h3>

                                    <p class="leading-relaxed text-sm sm:text-base">
                                        As part of your employment, you must use this system. By accessing and using it,
                                        you
                                        <strong class="text-zinc-900">acknowledge</strong>
                                        that you have been notified of the collection,
                                        processing, and storage of your work identification data in accordance with the
                                        <a href="https://www.officialgazette.gov.ph/2012/08/15/republic-act-no-10173/"
                                            target="_blank" class="text-green-600 font-semibold hover:underline">
                                            Data Privacy Act of 2012 (Republic Act No. 10173)
                                        </a>.
                                    </p>

                                    <div class="bg-zinc-50 rounded-lg p-3 sm:p-4 space-y-2 sm:space-y-3">
                                        <h4 class="text-sm sm:text-base font-bold text-zinc-900 flex items-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-green-600 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Data We Collect
                                        </h4>
                                        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm ml-6 sm:ml-7">
                                            <li>Work identification information (name & email)</li>
                                            <!-- THIS IS THE REVISED GPS LINE -->
                                            <li>GPS location data, used <strong class="text-zinc-800">only at the
                                                    moment of check-in and check-out</strong>
                                                to verify your presence at your designated workplace (geofencing).
                                                <strong class="text-zinc-800">The system does not track your location
                                                    in real-time.</strong>
                                            </li>
                                            <li>Attendance records (check-in/check-out times)</li>
                                            <li>Workplace assignment details</li>
                                        </ul>
                                    </div>

                                    <div class="bg-zinc-50 rounded-lg p-3 sm:p-4 space-y-2 sm:space-y-3">
                                        <h4 class="text-sm sm:text-base font-bold text-zinc-900 flex items-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-green-600 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Purpose of Data Collection
                                        </h4>
                                        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm ml-6 sm:ml-7">
                                            <li>Accurate attendance tracking and monitoring</li>
                                            <li>Workplace compliance and verification</li>
                                            <li>Generation of attendance reports</li>
                                            <li>System security and audit purposes</li>
                                            <li>Administrative and operational requirements</li>
                                        </ul>
                                    </div>

                                    <div class="bg-zinc-50 rounded-lg p-3 sm:p-4 space-y-2 sm:space-y-3">
                                        <h4 class="text-sm sm:text-base font-bold text-zinc-900 flex items-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-green-600 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Your Rights
                                        </h4>
                                        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm ml-6 sm:ml-7">
                                            <li>Right to be informed about data collection</li>
                                            <li>Right to access your work identification data</li>
                                            <li>Right to correct inaccurate information</li>
                                            <li>Right to object to data processing</li>
                                            <li>Right to data portability</li>
                                            <li>Right to file a complaint with the National Privacy Commission</li>
                                        </ul>
                                    </div>

                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
                                        <p class="text-xs sm:text-sm text-zinc-700">
                                            <strong class="text-green-800">Data Security:</strong> We implement
                                            appropriate technical and organizational measures to protect your work identification data from unauthorized access, disclosure, alteration, or destruction.
                                        </p>
                                    </div>

                                    <p class="text-xs sm:text-sm text-zinc-600 italic">
                                        For inquiries or concerns regarding your data privacy, please contact your
                                        system administrator or the Data Protection Officer.
                                    </p>
                                </div>

                                <!-- Scroll Indicator -->
                                <div x-show="!scrolledToBottom && !hasAgreed"
                                    class="sticky bottom-0 left-0 right-0 bg-gradient-to-t from-white via-white to-transparent pt-8 pb-4 text-center">
                                    <p class="text-sm text-zinc-500 flex items-center justify-center animate-bounce">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        Scroll down to continue
                                    </p>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="bg-zinc-50 p-4 sm:p-6 border-t border-zinc-200 flex-shrink-0">
                                <div class="space-y-3 sm:space-y-4">
                                    <!-- Checkbox Acknowledgement -->
                                    <label class="flex items-start space-x-2 sm:space-x-3 cursor-pointer group">
                                        <input type="checkbox" x-model="canProceed" @change="handleAgreementChange()"
                                            :disabled="!scrolledToBottom && !hasAgreed"
                                            class="mt-0.5 sm:mt-1 w-4 h-4 sm:w-5 sm:h-5 text-green-600 border-zinc-300 rounded focus:ring-green-500 focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0">
                                        <span class="text-xs sm:text-sm text-zinc-700 select-none"
                                            :class="(!scrolledToBottom && !hasAgreed) ? 'opacity-50' : ''">
                                            <!-- THIS IS THE REVISED CHECKBOX TEXT -->
                                            I <strong class="text-zinc-800">acknowledge</strong> that I have read and
                                            understood this Data Privacy Notice.
                                        </span>
                                    </label>

                                    <!-- Buttons -->
                                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                        <a :href="canProceed ? '{{ route('login') }}' : 'javascript:void(0)'"
                                            :class="canProceed ? 'bg-green-600 hover:bg-green-700 cursor-pointer' :
                                                'bg-zinc-300 cursor-not-allowed'"
                                            class="flex-1 inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base text-white font-semibold rounded-lg shadow-md transition-all duration-300 transform hover:scale-105"
                                            @click="if(!canProceed) $event.preventDefault()">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <!-- THIS IS THE REVISED BUTTON TEXT -->
                                            Acknowledge and Proceed
                                        </a>
                                        <!-- Only show cancel if user has already acknowledged (can review) -->
                                        <button x-show="hasAgreed" @click="closeModal()" type="button"
                                            class="flex-1 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base bg-white border-2 border-zinc-300 text-zinc-700 font-semibold rounded-lg hover:bg-zinc-50 transition-colors">
                                            Close
                                        </button>
                                    </div>

                                    <p class="text-[10px] sm:text-xs text-center text-zinc-500">
                                        <!-- THIS IS THE REVISED FOOTER TEXT -->
                                        By clicking "Acknowledge and Proceed", you confirm that you have read and
                                        understood this notice.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optional: Show small privacy link for users who want to review -->
                <div class="mt-12 text-center" x-data="privacyModal">
                    <p class="text-xs text-zinc-500 mb-2">
                        By accessing this system, you agree to the
                        <button @click="openModal()" type="button"
                            class="text-green-600 font-semibold hover:underline focus:outline-none">
                            Data Privacy Notice
                        </button>
                    </p>
                </div>
            </div>
        </div>

    </div>


    <script>
        // Privacy Modal Alpine Store (shared across all components)
        document.addEventListener('alpine:init', () => {
            // Create a shared store for privacy modal state
            Alpine.store('privacy', {
                showModal: false,
                hasAgreed: localStorage.getItem('privacyAgreed') === 'true',
                scrolledToBottom: false,
                canProceed: localStorage.getItem('privacyAgreed') === 'true',

                init() {
                    // Auto-show modal on first visit
                    if (!this.hasAgreed) {
                        setTimeout(() => {
                            this.showModal = true;
                        }, 500);
                    }
                },

                openModal() {
                    this.showModal = true;
                    this.scrolledToBottom = false;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    if (this.hasAgreed) {
                        this.showModal = false;
                        document.body.style.overflow = '';
                    }
                },

                checkScroll(el) {
                    const isAtBottom = (el.scrollHeight - el.scrollTop - el.clientHeight) < 50;
                    this.scrolledToBottom = isAtBottom;
                },

                handleAgreement() {
                    if (this.canProceed) {
                        localStorage.setItem('privacyAgreed', 'true');
                        this.hasAgreed = true;
                    } else {
                        localStorage.removeItem('privacyAgreed');
                        this.hasAgreed = false;
                    }
                }
            });

            // Initialize the store
            Alpine.store('privacy').init();

            // Component for elements that need to access the store
            Alpine.data('privacyModal', () => ({
                get showPrivacyModal() {
                    return Alpine.store('privacy').showModal;
                },
                get hasAgreed() {
                    return Alpine.store('privacy').hasAgreed;
                },
                get scrolledToBottom() {
                    return Alpine.store('privacy').scrolledToBottom;
                },
                get canProceed() {
                    return Alpine.store('privacy').canProceed;
                },
                set canProceed(value) {
                    Alpine.store('privacy').canProceed = value;
                },
                openModal() {
                    Alpine.store('privacy').openModal();
                },
                closeModal() {
                    Alpine.store('privacy').closeModal();
                },
                checkScrollPosition(el) {
                    Alpine.store('privacy').checkScroll(el);
                },
                handleAgreementChange() {
                    Alpine.store('privacy').handleAgreement();
                }
            }));
        });

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
