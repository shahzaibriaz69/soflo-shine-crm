<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auto Detailing Price Estimator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Auto Detailing Price Estimator</h2>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <form action="{{ route('estimator.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Customer Name</label>
                <input type="text" name="customer_name" required class="w-full border rounded p-2" placeholder="Enter customer name">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Select Service / Package</label>
                <select name="product_id" required class="w-full border rounded p-2">
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} (${{ $prod->price }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Vehicle Size</label>
                <select name="size" class="w-full border rounded p-2">
                    <option value="small">Small (Coupe / Sedan) - 0.9x</option>
                    <option value="medium" selected>Medium (Standard) - 1.0x</option>
                    <option value="large">Large (SUV) - 1.25x</option>
                    <option value="xl">XL (Truck / Van) - 1.5x</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Vehicle Condition</label>
                <select name="condition" class="w-full border rounded p-2">
                    <option value="standard" selected>Standard - $0</option>
                    <option value="moderate">Moderate Dirt / Pet Hair - +$50</option>
                    <option value="severe">Severe / Heavy Restoration - +$120</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-semibold p-2 rounded hover:bg-blue-700">
                Generate & Sync Quote
            </button>
        </form>
    </div>
</body>
</html>