<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Admin::factory()->create();

         $testUser = \App\Models\User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
    
        \App\Models\User::factory(10)->create()->each(function ($user) {
            
           
            \App\Models\Attendance::factory(20)->create([
                'user_id' => $user->id 
            ])->each(function ($attendance) {
                
               
                \App\Models\BreakTime::factory()->create([
                    'attendance_id' => $attendance->id 
                ]);
                
            });
        });
        
      
    }
}
