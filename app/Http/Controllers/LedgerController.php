<?php

namespace App\Http\Controllers;

class LedgerController extends Controller
{
    /**
     * Create a instance of LedgerController
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function index() {
        $user = auth()->user();

        if ($user->admin == true) {
            return response()->json([
                'message' => 'Admins can’t be a customer'
        ], 422);
        }

        $deposits = $user->deposits;
        $expenses = $user->expenses;
        $ledger = $deposits->merge($expenses);
        return response()->json($ledger);
    }
}
