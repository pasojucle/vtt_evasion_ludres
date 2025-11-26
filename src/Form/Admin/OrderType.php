<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Form\Admin\OrderLineType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderLines', CollectionType::class, [
                'label' => false,
                'entry_type' => OrderLineType::class,
                'entry_options' => [
                    'label' => false,
                    'order_status' => $options['status'],
                    'required' => false,
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, array|Collection $orderLines) use ($options) {
            if (OrderStatusEnum::ORDERED === $options['status']) {
                $form->add('comments', TextareaType::class, [
                    'label' => 'Commentaires',
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                    'required' => false,
                ]);
                $this->addBtnValidate($form, $orderLines);
            }
            if (OrderStatusEnum::VALIDED === $options['status']) {
                $this->addBtnComplete($form);
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var OrderHeader $order */
            $order = $event->getData();
            $formModifier($event->getForm(), $order->getOrderLines());
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $orderLines = (array_key_exists('orderLines', $data)) ? $data['orderLines'] : [];
            $formModifier($event->getForm(), $orderLines);
        });
    }

    private function allOrderLinesCheked(array|Collection $orderLines): bool
    {
        $linesCheked = 0;
        foreach ($orderLines as $orderLine) {
            $isAvailable = match (true) {
                $orderLine instanceof OrderLine => OrderLineStateEnum::UNAVAILABLE !== $orderLine->getState(),
                array_key_exists('state', $orderLine) => OrderLineStateEnum::UNAVAILABLE !== OrderLineStateEnum::tryFrom($orderLine['state']),
                default => null
            };
            if (null === $isAvailable) {
                return false;
            }

            ++$linesCheked;
        }
        $count = ($orderLines instanceof Collection) ? $orderLines->count() : count($orderLines);

        return $count === $linesCheked;
    }

    private function addBtnValidate(FormInterface $form, array|Collection $orderLines): void
    {
        $attrClass = 'btn btn-success';
        if (!$this->allOrderLinesCheked($orderLines)) {
            $attrClass .= ' disabled';
        }

        $form
            ->add('validate', SubmitType::class, [
                'label' => '<i class="fas fa-check"></i> Valider la commande',
                'label_html' => true,
                'attr' => [
                    'class' => $attrClass,
                ],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => '<i class="fas fa-times"></i> Annuler la commande',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
            ])
            ;
    }

    private function addBtnComplete(FormInterface $form): void
    {
        $form
        ->add('unValide', SubmitType::class, [
            'label' => '<i class="fa-solid fa-pen-to-square"></i> Modifier',
            'label_html' => true,
            'attr' => [
                'class' => 'btn btn-default',
            ],
        ])
        ->add('complete', SubmitType::class, [
            'label' => '<i class="fas fa-check"></i> Clôturer la commande',
            'label_html' => true,
            'attr' => [
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderHeader::class,
            'status' => OrderStatusEnum::IN_PROGRESS,
        ]);
    }
}
