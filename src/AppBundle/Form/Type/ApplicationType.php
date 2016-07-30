<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApplicationType.
 */
class ApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, ['label' => 'form.code'])
            ->add('title', TextType::class, ['label' => 'form.title'])
            ->add('url', UrlType::class, ['label' => 'form.url', 'required' => false])
            ->add('eventsType', ChoiceType::class, [
                'label' => 'form.events_type',
                'choices' => [
                    'form.asynchronous' => Application::TYPE_ASYNC,
                    'form.synchronous' => Application::TYPE_SYNC
                ]
            ])
            ->add('active', CheckboxType::class, ['label' => 'form.active_f', 'required' => false])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Application',
        ]);
    }
}
