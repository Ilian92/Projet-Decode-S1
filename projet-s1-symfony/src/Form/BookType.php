<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\BookPublisher;
use App\Entity\Work;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('publicationDate')
            ->add('currentUnitPrice')
            ->add('availableStock')
            ->add('coverImageUrl')
            ->add('weightGrams')
            ->add('releaseDate')
            ->add('work', EntityType::class, [
                'class' => Work::class,
                'choice_label' => 'id',
            ])
            ->add('bookPublisher', EntityType::class, [
                'class' => BookPublisher::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
