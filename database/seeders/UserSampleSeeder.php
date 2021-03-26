<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory([
            'email' => 'user@user.com',
        ])->create();

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'sample-token',
            // 1|klvN2VKKhJi06oigREYDMOtJAyKbyZAZpbaQxvvM
            'token' => 'eb3a39b61fc15d85e607be4b056d8125bb0ab313a9492079b10f8d2468ee77d9',
            'abilities' => '["*"]'
        ]);
    }
}
