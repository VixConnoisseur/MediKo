<?php
// Include configuration first
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediKo - Your Personal Health Companion</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        'primary-dark': '#2563EB',
                        success: '#10B981',
                        accent: '#8B5CF6',
                        danger: '#EF4444',
                        warning: '#F59E0B',
                        navy: '#1E40AF',
                        'text-dark': '#1F2937',
                        'text-medium': '#6B7280',
                        'text-light': '#9CA3AF',
                        border: '#E5E7EB',
                        'bg-light': '#F9FAFB',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-white text-gray-800 font-sans">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="#" class="flex items-center">
                    <i class="fas fa-pills text-primary text-2xl mr-2"></i>
                    <span class="text-xl font-bold text-primary">MediKo</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex space-x-8">
                <a href="#features" class="text-gray-600 hover:text-primary transition-colors">Features</a>
                <a href="#how-it-works" class="text-gray-600 hover:text-primary transition-colors">How It Works</a>
                <a href="#testimonials" class="text-gray-600 hover:text-primary transition-colors">Testimonials</a>
                <a href="#pricing" class="text-gray-600 hover:text-primary transition-colors">Pricing</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="hidden md:flex items-center space-x-4">
                <button onclick="openModal('login-modal')" class="px-4 py-2 text-primary hover:bg-blue-50 rounded-lg transition-colors">
                    Log In
                </button>
                <button onclick="openModal('signup-modal')" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg transition-colors shadow-md hover:shadow-lg">
                    Get Started
                </button>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-primary">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
            <div class="container mx-auto px-4 py-2 flex flex-col space-y-2">
                <a href="#features" class="py-2 px-4 text-gray-600 hover:bg-gray-50 rounded-md">Features</a>
                <a href="#how-it-works" class="py-2 px-4 text-gray-600 hover:bg-gray-50 rounded-md">How It Works</a>
                <a href="#testimonials" class="py-2 px-4 text-gray-600 hover:bg-gray-50 rounded-md">Testimonials</a>
                <a href="#pricing" class="py-2 px-4 text-gray-600 hover:bg-gray-50 rounded-md">Pricing</a>
                <div class="border-t border-gray-100 my-2"></div>
                <button onclick="openModal('login-modal')" class="w-full text-left py-2 px-4 text-primary hover:bg-blue-50 rounded-md">
                    Log In
                </button>
                <button onclick="openModal('signup-modal')" class="w-full text-left py-2 px-4 bg-primary hover:bg-primary-dark text-white rounded-md">
                    Get Started
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative py-16 md:py-24 overflow-hidden bg-cover bg-center" style="background-image: url('assets/images/hero_bg.jpg')">
        <!-- Dark overlay for better text readability -->
        <div class="absolute inset-0 bg-black/30 z-0"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-10 md:mb-0" data-aos="fade-right" data-aos-delay="100">
                    <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-6">
                        Never Miss a Dose Again with MediKo
                    </h1>
                    <p class="text-lg text-gray-100 mb-8">
                        Your personal health companion that helps you manage medications, track your health, and stay on top of your wellness journey.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <button onclick="openModal('signup-modal')" class="bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-lg font-medium text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 hover:scale-105">
                            Get Started - It's Free
                        </button>
                        <a href="#how-it-works" class="bg-white hover:bg-gray-50 text-primary border-2 border-primary px-8 py-3 rounded-lg font-medium text-lg text-center hover:scale-105 transition-transform duration-300">
                            How It Works
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2 flex justify-center" data-aos="fade-left" data-aos-delay="200">
                    <div class="relative">
                        <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100 transform transition-all duration-500 hover:scale-105">
                            <div class="text-center">
                                <i class="fas fa-pills text-6xl text-primary mb-4 animate-pulse"></i>
                                <p class="text-gray-600 font-medium">Your Personal Health Companion</p>
                            </div>
                        </div>
                        <!-- Animated floating elements -->
                        <div class="absolute -top-4 -left-4 w-8 h-8 bg-yellow-400 rounded-full opacity-70 animate-float" style="animation-delay: 0.5s;"></div>
                        <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-blue-400 rounded-full opacity-70 animate-float" style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>
            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2" data-aos="fade-up" data-aos-delay="300">
                <a href="#features" class="flex flex-col items-center text-white hover:text-primary transition-colors">
                    <span class="text-sm mb-2">Scroll Down</span>
                    <i class="fas fa-chevron-down animate-bounce"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">System Features</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Everything you need to manage your health in one place</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4 transform transition-transform duration-300 group-hover:scale-110">
                        <i class="fas fa-bell text-primary text-xl animate-pulse"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Smart Reminders</h3>
                    <p class="text-gray-600">Never miss a dose with customizable medication reminders via app notifications, email, or SMS.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="150">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4 transform transition-transform duration-300 group-hover:scale-110">
                        <i class="fas fa-pills text-green-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Medication Tracker</h3>
                    <p class="text-gray-600">Keep track of all your medications, dosages, and schedules in one convenient place.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-4 transform transition-transform duration-300 group-hover:scale-110">
                        <i class="fas fa-chart-line text-purple-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Health Insights</h3>
                    <p class="text-gray-600">Get valuable insights and trends about your health and medication adherence.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4 transform transition-transform duration-300 group-hover:scale-110">
                        <i class="fas fa-user-md text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Doctor Connect</h3>
                    <p class="text-gray-600">Share your medication history with healthcare providers for better care coordination.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="150">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-4 transform transition-transform duration-300 group-hover:scale-110">
                        <i class="fas fa-bell-slash text-yellow-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Missed Dose Alerts</h3>
                    <p class="text-gray-600">Get notified if you miss a dose and receive guidance on what to do next.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mb-4 transform transition-transform duration-300 group-hover:scale-110">
                        <i class="fas fa-sync-alt text-indigo-500 text-xl animate-spin-slow"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Auto-Refill Reminders</h3>
                    <p class="text-gray-600">Never run out of medication with timely refill reminders sent to your preferred pharmacy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Get started with MediKo in just a few simple steps</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white text-2xl font-bold rounded-full flex items-center justify-center mx-auto mb-4">1</div>
                    <h3 class="text-xl font-semibold mb-2">Create Your Profile</h3>
                    <p class="text-gray-600">Sign up and enter your basic health information to get personalized recommendations.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white text-2xl font-bold rounded-full flex items-center justify-center mx-auto mb-4">2</div>
                    <h3 class="text-xl font-semibold mb-2">Add Your Medications</h3>
                    <p class="text-gray-600">Enter your medications, dosages, and schedules or scan the barcode for quick addition.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary text-white text-2xl font-bold rounded-full flex items-center justify-center mx-auto mb-4">3</div>
                    <h3 class="text-xl font-semibold mb-2">Get Reminders & Track</h3>
                    <p class="text-gray-600">Receive timely reminders and track your medication intake with our easy-to-use interface.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Users Say</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Join thousands of users who have improved their health with MediKo</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-500 font-bold text-xl mr-4">JD</div>
                        <div>
                            <h4 class="font-semibold">John D.</h4>
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"MediKo has been a game-changer for managing my multiple medications. The reminders are a lifesaver!"</p>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-500 font-bold text-xl mr-4">MS</div>
                        <div>
                            <h4 class="font-semibold">Maria S.</h4>
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"I love how easy it is to track my family's medications all in one place. The interface is so user-friendly!"</p>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-500 font-bold text-xl mr-4">RK</div>
                        <div>
                            <h4 class="font-semibold">Robert K.</h4>
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">"The health insights have helped me understand my medication patterns better. Highly recommended!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-primary to-indigo-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to take control of your health?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Join thousands of users who trust MediKo to manage their medications and improve their health.</p>
            <button onclick="openModal('signup-modal')" class="bg-white text-primary hover:bg-gray-100 px-8 py-3 rounded-lg font-medium text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                Get Started for Free
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-pills text-primary text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-white">MediKo</span>
                    </div>
                    <p class="mb-4">Your personal health companion for better medication management and wellness.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a></li>
                        <li><a href="#testimonials" class="hover:text-white transition-colors">Testimonials</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQs</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Newsletter</h3>
                    <p class="mb-4">Subscribe to our newsletter for health tips and updates.</p>
                    <form class="flex">
                        <input type="email" placeholder="Your email" class="px-4 py-2 rounded-l-lg w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary">
                        <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-4 rounded-r-lg">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm">
                <p>&copy; <?php echo date('Y'); ?> MediKo. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="login-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-8 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
                <button onclick="closeModal('login-modal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <?php if (isset($loginError)): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>
            
            <form id="login-form" action="login_handler.php" method="POST" onsubmit="return handleLogin(event)">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="_token" value="<?php echo (new Security(Database::getInstance()))->generateCsrfToken(); ?>">
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="login-email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="your@email.com" required>
                </div>
                
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="#" class="text-sm text-primary hover:underline">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <input type="password" id="login-password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pr-10" placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword('login-password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="toggle-login-password" class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center mb-6">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-2 px-4 rounded-lg font-medium transition-colors">
                    Log In
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="#" onclick="closeModal('login-modal'); openModal('signup-modal');" class="text-primary font-medium hover:underline">
                        Sign up
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signup-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-8 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Create an Account</h2>
                <button onclick="closeModal('signup-modal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="signup-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="first-name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" id="first-name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="last-name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="last-name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pr-10" required>
                        <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="toggle-password" class="far fa-eye"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                </div>
                
                <div class="mb-6">
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirm-password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pr-10" required>
                        <button type="button" onclick="togglePassword('confirm-password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="toggle-confirm-password" class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" required>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-medium text-gray-700">I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a> and <a href="#" class="text-primary hover:underline">Privacy Policy</a></label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-2 px-4 rounded-lg font-medium transition-colors">
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="#" onclick="closeModal('signup-modal'); openModal('login-modal');" class="text-primary font-medium hover:underline">
                        Log in
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Handle login form submission
        async function handleLogin(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            
            try {
                // Disable the submit button to prevent multiple submissions
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                }
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to dashboard on successful login
                    window.location.href = data.redirect || '/bsit3a_guasis/mediko/pages/admin/dashboard.php';
                } else {
                    // Show error message
                    alert(data.message || 'Login failed. Please try again.');
                    
                    // If CSRF token is invalid, refresh the page to get a new one
                    if (data.message && data.message.includes('CSRF')) {
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                // Re-enable the submit button
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Log In';
                }
            }
            
            return false;
        }
        
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(`toggle-${inputId}`);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Initialize AOS
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false
            });

            // Remove any duplicate event listeners
            const loginForm = document.getElementById('login-form');
            if (loginForm) {
                // Clone the form and replace it to remove all event listeners
                const newForm = loginForm.cloneNode(true);
                loginForm.parentNode.replaceChild(newForm, loginForm);
                
                // Add the submit event listener to the new form
                newForm.addEventListener('submit', handleLogin);
            }
        });

        // Form submission handlers
        // Login form is now handled by the handleLogin function in the form's onsubmit attribute

        document.getElementById('signup-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add signup logic here
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            alert('Signup functionality will be implemented soon!');
            closeModal('signup-modal');
        });

        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

