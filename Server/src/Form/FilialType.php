<?php
/**
 * Copyright (c) 2020.
 */

namespace App\Form;


use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilialType extends CompanyType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modalAttrs = [ 'data-toggle' => "modal", 'data-target'=>"#mapModal", "readonly" => "readonly" ];
        $latAttrs = $modalAttrs;

        $builder
            ->add('title',null,['label' =>'filial_title'])
            ->add('address',null,['label' =>'filial_address'])
            ->add('lat',TextType::class,['label' =>'company_lat', 'attr' => $latAttrs])
            ->add('lng',TextType::class,['label' =>'company_lng','attr' => $modalAttrs])
            ->add('radius', TextType::class,['label' =>'company_radius','attr' => $modalAttrs])
            ->add('user',null,['label' =>'company_user'])
            ->add('Save', SubmitType::class)
        ;
    }
}