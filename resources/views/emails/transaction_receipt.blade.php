<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Transaction Receipt - {{ $audit->reference ?? '' }}</title>
</head>
<body style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color:#1f2937; background:#f7fafc; padding:24px;">
  <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e6edf0;">
    <div style="padding:20px 24px; background: linear-gradient(90deg,#064e3b,#059669); color:#fff;">
      <h2 style="margin:0;font-size:20px;">{{ $appName }} — Transaction Receipt</h2>
      <p style="margin:6px 0 0; font-size:13px; opacity:0.9;">Reference</p>
      <div style="font-family:monospace;font-weight:600;margin-top:6px;">{{ $audit->reference ?? '—' }}</div>
    </div>

    <div style="padding:20px;">
      <table style="width:100%;border-collapse:collapse;">
        <tbody>
          <tr>
            <td style="width:30%;padding:8px 0;color:#6b7280;">Customer</td>
            <td style="padding:8px 0;">{{ $user->name ?? 'Customer' }}</td>
          </tr>

          <tr>
            <td style="padding:8px 0;color:#6b7280;">Type</td>
            <td style="padding:8px 0;">{{ ucfirst($audit->source ?? 'Other') }}</td>
          </tr>

          <tr>
            <td style="padding:8px 0;color:#6b7280;">Date</td>
            <td style="padding:8px 0;">{{ optional($audit->created_at)->format('d M Y, h:i A') }}</td>
          </tr>

          <tr>
            <td style="padding:8px 0;color:#6b7280;">Description</td>
            <td style="padding:8px 0;">{{ $audit->description ?? '—' }}</td>
          </tr>

          <tr>
            <td style="padding:8px 0;color:#6b7280;">Amount</td>
            <td style="padding:8px 0;">
              <strong style="color:{{ $audit->direction === 'credit' ? '#059669' : '#be123c' }};">
                {{ $audit->direction === 'credit' ? '+ ' : '- ' }}₦{{ number_format($audit->amount ?? 0, 0, ',', ',') }}
              </strong>
            </td>
          </tr>

          <tr>
            <td style="padding:8px 0;color:#6b7280;">Balance Before</td>
            <td style="padding:8px 0;">₦{{ number_format($audit->balance_before ?? 0, 0, ',', ',') }}</td>
          </tr>

          <tr>
            <td style="padding:8px 0;color:#6b7280;">Balance After</td>
            <td style="padding:8px 0;">₦{{ number_format($audit->balance_after ?? 0, 0, ',', ',') }}</td>
          </tr>

          @php
            // only include customer-friendly meta keys
            $allowed = ['plan','phone','network','provider','smartcard','service_name','quantity','meter_no','meter_type','disco','token'];
            $meta = collect($audit->meta ?? [])->only($allowed)->filter();
          @endphp

          @if($meta->isNotEmpty())
            <tr>
              <td style="padding:8px 0;color:#6b7280;">Info</td>
              <td style="padding:8px 0;">
                <ul style="margin:0;padding-left:16px;">
                  @foreach($meta as $k => $v)
                    <li style="margin-bottom:4px;"><strong style="color:#374151;">{{ \Illuminate\Support\Str::of($k)->replace('_',' ')->title() }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</li>
                  @endforeach
                </ul>
              </td>
            </tr>
          @endif
        </tbody>
      </table>

      <div style="margin-top:16px;color:#6b7280;font-size:13px;">
        If you need a copy of this receipt in your account, sign in and visit Transactions.
      </div>
    </div>

    <div style="padding:12px 16px;border-top:1px solid #eef2f6;background:#fafafa;text-align:center;color:#9ca3af;font-size:12px;">
      Thank you for using {{ $appName }}.
    </div>
  </div>
</body>
</html>
