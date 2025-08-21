<?php

namespace App\Form;

use App\Entity\Race;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('race_duration', TextType::class, [
              'help' => 'Duration in seconds. (3600 = 1 hour), (86400 = 1 day)',
              'attr' => ['class' => 'form-control']
            ])
            ->add('type', ChoiceType::class, [
              'choices' => [
                'Single race' => 'single',
                'Repeating race' => 'repeating',
              ],
              'help' => '<div>A repeating race restarts immediately after time runs out. It requires little to no maintenance after it has been started.</div><div>A single has a clear finish when time runs out.</div>',
              'help_html' => true,
              'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Race::class,
        ]);
    }
}
