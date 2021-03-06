<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class UserSeeder extends Seeder{
    public function run(){
        $users = array(
            array(
                'username' => 'system',
                'name' => 'system',
            ),
        );
        
        foreach($users as $user){
            $u = User::where('username', $user['username'])->first();
            if (!$u){
                $u = new User;
                $u->username = $user['username'];
                $u->name = $user['name'];
                $u->created_timestamp_utc = time();
                $u->updated_timestamp_utc = time();
                $u->save();
            }
        }
    }
}