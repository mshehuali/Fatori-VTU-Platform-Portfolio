<x-app-layout>
    <div class="container py-4">
        
        {{-- Breadcrumb & Header --}}
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active text-white">Payment History</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0 text-white">Approved Payments</h3>
                <a href="{{ route('admin.payments.pending') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">
                    <i class="bi bi-hourglass-split me-1"></i> View Pending
                </a>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="card border-0 shadow-lg bg-dark text-white overflow-hidden" style="border-radius: 15px;">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="bg-secondary bg-opacity-10">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-4">Reference</th>
                            <th>User Details</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Approved Date</th>
                            <th class="text-end pe-4">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($payments as $payment)
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="ps-4">
                                    <span class="font-monospace fw-bold text-info small">{{ $payment->reference }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ optional($payment->user)->name ?? 'User' }}</span>
                                        <span class="small text-muted">{{ optional($payment->user)->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold fs-5 text-success">₦{{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small">
                                        <i class="bi bi-check-circle-fill me-1"></i> COMPLETED
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="text-white">{{ $payment->processed_at ? $payment->processed_at->format('M d, Y') : $payment->updated_at->format('M d, Y') }}</div>
                                        <div class="text-muted">{{ $payment->processed_at ? $payment->processed_at->format('h:i A') : '' }}</div>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('transactions.receipt', $payment->reference) }}" target="_blank" class="btn btn-sm btn-dark border border-secondary rounded-circle">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-folder-x text-muted display-1 opacity-25"></i>
                                    <p class="mt-3 text-muted">No approved payments found in history.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="card-footer bg-transparent border-top border-secondary border-opacity-10 py-3">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>