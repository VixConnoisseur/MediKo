<!-- app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Mediko' ?></title>
    <?php if (getenv('APP_ENV') === 'local'): ?>
        <script type="module" src="http://localhost:3000/@vite/client"></script>
        <script type="module" src="http://localhost:3000/assets/js/app.js"></script>
    <?php else: ?>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold">Mediko</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="/?page=home" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Home</a>
                    <a href="/?page=patients" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Patients</a>
                    <a href="/?page=appointments" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Appointments</a>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button type="button" class="text-white hover:text-gray-200 focus:outline-none" id="mobile-menu-button">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/?page=home" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Home</a>
                <a href="/?page=patients" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Patients</a>
                <a href="/?page=appointments" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-blue-700">Appointments</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">