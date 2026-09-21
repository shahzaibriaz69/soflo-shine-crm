@extends('main')

@section('content')
{{-- Legacy Bootstrap implementation retained temporarily but not rendered. --}}
{{--
<!-- Bootstrap 5 CSS CDN (Temporary fix for styling) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container-fluid px-4 py-4">
    
    <!-- Header & Action Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white fw-bold mb-0">Services & Packages</h3>
            <p class="text-muted small mb-0">Manage your detailing services, base pricing, and duration tiers.</p>
        </div>
        <button type="button" class="btn btn-primary px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            + Add New Service
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Services Table Card -->
    <div class="card bg-dark border-secondary rounded-4 text-white shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th class="ps-4">Service Name</th>
                            <th>Category</th>
                            <th>Base Price</th>
                            <th>Estimated Duration</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products ?? [] as $product)
                            @php
                                $pId = is_object($product) ? ($product->id ?? '') : ($product['id'] ?? '');
                                $pName = is_object($product) ? ($product->name ?? '') : ($product['name'] ?? '');
                                $pCategory = is_object($product) ? ($product->category ?? 'General') : ($product['category'] ?? 'General');
                                $pPrice = is_object($product) ? ($product->base_price ?? $product->price ?? 0) : ($product['base_price'] ?? $product['price'] ?? 0);
                                $pDuration = is_object($product) ? ($product->duration ?? '2-3 Hours') : ($product['duration'] ?? '2-3 Hours');
                                $pStatus = is_object($product) ? ($product->status ?? 'Active') : ($product['status'] ?? 'Active');
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-white">{{ $pName }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($pCategory) }}</span></td>
                                <td class="text-warning fw-bold">${{ number_format($pPrice, 2) }}</td>
                                <td class="text-light">{{ $pDuration }}</td>
                                <td>
                                    <span class="badge bg-{{ strtolower($pStatus) == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($pStatus) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-light me-1" title="Edit">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No services found. Click on "Add New Service" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <form action="{{ route('services.store') ?? '#' }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold" id="addServiceModalLabel">Add New Service Package</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SERVICE NAME</label>
                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Full Interior Detailing" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CATEGORY</label>
                        <select name="category" class="form-select bg-dark text-white border-secondary">
                            <option value="interior">Interior Detailing</option>
                            <option value="exterior">Exterior Detailing</option>
                            <option value="ceramic">Ceramic Coating</option>
                            <option value="full">Full Detail Package</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">BASE PRICE ($)</label>
                            <input type="number" step="0.01" name="base_price" class="form-control bg-dark text-white border-secondary" placeholder="199.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ESTIMATED DURATION</label>
                            <input type="text" name="duration" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 3-4 Hours">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">DESCRIPTION</label>
                        <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Briefly describe what is included in this package..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
--}}

<div class="max-w-7xl mx-auto" x-data="{ addServiceOpen: false }">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Services</h1>
            <p class="text-sm text-slate-400 mt-1">Manage your detailing services, prices, and durations.</p>
        </div>
        <button type="button" x-on:click="addServiceOpen = true"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-600/20 transition">
            <span class="text-lg leading-none">+</span> Add New Service
        </button>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-800">
            <h2 class="text-base font-bold text-white">Service catalog</h2>
            <p class="text-xs text-slate-500 mt-1">{{ count($products ?? []) }} services available</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-slate-800/60 text-[11px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Service name</th>
                        <th class="px-5 py-4 font-semibold">Category</th>
                        <th class="px-5 py-4 font-semibold">Base price</th>
                        <th class="px-5 py-4 font-semibold">Duration</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-sm">
                    @forelse($products ?? [] as $product)
                        @php
                            $pName = $product->name ?? 'Untitled service';
                            $pCategory = $product->category ?? 'General';
                            $pPrice = $product->base_price ?? $product->price ?? 0;
                            $pDuration = $product->duration ?? 'Standard time';
                            $isActive = $product->is_active ?? strtolower($product->status ?? 'active') === 'active';
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 font-semibold text-white">{{ $pName }}</td>
                            <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-xs font-medium text-blue-300">{{ ucfirst($pCategory) }}</span></td>
                            <td class="px-5 py-4 font-semibold text-emerald-400">${{ number_format($pPrice, 2) }}</td>
                            <td class="px-5 py-4 text-slate-400">{{ $pDuration }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $isActive ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-300' : 'bg-slate-800 border border-slate-700 text-slate-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>{{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right"><button type="button" class="text-sm font-medium text-slate-400 hover:text-white transition">Edit</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center"><p class="font-medium text-slate-300">No services found</p><p class="text-sm text-slate-500 mt-1">Create your first service to start building quotes.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="addServiceOpen" x-cloak x-on:keydown.escape.window="addServiceOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div x-on:click="addServiceOpen = false" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-lg bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800">
                <div><h2 class="text-lg font-bold text-white">Add New Service</h2><p class="text-xs text-slate-500 mt-1">Add a service to your catalog.</p></div>
                <button type="button" x-on:click="addServiceOpen = false" class="text-2xl leading-none text-slate-400 hover:text-white" aria-label="Close">&times;</button>
            </div>
            <form action="{{ route('services.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div><label for="service-name" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Service name</label><input id="service-name" type="text" name="name" required placeholder="e.g. Full Interior Detail" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-blue-500"></div>
                    <div><label for="service-category" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Category</label><select id="service-category" name="category" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500"><option value="interior">Interior detailing</option><option value="exterior">Exterior detailing</option><option value="ceramic">Ceramic coating</option><option value="full">Full detail package</option></select></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label for="service-price" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Base price</label><input id="service-price" type="number" name="base_price" min="0" step="0.01" required placeholder="199.00" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-blue-500"></div>
                        <div><label for="service-duration" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Duration</label><input id="service-duration" type="text" name="duration" placeholder="e.g. 3–4 hours" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-blue-500"></div>
                    </div>
                    <div><label for="service-description" class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Description</label><textarea id="service-description" name="description" rows="3" placeholder="What is included in this service?" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-blue-500 resize-none"></textarea></div>
                </div>
                <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-800"><button type="button" x-on:click="addServiceOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-slate-800">Cancel</button><button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 rounded-xl text-sm font-semibold text-white">Save Service</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
