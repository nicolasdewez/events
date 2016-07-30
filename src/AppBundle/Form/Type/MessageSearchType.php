<?php

namespace AppBundle\Form\Type;

use AppBundle\Workflow\MessageWorkflow as Workflow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessageSearchType.
 */
class MessageSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'form.title'])
            ->add('state', ChoiceType::class, [
                'label' => 'form.events_type',
                'choices' => [
                    'form.state.no_applications' => Workflow::STATE_NO_APPLICATIONS,
                    'form.state.partial' => Workflow::STATE_PARTIAL_SENT,
                    'form.state.sent' => Workflow::STATE_SENT,
                    'form.state.error' => Workflow::STATE_ERROR,
                ]
            ])
            ->add('page', HiddenType::class)
            ->add('submit', SubmitType::class, ['label' => 'form.search'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Form\Model\MessageSearch',
        ]);
    }
}
