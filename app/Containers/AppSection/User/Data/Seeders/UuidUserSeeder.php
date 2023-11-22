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
        for ($j = 1; $j <= 0; $j++) {
            UserUUID::factory()->create();
        }
        dump('Elapsed time: ' . $time = time() - $time);
    }
}
