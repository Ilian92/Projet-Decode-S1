<?php

namespace App\Form;

use App\Entity\SubscriptionOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('offerName')
            ->add('description')
            ->add('monthlyPrice')
            ->add('includedBooksCount')
            ->add('commitmentMonths')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubscriptionOffer::class,
        ]);
    }
}
