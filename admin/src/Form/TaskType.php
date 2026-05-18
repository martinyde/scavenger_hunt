<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('passKey', TextType::class, [
                'label' => 'Passkey',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control text-uppercase'],
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('solutions', CollectionType::class, [
                // each entry in the array will be an "email" field
                'entry_type' => TextType::class,
                'allow_add' => true,
                'label' => 'Solutions',
                'label_attr' => ['class' => 'invisible'],
                'attr' => ['class' => 'invisible'],
            ]
            )
            ->add('text_before', TextareaType::class, [
                'label' => 'Text before',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('text_after', TextareaType::class, [
                'label' => 'Text before',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
