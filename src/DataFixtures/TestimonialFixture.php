<?php

namespace App\DataFixtures;

use App\Entity\Testimonial;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestimonialFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly HttpClientInterface $client) {}

    public function load(ObjectManager $manager): void
    {
        $response = $this->client->request('GET', 'https://dummyjson.com/comments');
        $testimonials = $response->toArray();

        foreach ($testimonials['comments'] as $i => $data) {
            $testimonial = new Testimonial();
            $refKey = UserFixture::USER_TESTIMONIAL_REFERENCE . $i;

            if (!$this->hasReference($refKey, User::class)) {
                continue;
            }

            $ref = $this->getReference($refKey, User::class);

            $testimonial->setUser($ref)
                ->setContent($data['body'])
                ->setIsVerified(mt_rand(0, 1));

            $manager->persist($testimonial);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}
