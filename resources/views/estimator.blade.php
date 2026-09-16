@extends('main')

@section('content')
<!-- Bootstrap 5 CSS CDN (Temporary fix for styling) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></div>
<div class="container-fluid px-4 py-4">
    <form action="{{ route('estimator.store') }}" method="POST" id="quoteForm">
        @csrf
        
        <!-- Top Bar with Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="text-white fw-bold mb-0">Quote #1024 <span class="badge bg-secondary fs-6">DRAFT</span></h3>
                <p class="text-muted small mb-0">Create new estimates and track all saved customer quotes in real-time.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="action" value="draft" class="btn btn-outline-light px-4">Save as draft</button>
                <button type="submit" name="action" value="send" class="btn btn-primary px-4 fw-bold">Send quote</button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- LEFT SIDE: Form Inputs -->
            <div class="col-lg-6">
                <div class="card bg-dark border-secondary p-4 rounded-4 text-white">
                    
                    <!-- Customer, Phone & Email -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">CUSTOMER</label>
                            <input type="text" name="customer_name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. James Whitfield" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">PHONE</label>
                            <input type="text" name="phone" class="form-control bg-dark text-white border-secondary" placeholder="+1 (555) 000-0000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">EMAIL</label>
                            <input type="email" name="customer_email" class="form-control bg-dark text-white border-secondary" placeholder="john@example.com">
                        </div>
                    </div>

                    <!-- Vehicle Year, Make, Model -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-white small fw-bold">YEAR</label>
                            <input type="text" name="vehicle_year" class="form-control bg-dark text-white border-secondary" placeholder="2021">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white small fw-bold">MAKE</label>
                            <input type="text" name="vehicle_make" class="form-control bg-dark text-white border-secondary" placeholder="BMW">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-white small fw-bold">MODEL</label>
                            <input type="text" name="vehicle_model" class="form-control bg-dark text-white border-secondary" placeholder="X5">
                        </div>
                    </div>

                    <!-- Size Class & Condition -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">SIZE CLASS</label>
                            <select name="size" id="sizeSelect" class="form-select bg-dark text-white border-secondary">
                                <option value="small" class="bg-dark text-white">Small / Sedan (0.9x)</option>
                                <option value="medium" selected class="bg-dark text-white">Crossover / Mid SUV (1.0x)</option>
                                <option value="large" class="bg-dark text-white">Large / Truck (1.25x)</option>
                                <option value="xl" class="bg-dark text-white">XL SUV (1.5x)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white small fw-bold">CONDITION</label>
                            <select name="condition" id="conditionSelect" class="form-select bg-dark text-white border-secondary">
                                <option value="standard" selected class="bg-dark text-white">Standard (+ $0)</option>
                                <option value="moderate" class="bg-dark text-white">Moderate (+ $50)</option>
                                <option value="severe" class="bg-dark text-white">Severe (+ $120)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selected Services -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label text-white small fw-bold mb-0">SELECTED SERVICES</label>
                        </div>
                        <select name="product_id" id="serviceSelect" class="form-select bg-dark text-white border-secondary mb-3" required>
                            <option value="" class="bg-dark text-white">Choose a service package...</option>
                            @foreach($products as $product)
                                <option value="{{ $product['id'] }}" data-price="{{ $product['price'] }}" class="bg-dark text-white">
                                    {{ $product['name'] }} (${{ number_format($product['price'], 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Plan & Discount -->
                    <div class="mb-4">
                        <label class="form-label text-white small fw-bold mb-2">PLAN & DISCOUNT</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="p-2 border border-secondary rounded text-center bg-secondary bg-opacity-25">
                                    <small class="d-block fw-bold text-white">One-time</small>
                                    <span class="text-light" style="font-size: 11px;">Full price</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border border-secondary rounded text-center">
                                    <small class="d-block fw-bold text-white">Quarterly</small>
                                    <span class="text-light" style="font-size: 11px;">Save 10% / visit</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border border-secondary rounded text-center">
                                    <small class="d-block fw-bold text-white">Monthly</small>
                                    <span class="text-light" style="font-size: 11px;">Save 15% / visit</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quote Summary Breakdown -->
                    <div class="border-top border-secondary pt-3">
                        <div class="d-flex justify-content-between mb-2 text-light small">
                            <span>Subtotal</span>
                            <span id="summarySubtotal" class="text-white">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-light small">
                            <span>Add-ons</span>
                            <span class="text-white">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-light small">
                            <span>Tax (7%)</span>
                            <span id="summaryTax" class="text-white">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                            <span class="fw-bold fs-5 text-white">PER VISIT</span>
                            <span id="summaryTotal" class="fw-bold fs-3 text-warning">$0.00</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT SIDE: Live Customer Preview Card -->
            <div class="col-lg-6">
                <div class="card bg-white text-dark p-4 rounded-4 shadow-lg sticky-top" style="top: 20px;">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <h5 class="fw-bold text-primary mb-0">SOFLO SHINE</h5>
                            <small class="text-muted">DETAILING —</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-dark text-white">QUOTE #1024</span>
                            <div class="small text-muted mt-1">Date: {{ date('M d, Y') }}</div>
                        </div>
                    </div>

                    <div class="mb-3 bg-light p-3 rounded">
                        <small class="text-muted d-block fw-bold">PREPARED FOR</small>
                        <span id="previewCustomerName" class="fw-bold fs-6">James Whitfield</span>
                        <div class="text-muted small">7420 SW 62nd Ct, Pinecrest</div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr class="text-muted fs-7">
                                    <th>REQUESTED SERVICES</th>
                                    <th class="text-end">PRICE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span id="previewServiceName" class="fw-bold">Select a service package</span>
                                        <div class="text-muted" style="font-size: 11px;" id="previewServiceDesc">Interior + Exterior full detail</div>
                                    </td>
                                    <td class="text-end fw-bold" id="previewServicePrice">$0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top pt-3 mb-4">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Subtotal</span>
                            <span id="previewSubtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Tax (7%)</span>
                            <span id="previewTax">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-2 pt-2 border-top">
                            <span>Total Estimate</span>
                            <span id="previewTotal" class="text-primary">$0.00</span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow">
                        Accept Quote & Schedule &rarr;
                    </button>
                    <div class="text-center mt-2 text-muted" style="font-size: 10px;">
                        By accepting, you agree to our Terms & Conditions
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for Live Calculations and Preview Syncing -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerInput = document.querySelector('input[name="customer_name"]');
        const previewCustomer = document.getElementById('previewCustomerName');
        
        const serviceSelect = document.getElementById('serviceSelect');
        const sizeSelect = document.getElementById('sizeSelect');
        const conditionSelect = document.getElementById('conditionSelect');

        const previewServiceName = document.getElementById('previewServiceName');
        const previewServicePrice = document.getElementById('previewServicePrice');
        
        const summarySubtotal = document.getElementById('summarySubtotal');
        const summaryTax = document.getElementById('summaryTax');
        const summaryTotal = document.getElementById('summaryTotal');

        const previewSubtotal = document.getElementById('previewSubtotal');
        const previewTax = document.getElementById('previewTax');
        const previewTotal = document.getElementById('previewTotal');

        function updateEstimates() {
            previewCustomer.textContent = customerInput.value || 'James Whitfield';

            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const basePrice = selectedOption && selectedOption.dataset.price ? parseFloat(selectedOption.dataset.price) : 0;
            const serviceName = selectedOption && selectedOption.value ? selectedOption.text.split(' ($')[0] : 'Select a service package';

            previewServiceName.textContent = serviceName;

            let sizeMultiplier = 1.0;
            if (sizeSelect.value === 'small') sizeMultiplier = 0.9;
            if (sizeSelect.value === 'large') sizeMultiplier = 1.25;
            if (sizeSelect.value === 'xl') sizeMultiplier = 1.5;

            let conditionAddon = 0.0;
            if (conditionSelect.value === 'moderate') conditionAddon = 50.0;
            if (conditionSelect.value === 'severe') conditionAddon = 120.0;

            let subtotal = (basePrice * sizeMultiplier) + conditionAddon;
            let tax = subtotal * 0.07;
            let total = subtotal + tax;

            const formattedSubtotal = '$' + subtotal.toFixed(2);
            const formattedTax = '$' + tax.toFixed(2);
            const formattedTotal = '$' + total.toFixed(2);

            previewServicePrice.textContent = formattedSubtotal;
            summarySubtotal.textContent = formattedSubtotal;
            summaryTax.textContent = formattedTax;
            summaryTotal.textContent = formattedTotal;

            previewSubtotal.textContent = formattedSubtotal;
            previewTax.textContent = formattedTax;
            previewTotal.textContent = formattedTotal;
        }

        customerInput.addEventListener('input', updateEstimates);
        serviceSelect.addEventListener('change', updateEstimates);
        sizeSelect.addEventListener('change', updateEstimates);
        conditionSelect.addEventListener('change', updateEstimates);
    });
</script>
@endsection