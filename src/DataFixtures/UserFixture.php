<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserFixture extends Fixture
{
    public const string USER_TESTIMONIAL_REFERENCE = 'user-testimonial-';
    private string $uploadDir;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SluggerInterface $slugger,
    ) {
        $this->uploadDir = __DIR__ . '/../../assets/img/uploads/avatar/';
    }

    public function load(ObjectManager $manager): void
    {
        $this->clearDirectory();
        $response = $this->client->request('GET', 'https://dummyjson.com/users?limit=10');
        $users = $response->toArray();


        foreach ($users['users'] as $i => $data) {
            $avatarUrl = $data['image'];

            // Télécharger l'image via HttpClient
            $response = $this->client->request('GET', $avatarUrl, ['buffer' => false]);
            $imageContent = $response->getContent(false);

            // Déterminer l'extension depuis l'URL (ou fallback à 'jpg')
            $extension = pathinfo(parse_url($avatarUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = uniqid('user-') . '.' . $extension;

            // S'assurer que le dossier existe
            if (! is_dir($this->uploadDir)) {
                mkdir($this->uploadDir, 0755, true);
            }

            file_put_contents($this->uploadDir . $filename, $imageContent);

            $user = new User();
            $user->setFirstName($data['firstName'])
                ->setLastName($data['lastName'])
                ->setEmail($data['email'])
                ->setPhone($data['phone'])
                ->setUsername($data['username'])
                ->setAddress($data['address']['address'])
                ->setZipCode($data['address']['postalCode'])
                ->setCity($data['address']['city'])
                ->setIsVerified(mt_rand(0, 1))
                ->setRoles(['ROLE_USER'])
                ->setSlug($this->slugger->slug($data['username'])->lower())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setAvatar($filename);

            $manager->persist($user);

            $this->setReference(self::USER_TESTIMONIAL_REFERENCE . $i, $user);
        }

        $adminUrl = 'https://m.media-amazon.com/images/S/pv-target-images/16627900db04b76fae3b64266ca161511422059cd24062fb5d900971003a0b70._SX1080_FMjpg_.jpg';
        $adminResponse = $this->client->request('GET', $adminUrl, ['buffer' => false]);
        $adminContent = $adminResponse->getContent(false);
        $adminExtension = pathinfo(parse_url($adminUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $adminFilename = uniqid('user-') . '.' . $adminExtension;
        file_put_contents($this->uploadDir . $adminFilename, $adminContent);

        $admin = new User();
        $admin->setFirstName("John")
            ->setLastName("Doe")
            ->setEmail("johndoe@email.com")
            ->setPhone("032 033 034")
            ->setUsername("John Doe")
            ->setAddress("Address")
            ->setZipCode("6000")
            ->setCity("Antananarivo")
            ->setIsVerified(true)
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setSlug($this->slugger->slug($user->getUsername())->lower())
            ->setPassword($this->passwordHasher->hashPassword($admin, 'password'))
            ->setAvatar($adminFilename);

        $manager->persist($admin);

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
            mkdir($this->uploadDir, 0755, true);
        }
    }

    private function downloadAvatar(
        ?string $url
    ): ?string {
        if (! $url) {
            return null;
        }

        $resp = $this->client->request('GET', $url, ['buffer' => false]);
        $content = $resp->getContent(false);
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filename = uniqid('user-') . '.' . $extension;
        file_put_contents($this->uploadDir . $filename, $content);

        return $filename;
    }
}
