<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SubadquirenteType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin1234'),
            ],
        );

        $admin->wallet()->updateOrCreate(
            ['user_id' => $admin->getKey()],
            ['balance' => 10000.00],
        );

        $admin->subadquirentes()->updateOrCreate(
            [
                'user_id' => $admin->getKey(),
                'subadquirente' => SubadquirenteType::SUBADQ_A->value,
            ],
            ['is_active' => true],
        );

        User::factory(5)->withWallet()->withSubadquirente()->create();
    }
}
