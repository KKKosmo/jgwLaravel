<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MainTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 35; $i++) {
            DB::table('main')->insert([
                'dateInserted' => $faker->dateTimeBetween('-1 year', 'now'),
                'name' => $faker->name,
                'pax' => $faker->randomNumber(2),
                'vehicle' => $faker->randomNumber(2),
                'pets' => $faker->boolean,
                'videoke' => $faker->boolean,
                'partial_payment' => $faker->randomFloat(2, 0, 100),
                'full_payment' => $faker->randomFloat(2, 0, 100),
                'paid' => $faker->boolean,
                'checkIn' => $faker->date,
                'checkOut' => $faker->date,
                'room' => $faker->randomElement(['J', 'G', 'K1', 'K2', 'A', 'E']),
                'user' => $faker->userName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
