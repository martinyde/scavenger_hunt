<?php

namespace App\Form;

use App\Entity\Highscore;
use App\Entity\Participant;
use App\Entity\Race;
use App\Entity\ScavengerHunt;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HighscoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('progress_task_entry')
            ->add('progress_task_solution')
            ->add('time')
            ->add('created')
            ->add('participant_name')
            ->add('participant', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'id',
            ])
            ->add('race', EntityType::class, [
                'class' => Race::class,
                'choice_label' => 'id',
            ])
            ->add('scavenger_hunt', EntityType::class, [
                'class' => ScavengerHunt::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Highscore::class,
        ]);
    }
}
