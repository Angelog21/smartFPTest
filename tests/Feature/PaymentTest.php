<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    /**
     * test to create payment is successfully
     */
    public function test_create_payment_successful(): void
    {

        $userReceiver = User::factory()->create();

        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/api/payments',[
            "cpf"=>$userReceiver->cpf,
            "amount" => 1000,
            "payment_method"=>"pix",
            "description" => "Description test"
        ]);

        $response->assertStatus(200);
    }

    /**
     * test create payment without jwt token
     */
    public function test_create_payment_without_jwt_token(): void
    {
        $response = $this->get('/api/payments',[
            "cpf"=>"111111",
            "amount" => 90000000000000,
            "payment_method"=>"pix",
            "description" => "Description test"
        ]);

        $response->assertStatus(401)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test creating a payment with missing field
     */
    public function test_create_payment_with_missing_field(): void
    {
        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/api/payments',[
            "amount" => 9900,
        ]);

        $response->assertStatus(400)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test creating a payment with an amount greater than the available balance
     */
    public function test_create_payment_with_amount_greather_available_balance(): void
    {

        $userReceiver = User::factory()->create();

        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/api/payments',[
            "cpf"=>$userReceiver->cpf,
            "amount" => 90000000000000,
            "payment_method"=>"pix",
            "description" => "Description test"
        ]);

        $response->assertStatus(400)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test creating a payment with an invalid payment method
     */
    public function test_create_payment_with_invalid_payment_method(): void
    {

        $userReceiver = User::factory()->create();

        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/api/payments',[
            "cpf"=>$userReceiver->cpf,
            "amount" => 1000,
            "payment_method"=>"creditCard",
            "description" => "Description test"
        ]);

        $response->assertStatus(400)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test creating a payment but user not found
     */
    public function test_create_payment_but_user_not_found(): void
    {
        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/api/payments',[
            "cpf"=>"9638525897414",
            "amount" => 1000,
            "payment_method"=>"creditCard",
            "description" => "Description test"
        ]);

        $response->assertStatus(404)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test show all payments
     */
    public function test_get_all_payments_successful(): void
    {
        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->get('/api/payments?page=1');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'data'
        ]);
    }

    /**
     * test show all payments without jwt token
     */
    public function test_get_all_payments_without_jwt_token(): void
    {
        $response = $this->get('/api/payments?page=1');

        $response->assertStatus(401)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test show detail
     */
    public function test_get_detail_payment_successful(): void
    {
        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $payment = Payment::first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->get("/api/payments/{$payment->id}");

        $response->assertStatus(200)
        ->assertJsonStructure([
            'success', 'data'
        ]);
    }

    /**
     * test get detail payment without jwt token
     */
    public function test_get_detail_payment_without_jwt_token(): void
    {

        $payment = Payment::first();

        $response = $this->get("/api/payments/{$payment->id}");

        $response->assertStatus(401)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }

    /**
     * test get detail payment not found
     */
    public function test_get_detail_payment_not_fount(): void
    {

        $user = User::where('email', "root@smart.com")->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->get("/api/payments/1231213154");

        $response->assertStatus(404)
        ->assertJsonStructure([
            'success', 'message'
        ]);
    }
}
