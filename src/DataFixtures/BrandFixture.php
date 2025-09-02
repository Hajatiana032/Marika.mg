<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrandFixture extends Fixture
{
    public const string  BRAND_PRODUCT_REFERENCE = 'brand-product-';

    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
