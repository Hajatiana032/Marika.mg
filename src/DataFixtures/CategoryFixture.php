<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CategoryFixture extends Fixture
{
    public const string CATEGORY_PRODUCT_REFERENCE = 'category-product-';

    public function __construct(private readonly HttpClientInterface $client)
    {
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
        $response = $this->client->request('GET', 'https://dummyjson.com/products/categories');
        $categories = $response->toArray();
        /**
         * Excluding categories that are not relevant for the application.
         */
        $excludedCategories = [
            'furniture',
            'groceries',
            'home-decoration',
            'kitchen-accessories',
            'motorcycle',
            'vehicle',
        ];

        /**
         * Filtering out excluded categories.
         */
        $filteredCategories = array_filter($categories, fn($data) => ! in_array($data['slug'], $excludedCategories));

        foreach ($filteredCategories as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);

            $manager->persist($category);
            $this->addReference(self::CATEGORY_PRODUCT_REFERENCE.$data['slug'], $category);
        }
        $manager->flush();
    }
}
