<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                @import url('https://fonts.bunny.net/css?family=instrument-sans:400,500,600');
                @import url('https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600&display=swap');

                body {
                    font-family: 'Instrument Sans', sans-serif;
                }
            </style>
        @endif
    </head>
    <body class="text-[#1b1b18] min-h-screen flex lg:flex-row">
        <!-- Background IMG -->
        <div class="hidden h-screen p-6 lg:w-1/2 lg:flex">
            <div class="w-full h-full overflow-hidden rounded-xl ">
                <img src="{{ asset('images/login-image.png') }}" alt="Ilustrasi Login" class="w-full h-full object-cover">
            </div>
        </div>

        <!-- Form login -->
        <div class="w-full lg:w-1/2 bg-white flex items-center justify-center p-6">
            {{-- Form --}}
            <div class="w-full max-w-md">
                {{-- Logo dan Judul --}}
                <div class="flex items-center space-x-2 mb-8">
                    <svg width="52" height="50" viewBox="0 0 41 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M25.529 32.7902L8.708 39.9998L24.5555 22.9637L40.2407 20.1333L25.529 32.7902Z" fill="url(#paint0_linear_4719_3880)"/>
                        <path d="M26.0159 5.23365L8.00494 0L24.5555 22.964L40.2407 20.1335L26.0159 5.23365Z" fill="url(#paint1_linear_4719_3880)"/>
                        <path d="M24.6095 22.9105L8.70799 40L0 19.5995L8.00486 0L24.6095 22.9105Z" fill="url(#paint2_radial_4719_3880)"/>
                        <defs>
                            <linearGradient id="paint0_linear_4719_3880" x1="24.4473" y1="23.2842" x2="27.0897" y2="31.9013" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#31304D"/>
                                <stop offset="0.411667" stop-color="#68697E"/>
                                <stop offset="1" stop-color="#B6BBC4"/>
                            </linearGradient>
                            <linearGradient id="paint1_linear_4719_3880" x1="18.8223" y1="-4.53939" x2="29.0842" y2="22.9966" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#31304D"/>
                                <stop offset="1" stop-color="#B6BBC4"/>
                            </linearGradient>
                            <radialGradient id="paint2_radial_4719_3880" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(8.81616 22.3231) rotate(97.3134) scale(19.5449 12.0199)">
                                <stop stop-color="#B6BBC4"/>
                                <stop offset="1" stop-color="#31304D"/>
                            </radialGradient>
                        </defs>
                    </svg>
                    <h1 class="text-4xl font-extrabold text-[#161A30]">Gudangku</h1>
                </div>
                <div class="mb-6">
                    <h1 class="text-2xl font-medium text-gray-800">Login</h1>
                    <p class="text-sm text-gray-600">Silahkan masukkan email dan password</p>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-3 py-2 outline rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-3 py-2 outline rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember Me</label>
                    </div>
                    <div>
                    <button type="submit"
                    class="w-full bg-[#E3E3E3] text-[#777777] py-2 px-4 rounded-md 
                            hover:bg-[#31304D] hover:text-white 
                            active:bg-[#161A30] active:text-white 
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 
                            transition-colors duration-200">
                    Login
                    </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
