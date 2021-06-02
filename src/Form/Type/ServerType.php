<?php

namespace App\Form\Type;

use App\Entity\DataCenter;
use App\Entity\Distribution;
use App\Entity\Server;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'help'  => 'Leave blank for auto generation.',
                'required' => false,
            ])
            ->add('location', EntityType::class, [
                'label' => 'Data center',
                'class' => DataCenter::class,
            ])
            ->add('distribution', EntityType::class, [
                'label' => 'Distrib.',
                'class' => Distribution::class,
            ])
            ->add('cpu', RangeType::class, [
                'label' => 'CPU',
                'attr'  => [
                    'min' => 1,
                    'max' => 16,
                ],
            ])
            ->add('ram', RangeType::class, [
                'label' => 'RAM',
                'attr'  => [
                    'min' => 1,
                    'max' => 16,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Server::class);
    }
}