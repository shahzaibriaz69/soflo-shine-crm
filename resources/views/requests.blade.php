@extends('main')

@section('content')
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-950 p-8" x-data="{ openTextModal: false, selectedCustomer: null }">

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

                        <div>
                            <h2 class="text-lg font-bold text-white">{{ $request->name }}</h2>
                            <p class="text-xs text-slate-400">{{ $request->phone }} · {{ $request->reference_code }}</p>
                        </div>

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

                        <div class="bg-slate-950/50 border border-slate-800/80 rounded-lg p-3 max-w-2xl">
                            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase block mb-1">Preferred Day</span>
                            <div class="font-medium text-white text-sm">{{ $request->preferred_day }}</div>
                        </div>

                        @if($request->notes)
                            <p class="text-sm text-slate-300 italic">{{ $request->notes }}</p>
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

                        <!-- Text Customer Trigger Button -->
                        <button type="button" 
                            @click="openTextModal = true; selectedCustomer = { id: '{{ $request->id }}', name: '{{ $request->name }}', phone: '{{ $request->phone }}' }"
                            class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium rounded-lg text-sm transition border border-slate-700">
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

        <!-- Text Customer Modal with Backdrop Blur -->
        <div x-show="openTextModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-xl2 p-4">
            
            <div @click.away="openTextModal = false" class="bg-slate-900 border border-slate-800 rounded-xl w-full max-w-lg p-6 shadow-2xl relative">
                
                <!-- Close Button -->
                <button @click="openTextModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                    ✕
                </button>

                <h3 class="text-lg font-bold text-white mb-1">Text customer</h3>
                <p class="text-xs text-slate-400 mb-6">To: <span x-text="selectedCustomer?.name"></span> - <span x-text="selectedCustomer?.phone"></span></p>

                <form action="{{ route('customer.send-text') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="quote_id" x-bind:value="selectedCustomer?.id">

                    <!-- Message Quick Options -->
                    <div class="flex gap-2 flex-wrap">
                        <span class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs rounded-full text-slate-300 cursor-pointer border border-slate-700">On my way</span>
                        <span class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs rounded-full text-slate-300 cursor-pointer border border-slate-700">Delay update</span>
                        <span class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs rounded-full text-slate-300 cursor-pointer border border-slate-700">Job complete</span>
                        <span class="px-3 py-1 bg-blue-600 text-xs rounded-full text-white font-medium">Free-form</span>
                    </div>

                    <!-- Who's Going Dropdown -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Who's Going</label>
                        <select name="team_member" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-blue-500">
                            <option value="Luis Herrera">Luis Herrera</option>
                            <option value="Marco Diaz">Marco Diaz</option>
                            <option value="J. Solto">J. Solto</option>
                        </select>
                    </div>

                    <!-- Message Textarea -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Message — Edit Freely</label>
                        <textarea name="message_body" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-blue-500" placeholder="Type a message to the customer... e.g. running 30 min behind, sorry!"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-lg text-sm transition shadow-lg shadow-blue-600/20">
                        Send text message
                    </button>
                </form>

            </div>
        </div>

    </div>

    <!-- AJAX Delete / Decline Script -->
    <script>
        function declineRequest(id) {
            if (confirm('Are you sure you want to decline this request?')) {
                fetch(`/requests/${id}/decline`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
@endsection