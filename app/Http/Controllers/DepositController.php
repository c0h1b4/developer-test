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

/**
 *  @OA\Schema(
 *  schema="Deposit",
 *  allOf={
 *    @OA\Schema(
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="type", type="string", format="string"),
 *     @OA\Property(property="updated_at", type="string", format="string"),
 *     @OA\Property(property="created_at", type="string", format="string"),
 *     @OA\Property(property="amount", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="description", type="string", format="string"),
 *     @OA\Property(property="status", type="string", format="string"),
 *     @OA\Property(property="documentUrl", type="string", format="string"),
 *     @OA\Property(property="approved_by", type="integer", format="int64"),
 *   )}
 * ),
 * @OA\Post(
 * path="/api/deposit",
 * summary="Add deposit",
 * description="Add a deposit to balance and register the deposit. Must send the image of the check. The status of the deposit is Pending since it is waiting for approval.",
 * operationId="deposit",
 * tags={"deposit"},
 * security={ {"bearer": {} }},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass deposit details and document image",
 *    @OA\JsonContent(
 *       required={"amount","description"},
 *       @OA\Property(property="amount", type="integer", format="int64", example=10000),
 *       @OA\Property(property="description", type="string", format="text", example="Purchase description"),
 *       @OA\Property(property="image", type="string", format="base64", example="data:image/jpeg;base64"),
 *    ),
 * ),
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="Deposit added successfully."),
 *        @OA\Property(property="deposit", type="object", ref="#/components/schemas/Deposit"),
 *     )
 *  ),
 * )
 */

    public function __construct() {
        $this->middleware('auth:api');
    }

/**
 * @OA\GET(
 * path="/api/deposit/{id}",
 * summary="Get deposit",
 * description="Get deposit to see the document image",
 * operationId="deposit",
 * tags={"deposit"},
 * security={ {"bearer": {} }},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass deposit details and document image",
 * ),
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="Deposit added successfully."),
 *        @OA\Property(property="deposit", type="object", ref="#/components/schemas/Deposit"),
 *     )
 *  ),
 * @OA\Response(
 *    response=422,
 *    description="Insufficient funds",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Insufficient funds")
 *        )
 *     )
 * )
 */

    public function index() {
        $deposit = Deposit::find(request('id'));
        return response()->json($deposit);
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
            'message' => 'Deposit added successfully.',
            'deposit' => $deposit
        ], 201);
    }

/**
 * @OA\GET(
 * path="/api/pending",
 * summary="Get pending deposits",
 * description="Get list of all pending deposits (only for admins)",
 * operationId="deposit",
 * tags={"deposit"},
 * security={ {"bearer": {} }},
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="Pending Deposits"),
 *        @OA\Property(property="deposits", type="array",
 *          @OA\Items(ref="#/components/schemas/Deposit"),
 *        ),
 *     )
 *  ),
 * )
 */

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

/**
 * @OA\GET(
 * path="/api/approveDeposit/{id}",
 * summary="Approve pending deposit with id",
 * description="Approve a pending deposit with id (only for admins) then add value to user balance",
 * operationId="deposit",
 * tags={"deposit"},
 * security={ {"bearer": {} }},
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="Deposit approved."),
 *        @OA\Property(property="deposits", type="array",
 *          @OA\Items(ref="#/components/schemas/Deposit"),
 *        ),
 *     )
 *  ),
 * )
 */

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
        $deposit->approved_by = auth()->user()->id;
        $deposit->save();
        $deposit->user->balance += $deposit->amount;
        $deposit->user->save();

        return response()->json([
            'message' => 'Deposit approved.',
            'deposit' => $deposit,
        ], 201);

    }

/**
 * @OA\GET(
 * path="/api/rejectDeposit/{id}",
 * summary="Reject pending deposit with id",
 * description="Reject a pending deposit with id (only for admins) DON'T add value to user balance",
 * operationId="deposit",
 * tags={"deposit"},
 * security={ {"bearer": {} }},
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="Deposit rejected."),
 *        @OA\Property(property="deposits", type="array",
 *          @OA\Items(ref="#/components/schemas/Deposit"),
 *        ),
 *     )
 *  ),
 * )
 */

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
