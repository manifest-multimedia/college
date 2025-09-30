<!-- MHTIA Professional Theme Login -->
<x-guest-layout>
    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"none\" fill-rule=\"evenodd\"><g fill=\"%23ffffff\" fill-opacity=\"0.1\"><circle cx=\"30\" cy=\"30\" r=\"3\"/></g></g></svg>'); background-size: 60px 60px;"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 py-12 w-full">
                <!-- Institution Logo -->
                <div class="mb-8">
                    <div class="w-20 h-20 bg-white bg-opacity-10 rounded-xl flex items-center justify-center mb-6">
                        <x-authentication-card-logo />
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-4">
                        {{ config('branding.institution.name', config('app.name')) }}
                    </h1>
                    <p class="text-blue-100 text-lg leading-relaxed">
                        Professional Academic Management System
                    </p>
                </div>
                
                <!-- Features -->
                <div class="space-y-4">
                    <div class="flex items-center text-blue-100">
                        <div class="w-6 h-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        Secure Authentication
                    </div>
                    <div class="flex items-center text-blue-100">
                        <div class="w-6 h-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        Comprehensive Management Tools
                    </div>
                    <div class="flex items-center text-blue-100">
                        <div class="w-6 h-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        Real-time Analytics
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="text-center mb-8">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-6">
                        <x-authentication-card-logo />
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
                    <p class="text-gray-600">Please sign in to your account</p>
                </div>

                <!-- Validation Errors -->
                <x-validation-errors class="mb-4" />

                <!-- Status -->
                @session('status')
                    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 border border-green-300 rounded-lg">
                        {{ $value }}
                    </div>
                @endsession

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus 
                                   autocomplete="username"
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   required 
                                   autocomplete="current-password"
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" 
                               class="text-sm font-medium text-blue-600 hover:text-blue-500 transition duration-150">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        Sign In
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="mt-8 text-center">
                    <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                        @if(config('branding.institution.website_url'))
                            <a href="{{ config('branding.institution.website_url') }}" 
                               target="_blank" 
                               class="hover:text-gray-700 transition-colors">
                                Visit Website
                            </a>
                        @endif
                        @if(config('branding.institution.support_email'))
                            <span class="text-gray-300">|</span>
                            <a href="mailto:{{ config('branding.institution.support_email') }}" 
                               class="hover:text-gray-700 transition-colors">
                                Need Help?
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>