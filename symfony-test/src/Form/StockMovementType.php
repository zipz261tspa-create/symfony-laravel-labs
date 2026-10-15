<?php

namespace App\Form;

use App\Entity\product;
use App\Entity\StockMovement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockMovementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('movement_type')
            ->add('quantity')
            ->add('movement_date', null, [
                'widget' => 'single_text',
            ])
            ->add('note')
            ->add('product', EntityType::class, [
                'class' => product::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockMovement::class,
        ]);
    }
}
