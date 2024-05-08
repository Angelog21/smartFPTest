<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth.api', ['except' => ['login','register']]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            $credentials["email"] = strtolower($credentials["email"]);

            $user = User::where('email',$credentials["email"])->first();

            if($user){
                if($user->active == 0){
                    return response()->json([
                        "success"=>false,
                        "message"=>"User is inactive."
                    ]);
                }
            }else{
                return response()->json([
                    "success"=>true,
                    "message"=>"User not found."
                ]);
            }


            //if the token is not created
            $token = Auth::attempt($credentials);
            if (!$token) {
                if ($user->max_attempts >= 5) {
                    $user->active == 0;

                    return response()->json([
                        "success"=>true,
                        "message"=>"User has been inactivated for maximum login attempt."
                    ]);
                }

                $user->max_attempts++;
                $user->save();

                //max attempt to login is 5
                $attemps = 5 - $user->max_attempts;

                return response()->json([
                    "success"=>true,
                    "message"=>"The credentials are incorrect has a total of {$attemps} attempts."
                ]);
            }


            $user->max_attempts = 0;
            $user->save();

            $user = Auth::user();
            return response()->json([
                "data"=>$user,
                "token"=>$token
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error server']);
        }

    }

    public function register(Request $request){
        try {
            try {
                $request->validate([
                    'name' => 'required|max:100',
                    'cpf' => 'required|max:15',
                    'email' => 'required|email',
                    'password' => 'required',
                    'currency' => 'required',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    "success"=>false,
                    "message" => $e->getMessage(),
                ]);
            }

            $data = $request->all();

            if(User::where('email',strtolower($data["email"]))->exists()){
                return response()->json([
                    "success"=>false,
                    "message" => "User is exists.",
                ]);
            }

            $user = User::create([
                'name' => $data['name'],
                'cpf' => $data['cpf'],
                'email' => strtolower($data['email']),
                'password' => bcrypt($data['password']),
                'currency' => $data['currency'],
            ]);

            return response()->json([
                "success"=>true,
                "data"=>$user,
                "message"=>"User registered successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success"=>false,
                "message"=>"Server error."
            ]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }
}
