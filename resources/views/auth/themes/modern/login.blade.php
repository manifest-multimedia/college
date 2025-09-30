<!-- Modern Gradient Theme Login -->
<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-600 to-blue-600 flex items-center justify-center p-4">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-5 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white opacity-5 rounded-full blur-xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <!-- Glass Card -->
        <div class="relative z-10 w-full max-w-md">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl mb-6 shadow-xl">
                    <x-authentication-card-logo />
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    {{ config('branding.institution.name', config('app.name')) }}
                </h1>
                <p class="text-white text-opacity-80">
                    Modern Academic Management
                </p>
            </div>

            <!-- Glass Card -->
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl shadow-xl border border-white border-opacity-20 p-8">
                <!-- Header -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
                    <p class="text-white text-opacity-80">Sign in to continue</p>
                </div>

                <!-- Validation Errors -->
                <x-validation-errors class="mb-4" />

                <!-- Status -->
                @session('status')
                    <div class="mb-4 p-4 text-sm text-green-100 bg-green-500 bg-opacity-20 border border-green-400 border-opacity-30 rounded-lg backdrop-blur-sm">
                        {{ $value }}
                    </div>
                @endsession

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Email Address
                        </label>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               value="{{ old('email') }}"
                               required 
                               autofocus 
                               autocomplete="username"
                               class="block w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-30 rounded-xl text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-white focus:border-opacity-50 transition duration-200 backdrop-blur-sm"
                               placeholder="Enter your email">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-white text-opacity-90 mb-2">
                            Password
                        </label>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               autocomplete="current-password"
                               class="block w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-30 rounded-xl text-white placeholder-white placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-white focus:border-opacity-50 transition duration-200 backdrop-blur-sm"
                               placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="h-4 w-4 text-white bg-white bg-opacity-10 border-white border-opacity-30 rounded focus:ring-white focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-white text-opacity-80">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" 
                               class="text-sm font-medium text-white text-opacity-80 hover:text-white transition duration-150">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 bg-white bg-opacity-20 hover:bg-opacity-30 border border-white border-opacity-30 rounded-xl text-white font-medium transition duration-200 backdrop-blur-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Sign In
                    </button>
                </form>

                <!-- Decorative Elements -->
                <div class="mt-6 flex items-center justify-center">
                    <div class="flex space-x-2">
                        <div class="w-2 h-2 bg-white bg-opacity-30 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-white bg-opacity-30 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                        <div class="w-2 h-2 bg-white bg-opacity-30 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            @if(config('branding.institution.website_url') || config('branding.institution.support_email'))
                <div class="mt-8 text-center">
                    <div class="flex items-center justify-center space-x-6 text-sm text-white text-opacity-70">
                        @if(config('branding.institution.website_url'))
                            <a href="{{ config('branding.institution.website_url') }}" 
                               target="_blank" 
                               class="hover:text-white transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"></path>
                                </svg>
                                Website
                            </a>
                        @endif
                        @if(config('branding.institution.support_email'))
                            <a href="mailto:{{ config('branding.institution.support_email') }}" 
                               class="hover:text-white transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                                Support
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>