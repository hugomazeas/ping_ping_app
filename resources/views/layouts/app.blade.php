<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ping Pong Tracker')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['Bangers', 'cursive'],
                        'body': ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'bounce-slow': 'bounce 2s infinite',
                        'ping-slow': 'ping 2s cubic-bezier(0, 0, 0.2, 1) infinite',
                        'wiggle': 'wiggle 1s ease-in-out infinite',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; }
        .font-display { font-family: 'Bangers', cursive; letter-spacing: 0.05em; }

        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        .neon-text {
            text-shadow: 0 0 10px currentColor, 0 0 20px currentColor, 0 0 40px currentColor;
        }

        .neon-box {
            box-shadow: 0 0 15px rgba(255, 107, 107, 0.3), inset 0 0 15px rgba(255, 107, 107, 0.1);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-hover:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .funky-border {
            border: 3px solid transparent;
            background: linear-gradient(#1a1a2e, #1a1a2e) padding-box,
                        linear-gradient(135deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3) border-box;
        }

        .rainbow-text {
            background: linear-gradient(90deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3, #ff6b6b);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: rainbow 3s linear infinite;
        }

        @keyframes rainbow {
            to { background-position: 200% center; }
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
            animation: blob 7s infinite;
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
    </style>
</head>
<body class="gradient-bg text-white min-h-screen font-body overflow-x-hidden">
    <!-- Animated background blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="blob w-72 h-72 bg-pink-500 top-0 left-0"></div>
        <div class="blob w-96 h-96 bg-purple-500 top-1/2 right-0" style="animation-delay: -2s;"></div>
        <div class="blob w-64 h-64 bg-cyan-500 bottom-0 left-1/3" style="animation-delay: -4s;"></div>
    </div>

    <div class="relative container mx-auto px-4 py-8 max-w-4xl">
        @if(session('error'))
            <div class="bg-red-500/30 backdrop-blur border-2 border-red-400 text-red-200 px-6 py-4 rounded-2xl mb-6 font-semibold animate-wiggle">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
