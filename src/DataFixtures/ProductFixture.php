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
    private string $uploadDir;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SluggerInterface $slugger
    ) {
        $this->uploadDir = __DIR__.'/../../assets/img/upload/product_thumbnail/';
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

        $this->clearDirectory();

        foreach ($content['products'] as $item) {
            /**
             * Get category reference by slug.
             */
            $category = $this->getCategoryReference($item['category']);
            if ( ! $category) {
                continue;
            }

            if ( ! isset($item['brand'])) {
                continue;
            }
            $slug = $this->slugger->slug($item['brand'])->lower();
            $brand = $this->getBrandReference($slug);

            if ( ! $brand) {
                continue;
            }

            $filename = $this->downloadThumbnail($item['thumbnail']);
            $product = new Product();
            $product->setTitle($item['title']);
            $product->setSlug($this->slugger->slug($item['title'])->lower());
            $product->setDescription($item['description']);
            $product->setPrice($item['price']);
            $product->setStock($item['stock']);
            $product->setCategory($category);
            $product->setThumbnail($filename);
            $product->setBrand($brand);

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function clearDirectory(): void
    {
        if (is_dir($this->uploadDir)) {
            foreach (glob($this->uploadDir.'*') as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        } else {
            // Si le dossier n’existe pas, on le crée
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Get category reference by slug.
     *
     * @param  string  $slug
     * @return Category|null
     */
    private function getCategoryReference(string $slug): ?Category
    {
        $refKey = CategoryFixture::CATEGORY_PRODUCT_REFERENCE.$slug;
        if ( ! $this->hasReference($refKey, Category::class)) {
            return null;
        }

        return $this->getReference($refKey, Category::class);
    }

    private function getBrandReference(string $slug): ?Brand
    {
        $refKey = BrandFixture::BRAND_PRODUCT_REFERENCE.$slug;
        if ( ! $this->hasReference($refKey, Brand::class)) {
            return null;
        }

        return $this->getReference($refKey, Brand::class);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function downloadThumbnail(
        ?string $url
    ): ?string {
        if ( ! $url) {
            return null;
        }
        $resp = $this->client->request('GET', $url, ['buffer' => false]);
        $content = $resp->getContent(false);
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filename = uniqid('prod-').'.'.$extension;
        file_put_contents($this->uploadDir.$filename, $content);

        return $filename;
    }
}
