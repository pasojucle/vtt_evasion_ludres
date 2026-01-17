<?php

declare(strict_types=1);

namespace App\Tests\UseCase\Session;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Entity\BikeRide;
use App\Repository\UserRepository;
use App\Entity\Enum\LicenceStateEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\Common\UserFixtures;
use App\Repository\BikeRideTypeRepository;
use App\DataFixtures\Common\BikeRideTypeFixtures;
use App\UseCase\Session\UnregistrableSessionMessage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UnregistrableSessionMessageTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    /**
     * @dataProvider provideUnregistrableScenarios
     */
    public function testExecuteMessages(callable $setupUser, ?string $expectedMessage): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        
        $useCase = $container->get(UnregistrableSessionMessage::class);

        $user = $this->getUser();
        $container->get('security.token_storage')->setToken(new UsernamePasswordToken($user, 'none', $user->getRoles()));
        $bikeRides = $this->getBikeRides($user);
        $this->addSessions($user, $bikeRides);
        $this->entityManager->flush();

        $setupUser($user);

        $this->entityManager->flush();

        $message = $useCase->execute($user, $bikeRides[array_key_last($bikeRides)]);

        if (null === $expectedMessage) {
            $this->assertNull($message);
        } else {
            $this->assertStringContainsString($expectedMessage, $message);
        }
    }

    public function provideUnregistrableScenarios(): iterable
    {
        yield 'Utilisateur ayant fini sa période d\'essai' => [
            function (User $user) {
                $user->getLastLicence()->setState(LicenceStateEnum::TRIAL_FILE_RECEIVED);
            },
            'La période d\'essai est limité à 3 séances'
        ];

        yield 'Utilisateur avec dossier période d\'essai non finalisé' => [
            function (User $user) {
                $user->getLastLicence()->setState(LicenceStateEnum::TRIAL_FILE_PENDING);
            },
            'Vous avez un dossier d\'inscription non finalisé'
        ];

        yield 'Utilisateur avec dossier annuel non finalisé' => [
            function (User $user) {
                $user->getLastLicence()->setState(LicenceStateEnum::YEARLY_FILE_PENDING);
            },
            'Vous avez un dossier d\'inscription non finalisé'
        ];

        yield 'Tout est OK' => [
            function (User $user) {
                $user->getLastLicence()->setState(LicenceStateEnum::YEARLY_FILE_RECEIVED);
            },
            null
        ];
    }

    private function getUser(): User
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneBy(['licenceNumber' => UserFixtures::getLicenceNumberFromReference(UserFixtures::USER_SCHOLL_MEMBER)]);

        return $user;
    }

    private function getBikeRides(User $user): array
    {
        $bikeRideTypeRepository = static::getContainer()->get(BikeRideTypeRepository::class);
        $bikeRideType = $bikeRideTypeRepository->findOneBy(['name' => BikeRideTypeFixtures::getBikeRideTypeNameFromReference(BikeRideTypeFixtures::WINTER_MOUNTAIN_BIKING_SCHOOL)]);
        
        $bikeRides = [];
        $startAt = (new DateTimeImmutable())->setTime(0,0,0);
        for($i = 1; $i <= 4; ++$i) {
            $bikeRideStartAt = (clone $startAt)
                ->modify('next saturday')
                ->modify(sprintf('+%d weeks', $i - 1));
            $bikeRide = new BikeRide();
            $startAt = (new DateTimeImmutable())->setTime(0,0,0);

            $bikeRide->setBikeRideType($bikeRideType)
                ->setStartAt($bikeRideStartAt)
                ->setTitle($bikeRideType->getName())
                ->setContent($bikeRideType->getContent())
                ->setDisplayDuration(8 * $i)
                ->setClosingDuration($bikeRideType->getClosingDuration() ?? 0);
            $this->entityManager->persist($bikeRide);

            $cluster = new Cluster();
            $cluster->setBikeRide($bikeRide)
                ->setTitle($user->getLevel()->getTitle())
                ->setLevel($user->getLevel());
            $bikeRide->addCluster($cluster);
            $this->entityManager->persist($cluster);
            $bikeRides[] = $bikeRide;
        }

        return $bikeRides;
    }

    private function addSessions(User$user, array $bikeRides): void
    {
        for ($i = 0; $i < 3; $i++) {
            $session = new Session();
            $session->setUser($user)
                ->setCluster($bikeRides[$i]->getClusters()->first())
                ->setIsPresent(true);
            $this->entityManager->persist($session);
            $user->addSession($session);
        }
    }
}
