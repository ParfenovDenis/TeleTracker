<?php
/**
 * @license AVT
 */

namespace App\Form;

use App\Entity\Relation\DriverTruck;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DriverTruckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('dateTimeFrom', null, ['widget' => 'single_text', 'label' => 'date_time_from'])
            ->add('to', CheckboxType::class, ['mapped' => false, 'label' => 'date_time_to', 'required' => false])
            ->add('dateTimeTo', null, [
                'widget' => 'single_text',
                'label' => 'date_time_to',
                'required' => false,
                'attr' =>
                    ['style' => 'display: none;'],
                'label_attr' =>
                    ['style' => 'display: none;']
            ])


            ->add('driver', null, ['label' => 'driver', 'attr' => ['disabled' => 'disabled']])
            ->add('truck', null, ['label' => 'truck_title', "required" => 'required'])
            ->add('confirm', HiddenType::class, ['mapped' => false, 'attr' => ['value' => 0]])
            ->add('Save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DriverTruck::class,
        ]);
    }
}
