<?php

namespace App\Form;

use App\Entity\MonthlyBox;
use App\Entity\Order;
use App\Entity\Subscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthlyBoxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('referenceMonth')
            ->add('creationDate')
            ->add('table_order', EntityType::class, [
                'class' => Order::class,
                'label' => 'Commande associée',
                'choice_label' => function (Order $order) {
                    $date = $order->getOrderDate()?->format('d/m/Y') ?? '—';
                    return sprintf('Commande #%d - %s - %s', $order->getId(), $date, $order->getStatus() ?? '');
                },
                'placeholder' => '— Aucune commande —',
                'required' => false,
            ])
            ->add('subscription', EntityType::class, [
                'class' => Subscription::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MonthlyBox::class,
        ]);
    }
}
