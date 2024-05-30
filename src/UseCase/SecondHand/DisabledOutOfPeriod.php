<?php

declare(strict_types=1);

namespace App\UseCase\SecondHand;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\SecondHand;
use App\Repository\SecondHandRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ParameterService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DisabledOutOfPeriod
{
    public function __construct(
        private ParameterService $parameterService,
        private SecondHandRepository $secondHandRepository,
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UserDtoTransformer $userDtoTransformer,
        private UrlGeneratorInterface $urlGenerator,
        private MessageService $messageService,
    ) {
    }

    public function execute(): array
    {
        $duration = $this->parameterService->getParameterByName('SECOND_HAND_DURATION');
        $deadline = (new DateTimeImmutable())->setTime(0, 0, 0)->sub(new DateInterval(sprintf('P%sD', $duration)));
        $secondHands = $this->secondHandRepository->findOutOfPeriod($deadline);

        foreach ($secondHands as $secondHand) {
            $secondHand->setDisabled(true);
            $this->sendMailToSeller($secondHand);
        }

        $this->entityManager->flush();

        return [
            'codeError' => 0,
             'message' => (empty($secondHands))
                ? 'no secondHands to disabling'
                : sprintf('%d secondHands disabled', count($secondHands)),
        ];
    }

    private function sendMailToSeller(SecondHand $secondHand): void
    {
        $userDto = $this->userDtoTransformer->identifiersFromEntity($secondHand->getUser());
        $subject = sprintf('Votre annonce %s', $secondHand->getName());
        $content = $this->messageService->getMessageByName('SECOND_HAND_DISABLED_MESSAGE');

        $AdditionnalParams = [
            '{{ nom_annonce }}' => $secondHand->getName(),
            '{{ url }}' => $this->urlGenerator->generate('second_hand_user_list', [], UrlGeneratorInterface::ABSOLUTE_URL),
            '{{ durree }}' => $this->parameterService->getParameterByName('SECOND_HAND_DURATION'),
        ];
        
        $this->mailerService->sendMailToMember($userDto, $subject, $content, null, $AdditionnalParams);
    }
}
