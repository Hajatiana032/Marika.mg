<?php

namespace App\DataFixtures;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PaymentMethodFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cod = new PaymentMethod();
        $cod->setName('Paiement à la livraison');
        $cod->setType('cod');
        $cod->setIsActive(true);
        $manager->persist($cod);

        $stripe = new PaymentMethod();
        $stripe->setName('Carte bancaire (Stripe)');
        $stripe->setType('stripe');
        $stripe->setIsActive(true);
        $manager->persist($stripe);

        $manager->flush();
    }
}
