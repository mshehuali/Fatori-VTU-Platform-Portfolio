<x-app-layout>

<div class="container py-4">

    <h4 class="mb-3">Wallets</h4>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Current Balance</th>
                    <th>Ledger Balance</th>
                    <th>Status</th>
                    <th>Integrity</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                @forelse($wallets as $wallet)
                    <tr>
                        <td>{{ $wallet->id }}</td>

                        <td>
                            {{ $wallet->user->name ?? 'Unknown User' }}
                            <br>
                            <small class="text-muted">
                                ID: {{ $wallet->user_id }}
                            </small>
                        </td>

                        <td>{{ number_format($wallet->current_balance, 2) }}</td>
                        <td>{{ number_format($wallet->ledger_balance, 2) }}</td>

                        {{-- 🔐 Status --}}
                        <td>
                            @if($wallet->status === 'frozen')
                                <span class="badge bg-danger">Frozen</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>

                        {{-- 🟢 Integrity Badge --}}
                        <td>
                            <x-wallet.integrity-badge :wallet="$wallet" />
                        </td>

                        <td>
                            <small class="text-muted">
                                {{ $wallet->updated_at->diffForHumans() }}
                            </small>
                        </td>

                        {{-- ❄️ Freeze / Unfreeze Button --}}
                        <td>
                        {{-- ❄️ Freeze / Unfreeze Button --}}
                        <form action="{{ route('admin.wallets.toggle-freeze', $wallet->id) }}"
                            method="POST"
                            onsubmit="return confirm('Change wallet security state?');">
                            @csrf

                            @if($wallet->status === 'frozen')
                                <button class="btn btn-success btn-sm">
                                    Unfreeze
                                </button>
                            @else
                                <button class="btn btn-danger btn-sm">
                                    Freeze
                                </button>
                            @endif
                        </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">
                            No wallets found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</x-app-layout>
