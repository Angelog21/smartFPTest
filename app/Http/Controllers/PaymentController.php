<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class PaymentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth.api');
    }
    /**
     * Display a listing of the payments.
     */
    public function index()
    {
        try {
            //get all payments with the name of the payment method
            $payments = Payment::with(['payment_method' => function ($query) {

                $query->select('slug','name');

            }])->select('id','cpf','payment_ms','amount','status','created_at')->paginate(5);

            return response()->json([
                "success" => true,
                "data" => $payments
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message'=>"Error in get payments.",
            ],500);
        }
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        try {
            //validate the request and response with the exception, It is not in the main try because it is better to handle internal errors with a default message than to display all error messages
            try {
                $request->validate([
                    'cpf' => 'required|max:15',
                    'amount' => 'required|numeric',
                    'payment_method' => 'required',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    "success"=>false,
                    "message" => $e->getMessage(),
                ],400);
            }

            $data = $request->all();

            //Open transaction for creating payment and update user currency
            DB::beginTransaction();

            //verify if the origin user has less money than he wants to transfer
            $originUser = auth()->user();

            if ($originUser->currency < $data["amount"]) {
                return response()->json([
                    "success"=>false,
                    "message" => "Insufficient money for transfer.",
                ],400);
            }

            //search User Account to transfer payment
            $receivedUser = User::where('cpf',$data["cpf"])->first();

            if(!$receivedUser) {
                return response()->json([
                    "success"=>false,
                    "message" => "User not found.",
                ],404);
            }

            // The percentage is charged depending on the payment method

            $finalAmount = $data["amount"];

            if ($data["payment_method"] == "pix") {

                $finalAmount -= ($data["amount"] * 1.5) / 100;

            }elseif ($data["payment_method"] == "boleto") {

                $finalAmount -= ($data["amount"] * 2) / 100;

            }elseif ($data["payment_method"] == "transferencia_bancaria") {

                $finalAmount -= ($data["amount"] * 4) / 100;

            }else {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid payment method."
                ],400);
            }

            $payment = Payment::create([
                'origin_id' => $originUser->id,
                'receiver_id' => $receivedUser->id,
                'client_name' => $receivedUser->name,
                'cpf' => $data["cpf"],
                'description' => isset($data["description"]) ? $data["description"] : '',
                'payment_ms' => $data["payment_method"],
                'amount' => $finalAmount,
                'status' => 'pendiente',
                'payment_date' => Carbon::now(),
            ]);

            $originUser->currency -= $payment->amount;
            $originUser->save();

            DB::commit();

            return response()->json([
                "success"=>true,
                "data"=>$payment,
                "message"=>'Payment has been created successfully.'
            ],200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message'=>"Error in payment create.",
            ],500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(string $id)
    {
        try {
            if (!isset($id)) {
                return response()->json([
                    "success" => false,
                    "message" => "Missing id of payment"
                ],400);
            }

            //get detail payment
            $payment = Payment::whereId($id)->select(
                    'id',
                    'origin_id',
                    'receiver_id',
                    'payment_ms',
                    'amount',
                    'status',
                    'payment_date',
                    'description',
                    'created_at'
                )->first();


            if (!$payment) {
                return response()->json([
                    "success" => false,
                    "message" => "payment not found"
                ],404);
            }

            //If the payment is found, the relationships are loaded to optimize the query
            $payment->load([
                'payment_method' => function ($query) {

                    $query->select('slug','name');

                },
                'origin' => function ($query) {

                    $query->select('id','name','email','cpf');

                },
                'receiver' => function ($query) {

                    $query->select('id','name','email','cpf');

                }
            ]);

            return response()->json([
                "success" => true,
                "data" => $payment
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message'=>"Error in get detail payment.",
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function processPayment(Request $request)
    {
        try {
            try {
                $request->validate([
                    'paymentId' => 'required|string'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    "success"=>false,
                    "message" => $e->getMessage(),
                ]);
            }

            $payment = Payment::whereId($request->paymentId)->first();

            //validate if payment exists
            if (!$payment) {
                return response()->json([
                    "success" => false,
                    "message" => "payment not found"
                ],404);
            }

            //Validate if payment isn't pending
            if ($payment->status != 'pendiente') {

                return response()->json([
                    "success"=>false,
                    "message" => "Payment status is {$payment->status}",
                ],400);

            }

            $isProcessing = rand(1,100);

            //If the number is between 1 and 70, it is processed successfully
            if($isProcessing <= 70) {

                //The recipient's balance is added
                $receiverUser = $payment->load('receiver');
                $receiverUser->receiver->currency += $payment->amount;
                $receiverUser->receiver->save();

                $payment->status = 'pagado';
                $payment->save();

                return response()->json([
                    "success" => true,
                    "message" => "The payment has been successfully processed, the money has been added to the recipient's account"
                ],200);

            } else {
                //if its between 71 and 100, it is failed.

                //The origin's balance is returned
                $originUser = $payment->load('origin');
                $originUser->origin->currency += $payment->amount;
                $originUser->origin->save();

                $payment->status = 'fallido';
                $payment->save();

                return response()->json([
                    "success" => false,
                    "message" => "Payment processing has failed, the money has been returned to the origin account"
                ],200);
            }


        } catch (\Exception $e) {
            return response()->json([
                "success"=>false,
                "message" => "Error processing payment",
            ],500);
        }
    }
}
