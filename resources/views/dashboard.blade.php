@extends('main')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    darkBg: '#090d16',
                    cardBg: '#111726',
                    cardBorder: '#1c2536',
                    brandBlue: '#3b82f6',
                    brandOrange: '#f97316'
                }
            }
        }
    }
</script>

<style>
    /* Card Hover Effect */
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        border-color: #3b82f666;
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5);
    }
    /* Page Load Animation */
    .fade-in {
        animation: fadeIn 0.8s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="space-y-5 fade-in font-sans text-slate-300">

    <!-- Header Section -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Good afternoon, J.</h1>
            <p class="text-xs text-slate-400 mt-0.5">Here's how SoFlo Shine is doing this month.</p>
        </div>
        <div class="flex items-center gap-1 bg-cardBg border border-cardBorder p-1 rounded-lg text-xs">
            <button class="px-3 py-1 bg-brandBlue text-white font-medium rounded-md shadow-sm">August</button>
            <button class="px-3 py-1 text-slate-400 hover:text-white transition">03</button>
            <button class="px-3 py-1 text-slate-400 hover:text-white transition">YTD</button>
        </div>
    </div>

    <!-- Top 4 KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- REVENUE -->
        <div class="bg-cardBg p-4 rounded-xl border border-cardBorder card-hover">
            <div class="flex justify-between items-center text-slate-400 text-[11px] font-bold tracking-wider">
                <span>REVENUE</span>
                <span class="text-brandBlue font-bold">$</span>
            </div>
            <p class="text-3xl font-extrabold text-white mt-2 tracking-tight">$12,840</p>
            <p class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1 font-medium">
                <span>▲ 16%</span> <span class="text-slate-500 font-normal">over July</span>
            </p>
        </div>

        <!-- EXPENSES -->
        <div class="bg-cardBg p-4 rounded-xl border border-cardBorder card-hover">
            <div class="flex justify-between items-center text-slate-400 text-[11px] font-bold tracking-wider">
                <span>EXPENSES</span>
                <span class="text-orange-500">📊</span>
            </div>
            <p class="text-3xl font-extrabold text-white mt-2 tracking-tight">$5,850</p>
            <p class="text-[11px] text-slate-400 mt-1">materials · labor · overhead</p>
        </div>

        <!-- NET PROFIT -->
        <div class="bg-cardBg p-4 rounded-xl border border-cardBorder card-hover">
            <div class="flex justify-between items-center text-slate-400 text-[11px] font-bold tracking-wider">
                <span>NET PROFIT</span>
                <span class="text-emerald-400">📈</span>
            </div>
            <p class="text-3xl font-extrabold text-white mt-2 tracking-tight">$6,990</p>
            <p class="text-[11px] text-emerald-400 mt-1 font-medium">54.4% margin</p>
        </div>

        <!-- PIPELINE -->
        <div class="bg-cardBg p-4 rounded-xl border border-cardBorder card-hover">
            <div class="flex justify-between items-center text-slate-400 text-[11px] font-bold tracking-wider">
                <span>PIPELINE</span>
                <span class="text-purple-400">⏳</span>
            </div>
            <p class="text-3xl font-extrabold text-white mt-2 tracking-tight">$3,694</p>
            <p class="text-[11px] text-slate-400 mt-1">7 open leads</p>
        </div>
    </div>

    <!-- Row 1: Expense Breakdown, Financial Activity, 03 Expenses -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        
        <!-- Donut Chart Card -->
        <div class="bg-cardBg p-5 rounded-xl border border-cardBorder card-hover flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-white">Expense breakdown</h2>
                <p class="text-[11px] text-slate-400">August - all categories</p>
            </div>
            
            <div class="relative my-3 flex justify-center items-center">
                <div class="w-40 h-40">
                    <canvas id="expenseDonut"></canvas>
                </div>
                <div class="absolute text-center">
                    <p class="text-lg font-bold text-white">$5,850</p>
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider">TOTAL SPENT</p>
                </div>
            </div>

            <div class="space-y-1.5 text-[11px]">
                <div class="flex justify-between items-center"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Labor</span><span class="font-bold text-white">$3,240</span></div>
                <div class="flex justify-between items-center"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-teal-400"></span>Materials & supplies</span><span class="font-bold text-white">$1,560</span></div>
                <div class="flex justify-between items-center"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Fuel</span><span class="font-bold text-white">$412</span></div>
                <div class="flex justify-between items-center"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-500"></span>Insurance</span><span class="font-bold text-white">$286</span></div>
                <div class="flex justify-between items-center"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span>Marketing</span><span class="font-bold text-white">$250</span></div>
            </div>
        </div>

        <!-- Main Financial Activity Bar Chart -->
        <div class="bg-cardBg p-5 rounded-xl border border-cardBorder card-hover">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-sm font-bold text-white">Financial activity</h2>
                    <p class="text-[11px] text-slate-400">Income vs expenses - last 4 months</p>
                </div>
                <div class="text-right">
                    <p class="text-base font-extrabold text-emerald-400">$6,990 <span class="text-[10px] text-slate-400 font-normal">net profit</span></p>
                    <p class="text-[10px] text-slate-400">54% margin</p>
                </div>
            </div>
            <div class="h-56 relative">
                <canvas id="financialBars"></canvas>
            </div>
        </div>

        <!-- 03 Expenses Progress -->
        <div class="bg-cardBg p-5 rounded-xl border border-cardBorder card-hover flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center">
                    <h2 class="text-sm font-bold text-white">03 expenses</h2>
                    <span class="text-[9px] font-bold tracking-wider px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 border border-emerald-800 uppercase">ON TRACK</span>
                </div>
                <p class="text-[11px] text-slate-400">vs quarterly budget</p>
                
                <p class="text-xl font-extrabold text-white mt-3">$10,740 <span class="text-xs font-normal text-slate-400">/ $18,000 budget</span></p>
                
                <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-brandBlue h-1.5 rounded-full" style="width: 60%"></div>
                </div>
            </div>

            <div class="space-y-2 text-[11px] pt-3 border-t border-slate-800">
                <div class="flex justify-between text-slate-400"><span>July <span class="text-[9px] text-slate-500">$6,000 budgeted</span></span> <span class="text-white font-medium">$4,890</span></div>
                <div class="flex justify-between text-white font-bold"><span>August (MTD) <span class="text-[9px] text-slate-400 font-normal">$6,000 budgeted</span></span> <span>$5,850</span></div>
                <div class="flex justify-between text-slate-400"><span>September <span class="text-[9px] text-slate-500">projected</span></span> <span>$4,200</span></div>
            </div>
        </div>

    </div>

    <!-- Row 2: Profit Trend, Insight, Plan Members -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        
        <!-- Profit Trend Line Chart (Span 2 Columns) -->
        <div class="xl:col-span-2 bg-cardBg p-5 rounded-xl border border-cardBorder card-hover flex flex-col justify-between">
            <div class="flex justify-between items-center mb-2">
                <div>
                    <h2 class="text-sm font-bold text-white">Profit trend</h2>
                    <p class="text-[11px] text-slate-400">Last 6 weeks</p>
                </div>
                <span class="text-xs text-slate-400 font-semibold">avg $1,165/wk</span>
            </div>
            <div class="h-44 relative">
                <canvas id="profitLine"></canvas>
            </div>
        </div>

        <!-- Right Side: Insight Card & Plan Members -->
        <div class="space-y-4">
            
            <!-- Insight Box -->
            <div class="bg-gradient-to-r from-blue-950 to-slate-900 p-4 rounded-xl border border-blue-900/50">
                <div class="flex items-center gap-2 text-blue-400 font-bold text-xs uppercase tracking-wider mb-1">
                    <span>✧</span> INSIGHT
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Fuel spend is <span class="text-white font-semibold">22% over July</span> — three Broward jobs drove it. A trip fee beyond 15 mi would have covered <span class="text-emerald-400 font-semibold">$98 of it</span>.
                </p>
            </div>

            <!-- Plan Members Table -->
            <div class="bg-cardBg p-4 rounded-xl border border-cardBorder card-hover">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-sm font-bold text-white">Plan members</h2>
                    <a href="#" class="text-[11px] text-brandBlue hover:underline">All members →</a>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold text-white">Maria Delgado</p>
                            <p class="text-[10px] text-slate-400">2 vehicles · next visit: Sep 12</p>
                        </div>
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-amber-950 text-amber-500 border border-amber-900 uppercase">MONTHLY</span>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                        <div>
                            <p class="font-semibold text-white">Coastal Fleet Rentals</p>
                            <p class="text-[10px] text-slate-400">10 vehicles · next visit: Oct 1</p>
                        </div>
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-900 uppercase">QUARTERLY</span>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                        <div>
                            <p class="font-semibold text-white">Sunrise Rideshare Co.</p>
                            <p class="text-[10px] text-slate-400">5 vehicles · next visit: Aug 28</p>
                        </div>
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-900 uppercase">QUARTERLY</span>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2.5 border-t border-slate-800 text-xs font-bold text-white mt-2">
                    <span>Recurring value</span>
                    <span class="text-brandBlue text-sm">$1,930/mo</span>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Chart.js Engine with Smooth Candle Grow Animation -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Animation Config for Growing Candles Bottom to Top
    const bottomToTopAnimation = {
        y: {
            duration: 1800,
            easing: 'easeOutQuart',
            from: (ctx) => ctx.type === 'data' ? ctx.chart.scales.y.getPixelForValue(0) : undefined
        }
    };

    // 1. Financial Bars (Income vs Expense)
    new Chart(document.getElementById('financialBars'), {
        type: 'bar',
        data: {
            labels: ['MAY', 'JUN', 'JUL', 'AUG'],
            datasets: [
                {
                    label: 'Income',
                    data: [7000, 7500, 11000, 12840],
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    barPercentage: 0.5,
                    categoryPercentage: 0.6
                },
                {
                    label: 'Expense',
                    data: [4000, 4500, 5000, 5850],
                    backgroundColor: '#f97316',
                    borderRadius: 4,
                    barPercentage: 0.5,
                    categoryPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animations: bottomToTopAnimation,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#1c2536' }, ticks: { color: '#64748b', font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } } }
            }
        }
    });

    // 2. Expense Donut Chart
    new Chart(document.getElementById('expenseDonut'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [3240, 1560, 412, 286, 250],
                backgroundColor: ['#3b82f6', '#2dd4bf', '#f59e0b', '#6366f1', '#f43f5e'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: { legend: { display: false } },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1600
            }
        }
    });

    // 3. Profit Line Chart
    new Chart(document.getElementById('profitLine'), {
        type: 'line',
        data: {
            labels: ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8'],
            datasets: [{
                data: [1000, 1200, 950, 1400, 1100, 1600, 1350, 1550],
                borderColor: '#3b82f6',
                borderWidth: 2,
                fill: false,
                tension: 0.35,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animations: bottomToTopAnimation,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#1c2536' }, ticks: { color: '#64748b', font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
            }
        }
    });
</script>
@endsection