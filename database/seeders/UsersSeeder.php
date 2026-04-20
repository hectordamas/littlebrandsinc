<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new \App\Models\User();
            $user->name = 'Coach ' . ($i + 1);
            $user->email = 'coach' . ($i + 1) . '@example.com';
            $user->password = bcrypt('password');
            $user->whatsapp = '123456789' . $i;
            $user->dial_code = '+1';
            $user->role = 'Coach';
            $user->save();
        }

        for ($i = 0; $i < 10; $i++) {
            $user = new \App\Models\User();
            $user->name = 'Administrador ' . ($i + 1);
            $user->email = 'admin' . ($i + 1) . '@example.com';
            $user->password = bcrypt('password');
            $user->whatsapp = '123456789' . $i;
            $user->dial_code = '+1';
            $user->role = 'Administrador';
            $user->save();
        }

        for ($i = 0; $i < 10; $i++) {
            $user = new \App\Models\User();
            $user->name = 'Padre ' . ($i + 1);
            $user->email = 'padre' . ($i + 1) . '@example.com';
            $user->password = bcrypt('password');
            $user->role = 'Padre';
            $user->whatsapp = '123456789' . $i;
            $user->dial_code = '+1';
            $user->save();
        }

        $user = new \App\Models\User();
        $user->name = "Héctor Damas";
        $user->email = "hectorgabrieldm@hotmail.com";
        $user->password = bcrypt("alinware98_");
        $user->role = "Administrador";
        $user->dial_code = "+58";
        $user->whatsapp = "4241930033";
        $user->save();

        $user = new \App\Models\User();
        $user->name = "Mauricio Andrade";
        $user->email = "mauro99mas@gmail.com";
        $user->password = bcrypt("123456789");
        $user->role = "Administrador";
        $user->dial_code = "+58";
        $user->whatsapp = "424123456789";
        $user->save();
    }
}
