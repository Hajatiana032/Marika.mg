<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Model\SearchData;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', HiddenType::class, [
                'data' => null,
            ])
            ->add('c', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'choice_value' => 'slug',
                'expanded' => true,
                'required' => false,
                'placeholder' => false,
            ])
            ->add('b', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => 'name',
                'choice_value' => 'slug',
                'expanded' => true,
                'required' => false,
                'placeholder' => false,
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->join('b.products', 'p');
                },
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
