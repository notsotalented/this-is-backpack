<?php

namespace App\Containers\AppSection\User\Data\Seeders;

use App\Ship\Parents\Seeders\Seeder as ParentSeeder;
use App\Containers\AppSection\User\Models\UserUUID;

class UuidUserSeeder extends ParentSeeder
{
    public function run()
    {
        $total_time = time();
        for ($i = 1; $i <= 4; $i++) {
            $time = time();
            //Loop from j = 1 to 250000
            for ($j = 1; $j<= 250000; $j++) {
                UserUUID::factory()->create();
            }
            dump('Elapsed time' . $i . ': '. $time = time() - $time);
        }
        dump('Total time: ' . $total_time = time() - $total_time);
    }
}
