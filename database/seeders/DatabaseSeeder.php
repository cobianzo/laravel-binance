<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        
        $user = new \App\Models\User();
        $user->password = \Hash::make('WeL0veTil0v?');
        $user->email = 'cobianzo@yahoo.es';
        $user->name = 'Alvaro';
        $user->b_key = '';
        $user->b_private = '';
        $user->save();
        // same as
        // DB::table('users')->insert(['name'=>'MyUsername','email'=>'thisis@myemail.com','password'=>Hash::make('123456')])

    }
}
