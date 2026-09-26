<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Wallet;
use App\Models\WalletAudit;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TransactionsExport;

class TransactionController extends Controller
{
    /**
     * 📄 Transaction List
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ✅ Always ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_balance' => 0,
                'ledger_balance'  => 0,
            ]
        );

        $query = $this->buildFilteredQuery($wallet, $request);

        $transactions = $query
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        // Group by date for UI
        $groupedTransactions = $transactions->getCollection()
            ->groupBy(fn (WalletAudit $trx) =>
                optional($trx->created_at)->toDateString() ?? 'unknown'
            );

        $types = [
            'funding'      => 'Wallet Funding',
            'airtime'      => 'Airtime',
            'data'         => 'Data',
            'cable'        => 'Cable TV',
            'electricity'  => 'Electricity',
            'referral'     => 'Referral Bonus',
        ];

        $statuses = [
            'success' => 'Success',
            'pending' => 'Pending',
            'failed'  => 'Failed',
        ];

        $filters = $request->only([
            'reference',
            'type',
            'status',
            'date_from',
            'date_to',
        ]);

        return view('transactions.index', compact(
            'transactions',
            'groupedTransactions',
            'filters',
            'types',
            'statuses'
        ));
    }

    /**
     * 🧾 Receipt (ID based – CANONICAL)
     */
    public function show(WalletAudit $audit)
    {
        $audit->refresh();

        // Decode meta safely
        if (is_string($audit->meta)) {
            $audit->meta = json_decode($audit->meta, true);
        }

        $user = Auth::user();

        // ✅ Ownership check
        $isOwner =
            $audit->user_id === $user->id ||
            optional($audit->wallet)->user_id === $user->id;

        $isAdmin = Gate::allows('is-admin');

        if (! $isOwner && ! $isAdmin) {
            abort(403, 'Unauthorized access to this transaction.');
        }

        // Decide whose name appears on receipt
        $receiptUser = $isOwner
            ? $user
            : ($audit->user ?? optional($audit->wallet)->user ?? $user);

        return view('transactions.receipt', [
            'audit' => $audit,
            'user'  => $receiptUser,
        ]);
    }

    /**
     * 🔗 Receipt via Reference (redirects to canonical show)
     */
    public function receipt(string $reference)
    {
        $audit = WalletAudit::where('reference', $reference)->firstOrFail();
        return redirect()->route('transactions.show', $audit);
    }

    /**
     * 📤 Export Transactions
     */
    public function export(Request $request, string $format)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return back()->with('error', 'No wallet found.');
        }

        $transactions = $this->buildFilteredQuery($wallet, $request)->get();

        if (in_array($format, ['csv', 'xlsx'])) {
            return Excel::download(
                new TransactionsExport($transactions),
                'transactions_' . now()->format('Ymd_His') . '.' . $format
            );
        }

        if ($format === 'pdf') {
            return Pdf::loadView('transactions.export-pdf', [
                'transactions' => $transactions,
                'user' => $user,
            ])->download('transactions_' . now()->format('Ymd_His') . '.pdf');
        }

        abort(404);
    }

    /**
     * 🔍 Shared Filter Logic
     */
    protected function buildFilteredQuery(Wallet $wallet, Request $request)
    {
        $query = $wallet->audits()->orderByDesc('created_at');

        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%' . $request->reference . '%');
        }

        if ($request->filled('type')) {
            $query->where('source', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }
}
