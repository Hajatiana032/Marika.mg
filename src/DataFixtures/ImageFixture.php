<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImageFixture extends Fixture implements DependentFixtureInterface
{

    private string $uploadDir;
    public function __construct(private HttpClientInterface $client)
    {
        $this->uploadDir = __DIR__ . '/../../assets/img/uploads/products/';
    }

    public function load(ObjectManager $manager): void
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/products?limit=0');
        $content = $response->toArray();

        $this->clearDirectory();

        foreach ($content['products'] as $key => $item) {
            foreach ($item['images'] as $file) {
                $product = $this->getProductReference($key);
                if (! $product) {
                    continue;
                }

                $imageName = $this->downloadImage($file);

                $image = new Image();
                $image->setName($imageName)
                    ->setProduct($product);
                $manager->persist($image);
            }
        }

        $manager->flush();
    }


    private function clearDirectory(): void
    {
        if (is_dir($this->uploadDir)) {
            foreach (glob($this->uploadDir . '*') as $file) {
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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function downloadImage(
        ?string $url
    ): ?string {
        if (! $url) {
            return null;
        }
        $resp = $this->client->request('GET', $url, ['buffer' => false]);
        $content = $resp->getContent(false);
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filename = uniqid('prod-') . '.' . $extension;
        file_put_contents($this->uploadDir . $filename, $content);

        return $filename;
    }

    private function getProductReference(int $key): ?Product
    {
        $refKey = ProductFixture::PRODUCT_IMAGE_REFERENCE . $key;
        if (! $this->hasReference($refKey, Product::class)) {
            return null;
        }

        return $this->getReference($refKey, Product::class);
    }

    public function getDependencies(): array
    {
        return [
            ProductFixture::class
        ];
    }
}
