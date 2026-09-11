@extends('main')

@section('content')
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-950 p-8">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Quote requests</h1>
            <p class="text-sm text-slate-400 mt-1">Online submissions from the website, Instagram, Facebook and TikTok – vehicle, service, preferred day and photos.</p>
        </div>
        @if($newRequestsCount > 0)
            <div class="mt-4 md:mt-0 inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                {{ $newRequestsCount }} new since yesterday
            </div>
        @endif
    </div>

    <!-- Requests List -->
    <div class="space-y-6">
        @forelse($quoteRequests as $request)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 flex flex-col lg:flex-row gap-6 justify-between items-start">
                
                <!-- Left Details -->
                <div class="flex-1 space-y-4">
                    <!-- Source & Time -->
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded text-xs font-medium 
                            @if($request->source_type == 'instagram') bg-pink-500/10 text-pink-400 border border-pink-500/20 
                            @elseif($request->source_type == 'tiktok') bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 
                            @elseif($request->source_type == 'facebook') bg-blue-600/10 text-blue-400 border border-blue-600/20 
                            @else bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 @endif">
                            {{ $request->source_label }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $request->time_ago }}</span>
                        @if($request->badge_status == 'new')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950 uppercase tracking-wider">NEW</span>
                        @elseif($request->badge_status == 'quoted')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 uppercase tracking-wider border border-slate-700">QUOTED</span>
                        @endif
                    </div>

                    <!-- Customer Info -->
                    <div>
                        <h2 class="text-lg font-bold text-white">{{ $request->name }}</h2>
                        <p class="text-xs text-slate-400">{{ $request->phone }} · {{ $request->reference_code }}</p>
                    </div>

                    <!-- Vehicle & Service Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                        <div class="bg-slate-950/50 border border-slate-800/80 rounded-lg p-3">
                            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase block mb-1">Vehicle</span>
                            <div class="font-medium text-white text-sm">{{ $request->vehicle_title }}</div>
                            <div class="text-xs text-slate-400">{{ $request->vehicle_type }}</div>
                        </div>
                        <div class="bg-slate-950/50 border border-slate-800/80 rounded-lg p-3">
                            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase block mb-1">Service Requested</span>
                            <div class="font-medium text-white text-sm">{{ $request->service_requested }}</div>
                            <div class="text-xs text-emerald-400 font-medium">Guide price ${{ number_format($request->guide_price, 2) }}</div>
                        </div>
                    </div>

                    <!-- Preferred Day -->
                    <div class="bg-slate-950/50 border border-slate-800/80 rounded-lg p-3 max-w-2xl">
                        <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase block mb-1">Preferred Day</span>
                        <div class="font-medium text-white text-sm">{{ $request->preferred_day }}</div>
                    </div>

                    @if($request->notes)
                        <p class="text-sm text-slate-300 italic">{{ $request->notes }}</p>
                    @endif

                    <!-- Photos Attached -->
                    @if($request->photos)
                        <div>
                            <span class="text-xs text-slate-400 block mb-2">{{ count($request->photos) }} photos attached</span>
                            <div class="flex flex-wrap gap-3">
                                @foreach($request->photos as $photo)
                                    <div class="w-28 h-20 bg-slate-950 border border-slate-800 rounded-lg flex flex-col items-center justify-center p-2 text-center text-[11px] text-slate-400">
                                        <svg class="w-5 h-5 text-slate-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="truncate w-full">{{ $photo }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Action Buttons -->
                <div class="flex lg:flex-col gap-3 w-full lg:w-48 shrink-0">
                    <form action="{{ route('requests.build-quote', $request->id) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-lg text-sm transition shadow-lg shadow-blue-600/20 text-center">
                            Build quote →
                        </button>
                    </form>
                    <button type="button" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium rounded-lg text-sm transition border border-slate-700">
                        Text customer
                    </button>
                    <button type="button" onclick="declineRequest({{ $request->id }})" class="w-full py-2.5 px-4 bg-transparent hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 font-medium rounded-lg text-sm transition border border-slate-700 hover:border-rose-500/20">
                        Decline
                    </button>
                </div>

            </div>
        @empty
            <div class="text-center py-12 text-slate-500">
                No quote requests found.
            </div>
        @endforelse
    </div>
</div>

<!-- AJAX Delete / Decline Script -->
<script>
    function declineRequest(id) {
        if(confirm('Are you sure you want to decline this request?')) {
            fetch(`/requests/${id}/decline`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }
    }
</script>
@endsection