<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Enum\ColorVariant;
use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderHeader;
use App\Entity\OrderLine;
use App\Form\Admin\OrderLineType;
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
                    'order_id' => $options['id'],
                    'order_status' => $options['status'],
                    'required' => false,
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, array $orderLines) use ($options) {
            if (OrderStatusEnum::ORDERED === $options['status']) {
                $form->add('comments', TextareaType::class, [
                    'label' => 'Commentaires',
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
            $formModifier($event->getForm(), $order->getOrderLines()->getValues());
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $orderLines = $data['orderLines'] ?? [];
            $formModifier($event->getForm(), $orderLines);
        });
    }

    private function canValidate(array $orderLines): bool
    {
        $availabilities = [];
        foreach ($orderLines as $orderLine) {
            $availability = $orderLine instanceof OrderLine
                ? $orderLine->getState()
                : (array_key_exists('state', $orderLine) ? OrderLineStateEnum::tryFrom($orderLine['state']) : null);

            if (null === $availability) {
                return false;
            }
            $availabilities[] = $availability;
        }

        return array_any($availabilities, fn(OrderLineStateEnum $availability) => OrderLineStateEnum::UNAVAILABLE !== $availability);
    }

    private function addBtnValidate(FormInterface $form, array $orderLines): void
    {
        dump($this->canValidate($orderLines));
        $form
            ->add('cancel', SubmitType::class, [
                'label' => 'Annuler',
                'attr' => [
                    'data-icon' => 'lucide:delete',
                    'data-variant' => ColorVariant::DESTRUCTIVE->value,
                ],
            ])
            ->add('validate', SubmitType::class, [
                'label' => 'Valider',
                'disabled' => !$this->canValidate($orderLines),
            ])
            ;
    }

    private function addBtnComplete(FormInterface $form): void
    {
        $form
        ->add('unValide', SubmitType::class, [
            'label' => 'Modifier',
                'attr' => [
                    'data-icon' => 'lucide:pencil'
                ],
        ])
        ->add('complete', SubmitType::class, [
            'label' => 'Clôturer',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderHeader::class,
            'id' => 0,
            'status' => OrderStatusEnum::IN_PROGRESS,
            'attr' => [
                'data-controller' => 'form-modifier'
            ]
        ]);
    }
}
