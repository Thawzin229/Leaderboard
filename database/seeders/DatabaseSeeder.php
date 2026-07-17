<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PointTransactionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $users = collect([
            ['name' => 'Kyaw Kyaw', 'email' => 'kyaw@example.com'],
            ['name' => 'Khine Khine', 'email' => 'khine@example.com'],
            ['name' => 'Zaw Zaw', 'email' => 'zaw@example.com'],
            ['name' => 'Nilar', 'email' => 'nilar@example.com'],
            ['name' => 'Yadana', 'email' => 'yadana@example.com'],
            ['name' => 'Aung Kyaw', 'email' => 'aungkyaw@example.com'],
            ['name' => 'Mya Thandar', 'email' => 'myathandar@example.com'],
            ['name' => 'Zaw Min', 'email' => 'zawmin@example.com'],
            ['name' => 'Su Su Hlaing', 'email' => 'susuhlaing@example.com'],
            ['name' => 'Kyaw Zaw', 'email' => 'kyawzaw@example.com'],
            ['name' => 'Hnin Wai', 'email' => 'hninwai@example.com'],
            ['name' => 'Mya Mya', 'email' => 'myama@example.com'],
        ])->map(fn (array $data) => User::factory()->create([
            ...$data,
            'password' => Hash::make('password'),
        ]));

        auth()->login($admin);

        $service = app(PointTransactionService::class);

        ## base on my understanding, the starting balance for each user is set to 100 points which is equal to the initial point allocation or total points.But you will face a problem if you try to deduct points from a user who is in earn state and there is not enough points and you will need to set total point and initial point unequally in order to test the functionality of 'earn' to 'deduct'.

        $service->create([
            'user_id' => $users[0]->id,
            'points' => 120,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[1]->id,
            'points' => 100,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[2]->id,
            'points' => 100,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[3]->id,
            'points' => 90,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[4]->id,
            'points' => 75,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[5]->id,
            'points' => 60,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[6]->id,
            'points' => 50,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[7]->id,
            'points' => 80,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[8]->id,
            'points' => 125,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
        $service->create([
            'user_id' => $users[9]->id,
            'points' => 65,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);
         $service->create([
            'user_id' => $users[10]->id,
            'points' => 26,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);

        $service->create([
            'user_id' => $users[11]->id,
            'points' => 30,
            'action_type' => 'Earn',
            'description' => 'starting balance',
        ]);

        auth()->logout();
    }
}