<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $methods = [
            [
                'name'=>'Pix',
                'slug'=>'pix'
            ],
            [
                'name'=>'Boleto',
                'slug'=>'boleto'
            ],
            [
                'name'=>'Transferencia bancaria',
                'slug'=>'transferencia_bancaria'
            ]
        ];

        foreach ($methods as $method) {
            PaymentMethod::create([
                'name' => $method["name"],
                'slug' => $method["slug"],
            ]);
        }
    }
}
