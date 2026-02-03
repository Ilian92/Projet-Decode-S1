<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\MonthlyBox;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderDate')
            ->add('status')
            ->add('totalAmount')
            ->add('shippingCost')
            ->add('trackingNumber')
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'id',
            ])
            ->add('monthlyBox', EntityType::class, [
                'class' => MonthlyBox::class,
                'choice_label' => 'id',
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
