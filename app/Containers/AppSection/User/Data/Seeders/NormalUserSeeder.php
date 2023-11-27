<?php

namespace App\Containers\AppSection\User\Data\Seeders;

use App\Ship\Parents\Seeders\Seeder as ParentSeeder;
use App\Containers\AppSection\User\Models\User;

class NormalUserSeeder extends ParentSeeder
{
    public function run()
    {
        $time = time();
        //Loop from j = 1 to 1000000
        for ($j = 1; $j <= 1000000; $j++) {
            User::factory()->create();
        }
        dump('Elapsed time: ' . $time = time() - $time);

    }
}
