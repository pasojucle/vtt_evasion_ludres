<?php

declare(strict_types=1);

namespace App\UseCase\SecondHand;

use DateInterval;
use DateTimeImmutable;
use App\Entity\SecondHand;
use App\Service\MailerService;
use App\Service\ParameterService;
use App\Repository\SecondHandRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\DtoTransformer\UserDtoTransformer;
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

        return $secondHands;
    }

    private function sendMailToSeller(SecondHand $secondHand): void
    {
        $userDto = $this->userDtoTransformer->fromEntity($secondHand->getUser());
        $data = [
            'user' => $userDto,
            'name' => $userDto->member->name,
            'firstName' => $userDto->member->firstName,
            'email' => $userDto->mainEmail,
            'subject' => sprintf('Votre annonce %s', $secondHand->getName()),
            'url' => $this->urlGenerator->generate('second_hand_user_list', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'secondHandName' => $secondHand->getName(),
            'duration' => $this->parameterService->getParameterByName('SECOND_HAND_DURATION'),
        ];

        $this->mailerService->sendMailToMember($data, 'SECOND_HAND_DISABLED_MESSAGE');
    }
}