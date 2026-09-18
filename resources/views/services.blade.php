@extends('main')

@section('content')
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
@endsection