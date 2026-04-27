<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['cpf' => '12345678900'],
            [
                'name' => 'Administrador',
                'email' => 'admin1@teste.com',
                'password' => Hash::make('12345678'),
                'tipo' => 'admin',
                'status' => 'aprovado',
            ]
        );

        User::updateOrCreate(
            ['cpf' => '22222222222'],
            [
                'name' => 'Preceptor Teste',
                'email' => 'preceptor@teste.com',
                'password' => Hash::make('12345678'),
                'tipo' => 'preceptor',
                'status' => 'aprovado',
            ]
        );

        User::updateOrCreate(
            ['cpf' => '33333333333'],
            [
                'name' => 'Residente Teste',
                'email' => 'residente@teste.com',
                'password' => Hash::make('12345678'),
                'tipo' => 'residente',
                'status' => 'aprovado',
            ]
        );
    }
}