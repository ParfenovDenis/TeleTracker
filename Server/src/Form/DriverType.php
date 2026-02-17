<?php
/**
 * @license  AVT
 */

namespace App\Form;

use App\Entity\Customers\Company;
use App\Entity\Customers\Driver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', null, ['label' => 'driver_lastname'])
            ->add('firstName', null, ['label' => 'driver_firstname'])
            ->add('middleName', null, ['label' => 'driver_middlename'])
            ->add('driverLicense', null, ['label' => 'driver_license'])
            ->add('DriverLicenseExpirationDate', null, ['label' => 'driver_license_exp_date'])
            ->add('employmentDate', null, ['label' => 'driver_employment_date', 'years' =>
                [2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011,
                    2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020]

            ])
            ->add('dismissalDate', null, ['label' => 'driver_dismissal_date'])
            ->add('company', EntityType::class,
                [
                    'label' => 'Компания/Филиал',
                    "attr" => ["readonly" => "readonly"],
                    'class'  => Company::class
                ])
            ->add('Save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Driver::class,
        ]);
    }
}
