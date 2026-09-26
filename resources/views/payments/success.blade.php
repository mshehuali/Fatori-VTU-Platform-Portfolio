@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container mx-auto max-w-lg py-8">
    <h1 class="text-2xl font-semibold mb-4">Payment Successful</h1>

    <div class="bg-white p-4 rounded shadow">
        <p class="mb-2">Thank you. Your wallet has been funded.</p>

        <p><strong>Reference:</strong> {{ $reference ?? 'N/A' }}</p>
        <p><strong>Amount:</strong> ₦{{ number_format($amount ?? 0, 2) }}</p>

        <div class="mt-4">
            <a href="{{ route('vtu.dashboard') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Back to Dashboard</a>
            <a href="{{ route('transactions.index') }}" class="ml-2 px-4 py-2 bg-gray-200 rounded">View Transactions</a>
        </div>
    </div>
</div>
@endsection
