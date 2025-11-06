<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert a record into company_infos table
        DB::table('company_infos')->insert([
            'id' => 1,
            'name' => 'Families',
            'address' => '46,Abdul Hameed Street,Colombo 12.',
            'phone' => '0750469180',
            'phone2' => '0776771935',
            'email' => 'familiesgroup6@gmail.com',
            'website' => '',
            'logo' => '/images/jaan_logo.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
