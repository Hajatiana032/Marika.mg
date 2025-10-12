<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductFixture extends Fixture
{
    public const string PRODUCT_IMAGE_REFERENCE = 'product-image-';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SluggerInterface $slugger
    ) {}

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

        foreach ($content['products'] as $key => $item) {
            /**
             * Get category reference by slug.
             */
            $category = $this->getCategoryReference($item['category']);
            if (! $category) {
                continue;
            }

            if (! isset($item['brand'])) {
                continue;
            }
            $slug = $this->slugger->slug($item['brand'])->lower();
            $brand = $this->getBrandReference($slug);

            if (! $brand) {
                continue;
            }

            $product = new Product();
            $product->setTitle($item['title']);
            $product->setSlug($this->slugger->slug($item['title'])->lower());
            $product->setDescription($item['description']);
            $product->setPrice($item['price']);
            $product->setStock($item['stock']);
            $product->setCategory($category);
            $product->setBrand($brand);

            $manager->persist($product);

            $this->addReference(self::PRODUCT_IMAGE_REFERENCE . $key, $product);
        }

        $manager->flush();
    }

    /**
     * Get category reference by slug.
     *
     * @param  string  $slug
     * @return Category|null
     */
    private function getCategoryReference(string $slug): ?Category
    {
        $refKey = CategoryFixture::CATEGORY_PRODUCT_REFERENCE . $slug;
        if (! $this->hasReference($refKey, Category::class)) {
            return null;
        }

        return $this->getReference($refKey, Category::class);
    }

    private function getBrandReference(string $slug): ?Brand
    {
        $refKey = BrandFixture::BRAND_PRODUCT_REFERENCE . $slug;
        if (! $this->hasReference($refKey, Brand::class)) {
            return null;
        }

        return $this->getReference($refKey, Brand::class);
    }
}