window.handleLogin = async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    try {
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Logging in...';
        
        const formData = new FormData(form);
        
        // Log form data being sent
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
        
        const response = await fetch(window.location.origin + '/bsit3a_guasis/mediko/login_handler.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        // First, get the response as text
        const responseText = await response.text();
        console.log('Raw response:', responseText); // Log raw response
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('Parsed response:', data);
        } catch (e) {
            console.error('Failed to parse JSON response. Response was:', responseText);
            throw new Error('Invalid server response. Please try again later.');
        }
        
        if (!response.ok) {
            console.error('Server returned error status:', response.status, 'Message:', data.message || 'No error message');
            throw new Error(data.message || `Server returned ${response.status}`);
        }
        
        if (data.success) {
            if (data.redirect) {
                console.log('Login successful, redirecting to:', data.redirect);
                window.location.href = data.redirect;
            } else {
                console.error('Login successful but no redirect URL provided');
                throw new Error('Login successful but no redirect URL provided');
            }
        } else {
            console.error('Login failed:', data.message || 'No error message');
            throw new Error(data.message || 'Login failed. Please try again.');
        }
    } catch (error) {
        console.error('Login error:', {
            name: error.name,
            message: error.message,
            stack: error.stack
        });
        alert('Login failed: ' + (error.message || 'Please check your credentials and try again.'));
    } finally {
        // Reset button state
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    }
    
    return false;
};

// Add this to your existing JavaScript in index.php
window.togglePassword = function(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(`toggle-${inputId}`);
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
};
    </script>
</body>
</html>
