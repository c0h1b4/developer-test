<?php

namespace App\Http\Controllers;

use App\Models\Expense;

class ExpenseController extends Controller
{
    /**
     * Create a instance of ExpenseController
     * @return void
     */

/**
 *  @OA\Schema(
 *  schema="Expense",
 *  allOf={
 *    @OA\Schema(
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="type", type="string", format="string"),
 *     @OA\Property(property="updated_at", type="string", format="string"),
 *     @OA\Property(property="created_at", type="string", format="string"),
 *     @OA\Property(property="amount", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="description", type="integer", format="int64"),
 *   )},
 * )
 * @OA\Post(
 * path="/api/expense",
 * summary="Add purchase",
 * description="Add a debit to balance and register the expense. If value is less than balance, throw error.",
 * operationId="expense",
 * tags={"expense"},
 * security={ {"bearer": {} }},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass purchase details",
 *    @OA\JsonContent(
 *       required={"amount","description"},
 *       @OA\Property(property="amount", type="integer", format="int64", example=10000),
 *       @OA\Property(property="description", type="string", format="text", example="Purchase description"),
 *    ),
 * ),
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="Expense added successfully"),
 *     )
 *  ),
 * @OA\Response(
 *    response=422,
 *    description="Insufficient funds",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Insufficient funds")
 *        )
 *     )
 * ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *    )
 *   )
 * )
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
