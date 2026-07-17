<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePointTransactionRequest;
use App\Http\Requests\UpdatePointTransactionRequest;
use App\Models\PointTransaction;
use App\Models\User;
use App\Services\PointTransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PointTransactionController extends Controller
{
    private  $transactionService;
    public function __construct(PointTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    public function index(Request $request)
    {
        return Inertia::render('Transactions/Index', [
            'transactions' => $this->transactionService->getTransactions($request->only('search', 'action_type', 'user_id')),
            'filters' => $request->only('search', 'action_type', 'user_id'),
            'users' => User::query()->select('id', 'name', 'email')->orderBy('name')->limit(200)->get(),
        ]);
    }

    #create page
    public function create(Request $request)
    {
        return Inertia::render('Transactions/Form', [
            'transaction' => null,
            'selectedUserId' => $request->integer('user_id') ?: null,
            'users' => User::query()->select('id', 'name', 'email', 'total_points')->orderBy('name')->limit(500)->get(),
        ]);
    }

    public function store(StorePointTransactionRequest $request)
    {
        $this->transactionService->create($request->validated());

        return redirect()->route('transactions.index')->with('success', 'Point transaction recorded.');
    }

    public function edit(PointTransaction $transaction)
    {
        return Inertia::render('Transactions/Form', [
            'transaction' => $transaction->load('user'),
            'selectedUserId' => null,
            'users' => User::query()->select('id', 'name', 'email', 'total_points')->orderBy('name')->limit(500)->get(),
        ]);
    }

    public function update(UpdatePointTransactionRequest $request, PointTransaction $transaction)
    {
        $this->transactionService->update($transaction, $request->validated());

        return redirect()->route('transactions.index')->with('success', 'Point transaction updated.');
    }

    public function destroy(PointTransaction $transaction)
    {
        $this->transactionService->delete($transaction);

        return redirect()->route('transactions.index')->with('success', 'Point transaction deleted.');
    }
}
