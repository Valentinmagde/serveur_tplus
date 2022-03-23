<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'uniqueId' => '5efe38b32b8ee',
            'username' => 'admin',
            'hashedPassword' => '$2y$10$uQAo1qhlVG0TIs1MeoFD2u0J.EqpLrkyWSUDUCCQdZL9dWysf7Jsi',
            'firstName' =>'The',
            'lastName' => 'Admin',
            'role' => 0,
            'roleTitle' => 'Admin',
            'date_creation' => 1592308800,
        ]);

        DB::table('users')->insert([
            'uniqueId' => '5f06e32e23553',
            'username' => 'support',
            'hashedPassword' => '$2y$10$z9SSU99OQIfizsDLT8ogsOi85w.WzYx1i6STbrK2TtPtI1wYciaVi',
            'firstName' =>'The',
            'lastName' => 'Support',
            'role' => 1,
            'roleTitle' => 'Support',
            'date_creation' => 1594286639,
        ]);
    }
}