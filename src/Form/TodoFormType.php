<?php

namespace App\Form;

use App\Entity\Todo;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TodoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // dd($options);
        $builder
            ->add('title', TextType::class, [
                'label' => "Un titre en quelque mots",
                'attr' => [
                    'placeholder' => "entrez le titre ici"
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Mais encore ?',
                'attr' => [
                    'placeholder' => 'Entrez ici votre mémo'
                ]
            ]);
        if($options['data']->getId() == null)
        {
            $builder->add('date_for', DateType::class, [
                'label' => 'A faire pour :',
                'years' => ['2021', '2022'],
                'format' => 'dd MM yyyy',
                'data' => new \DateTime('now', new \DateTimeZone('Europe/Paris'))
            ]);
        } 
        else
        {
            $builder->add('date_for', DateType::class, [
                'label' => 'A faire pour :',
                'years' => ['2021', '2022'],
                'format' => 'dd MM yyyy',
            ]);
        }

            $builder->add('category', EntityType::class, [
                'label' => 'Quelle catégorie ?',
                'class' => Category::class,
                'choice_label' => "name"
            ])

            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
        ]);
    }
}
