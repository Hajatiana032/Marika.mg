<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductFixture extends Fixture
{
    private string $uploadsDir;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SluggerInterface $slugger
    ) {
        $this->uploadsDir = __DIR__.'/../../assets/img/uploads/product_thumbnail/';
    }

    public function load(ObjectManager $manager): void
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/products?limit=0');
        $content = $response->toArray();


        foreach ($content['products'] as $item) {
            /**
             * Get category reference by slug.
             */
            $category = $this->getCategoryReference($item['category']);
            if ( ! $category) {
                continue;
            }

            $this->downloadThumbnail($item['thumbnail']);
            $product = new Product();
            $product->setTitle($item['title']);
            $product->setSlug($this->slugger->slug($item['title'])->lower());
            $product->setDescription($item['description']);
            $product->setPrice($item['price']);
            $product->setStock($item['stock']);
            $product->setCategory($category);

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function getCategoryReference(string $slug): ?Category
    {
        $refKey = CategoryFixture::CATEGORY_PRODUCT_REFERENCE.$slug;
        if ( ! $this->hasReference($refKey, Category::class)) {
            return null;
        }

        return $this->getReference($refKey, Category::class);
    }

    private function downloadThumbnail(?string $url): ?string
    {
        if ( ! $url) {
            return null;
        }

        $resp = $this->client->request('GET', $url, ['buffer' => false]);
        $content = $resp->getContent(false);
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filename = uniqid('prod-').'.'.$extension;
        file_put_contents($this->uploadsDir.$filename, $content);

        return $filename;
    }

//    public function downloadThumbnail()
//    {
//        $uploadDir = __DIR__.'/../../assets/img/uploads/product_thumbnail/';
//        if (is_dir($uploadDir)) {
//            foreach (glob($uploadDir.'*') as $file) {
//                if (is_file($file)) {
//                    unlink($file);
//                }
//            }
//        } else {
//            // Si le dossier n’existe pas, on le crée
//            mkdir($uploadDir, 0755, true);
//        }
//
//        if ( ! empty($item['thumbnail'])) {
//            $responseThumbnail = $this->client->request('GET', $item['thumbnail'], ['buffer' => false]);
//            $thumbnailContent = $responseThumbnail->getContent(false);
//            $extension = pathinfo($item['thumbnail'], PATHINFO_EXTENSION);
//            $fileName = uniqid('prod-').'.'.$extension;
//            $filePath = $uploadDir.$fileName;
//
//            return file_put_contents($filePath, $thumbnailContent);
//        }
//    }
}
