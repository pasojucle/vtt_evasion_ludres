<?php

declare(strict_types=1);

namespace App\Tests\UseCase\Session;

use App\DataFixtures\Common\BikeRideTypeFixtures;
use App\DataFixtures\Common\UserFixtures;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Member;
use App\Entity\Session;
use App\Repository\BikeRideRepository;
use App\Repository\BikeRideTypeRepository;
use App\Repository\ClusterRepository;
use App\Repository\MemberRepository;
use App\Service\SeasonService;
use App\UseCase\Session\UnregistrableSessionMessage;
use Doctrine\ORM\EntityManagerInterface;
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

        /**@var Member $member */
        $member = $this->getUser();
        $container->get('security.token_storage')->setToken(new UsernamePasswordToken($member, 'none', $member->getRoles()));
        $bikeRides = $this->getBikeRides();
        $this->addSessions($member, $bikeRides);
        $this->entityManager->flush();

        $setupUser($member);

        $this->entityManager->flush();

        $result = $useCase->execute($member, $bikeRides[array_key_last($bikeRides)]);
        $message = $result['message'];

        if (null === $expectedMessage) {
            $this->assertNull($message);
        } else {
            $this->assertStringContainsString($expectedMessage, $message);
        }
    }

    public function provideUnregistrableScenarios(): iterable
    {
        yield 'Utilisateur ayant fini sa période d\'essai' => [
            function (Member $member) {
                $member->getLastLicence()->setState(LicenceStateEnum::TRIAL_FILE_RECEIVED);
            },
            'La période d\'essai est limité à 3 séances'
        ];

        yield 'Utilisateur avec dossier période d\'essai non finalisé' => [
            function (Member $member) {
                $member->getLastLicence()->setState(LicenceStateEnum::TRIAL_FILE_PENDING);
            },
            'Vous avez un dossier d\'inscription non finalisé'
        ];

        yield 'Utilisateur avec dossier annuel non finalisé' => [
            function (Member $member) {
                $member->getLastLicence()->setState(LicenceStateEnum::YEARLY_FILE_PENDING);
            },
            'Vous avez un dossier d\'inscription non finalisé'
        ];

        yield 'Tout est OK' => [
            function (Member $member) {
                $member->getLastLicence()->setState(LicenceStateEnum::YEARLY_FILE_RECEIVED);
            },
            null
        ];

        yield 'Utilisateur avec dossier de l\'année précédente' => [
            function (Member $member) {
                $seasonService = static::getContainer()->get(SeasonService::class);
                $member->getLastLicence()->setSeason($seasonService->getPreviousSeason());
            },
            'Inscription impossible'
        ];
    }

    private function getUser(): Member
    {
        $userRepository = static::getContainer()->get(MemberRepository::class);

        $member = $userRepository->findOneBy(['licenceNumber' => UserFixtures::getLicenceNumberFromReference(UserFixtures::USER_SCHOLL_MEMBER)]);

        return $member;
    }

    private function getBikeRides(): array
    {
        $bikeRideTypeRepository = static::getContainer()->get(BikeRideTypeRepository::class);
        $bikeRideRepository = static::getContainer()->get(BikeRideRepository::class);
        $bikeRideType = $bikeRideTypeRepository->findBy(['name' => BikeRideTypeFixtures::getBikeRideTypeNameFromReference(BikeRideTypeFixtures::WINTER_MOUNTAIN_BIKING_SCHOOL)]);

        return $bikeRideRepository->findBy(['bikeRideType' => $bikeRideType]);
    }

    private function addSessions(Member $member, array $bikeRides): void
    {
        $clusterRepository = static::getContainer()->get(ClusterRepository::class);
        for ($i = 0; $i < 3; $i++) {
            $cluster = $clusterRepository->findOneBy([
                'bikeRide' => $bikeRides[$i],
                'level' => $member->getLevel(),
            ]);

            $session = new Session();
            $session->setUser($member)
                ->setCluster($cluster)
                ->setIsPresent(true);
            $this->entityManager->persist($session);
            $member->addSession($session);
        }
    }
}
