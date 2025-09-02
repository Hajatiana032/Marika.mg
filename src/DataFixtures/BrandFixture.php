<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrandFixture extends Fixture
{
    public const string  BRAND_PRODUCT_REFERENCE = 'brand-product-';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SluggerInterface $slugger
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/products?limit=0');
        $content = $response->toArray();

        $brands = [];
        foreach ($content['products'] as $item) {
            if ( ! empty($item['brand'])) {
                $brandName = trim($item['brand']);
                $brands[$brandName] = $brandName;
            }
        }

        foreach ($brands as $name) {
            $brand = new Brand();
            $brand->setName($name)
                ->setSlug($this->slugger->slug($name)->lower());
            $manager->persist($brand);
            $this->addReference(self::BRAND_PRODUCT_REFERENCE.$brand->getSlug(), $brand);
        }

        $manager->flush();
    }
}
