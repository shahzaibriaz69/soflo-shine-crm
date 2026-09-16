@extends('main')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Quotes & Estimates Management</h1>
            <p class="text-xs text-slate-400">Generate new estimates and track all saved customer quotes in real-time.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-950 border border-emerald-800 text-emerald-300 text-sm rounded-xl shadow">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-950 border border-rose-800 text-rose-300 text-sm rounded-xl shadow">
            {{ session('error') }}
        </div>
    @endif

    <!-- Grid Layout: Left Side Form (Estimator), Right Side History -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Quick Estimate Generator Form -->
        <div class="bg-[#0f172a] border border-slate-800 p-6 rounded-xl shadow-xl h-fit">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>⚡</span> Quick Quote Generator
            </h2>

            <form action="{{ route('estimator.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Customer Name</label>
                    <input type="text" name="customer_name" required placeholder="e.g. John Doe"
                        class="w-full bg-[#1e293b] border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Select Service / Package</label>
                    <select name="product_id" required
                        class="w-full bg-[#1e293b] border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Choose a service...</option>
                        @if(!empty($products))
                            @foreach($products as $product)
                                @php
                                    $pId = is_object($product) ? ($product->id ?? '') : ($product['id'] ?? '');
                                    $pName = is_object($product) ? ($product->name ?? '') : ($product['name'] ?? '');
                                    $pPrice = is_object($product) ? ($product->price ?? 0) : ($product['price'] ?? 0);
                                @endphp
                                <option value="{{ $pId }}">
                                    {{ $pName }} (${{ $pPrice }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Vehicle Size</label>
                    <select name="size" required
                        class="w-full bg-[#1e293b] border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="sedan">Sedan (0.9x)</option>
                        <option value="midsize" selected>Midsize / SUV (1.0x)</option>
                        <option value="large">Large / Truck (1.2x)</option>
                        <option value="xl">XL / Oversized (1.5x)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Vehicle Condition</label>
                    <select name="condition" required
                        class="w-full bg-[#1e293b] border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                        <option value="good">Good / Clean (+$0)</option>
                        <option value="average" selected>Average (+$30)</option>
                        <option value="dirty">Heavy Dirt / Pet Hair (+$70)</option>
                        <option value="severe">Severe Condition (+$120)</option>
                    </select>
                </div>

                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 rounded-lg text-sm shadow transition mt-2">
                    Generate & Sync to GHL
                </button>
            </form>
        </div>

        <!-- Right Column: Quotes History Table -->
        <div class="lg:col-span-2 bg-[#0f172a] border border-slate-800 rounded-xl overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-[#1e293b]/40">
                <h3 class="font-bold text-white text-md">Generated Quotes History</h3>
                <span class="text-xs text-slate-400">Total Quotes: {{ isset($quotes) ? count($quotes) : 0 }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#1e293b] text-slate-400 uppercase text-xs tracking-wider border-b border-slate-800">
                            <th class="p-3">ID</th>
                            <th class="p-3">Customer</th>
                            <th class="p-3">Size</th>
                            <th class="p-3">Condition</th>
                            <th class="p-3">Total</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-300 text-sm divide-y divide-slate-800">
                        @forelse($quotes ?? [] as $quote)
                            <tr class="hover:bg-[#1e293b]/50 transition">
                                <td class="p-3 font-semibold text-white">#{{ $quote->id ?? '' }}</td>
                                <td class="p-3 font-medium text-white">{{ $quote->customer_name ?? '' }}</td>
                                <td class="p-3 uppercase text-xs text-slate-400">{{ $quote->vehicle_size ?? '' }}</td>
                                <td class="p-3 capitalize text-xs text-slate-400">{{ $quote->vehicle_condition ?? '' }}</td>
                                <td class="p-3 font-bold text-emerald-400">${{ number_format($quote->total_amount ?? 0, 2) }}</td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] uppercase font-bold tracking-wider 
                                        {{ ($quote->status ?? '') == 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }}">
                                        {{ $quote->status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-500 text-xs">{{ $quote->created_at ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-8 text-slate-500">No quotes generated yet. Use the form on the left to create your first estimate!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection