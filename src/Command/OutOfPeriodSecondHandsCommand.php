<?php

namespace App\Command;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\SecondHand;
use App\Service\ParameterService;
use App\Repository\SecondHandRepository;
use App\Service\MailerService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Print_;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

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
        private SecondHandRepository $secondHandRepository,
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UserDtoTransformer $userDtoTransformer,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->ssio = new SymfonyStyle($input, $this->output);

        $duration = $this->parameterService->getParameterByName('SECOND_HAND_DURATION');
        $deadline = (new DateTimeImmutable())->setTime(0,0,0)->sub(new DateInterval(sprintf('P%sD', $duration)));
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
