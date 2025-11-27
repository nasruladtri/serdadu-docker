@extends('layouts.admin', ['title' => 'Pengaturan Akun'])

@section('content')
    <div class="max-w-5xl mx-auto animate-fade-in-up">
        
        <form method="post" action="{{ route('admin.account.update') }}" enctype="multipart/form-data" x-data="{ isDirty: false, avatarPreview: null, bannerPreview: null }" @input="isDirty = true" @change="isDirty = true">
            @csrf
            @method('patch')

            <!-- Profile Header -->
            <div class="relative overflow-hidden bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-200 dark:border-slate-700 mb-8 group">
                <!-- Banner Image -->
                <div class="absolute top-0 left-0 w-full h-48 bg-slate-200 dark:bg-slate-700 transition-all duration-300">
                    <template x-if="bannerPreview">
                        <img :src="bannerPreview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!bannerPreview">
                        @if($user->banner)
                            <img src="{{ asset($user->banner) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-r from-emerald-500 to-teal-600">
                                <div class="absolute inset-0 bg-black/10"></div>
                                <div class="absolute bottom-0 left-0 w-full h-px bg-white/20"></div>
                            </div>
                        @endif
                    </template>
                    
                    <!-- Banner Upload Overlay -->
                    <label for="banner-upload" class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center cursor-pointer transition-opacity duration-300">
                        <div class="bg-white/20 backdrop-blur-md border border-white/30 rounded-full px-4 py-2 text-white font-medium flex items-center gap-2 hover:bg-white/30 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Ubah Banner</span>
                        </div>
                    </label>
                    <input type="file" id="banner-upload" name="banner" class="hidden" accept="image/*" @change="const file = $event.target.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => bannerPreview = e.target.result; reader.readAsDataURL(file); }">
                </div>
                
                <div class="relative px-6 pb-6 md:px-8 md:pb-8 pt-24 md:pt-32 pointer-events-none">
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-4 md:gap-6 pointer-events-auto text-center md:text-left">
                        <!-- Avatar -->
                        <div class="relative group/avatar flex-shrink-0">
                            <div class="w-28 h-28 md:w-32 md:h-32 rounded-2xl bg-white dark:bg-slate-800 p-1 shadow-xl ring-4 ring-white/50 dark:ring-slate-700/50 overflow-hidden">
                                <template x-if="avatarPreview">
                                    <img :src="avatarPreview" class="w-full h-full object-cover rounded-xl">
                                </template>
                                <template x-if="!avatarPreview">
                                    @if($user->avatar)
                                        <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover rounded-xl">
                                    @else
                                        <div class="w-full h-full rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-4xl font-bold text-emerald-600 dark:text-emerald-400 uppercase">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                    @endif
                                </template>
                            </div>

                            <!-- Avatar Upload Overlay -->
                            <label for="avatar-upload" class="absolute inset-0 flex items-center justify-center cursor-pointer opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-300">
                                <div class="absolute inset-0 bg-black/40 rounded-2xl"></div>
                                <div class="relative bg-white/20 backdrop-blur-md border border-white/30 rounded-full p-2 text-white hover:bg-white/30 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                            </label>
                            <input type="file" id="avatar-upload" name="avatar" class="hidden" accept="image/*" @change="const file = $event.target.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => avatarPreview = e.target.result; reader.readAsDataURL(file); }">
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-1 mb-0 md:mb-2 w-full">
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $user->name }}</h1>
                            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm md:text-base">{{ $user->email }}</p>
                        </div>

                        <!-- Action Button -->
                        <div class="mb-0 md:mb-2 w-full md:w-auto">
                            <button type="submit" 
                                x-show="isDirty"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-cloak
                                class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all shadow-lg shadow-emerald-500/30">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('status') === 'profile-updated')
                <div class="mb-8 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex items-start gap-3 animate-fade-in">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Berhasil!</h4>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">Profil akun Anda telah berhasil diperbarui.</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Personal Information -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-lg border border-slate-200 dark:border-slate-700 h-full">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Informasi Pribadi</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required 
                                    class="pl-10 block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 transition-colors py-2.5">
                            </div>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Alamat Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required 
                                    class="pl-10 block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 transition-colors py-2.5">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-lg border border-slate-200 dark:border-slate-700 h-full">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Keamanan Akun</h2>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-amber-50 dark:bg-amber-900/10 rounded-xl p-4 border border-amber-100 dark:border-amber-900/20 mb-6">
                            <p class="text-sm text-amber-800 dark:text-amber-300">
                                Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password saat ini.
                            </p>
                        </div>

                        <div>
                            <label for="current_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password Saat Ini</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input type="password" name="current_password" id="current_password" 
                                    class="pl-10 block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 transition-colors py-2.5">
                            </div>
                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </div>
                                <input type="password" name="password" id="password" 
                                    class="pl-10 block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 transition-colors py-2.5">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                    class="pl-10 block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 transition-colors py-2.5">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection
