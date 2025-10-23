<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro mobile',
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('zipCode', IntegerType::class, [
                'label' => 'Code postal',
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'label' => 'Ville',
                'attr' => ['class' => 'form-control'],
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => 'Sélectionner une ville',
            ])
            ->add('payOnDelivery', CheckboxType::class, [
                'label' => 'Payer à la livraison',
                'attr' => ['class' => 'form-check-input border-blue-600 border-2'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
