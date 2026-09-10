<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoFlo Shine CRM</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#111827', // Tailwind dark gray (close to image)
                            bg: '#1a1f2e',   // Original image dark bg color
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-brand-bg text-slate-300 antialiased h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-brand-dark flex-shrink-0 border-r border-slate-700 flex flex-col">
        <div class="p-6">
            <h1 class="text-xl font-bold tracking-wider text-blue-400">SoFlo Shine</h1>
            <span class="text-xs bg-blue-600 px-2 py-0.5 rounded font-semibold text-white">CRM</span>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-1">

            <!-- OVERVIEW SECTION -->
            <div class="pt-2 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Overview</div>
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                <span>📊</span> Dashboard
            </a>

            <!-- Pipeline Link Cleaned -->
            <a href="{{ route('pipeline.index') }}"
                class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ request()->routeIs('pipeline.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    <span>Pipeline</span>
                </div>
                <span class="px-2 py-0.5 text-xs font-semibold text-white bg-amber-600 rounded-full">
                    {{ \DB::table('opportunities')->count() }}
                </span>
            </a>

            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>📅</span> Calendar
            </a>

            <!-- OPERATIONS SECTION -->
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Operations</div>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>💬</span> Requests
            </a>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>👥</span> Customers
            </a>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>📝</span> Quotes
            </a>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>🛠️</span> Jobs
            </a>

            <!-- BUSINESS SECTION -->
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Business</div>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>💰</span> Financials
            </a>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>🧑‍💼</span> Team
            </a>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                <span>⚙️</span> Settings
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-700">
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-800 transition">
                <img src="https://i.pravatar.cc/40?u=jsoflo" alt="J. SoFlo"
                    class="w-10 h-10 rounded-full border border-blue-500">
                <div>
                    <p class="font-semibold text-white">J. SoFlo</p>
                    <p class="text-xs text-slate-400">Admin</p>
                </div>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Top Nav -->
        <header class="bg-brand-dark p-4 border-b border-slate-700 flex items-center justify-between">
            <div class="relative w-64">
                <input type="search" placeholder="Search customers, jobs, quotes..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-full px-4 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-center gap-4">
                <p class="text-sm text-slate-400">Thu, Aug 20, 2026</p>
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg text-sm">
                    + New Quote
                </button>
            </div>
        </header>

        <!-- Main Content Area (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </div>
    </main>

</body>

</html>