<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'J&T Express',
            'code' => 'JNT',
            'contact_person' => 'Customer Service J&T',
            'email' => 'cs@jnt.co.id',
            'phone' => '628xxxxxxxxxx',
            'address' => 'Jakarta, Indonesia',
        ]);
    }
}
