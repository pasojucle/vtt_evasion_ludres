<?php

namespace App\Command;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\SecondHand;
use App\Repository\SecondHandRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ParameterService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'second-hands:out-of-period',
    description: 'Disabled out of period second hands',
)]
class OutOfPeriodSecondHandsCommand extends Command
{
    private ProgressBar $progressBar;
    private OutputInterface $output;
    private SymfonyStyle $ssio;
    public function __construct(
        private ParameterService $parameterService,
        private MessageService $messageService,
        private SecondHandRepository $secondHandRepository,
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UserDtoTransformer $userDtoTransformer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->ssio = new SymfonyStyle($input, $this->output);

        $duration = $this->parameterService->getParameterByName('SECOND_HAND_DURATION');
        $deadline = (new DateTimeImmutable())->setTime(0, 0, 0)->sub(new DateInterval(sprintf('P%sD', $duration)));
        $secondHands = $this->secondHandRepository->findOutOfPeriod($deadline);
        $this->progressBar = new ProgressBar($this->output, count($secondHands));
        $this->progressBar->start();
        foreach ($secondHands as $secondHand) {
            $secondHand->setDisabled(true);
            $this->sendMailToSeller($secondHand);
            $this->progressBar->advance();
        }

        $this->entityManager->flush();
        $this->progressBar->finish();
        $this->ssio->success('Successfull.');

        return Command::SUCCESS;
    }

    private function sendMailToSeller(SecondHand $secondHand): void
    {
        $userDto = $this->userDtoTransformer->identifiersFromEntity($secondHand->getUser());
        $subject = sprintf('Votre annonce %s', $secondHand->getName());
        $content = $this->messageService->getMessageByName('SECOND_HAND_DISABLED_MESSAGE');
        $additionalParams = [
            '{{ url }}' => $this->urlGenerator->generate('second_hand_user_list', [], UrlGeneratorInterface::ABSOLUTE_URL),
            '{{ nom_annonce }}' => $secondHand->getName(),
            '{{ durree }}' => $this->parameterService->getParameterByName('SECOND_HAND_DURATION'),
        ];
        
        $this->mailerService->sendMailToMember($userDto, $subject, $content, null, $additionalParams);
    }
}
