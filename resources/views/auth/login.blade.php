<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Serdadu</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-pattern {
            background-color: #009B4D;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        /* Fix autofill text visibility */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #111827 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left Side - Branding -->
        <div class="md:w-1/2 bg-pattern flex flex-col justify-between p-8 md:p-12 text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ asset('img/kabupaten-madiun.png') }}" alt="Logo Kabupaten Madiun" class="w-10 h-10 object-contain bg-white/10 rounded-lg p-1">
                    <span class="text-sm font-medium tracking-wider uppercase opacity-90">Dinas Kependudukan & Pencatatan Sipil Kabupaten Madiun</span>
                </div>
            </div>
            
            <div class="relative z-10 my-auto">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Serdadu</h1>
                <p class="text-xl md:text-2xl font-light opacity-90 mb-8">Sistem Rekap Data Terpadu</p>
                <p class="text-sm opacity-75 max-w-md leading-relaxed">
                    Platform terintegrasi untuk pengelolaan dan visualisasi data kependudukan Kabupaten Madiun yang akurat dan real-time.
                </p>
            </div>
            
            <div class="relative z-10 text-xs opacity-60">
                &copy; {{ date('Y') }} Dinas Kependudukan dan Pencatatan Sipil Kabupaten Madiun.
            </div>
            
            <!-- Decorative Circles -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-white opacity-5 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-60 h-60 rounded-full bg-black opacity-10 blur-3xl"></div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="md:w-1/2 bg-white flex items-center justify-center p-8 md:p-12">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center md:text-left">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Selamat Datang</h2>
                    <p class="mt-2 text-sm text-gray-600">Silakan masuk untuk mengakses dashboard admin.</p>
                </div>
                
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />
                
                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                    @csrf
                    
                    <div class="space-y-5">
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required 
                                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D] sm:text-sm transition duration-150 ease-in-out" 
                                    placeholder="nama@email.com" value="{{ old('email') }}" autofocus>
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" autocomplete="current-password" required 
                                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#009B4D] focus:border-[#009B4D] sm:text-sm transition duration-150 ease-in-out" 
                                    placeholder="••••••••">
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-[#009B4D] focus:ring-[#009B4D] border-gray-300 rounded transition duration-150 ease-in-out">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-600">
                                Ingat saya
                            </label>
                        </div>
                        
                        @if (Route::has('password.request'))
                            <div class="text-sm">
                                <a href="{{ route('password.request') }}" class="font-medium text-[#009B4D] hover:text-[#007a3d] transition duration-150 ease-in-out">
                                    Lupa password?
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-[#009B4D] hover:bg-[#007a3d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#009B4D] transition duration-150 ease-in-out shadow-sm hover:shadow-md">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-[#007a3d] group-hover:text-[#006331] transition ease-in-out duration-150" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Masuk
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 text-center">
                    <a href="{{ route('public.landing') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 transition duration-150 ease-in-out flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
