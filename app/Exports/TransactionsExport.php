<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected Collection $transactions;

    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions->map(function ($trx) {
            $typeMap = [
                'funding'     => 'Wallet Funding',
                'airtime'     => 'Airtime',
                'data'        => 'Data',
                'cable'       => 'Cable TV',
                'electricity' => 'Electricity',
            ];

            return [
                'date'            => $trx->created_at->format('Y-m-d H:i:s'),
                'type'            => $typeMap[$trx->source] ?? ucfirst($trx->source ?? 'Other'),
                'description'     => $trx->description,
                'reference'       => $trx->reference,
                'direction'       => $trx->direction,
                'amount'          => $trx->amount,
                'balance_before'  => $trx->balance_before,
                'balance_after'   => $trx->balance_after,
                'status'          => $trx->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Description',
            'Reference',
            'Direction',
            'Amount',
            'Balance Before',
            'Balance After',
            'Status',
        ];
    }
}
