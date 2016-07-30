<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Application;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EventType.
 */
class EventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, ['label' => 'form.code'])
            ->add('active', CheckboxType::class, ['label' => 'form.active_m', 'required' => false])
            ->add('applications', CollectionType::class, [
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Application::class,
                    'choice_label' => 'code',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->where('a.active = TRUE')
                            ->orderBy('a.code', 'ASC');
                    },
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Event',
        ]);
    }
}
