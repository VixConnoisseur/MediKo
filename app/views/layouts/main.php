<?php 
$title = $title ?? 'Mediko - Healthcare System';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite assets -->
    <?php if (getenv('APP_ENV') === 'local'): ?>
        <script type="module" src="http://localhost:3000/@vite/client"></script>
        <script type="module" src="http://localhost:3000/assets/js/app.js"></script>
    <?php else: ?>
        <link rel="stylesheet" href="/build/assets/app.css">
        <script type="module" src="/build/assets/app.js" defer></script>
    <?php endif; ?>
</head>
<body class="font-sans antialiased text-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex-shrink-0">
                        <a href="/" class="flex items-center">
                            <span class="text-2xl font-bold text-blue-600">Mediko</span>
                        </a>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="hidden md:flex space-x-8">
                        <a href="/" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Home</a>
                        <a href="/appointments" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Appointments</a>
                        <a href="/patients" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Patients</a>
                        <a href="/doctors" class="text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">Doctors</a>
                    </nav>
                    
                    <!-- User menu -->
                    <div class="ml-4 flex items-center md:ml-6">
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                id="user-menu" 
                                aria-expanded="false" 
                                aria-haspopup="true"
                            >
                                <span class="sr-only">Open user menu</span>
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                    <?= strtoupper(substr('User', 0, 1)) ?>
                                </div>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
                                role="menu" 
                                aria-orientation="vertical" 
                                aria-labelledby="user-menu"
                                style="display: none;"
                            >
                                <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Your Profile</a>
                                <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Settings</a>
                                <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign out</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Icon when menu is closed -->
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Icon when menu is open -->
                            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu, show/hide based on menu state -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="/" class="bg-blue-50 border-blue-500 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Home</a>
                    <a href="/appointments" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Appointments</a>
                    <a href="/patients" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Patients</a>
                    <a href="/doctors" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Doctors</a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                <?= strtoupper(substr('User', 0, 1)) ?>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">User Name</div>
                            <div class="text-sm font-medium text-gray-500">user@example.com</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="/profile" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Your Profile</a>
                        <a href="/settings" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Settings</a>
                        <a href="/logout" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Sign out</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <?php if (isset($_SESSION['flash_messages'])): ?>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <?php foreach ($_SESSION['flash_messages'] as $type => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?= $type ?> mb-4">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['flash_messages']); ?>
                </div>
            <?php endif; ?>
            
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex justify-center md:justify-start">
                        <div class="flex-shrink-0">
                            <span class="text-xl font-bold text-blue-600">Mediko</span>
                        </div>
                        <p class="ml-4 text-base text-gray-500">
                            &copy; <?= date('Y') ?> Mediko Healthcare. All rights reserved.
                        </p>
                    </div>
                    <div class="mt-8 md:mt-0 md:order-1">
                        <p class="text-center text-base text-gray-500">
                            v1.0.0
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <script>
        // Toggle mobile menu
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const expanded = this.getAttribute('aria-expanded') === 'true' || false;
                    this.setAttribute('aria-expanded', !expanded);
                    mobileMenu.classList.toggle('hidden');
                    
                    // Toggle between menu and close icon
                    const menuIcon = this.querySelector('svg:not(.hidden)');
                    const closeIcon = this.querySelector('svg.hidden');
                    
                    if (menuIcon) menuIcon.classList.add('hidden');
                    if (closeIcon) closeIcon.classList.remove('hidden');
                });
            }
        });
    </script>
</body>
</html>
