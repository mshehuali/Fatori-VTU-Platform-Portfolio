<x-app-layout>

<div class="container py-4">
    <h3 class="mb-3">Wallet Activity Log</h3>

    <table class="table table-sm">
        <thead>
            <tr>
                <th>Ref</th>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Time</th>
            </tr>
        </thead>

        <tbody>
            @foreach($activity as $tx)
            @php
                $statusBadge = $tx->status === 'success'
                    ? 'badge bg-success'
                    : ($tx->status === 'failed' ? 'badge bg-danger' : 'badge bg-secondary');
            @endphp
            <tr>
                <td>{{ $tx->reference }}</td>
                <td>{{ optional($tx->user)->email ?? 'N/A' }}</td>
                <td>{{ strtoupper($tx->direction) }}</td>
                <td>{{ $tx->amount }}</td>
                <td><span class="{{ $statusBadge }}">{{ $tx->status }}</span></td>
                <td>{{ $tx->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $activity->links() }}

</div>

</x-app-layout>
