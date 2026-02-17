<?php
/**
 * @license AVT
 *
 */
namespace App\Form;

use App\Entity\Customers\Truck;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TruckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateTimeIn = new \DateTime();
        $builder
            ->add('brand',null,['label' =>'truck_brand'])
            ->add('model',null,['label' =>'truck_model'])
            ->add('numberPlate',null,['label' =>'truck_number_plate'])
            ->add('vin',null,['label' =>'truck_vin'])
            ->add('fuelTank',null,['label' =>'truck_tank'])
            ->add('fuelFlowRate',null,['label' =>'truck_flow_rate'])
            ->add('sts',null,['label' =>'truck_sts'])
            ->add('pts',null,['label' =>'truck_pts'])
            ->add('fuelCard',null,['label' =>'truck_fuel_card'])
            ->add('DateTimeIn',null,['label' =>'truck_date_in','data' => $dateTimeIn])
            ->add('DateTimeOut',null,['label' =>'truck_date_out', 'data' => null])
            ->add('company',null,['label' =>'truck_company', "attr" => ["readonly" => "readonly"]])
            ->add('icon',null,['label' =>'truck_icon'])
            ->add('Save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Truck::class,
        ]);
    }
}
