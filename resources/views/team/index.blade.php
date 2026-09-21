@extends('main')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Team</h2>
                <p class="text-sm text-slate-400 mt-1">Service providers get their own color, calendar, and schedule.</p>
            </div>
            <a href="{{ route('team.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-blue-600/20 transition flex items-center gap-2">
                <span>+</span> Add employee
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-950/50 border border-emerald-800 text-emerald-400 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-3">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Team Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($teamMembers as $member)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between shadow-xl hover:border-slate-700 transition">
                    <div>
                        <!-- Top Row: Avatar, Name & Role -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold text-lg">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-white font-bold text-base">{{ $member->name }}</h3>
                                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-blue-950/80 text-blue-400 border border-blue-800/40 rounded-full inline-block mt-1">
                                        {{ ucfirst($member->role) }}
                                    </span>
                                </div>
                            </div>
                            <a href="#" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs px-3 py-1.5 rounded-xl border border-slate-700 transition">
                                Edit
                            </a>
                        </div>

                        <!-- Contact Details -->
                        <div class="space-y-2 text-sm text-slate-400 mb-6">
                            <div class="flex items-center gap-2">
                                <span>📧</span>
                                <span class="truncate">{{ $member->email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span>📞</span>
                                <span>{{ $member->phone ?? 'No phone added' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Box -->
                    <div class="border-t border-slate-800/80 pt-4 flex justify-between items-center text-xs">
                        <div>
                            <span class="text-slate-500 uppercase block font-semibold text-[10px]">GHL Sync ID</span>
                            <span class="text-slate-300 font-mono">{{ $member->ghl_user_id ?? 'Not Synced' }}</span>
                        </div>
                        <form action="{{ route('team.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Do you really want to delete this employee?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 font-medium transition">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-slate-900/40 border border-slate-800/80 rounded-2xl">
                    <div class="text-4xl mb-3">👥</div>
                    <p class="text-slate-300 font-medium mb-1">No team members found</p>
                    <p class="text-xs text-slate-500">Get started by clicking the "+ Add employee" button above.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection