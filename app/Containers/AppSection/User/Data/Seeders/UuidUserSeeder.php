<?php

namespace App\Containers\AppSection\User\Data\Seeders;

use App\Ship\Parents\Seeders\Seeder as ParentSeeder;
use App\Containers\AppSection\User\Models\UserUUID;

class UuidUserSeeder extends ParentSeeder
{
    public function run()
    {
        $time = time();
        //Loop from j = 1 to 1000
        for ($j = 1; $j <= 1000000; $j++) {
            UserUUID::factory()->create();
        }
        dump('Total time: ' . $total_time = time() - $total_time);
    }
}
