<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = file_get_contents(__DIR__.'/data/city.json');
        $json = json_decode($data, true);
        foreach ($json as $value) {
            $city = new City();
            $city->setName($value['city'].' ('.$value['admin_name'].')')
                ->setShippingCost(mt_rand(3000, 20000));

            $manager->persist($city);
        }
        $manager->flush();
    }
}
