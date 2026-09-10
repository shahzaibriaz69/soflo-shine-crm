@extends('main')

@section('content')
<div x-data="{ currentTab: @js($activeTab) }" class="flex-1 flex flex-col h-full">
    
    <!-- Top Bar with Title and Tabs -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Pipelines</h1>
            <p class="text-xs text-slate-400 mt-0.5" x-show="currentTab === 'sales'">
                7 open in Sales · $3,694 in play · click → to advance · final phase hands off to another pipeline
            </p>
            <p class="text-xs text-slate-400 mt-0.5" x-show="currentTab === 'recurring'" style="display: none;">
                2 open in Recurring plans · $4,500 in play · click → to advance · final phase hands off to another pipeline
            </p>
        </div>

        <!-- Switch Tabs & New Pipeline Button -->
        <div class="flex items-center gap-3">
            <div class="bg-slate-800/80 p-1 rounded-lg flex items-center border border-slate-700">
                <button @click="currentTab = 'sales'" 
                    :class="currentTab === 'sales' ? 'bg-blue-600 text-white font-medium shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-xs transition">
                    Sales (8)
                </button>
                <button @click="currentTab = 'recurring'" 
                    :class="currentTab === 'recurring' ? 'bg-blue-600 text-white font-medium shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-md text-xs transition">
                    Recurring plans (3)
                </button>
            </div>

            <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-4 rounded-lg text-xs flex items-center gap-1.5 shadow">
                <span>+ New pipeline</span>
            </button>
        </div>
    </div>

    <!-- ================= 1. SALES PIPELINE VIEW ================= -->
    <div x-show="currentTab === 'sales'" class="flex-1 flex gap-4 overflow-x-auto pb-4">
        @foreach($salesPipelines as $pipeline)
            @foreach($pipeline->stages as $stage)
                <div class="w-72 flex-shrink-0 bg-slate-900/40 rounded-xl p-3 border border-slate-800/60 flex flex-col"
                     ondragover="allowDrop(event)" 
                     ondrop="drop(event, '{{ $stage->id }}')">
                    
                    <div class="flex justify-between items-center mb-3 px-1">
                        <span class="text-xs font-bold text-slate-300 tracking-wider">{{ strtoupper($stage->name) }}</span>
                        <span class="text-xs text-slate-500 font-semibold">${{ number_format($stage->opportunities->sum('value')) }}</span>
                    </div>

                    <div class="space-y-3 flex-1 min-h-[300px]" data-stage-id="{{ $stage->id }}">
                        @foreach($stage->opportunities as $opp)
                            <div class="bg-slate-800/90 border border-slate-700/60 rounded-xl p-3 shadow cursor-grab active:cursor-grabbing hover:border-slate-600 transition"
                                 draggable="true" 
                                 ondragstart="drag(event)" 
                                 data-id="{{ $opp->id }}">
                                <h4 class="text-sm font-semibold text-white mb-1">{{ $opp->name }}</h4>
                                <p class="text-xs text-slate-400 mb-2">{{ $opp->description }}</p>
                                <div class="flex justify-between items-center text-[11px] pt-2 border-t border-slate-700/50">
                                    <span class="text-slate-500">{{ $opp->time_in_stage ?? '1d in stage' }}</span>
                                    <span class="font-semibold text-white bg-slate-900 px-2 py-0.5 rounded border border-slate-700">${{ number_format($opp->value, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endforeach

        <!-- Add Phase Box for Sales -->
        <div class="w-72 flex-shrink-0 bg-slate-900/20 border border-dashed border-slate-800 rounded-xl p-3 flex flex-col justify-start">
            <span class="text-xs font-bold text-slate-400 tracking-wider mb-3">ADD PHASE</span>
            <input type="text" placeholder="e.g. Deposit paid" class="bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-300 mb-2 focus:outline-none focus:border-slate-700">
            <button class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium py-2 rounded-lg text-xs transition">+ Add phase</button>
        </div>
    </div>

    <!-- ================= 2. RECURRING PLANS PIPELINE VIEW ================= -->
    <div x-show="currentTab === 'recurring'" style="display: none;" class="flex-1 flex gap-4 overflow-x-auto pb-4">
        @foreach($recurringPipelines as $pipeline)
            @foreach($pipeline->stages as $stage)
                <div class="w-72 flex-shrink-0 bg-slate-900/40 rounded-xl p-3 border border-slate-800/60 flex flex-col"
                     ondragover="allowDrop(event)" 
                     ondrop="drop(event, '{{ $stage->id }}')">
                    
                    <div class="flex justify-between items-center mb-3 px-1">
                        <span class="text-xs font-bold text-slate-300 tracking-wider">{{ strtoupper($stage->name) }}</span>
                        <span class="text-xs text-slate-500 font-semibold">${{ number_format($stage->opportunities->sum('value')) }}</span>
                    </div>

                    <div class="space-y-3 flex-1 min-h-[300px]" data-stage-id="{{ $stage->id }}">
                        @foreach($stage->opportunities as $opp)
                            <div class="bg-slate-800/90 border border-slate-700/60 rounded-xl p-3 shadow cursor-grab active:cursor-grabbing hover:border-slate-600 transition"
                                 draggable="true" 
                                 ondragstart="drag(event)" 
                                 data-id="{{ $opp->id }}">
                                <h4 class="text-sm font-semibold text-white mb-1">{{ $opp->name }}</h4>
                                <p class="text-xs text-slate-400 mb-2">{{ $opp->description }}</p>
                                <div class="flex justify-between items-center text-[11px] pt-2 border-t border-slate-700/50">
                                    <span class="text-slate-500">{{ $opp->time_in_stage ?? '32d in stage' }}</span>
                                    <span class="font-semibold text-white bg-slate-900 px-2 py-0.5 rounded border border-slate-700">${{ number_format($opp->value, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endforeach

        <!-- Add Phase Box for Recurring -->
        <div class="w-72 flex-shrink-0 bg-slate-900/20 border border-dashed border-slate-800 rounded-xl p-3 flex flex-col justify-start">
            <span class="text-xs font-bold text-slate-400 tracking-wider mb-3">ADD PHASE</span>
            <input type="text" placeholder="e.g. Deposit paid" class="bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-300 mb-2 focus:outline-none focus:border-slate-700">
            <button class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium py-2 rounded-lg text-xs transition">+ Add phase</button>
        </div>
    </div>

</div>

<!-- JavaScript for Drag and Drop & Database Update -->
<script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.effectAllowed = 'move';
        ev.dataTransfer.setData("text/plain", ev.currentTarget.getAttribute('data-id'));
    }

    function drop(ev, stageId) {
        ev.preventDefault();
        const oppId = ev.dataTransfer.getData("text/plain");
        const cardElement = document.querySelector(`[data-id='${CSS.escape(oppId)}']`);
        
        if (!cardElement) return;

        let dropZone = ev.target.closest('[data-stage-id]');
        if (!dropZone || dropZone.dataset.stageId !== String(stageId)) return;

        const originalParent = cardElement.parentElement;
        const originalNextSibling = cardElement.nextElementSibling;
        if (originalParent === dropZone) return;

        dropZone.appendChild(cardElement);

        fetch("{{ route('pipeline.update-stage') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: oppId,
                    stage_id: stageId
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Unable to save the stage change.');
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Unable to update stage.');
            })
            .catch(error => {
                console.error('Error updating stage:', error);
                if (originalNextSibling && originalNextSibling.parentElement === originalParent) {
                    originalParent.insertBefore(cardElement, originalNextSibling);
                } else {
                    originalParent.appendChild(cardElement);
                }
            });
    }
</script>
@endsection