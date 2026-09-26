<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Statement - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937; /* Gray-900 */
            line-height: 1.5;
        }
        
        /* HEADER */
        .header {
            width: 100%;
            padding-bottom: 20px;
            border-bottom: 2px solid #10b981; /* Emerald-500 */
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            text-transform: uppercase;
            float: left;
        }
        .meta {
            text-align: right;
            float: right;
        }
        .meta p {
            margin: 0;
            font-size: 10px;
            color: #6b7280; /* Gray-500 */
        }

        /* SUMMARY BOX */
        .summary-box {
            background-color: #f9fafb; /* Gray-50 */
            border: 1px solid #e5e7eb; /* Gray-200 */
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .summary-table { width: 100%; }
        .summary-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }
        .balance-large {
            font-size: 18px;
            color: #10b981;
        }

        /* TRANSACTIONS TABLE */
        table.txn-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.txn-table th {
            text-align: left;
            background-color: #10b981;
            color: white;
            padding: 10px 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.txn-table td {
            border-bottom: 1px solid #f3f4f6;
            padding: 12px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        table.txn-table tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        /* HELPERS */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #059669; font-weight: bold; } /* Green */
        .text-danger { color: #dc2626; font-weight: bold; }  /* Red */
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-failed  { background: #fee2e2; color: #b91c1c; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
        .clear { clear: both; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="logo">
            {{ config('app.name', 'Fatori Tech') }}
        </div>
        <div class="meta">
            <p><strong>Statement Date:</strong> {{ now()->format('d M Y') }}</p>
            <p><strong>Account Email:</strong> {{ $user->email }}</p>
        </div>
        <div class="clear"></div>
    </div>

    {{-- ACCOUNT SUMMARY --}}
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Customer Name</div>
                    <div class="summary-value">{{ $user->name }}</div>
                </td>
                <td>
                    <div class="summary-label">Generated At</div>
                    <div class="summary-value">{{ now()->format('h:i A') }}</div>
                </td>
                <td class="text-right">
                    <div class="summary-label">Closing Balance</div>
                    {{-- Shows the balance AFTER the very last transaction in this list, or current wallet balance --}}
                    <div class="summary-value balance-large">
                        ₦{{ number_format($transactions->first()->balance_after ?? $user->wallet->current_balance, 2) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TRANSACTIONS TABLE --}}
    <table class="txn-table">
        <thead>
            <tr>
                <th width="15%">Date</th>
                <th width="12%">Type</th>
                <th width="28%">Description</th>
                <th width="15%">Reference</th>
                <th width="15%" class="text-right">Amount</th>
                <th width="15%" class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $trx)
                @php
                    $typeMap = [
                        'funding'     => 'Funding',
                        'airtime'     => 'Airtime',
                        'data'        => 'Data',
                        'cable'       => 'Cable TV',
                        'electricity' => 'Electricity',
                        'referral_bonus' => 'Referral',
                    ];
                    $label = $typeMap[$trx->source] ?? ucfirst(str_replace('_', ' ', $trx->source));
                    $isCredit = $trx->direction === 'credit';
                @endphp
                <tr>
                    <td>
                        <strong>{{ $trx->created_at->format('d M Y') }}</strong><br>
                        <span style="color: #6b7280; font-size: 9px;">{{ $trx->created_at->format('h:i A') }}</span>
                    </td>
                    <td>{{ $label }}</td>
                    <td>{{ $trx->description }}</td>
                    <td style="font-family: monospace; color: #4b5563;">
                        {{ substr($trx->reference, 0, 14) }}...
                    </td>
                    <td class="text-right">
                        <span class="{{ $isCredit ? 'text-success' : 'text-danger' }}">
                            {{ $isCredit ? '+' : '-' }}₦{{ number_format($trx->amount, 2) }}
                        </span>
                    </td>
                    <td class="text-right">
                        ₦{{ number_format($trx->balance_after, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 30px; color: #9ca3af;">
                        No transactions found for this period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        This is a computer-generated document and does not require a signature. <br>
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>

</body>
</html>