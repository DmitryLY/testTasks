<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use Faker\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\TestTasks\TestTasks;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        TestTasks::initTables();

        $users = [
            [ 'Иван Антонов' , 'ivan@test-r.ru', '2345'],
            [ 'Ирина Фёдорова' , 'irina@test-r.ru', '1234'],
            [ 'Елена Андреева' , 'elena@test-r.ru', '9876'],
        ];

        foreach( $users as &$user ){
            $id = DB::table('users')->insertGetId([
                'name' => $user[0],
                'email' => $user[1],
                'password' => md5($user[2]),
            ]);
            $user[] = $id;
        }

        unset( $user );

        for( $i = 0; $i < 25; $i++  ){
            $faker = Factory::create('ru_RU');

            DB::table('tasks')->insert([
                'responsible_user_id' => Arr::random($users)[3],
                'creator_user_id' => Arr::random($users)[3],
                'complete_at' => $faker->dateTimeBetween($startDate = '0 days', $endDate = '60 days')->format('Y-m-d h:i:s'),
                'created_at' => $faker->dateTimeBetween($startDate = '-60 days', $endDate = '60 days')->format('Y-m-d h:i:s'),
                'title' => implode( ' ', array_slice( explode( ' ', $faker->realText() ), 0, rand(2, 3) ) ),
                'description' => $faker->realText(),
            ]);

        }
    }
}
