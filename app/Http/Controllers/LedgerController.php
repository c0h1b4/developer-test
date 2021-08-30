<?php

namespace App\Http\Controllers;

class LedgerController extends Controller
{
    /**
     * Create a instance of LedgerController
     * @return void
     */
/**
 * @OA\Schema(
 *    schema="Ledger",
 *   anyOf={
 *    @OA\Schema(ref="#/components/schemas/Deposit"),
 *    @OA\Schema(ref="#/components/schemas/Expense"),
 * })
 * @OA\GET(
 * path="/api/balance",
 * summary="Account balance information",
 * description="Detailed account balance information",
 * operationId="index",
 * tags={"ledger"},
 * security={ {"bearer": {} }},
 * @OA\Response(
 *   response=200,
 *   description="Success",
 *   @OA\JsonContent(
 *    @OA\Property(property="ledger", type="array",
 *      @OA\Items(ref="#/components/schemas/Ledger"),
 *      ),
 *
 *    )
 * ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *    )
 * )
 * )
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
        $ledger = array('ledger' => $deposits->merge($expenses));
        return response()->json($ledger);
    }
}
