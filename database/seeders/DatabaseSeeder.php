<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Price;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Vehicletype;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as ModelsRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
        ]);

        $role = ModelsRole::create([
            'name' => 'Owner'
        ]);

        $user->assignRole([$role->id]);

        $user2 = User::create([
            'name' => 'manager',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('manager123'),
        ]);

        $role2 = ModelsRole::create([
            'name' => 'Manager'
        ]);

        $user2->assignRole([$role2->id]);

        $user3 = User::create([
            'name' => 'customer service',
            'email' => 'customerservice@gmail.com',
            'password' => bcrypt('customerservice123'),
        ]);

        $role3 = ModelsRole::create([
            'name' => 'Customer Service'
        ]);

        $user3->assignRole([$role3->id]);

        $user4 = User::create([
            'name' => 'korwil',
            'email' => 'korwil@gmail.com',
            'password' => bcrypt('korwil123'),
        ]);

        $role4 = ModelsRole::create([
            'name' => 'Korwil'
        ]);

        $user4->assignRole([$role4->id]);

        $user5 = User::create([
            'name' => 'sub korwil',
            'email' => 'subkorwil@gmail.com',
            'password' => bcrypt('subkorwil123'),
        ]);

        $role5 = ModelsRole::create([
            'name' => 'Sub Korwil'
        ]);

        $user5->assignRole([$role5->id]);

        Price::create([
            'code' => 'MJL',
            'city' => 'Majalengka',
            'price' => '1500',
            'description' => '',
            'status' => 'Active',
            'morning_busy_start' => '11:11',
            'morning_busy_end' => '11:11',
            'morning_busy_price' => '500',
            'afternoon_busy_start' => '11:11',
            'afternoon_busy_end' => '11:11',
            'afternoon_busy_price' => '500',
            'rainy_status' => 'Active',
            'rainy_price' => '100'
        ]);

        Vehicletype::create([
            'image' => 'motor.jpg',
            'vehicletype' => 'motor',
            'type' => 'motor',
            'passenger' => '1',
            'price' => '500',
            'status' => 'Active'
        ]);
    }
}
