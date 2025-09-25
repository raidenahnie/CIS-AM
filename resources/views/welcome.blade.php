<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIS-AM | Attendance Monitoring System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-800">

    <!-- Navbar -->
    <header class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-indigo-600">CIS-AM</h1>
            <nav class="space-x-6 hidden md:flex">
                <a href="#features" class="hover:text-indigo-600">Features</a>
                <a href="#about" class="hover:text-indigo-600">About</a>
                <a href="#contact" class="hover:text-indigo-600">Contact</a>
            </nav>
            <div>
                <a href="{{ route('login') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
                    Login
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 bg-[url('/img/cid-ams-bg.jpg')] bg-cover bg-center text-white">
        <div class="absolute inset-0 backdrop-blur-sm"></div>
        <div class="relative max-w-5xl mx-auto text-center px-6">
            <h2 class="text-4xl md:text-6xl font-extrabold mb-6">CIS - Attendance Monitoring</h2>
            <p class="text-lg md:text-xl mb-8">
                Track attendance seamlessly with GPS-powered validation.<br>
                Secure, accurate, and built for modern organizations.
            </p>
            <a href="{{ route('register') }}"
                class="px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg shadow hover:bg-gray-200">
                Get Started
            </a>
        </div>
    </section>



    <!-- Features -->
    <section id="features" class="py-20 bg-gray-100">
        <div class="max-w-6xl mx-auto px-6">
            <h3 class="text-3xl font-bold text-center mb-12">Key Features</h3>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                    <h4 class="font-semibold text-xl mb-3">GPS Tracking</h4>
                    <p class="text-gray-600">Ensure employees check-in within the designated geofence location.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                    <h4 class="font-semibold text-xl mb-3">Realtime Dashboard</h4>
                    <p class="text-gray-600">Monitor attendance records live on an interactive map and dashboard.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                    <h4 class="font-semibold text-xl mb-3">Reports & Analytics</h4>
                    <p class="text-gray-600">Export daily, weekly, or monthly attendance summaries with ease.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="py-20">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h3 class="text-3xl font-bold mb-6">About CIS-AM</h3>
            <p class="text-gray-700 text-lg">
                CIS-AM (Curriculum Implementation System - Attendance Monitoring) is designed
                to simplify and secure attendance tracking using cutting-edge GPS technology.
                Whether you’re an educational institution or a workplace, CIS-AM adapts to your needs.
            </p>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-20 bg-gray-100">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h3 class="text-3xl font-bold mb-6">Get in Touch</h3>
            <p class="mb-4 text-gray-600">Have questions or want a demo? Contact us today.</p>
            <a href="mailto:info@cid-ams.test"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
                Email Us
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-6 border-t text-center text-gray-600">
        © {{ date('Y') }} CID DepEd C. All Rights Reserved.
    </footer>

</body>

</html>
