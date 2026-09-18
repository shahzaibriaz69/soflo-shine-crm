@extends('main')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Manage Packages</h2>
            <p class="text-sm text-slate-400 mt-1">Aapke alag se manage hone wale pricing packages.</p>
        </div>
        <button onclick="document.getElementById('addPackageModal').classList.remove('hidden')" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-blue-600/20 transition flex items-center gap-2">
            <span>➕</span> Add New Package
        </button>
    </div>

    @if(session('success'))
        <div class="bg-emerald-950/50 border border-emerald-800 text-emerald-400 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-3">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packages as $package)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between shadow-xl">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <span class="px-3 py-1 text-xs font-semibold bg-blue-950/80 text-blue-400 border border-blue-800/40 rounded-full">
                            {{ $package->category }}
                        </span>
                        <span class="text-xl font-bold text-emerald-400">
                            ${{ number_format($package->price, 2) }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">{{ $package->name }}</h3>
                    <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                        {{ $package->description }}
                    </p>
                </div>
                <div class="border-t border-slate-800/80 pt-4 flex items-center justify-between text-xs text-slate-400">
                    <span>⏱️ {{ $package->duration }}</span>
                    <span class="px-2.5 py-1 rounded-full font-medium bg-emerald-950 text-emerald-400 border border-emerald-800/30">
                        {{ $package->status }}
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-slate-900/40 border border-slate-800/80 rounded-2xl">
                <div class="text-4xl mb-3">🎁</div>
                <p class="text-slate-300 font-medium mb-1">Koi package maujood nahi hai</p>
                <p class="text-xs text-slate-500">Naya package add karne ke liye upar button par click karein.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    <div id="addPackageModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
            <div class="flex justify-between items-center mb-6 border-b border-slate-800 pb-4">
                <h3 class="text-lg font-bold text-white">Create New Package</h3>
                <button onclick="document.getElementById('addPackageModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>

            <form action="{{ route('packages.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Package Name</label>
                        <input type="text" name="name" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500" placeholder="e.g. Gold Detailing Package">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Category</label>
                        <input type="text" name="category" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500" placeholder="e.g. Complete Protection">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Price ($)</label>
                            <input type="number" step="0.01" name="price" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500" placeholder="299.00">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Duration</label>
                            <input type="text" name="duration" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500" placeholder="e.g. 4 - 5 Hours">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase">Description</label>
                        <textarea name="description" rows="3" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 resize-none" placeholder="Package ki tafseel..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8 border-t border-slate-800 pt-4">
                    <button type="button" onclick="document.getElementById('addPackageModal').classList.add('hidden')" class="px-5 py-2.5 bg-slate-800 text-slate-300 rounded-xl text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold">Save Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection