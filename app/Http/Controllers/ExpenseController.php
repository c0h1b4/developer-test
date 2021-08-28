<?php

namespace App\Http\Controllers;

use App\Models\Expense;

class ExpenseController extends Controller
{
    /**
     * Create a instance of ExpenseController
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function expense() {

        if (auth()->user()->admin == true) {
            return response()->json([
                'message' => 'Admins can’t be a customer'
        ], 422);
        }

        if (request('amount') <= 0) {
            return response()->json([
                'message' => 'Amount cannot be zero or negative'
            ], 422);
        }

        if (auth()->user()->balance < request('amount')) {
            return response()->json([
                'message' => 'Insufficient funds'
            ], 422);
        }

        $expense = new Expense();
        $expense->user_id = auth()->user()->id;
        $expense->type = 'expense';
        $expense->amount = request('amount');
        $expense->description = request('description');
        $expense->user->balance -= $expense->amount;
        $expense->save();
        $expense->user->save();

        return response()->json([
            'message' => 'Expense added successfully'
        ], 200);

    }
}
