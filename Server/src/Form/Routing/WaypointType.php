<?php
/**
 * @license AVT
 */
namespace App\Form\Routing;

use App\Entity\Customers\Company;
use App\Entity\Routing\Waypoint;
use App\Form\RouteType;
use Symfony\Component\Form\AbstractType as AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WaypointType extends AbstractType
{


    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('documentId', null, [
                'label' => 'waypoint_document_id',
                'attr' =>
                    [
                        'readonly' => 'readonly',
                        'data-toggle' => 'modal',
                        'data-target' => '#DocumentsModal',
                    ],
            ])
            ->add('totalInvoiceValue', null, ['label' => 'waypoint_total_invoice_value'])
            ->add('route', RouteType::class, [/*'attr' => ['style' => 'display: none;'],*/ 'label' => false])
            ->add('company', CompanyTreeType::class, [
                'label' => 'waypoint_company',
                'class' => Company::class,
                'group_by' => function (Company $company) {
                    if ($company->getParent()) {
                        return $company->getParent()->getTitle();
                    }

                    return null;
                },
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Waypoint::class,
        ]);
    }
}
