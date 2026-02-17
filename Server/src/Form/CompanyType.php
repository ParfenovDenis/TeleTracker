<?php
/**
 * @license AVT
 */
namespace App\Form;

use App\Entity\Customers\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modalAttrs = [ 'data-toggle' => "modal", 'data-target'=>"#mapModal", "readonly" => "readonly"];
        $builder
            ->add('title',null,['label' =>'company_title'])
            ->add('groupCompanies',null,['label' =>'group_companies'])
            ->add('officialName',null,['label' =>'company_official_name'])
            ->add('inn',null,['label' =>'company_inn'])
            ->add('manager',null,['label' =>'company_manager'])
            ->add('address',null,['label' =>'company_address'])
            ->add('lat',TextType::class,['label' =>'company_lat','attr' => $modalAttrs])
            ->add('lng',TextType::class,['label' =>'company_lng','attr' => $modalAttrs])
            ->add('radius', TextType::class,['label' =>'company_radius','attr' => $modalAttrs])
            ->add('user',null,['label' =>'company_user'])
            ->add('Save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
