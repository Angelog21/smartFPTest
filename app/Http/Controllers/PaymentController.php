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
            //get all payments with de name of the payment method
            $payments = Payment::with(['payment_method' => function ($query) {
                $query->select('slug','name');
            }])->paginate(5);

            return response()->json([
                "success" => true,
                "data" => $payments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message'=>"Error in get payments.",
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
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
                ]);
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
                ]);
            }
    
            //search User Account to transfer payment
            $receivedUser = User::where('cpf',$data["cpf"])->first();
    
            if(!$receivedUser) {
                return response()->json([
                    "success"=>false,
                    "message" => "User not found.",
                ]);
            }

            // The percentage is charged depending on the payment method

            $finalAmount = $data["amount"];

            if ($data["payment_method"] == "pix") {

                $finalAmount -= ($data["amount"] * 1.5) / 100;

            }elseif ($data["payment_method"] == "boleto") {

                $finalAmount -= ($data["amount"] * 2) / 100;

            }elseif ($data["payment_method"] == "transferencia_bancaria") {
                
                $finalAmount -= ($data["amount"] * 4) / 100;

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
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message'=>"Error in payment create.",
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}
