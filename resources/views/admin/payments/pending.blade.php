<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<x-app-layout>
    <div class="container py-4">
        
        {{-- Breadcrumb & Header --}}
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a>
            </li>
            <li class="breadcrumb-item active text-white" aria-current="page">Pending Approvals</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-0 text-white">Pending Payment Approvals</h3>
            <p class="text-muted small mb-0">Review and credit manual wallet funding requests</p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            {{-- Navigation to History --}}
            <a href="{{ route('admin.payments.history') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="bi bi-check-all me-1"></i> View Approved
            </a>

            {{-- Counter Badge --}}
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-hourglass-split me-2"></i>{{ $payments->total() }} Awaiting Action
            </span>
        </div>
    </div>
</div>

        {{-- Main Content --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-3">Reference</th>
                            <th>User Details</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date/Time</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td class="ps-4">
                                    <span class="font-monospace fw-bold text-primary">{{ $payment->reference }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark">{{ optional($payment->user)->name ?? 'Unknown User' }}</span>
                                        <span class="small text-muted">{{ optional($payment->user)->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold fs-5 text-dark">₦{{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1 rounded-pill small text-uppercase">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar3 me-1"></i> {{ $payment->created_at->format('M d, Y') }}<br>
                                        <i class="bi bi-clock me-1"></i> {{ $payment->created_at->format('h:i A') }}
                                        <span class="d-block text-info">({{ $payment->created_at->diffForHumans() }})</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('admin.payments.manual.approve', $payment->reference) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to approve this payment of ₦{{ number_format($payment->amount, 2) }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="bi bi-check2-circle me-1"></i> Approve & Credit
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-check-all text-success display-1 opacity-25"></i>
                                        <p class="mt-3 text-muted fw-semibold">No pending payments found at the moment.</p>
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm rounded-pill px-4">Return to Dashboard</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($payments->hasPages())
                <div class="card-footer bg-white py-3 border-top-0">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>