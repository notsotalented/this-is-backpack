<?php

namespace App\Containers\AppSection\User\Data\Seeders;

use App\Ship\Parents\Seeders\Seeder as ParentSeeder;
use App\Containers\AppSection\User\Models\UserUUID;
use App\Containers\AppSection\User\Models\User;

class NormalUserSeeder extends ParentSeeder
{
    public function run()
    {
        //Loop from i = 1 to 1000
        for ($i = 1; $i <= 1000; $i++) {
            UserUUID::factory()->count(1000)->create();
            dump($i);
        }

        //Loop from j = 1 to 1000
        for ($j = 1; $j<= 1000; $j++) {
            User::factory()->count(1000)->create();
            dump($j);
        }
    }
}
