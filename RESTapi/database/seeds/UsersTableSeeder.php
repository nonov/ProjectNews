<?php

use Illuminate\Database\Seeder;
use \App\User;

 
class UsersTableSeeder extends Seeder {
 
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    DB::table('users')->delete();
 
        User::create(array(
            'name'          => 'christian',
            'email'         => 'christian@kocke.fr',
            'password'      => Hash::make('password') //hashes our password nicely for us
 
        ));
 
    }
 
}