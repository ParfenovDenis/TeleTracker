<?php
/*
 * Copyright (c) 2021.
 */

namespace App\Form;

use App\Entity\Routing\Route;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('dateTime', null, [
                /*'attr' => ['style' => 'display: none;'],*/
                'label' => false,
            ])
            ->add('truck', null, ['label' => 'truck_title']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Route::class,
        ]);
    }
}
