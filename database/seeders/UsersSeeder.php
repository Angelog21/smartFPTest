<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use DateTime;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Root User',
            'cpf' => '26530665',
            'password' => bcrypt('secret*123'),
            'email' => 'root@smart.com',
            'currency' => 100000
        ]);
    }
}
