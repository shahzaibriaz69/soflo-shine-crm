@extends('main')

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkBg: '#0b111e',
                        cardBg: '#131b2e',
                        cardHover: '#18223a',
                        cardBorder: '#1e293b',
                        brandBlue: '#2563eb',
                        wonGreen: '#15803d',
                    }
                }
            }
        }
    </script>

    <div class="space-y-6 font-sans text-slate-200">

        <!-- Top Navigation Header -->
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Pipelines</h1>
                <p class="text-xs text-slate-400 mt-1">
                    <span class="font-semibold text-slate-300">7 open in Sales</span> ·
                    <span class="text-slate-300 font-semibold">$3,694 in play</span> ·
                    drag & drop card to move across stages · final phase hands off to another pipeline
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button class="px-3 py-1.5 bg-brandBlue text-white text-xs font-semibold rounded-full shadow-sm">
                    Sales (8)
                </button>
                <button
                    class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 border border-slate-700/60 text-xs rounded-full font-medium transition">
                    Recurring plans (3)
                </button>
                <button
                    class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 border border-slate-700/60 text-xs rounded-full font-medium transition flex items-center gap-1">
                    <span>+</span> New pipeline
                </button>
            </div>
        </div>

        <!-- Kanban Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-3 items-start overflow-x-auto pb-4">

            @foreach($stages as $stageKey => $stage)
                @php
                    $stageItems = collect($opportunities)->where('stage', $stageKey);
                    $stageTotal = $stageItems->sum('value');
                @endphp

                <div class="space-y-2">
                    <!-- Column Header -->
                    <div class="flex justify-between items-center px-1">
                        <h2 class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">{{ $stage['title'] }}</h2>
                        <span class="text-[11px] font-bold text-slate-400 stage-total" data-stage="{{ $stageKey }}">
                            @if($stageTotal > 0)
                                ${{ number_format($stageTotal) }}
                            @else
                                {{ count($stageItems) }}
                            @endif
                        </span>
                    </div>

                    <!-- Cards Column / Drop Zone -->
                    <div class="space-y-2 min-h-[450px] rounded-lg p-1 transition-colors drop-zone" data-stage="{{ $stageKey }}"
                        ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)"
                        ondrop="handleDrop(event, '{{ $stageKey }}')">

                        @foreach($stageItems as $item)
                            <div id="card-{{ $item['id'] }}" draggable="true"
                                ondragstart="handleDragStart(event, '{{ $item['id'] }}')"
                                class="bg-cardBg border border-slate-800 rounded-lg p-3 hover:border-slate-600 transition space-y-2 cursor-grab active:cursor-grabbing shadow-sm hover:shadow-md">

                                <!-- Card Header (Arrow removed) -->
                                <div class="flex justify-between items-start">
                                    <h3 class="text-xs font-bold text-white tracking-wide">{{ $item['name'] }}</h3>
                                </div>

                                <!-- Description -->
                                <p class="text-[11px] text-slate-400 leading-tight">
                                    {{ $item['description'] }}
                                </p>

                                <!-- Footer Stats -->
                                <div class="flex justify-between items-center pt-1 text-[10px] text-slate-400 font-medium">
                                    <span>{{ $item['time_in_stage'] }}</span>
                                    @if(($item['value'] ?? 0) > 0)
                                        <span class="font-bold text-white">${{ number_format($item['value']) }}</span>
                                    @else
                                        <span class="text-slate-500">–</span>
                                    @endif
                                </div>

                                <!-- Special "Hand off to..." button for WON stage -->
                                @if(!empty($item['hand_off']))
                                    <button
                                        class="w-full mt-2 py-1.5 px-2 bg-emerald-900/40 hover:bg-emerald-900/60 border border-emerald-600/50 text-emerald-400 rounded text-[10px] font-semibold flex justify-between items-center transition">
                                        <span>Hand off to... ↗</span>
                                        <span>v</span>
                                    </button>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Add Phase Column -->
            <div class="space-y-2">
                <div class="px-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    ADD PHASE
                </div>
                <div class="border border-dashed border-slate-800 rounded-lg p-3 bg-cardBg/40 space-y-2">
                    <input type="text" placeholder="e.g. Deposit paid"
                        class="w-full bg-slate-900/80 border border-slate-800 rounded px-2.5 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-slate-700" />
                    <button
                        class="w-full py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded transition">
                        + Add phase
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Drag and Drop JavaScript Logic -->
    <script>
        let draggedCardId = null;

        function handleDragStart(event, id) {
            draggedCardId = id;
            event.dataTransfer.setData("text/plain", id);
            event.dataTransfer.effectAllowed = "move";
            setTimeout(() => {
                const el = document.getElementById(`card-${id}`);
                if (el) el.classList.add('opacity-40');
            }, 0);
        }

        function handleDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = "move";
            const dropZone = event.currentTarget;
            dropZone.classList.add('bg-slate-800/30', 'border-slate-700');
        }

        function handleDragLeave(event) {
            const dropZone = event.currentTarget;
            dropZone.classList.remove('bg-slate-800/30', 'border-slate-700');
        }

        function handleDrop(event, targetStage) {
            event.preventDefault();
            const dropZone = event.currentTarget;
            dropZone.classList.remove('bg-slate-800/30', 'border-slate-700');

            const cardId = event.dataTransfer.getData("text/plain") || draggedCardId;
            const cardElement = document.getElementById(`card-${cardId}`);

            if (cardElement && dropZone) {
                cardElement.classList.remove('opacity-40');
                dropZone.appendChild(cardElement);

                // Database Sync via AJAX Call
                fetch("{{ route('pipeline.updateStage') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        id: cardId,
                        stage: targetStage
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Server Response:", data);
                    })
                    .catch(error => {
                        console.error("Database update error:", error);
                    });
            }
        }

        document.addEventListener('dragend', function (event) {
            if (draggedCardId) {
                const cardElement = document.getElementById(`card-${draggedCardId}`);
                if (cardElement) {
                    cardElement.classList.remove('opacity-40');
                }
            }
        });
    </script>
@endsection