<x-app-layout>
<div class="container py-4">

<h3 class="mb-3">Webhook Monitor</h3>

<h6>Failed Webhooks</h6>
<table class="table table-sm">
<tr>
    <th>ID</th><th>Reason</th><th>Attempts</th><th>Action</th>
</tr>

@foreach($failures as $f)
<tr>
    <td>{{ $f->id }}</td>
    <td>{{ $f->error_message }}</td>
    <td>{{ $f->retry_count }}</td>
    <td>
        <form method="POST" action="{{ route('admin.webhooks.retry',$f->id) }}">
            @csrf
            <button class="btn btn-warning btn-sm">Retry</button>
        </form>
    </td>
</tr>
@endforeach
</table>

<hr>

<h6>Recent Events</h6>
<table class="table table-sm">
<tr>
    <th>ID</th><th>Type</th><th>Status</th><th>Time</th>
</tr>
@foreach($events as $e)
<tr>
    <td>{{ $e->id }}</td>
    <td>{{ $e->event_type }}</td>
    <td>{{ $e->status }}</td>
    <td>{{ $e->created_at->diffForHumans() }}</td>
</tr>
@endforeach
</table>

</div>
</x-app-layout>
