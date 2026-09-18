<!DOCTYPE html>
<html lang="en" class="h-full bg-[#090d16]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SoFlo Shine CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center font-sans text-slate-300 selection:bg-blue-500 selection:text-white relative overflow-hidden">

    <!-- Background Ambient Glow Effects -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md px-6 z-10 animate-fade-in">
        
        <!-- Brand Header with Pulse Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600/20 to-blue-500/5 border border-blue-500/30 text-blue-400 text-2xl font-bold mb-3 shadow-xl shadow-blue-500/10 backdrop-blur-md">
                ✦
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center justify-center gap-2">
                SoFlo Shine <span class="text-blue-500 font-medium">CRM</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Auto Detailing & Fleet Management Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#111726]/90 backdrop-blur-xl border border-[#1c2536] rounded-2xl p-8 shadow-2xl shadow-black/60 relative">
            
            <!-- Subtle top border accent light -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-1/3 h-[2px] bg-gradient-to-r from-transparent via-blue-500 to-transparent"></div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-5 text-xs font-medium text-emerald-400 bg-emerald-950/50 border border-emerald-800/60 p-3 rounded-xl flex items-center gap-2">
                    <span>✓</span> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="w-full bg-[#090d16]/80 border border-[#1c2536] rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all shadow-inner"
                        placeholder="admin@sofloshine.com">
                    @error('email')
                        <p class="text-xs text-rose-400 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full bg-[#090d16]/80 border border-[#1c2536] rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all shadow-inner"
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-xs text-rose-400 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label for="remember_me" class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#090d16] border-[#1c2536] text-blue-600 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer">
                        <span class="text-slate-400 group-hover:text-slate-300 transition-colors">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-blue-400 hover:text-blue-300 transition-colors font-medium">Forgot password?</a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-semibold rounded-xl shadow-lg shadow-blue-600/25 transition-all duration-200 text-sm flex items-center justify-center gap-2 group active:scale-[0.99]">
                        <span>Sign In to Dashboard</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 space-y-1">
            <p class="text-[11px] text-slate-500 font-medium">
                SoFlo Shine Auto Detailing &bull; Secure Enterprise Portal
            </p>
            <p class="text-[10px] text-slate-600">
                Protected by Role-Based Access Control (RBAC)
            </p>
        </div>
    </div>

</body>
</html>