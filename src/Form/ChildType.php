<?php

namespace App\Form;

use App\Entity\Allergen;
use App\Entity\Child;
use App\Entity\User;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName')
            ->add('firstName')
            ->add('birthDate')
            ->add('sex')
            ->add('allergen',CollectionType::class, [
                'entry_type' => AllergenType::class,
                'required' => false
            ])
            ->add('allergen',EntityType::class, [
                'class' => Allergen::class,
                'multiple' => true,
                'label' => 'Allergène',
                'choice_label' => 'type',
                'required' => false
            ])
            ->add('user',EntityType::class, [
                'class' => User::class,
                'label' => 'FAMILLE',
                'choice_label' => 'lastName'
            ])

            ->add('submit', SubmitType::class)
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
        ]);
    }
}
