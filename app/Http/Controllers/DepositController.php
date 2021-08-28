<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * Create a instance of DepositController
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function deposit(Request $request) {

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

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $profleTitle = time().'.'.$request->image->extension();
        $request->image->storeAs('public', $profleTitle);

        $deposit = new Deposit();
        $deposit->user_id = auth()->user()->id;
        $deposit->type = 'deposit';
        $deposit->amount = $request->amount;
        $deposit->description = $request->description;
        $deposit->status = 'pending';
        $deposit->documentUrl = 'http://localhost:8000/storage/' . $profleTitle;
        $deposit->save();

        return response()->json([
            'message' => 'Deposit request has been sent.',
            'deposit' => $deposit
        ], 201);
    }

    public function pending() {
        if (auth()->user()->admin != true) {
            return response()->json([
                'message' => 'You are not admin'
        ], 422);
        }
        $deposits = Deposit::where('status', 'pending')->get();
        return response()->json([
            'message' => 'Pending Deposits',
            'deposits' => $deposits
        ], 200);
    }

    public function approveDeposit() {

        if (auth()->user()->admin != true) {
            return response()->json([
                'message' => 'You are not authorized to approve this deposit.'
            ], 401);
        }

        $deposit = Deposit::find(request('id'));

        if ($deposit->status != 'pending') {
            return response()->json([
                'message' => 'Deposit request has already been processed.'
            ], 422);
        }

        $deposit->status = 'approved';
        $deposit->save();
        $deposit->user->balance += $deposit->amount;
        $deposit->user->save();

        return response()->json([
            'message' => 'Deposit approved.',
            'deposit' => $deposit,
        ], 201);

    }

    public function rejectDeposit() {

        if (auth()->user()->admin != true) {
            return response()->json([
                'message' => 'You are not authorized to reject this deposit.'
            ], 401);
        }

        $deposit = Deposit::find(request('id'));

        if ($deposit->status != 'pending') {
            return response()->json([
                'message' => 'Deposit request has already been processed.'
            ], 422);
        }

        $deposit->status = 'reject';
        $deposit->save();

        return response()->json([
            'message' => 'Deposit rejected.',
            'deposit' => $deposit,
        ], 201);

    }

}
