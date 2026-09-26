<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<x-app-layout>
    <div class="container py-4">

        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Admin Dashboard</h3>
                <p class="text-muted small mb-0">Platform overview, financial health, and system status</p>
            </div>
            <div class="d-flex gap-2">
                 <a href="{{ route('admin.system.health') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="bi bi-activity me-2"></i> System Health
                </a>
                <span class="badge bg-dark px-3 py-2 shadow-sm d-flex align-items-center">
                    <i class="bi bi-clock me-2"></i>{{ now()->format('Y-m-d H:i') }}
                </span>
            </div>
        </div>

        {{-- Financial Overview Row --}}
        <div class="row g-4 mb-4">
             <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50 text-uppercase small fw-bold">Total Revenue</h6>
                        <h2 class="fw-bold mb-0">₦{{ number_format($financials['total_revenue'] ?? 0, 2) }}</h2>
                        <small class="text-white-50">Lifetime transaction volume</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50 text-uppercase small fw-bold">Net Profit</h6>
                        <h2 class="fw-bold mb-0">₦{{ number_format($financials['net_profit'] ?? 0, 2) }}</h2>
                        <small class="text-white-50">Total fees earned</small>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Pending Payouts</h6>
                        <h2 class="fw-bold mb-0 text-warning">₦{{ number_format($financials['pending_payouts'] ?? 0, 2) }}</h2>
                        <small class="text-muted">Awaiting processing</small>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Total Users</h6>
                        <h2 class="fw-bold mb-0 text-dark">{{ number_format($userStats['total_users'] ?? 0) }}</h2>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> {{ $userStats['new_users_today'] ?? 0 }} today</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Wallet Integrity Summary Card --}}
            <div class="col-md-4">
                @php
                    $walletStats = $walletStats ?? ['total' => 0, 'ok' => 0, 'uninitialized' => 0, 'mismatch' => 0];
                    $riskClass = match(true) {
                        ($walletStats['mismatch'] ?? 0) > 0 => 'border-danger bg-danger-subtle text-danger',
                        ($walletStats['uninitialized'] ?? 0) > 0 => 'border-warning bg-warning-subtle text-warning-emphasis',
                        default => 'border-success bg-success-subtle text-success'
                    };
                    $statusLabel = match(true) {
                        ($walletStats['mismatch'] ?? 0) > 0 => 'CRITICAL MISMATCH',
                        ($walletStats['uninitialized'] ?? 0) > 0 => 'ATTENTION REQUIRED',
                        default => 'SYSTEM SECURE'
                    };
                @endphp

                <div class="card h-100 shadow-sm border-2 {{ $riskClass }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-bold text-uppercase small opacity-75">Wallet Integrity</h6>
                            <span class="badge rounded-pill {{ str_replace(['border-', 'bg-'], 'bg-', $riskClass) }} shadow-sm">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h2 class="mb-0 fw-bold">{{ $walletStats['total'] }}</h2>
                                <p class="small mb-0 opacity-75">Total Wallets</p>
                            </div>
                            <div class="text-end small">
                                <div class="mb-1">OK: <strong>{{ $walletStats['ok'] }}</strong></div>
                                <div class="mb-1 text-danger">Mismatch: <strong>{{ $walletStats['mismatch'] ?? 0 }}</strong></div>
                                <div class="text-warning-emphasis">New: <strong>{{ $walletStats['uninitialized'] }}</strong></div>
                            </div>
                        </div>
                         <div class="mt-3 text-center">
                            <a href="{{ route('admin.wallets.index') }}" class="btn btn-sm btn-outline-dark w-100">Manage Wallets</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Webhook Monitor Card --}}
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 border-top border-primary border-4">
                    <div class="card-body text-center py-4">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Webhook Monitor</h6>
                        <div class="row g-0">
                            <div class="col-4 border-end">
                                <div class="h5 fw-bold mb-0 text-primary">{{ $stats['processed'] ?? 0 }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">PROCESSED</div>
                            </div>
                            <div class="col-4 border-end">
                                <div class="h5 fw-bold mb-0 text-danger">{{ $stats['failed'] ?? 0 }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">FAILED</div>
                            </div>
                            <div class="col-4">
                                <div class="h5 fw-bold mb-0 text-warning">{{ $stats['retries'] ?? 0 }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">RETRIED</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.webhooks') }}" class="btn btn-sm btn-outline-primary mt-3 px-4 rounded-pill">View Logs</a>
                    </div>
                </div>
            </div>

            {{-- Recent Failed Transactions --}}
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 bg-light">
                     <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-uppercase small text-muted mb-0">Recent Failed Transactions</h6>
                         <a href="{{ route('admin.payments.failed') }}" class="text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                         <ul class="list-group list-group-flush bg-transparent">
                            @forelse($failedTransactions ?? [] as $ftx)
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center small">
                                    <div>
                                        <span class="fw-bold text-dark">{{ $ftx->reference }}</span><br>
                                        <span class="text-muted" style="font-size: 0.7rem;">{{ $ftx->created_at->diffForHumans() }}</span>
                                    </div>
                                    <span class="text-danger fw-bold">₦{{ number_format($ftx->amount) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item bg-transparent text-center text-muted small py-4">No recent failed transactions.</li>
                            @endforelse
                        </ul>
                    </div>
                     <div class="card-footer bg-transparent border-0">
                        <div class="d-grid gap-2">
                             <a href="{{ route('admin.payments.pending') }}" class="btn btn-warning btn-sm text-dark fw-bold">
                                <i class="bi bi-hourglass-split me-1"></i> Review Pending Approvals
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Approvals Alert --}}
        @if(isset($payment) && $payment)
            <div class="alert alert-white border shadow-sm d-flex justify-content-between align-items-center mt-4 mb-0 py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-warning-subtle text-warning rounded-circle p-2 me-3">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark">Action Required: Manual Approval</strong>
                        <span class="small text-muted">Ref: <strong>{{ $payment->reference }}</strong></span>
                    </div>
                </div>
                <form action="{{ route('admin.payments.manual.approve', $payment->reference) }}" method="POST" onsubmit="return confirm('Confirm manual credit?')">
                    @csrf
                    <button class="btn btn-success btn-sm rounded-pill px-4 shadow-sm">
                        Approve & Credit
                    </button>
                </form>
            </div>
        @endif

        {{-- Tools Section --}}
        @can('is-admin')
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex align-items-center gap-3">
                            <h6 class="mb-0 fw-bold me-auto">Admin Tools</h6>
                            
                            {{-- ✅ Price Control Button --}}
                            <a href="{{ route('admin.pricing') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-tag-fill me-1"></i> Price Control
                            </a>

                            {{-- Other Tools --}}
                             <a href="{{ route('admin.fraud.timeline') }}" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-shield-exclamation me-1"></i> Fraud Timeline
                            </a>
                             <a href="{{ route('admin.activity') }}" class="btn btn-outline-dark btn-sm">
                                <i class="bi bi-list-check me-1"></i> System Log
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Recent Activity Table --}}
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Wallet Activity</h6>
                <a href="{{ route('admin.activity') }}" class="btn btn-sm btn-light text-primary fw-bold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th class="ps-4">Reference</th>
                            <th>User Email</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent ?? [] as $tx)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-decoration-none fw-semibold text-primary">
                                        {{ $tx->reference }}
                                    </span>
                                </td>
                                <td><span class="text-muted small">{{ optional($tx->user)->email ?? 'N/A' }}</span></td>
                                <td>
                                    <span class="small fw-bold {{ $tx->direction == 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ strtoupper($tx->direction) }}
                                    </span>
                                </td>
                                <td class="fw-bold">₦{{ number_format($tx->amount, 2) }}</td>
                                <td>
                                    @php
                                        $badge = match($tx->status) {
                                            'success' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }} px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                                        {{ strtoupper($tx->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4 text-muted small">{{ $tx->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No recent activity detected</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($recent) && $recent->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $recent->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>