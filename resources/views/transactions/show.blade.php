@extends('layouts.app')

@section('title', 'Transaction Receipt')

@section('content')
<div class="container mx-auto max-w-lg py-8">
    <h1 class="text-2xl font-semibold mb-4">Transaction Receipt</h1>

    <div class="bg-white p-4 rounded shadow">
        <p><strong>Reference:</strong> {{ $audit->reference }}</p>
        <p><strong>Date:</strong> {{ $audit->created_at->format('Y-m-d H:i') }}</p>
        <p><strong>Type:</strong> {{ ucfirst($audit->direction) }}</p>
        <p><strong>Source:</strong> {{ $audit->source }}</p>
        <p><strong>Amount:</strong> ₦{{ number_format($audit->amount, 2) }}</p>
        <p><strong>Balance Before:</strong> ₦{{ number_format($audit->balance_before, 2) }}</p>
        <p><strong>Balance After:</strong> ₦{{ number_format($audit->balance_after, 2) }}</p>

        <p><strong>Status:</strong>
            <span class="font-semibold">
                {{ ucfirst($audit->status) }}
            </span>
        </p>

        {{-- ============================
             FRIENDLY TOKEN / PIN OUTPUT
        ============================= --}}
        @if(isset($audit->meta['token']))
            <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[11px] text-slate-500 mb-1">Electricity Token</p>

                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-sm tracking-wide">
                        {{ $audit->meta['token'] }}
                    </span>

                    <button
                        onclick="navigator.clipboard.writeText('{{ $audit->meta['token'] }}'); copyToast('Token copied');"
                        class="text-xs px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                    >
                        Copy
                    </button>
                </div>
            </div>
        @endif

        @if(isset($audit->meta['pins']) && is_array($audit->meta['pins']))
            <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <p class="text-[11px] text-slate-500 mb-1">PIN(s)</p>

                @foreach($audit->meta['pins'] as $pin)
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="font-mono text-sm tracking-wide">{{ $pin }}</span>

                        <button
                            onclick="navigator.clipboard.writeText('{{ $pin }}'); copyToast('PIN copied');"
                            class="text-xs px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                        >
                            Copy
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ============================
             RAW JSON METADATA (DEV VIEW)
        ============================= --}}
        @if(!empty($audit->meta))
            <hr class="my-3">
            <h3 class="font-medium">Metadata</h3>
            <pre class="text-sm bg-gray-50 p-2 rounded">
{{ json_encode($audit->meta, JSON_PRETTY_PRINT) }}
            </pre>
        @endif

        <hr class="my-4">

        {{-- ============================
             ADMIN ACTIONS
        ============================= --}}
        @if(auth()->check() && auth()->user()->is_admin)

            @if(!in_array($audit->status, ['disputed','reversed']))
                <div class="mb-4">
                    <h3 class="font-semibold text-red-600 mb-2">Admin Action</h3>

                    <form method="POST" action="{{ route('txn.dispute', $audit->reference) }}" class="space-y-2">
                        @csrf
                        <input
                            type="text"
                            name="reason"
                            placeholder="Enter dispute reason"
                            required
                            class="w-full border rounded px-2 py-1 text-sm"
                        >

                        <button
                            type="submit"
                            onclick="return confirm('Mark this transaction as DISPUTED?')"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm"
                        >
                            ⚠️ Mark as Disputed
                        </button>
                    </form>
                </div>
            @endif

            @if($audit->status === 'disputed')
                <div class="mt-3">
                    <form method="POST" action="{{ route('txn.rollback', $audit->reference) }}">
                        @csrf
                        <button
                            type="submit"
                            onclick="return confirm('CONFIRM ROLLBACK? This will update wallet balance.')"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm"
                        >
                            🔄 Rollback Transaction
                        </button>
                    </form>
                </div>
            @endif

        @endif

        <div class="mt-5">
            <a href="{{ route('transactions.index') }}" class="text-blue-600 hover:underline">
                ← Back to Transactions
            </a>
        </div>
    </div>
</div>

{{-- Toast for Copy Actions --}}
<script>
function copyToast(msg) {
    const el = document.createElement('div');
    el.textContent = msg;
    el.className =
        'fixed bottom-4 right-4 bg-emerald-600 text-white text-xs px-3 py-1.5 ' +
        'rounded-xl shadow-lg opacity-0 transition';

    document.body.appendChild(el);

    requestAnimationFrame(() => el.classList.add('opacity-100'));

    setTimeout(() => {
        el.classList.remove('opacity-100');
        setTimeout(() => el.remove(), 300);
    }, 1200);
}
</script>
@endsection
