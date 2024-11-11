<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Session;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Repository\LevelRepository;
use App\Entity\Enum\RegistrationEnum;
use App\Repository\SessionRepository;
use App\Repository\BikeRideRepository;
use App\Repository\BikeRideTypeRepository;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Tests\Controller\AbstractTestController;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;


class BikeRideControllerTest extends AbstractTestController
{
    public function testAdminBikeRide()
    {
        $this->init();
        $this->cleanDataBase();
        $this->testAdminSchedule();
        $bikeRideType = $this->testAdminAddBikeRide(2);
        $bikeRideRepository = static::getContainer()->get(BikeRideRepository::class);
        $bikeRide = $bikeRideRepository->find(1);
        $this->testAdminBikeRideClusterShow($bikeRideType);
        $users = $this->userRepository->findAllByCurrentSeason();
        $user = $users[rand(0, count($users) - 1)];
        $session = $this->testAdminAddSession($bikeRide, $user);
        $this->testAdminClusterShow($session);
        $this->testBikeRideUser($user);
    }

    private function cleanDataBase():void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS=0;");
        foreach(['session', 'cluster', 'bike_ride'] as $table) {
            $query = sprintf("TRUNCATE TABLE `%s`", $table);
            $connection->executeQuery($query);
        }
        $connection->executeQuery("SET FOREIGN_KEY_CHECKS=1;");
    }

    private function testAdminSchedule(): void
    {
        $this->loginAdmin();
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/admin/calendrier');
        $this->assertSelectorTextContains('.wrapper h1', 'Programme des sorties');
    }

    private function testAdminAddBikeRide(int $bikeRideTypeId): BikeRideType
    {
        $this->client->clickLink('Ajouter une sortie');
        $this->assertSelectorTextContains('.wrapper h1', 'Modifier une sortie');
        $bikeRideTypeRepository = static::getContainer()->get(BikeRideTypeRepository::class);
        $bikeRideType = $bikeRideTypeRepository->find($bikeRideTypeId);
        $startAt = (new DateTime());
        $startAt->add(new DateInterval(sprintf('P%dD', 7 - $startAt->format('w'))));
        $closingDuration = $bikeRideType->getClosingDuration() ?? 0;

        $form = $this->client->getCrawler()->selectButton('Enregistrer')->form();

        $this->addAutocompleteField($form, 'bike_ride[content]'); 

        $form['bike_ride[bikeRideType]'] = (string) $bikeRideTypeId;
        $form['bike_ride[title]'] = $bikeRideType->getName();
        $form['bike_ride[content]'] = $bikeRideType->getContent();
        $form['bike_ride[startAt]'] = $startAt->format('d/m/Y');
        $form['bike_ride[displayDuration]'] = '8';
        $form['bike_ride[closingDuration]'] = (string) $closingDuration;

        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1,'li.list-dropdown');
        $this->assertSelectorExists('a[href="/admin/sortie/groupe/1"]');
        return $bikeRideType;
    }

    private function testAdminBikeRideClusterShow(BikeRideType $bikeRideType): void
    {
        $this->client->request('GET', '/admin/sortie/groupe/1');
        $clusters = 0;
        if (RegistrationEnum::SCHOOL === $bikeRideType->getRegistration()) {
            $levelRepository = static::getContainer()->get(LevelRepository::class);
            $clusters = count($levelRepository->findAllTypeMember());
        }
        if (RegistrationEnum::CLUSTERS === $bikeRideType->getRegistration()) {
            $clusters = $bikeRideType()->getClusters()->count();
        }
        if ($bikeRideType->isNeedFramers()) {
            ++$clusters;
        }
        $this->assertSelectorCount($clusters,'div.cluster-container');
    }

    private function testAdminAddSession(BikeRide $bikeRide, User $user): Session
    {
        $this->client->request('GET', sprintf('/admin/rando/inscription/%s', $bikeRide->getId()));
        $form = $this->client->getCrawler()->selectButton('session_submit')->form();
        /** @var ChoiceFormField $formUserSession */
        $formUserSession = $form['session[user]'];
        $formUserSession->disableValidation()->setValue((string) $user->getId());
        $this->client->submit($form);
        $sessionRepository = static::getContainer()->get(SessionRepository::class);
        return $sessionRepository->findOneByUserAndBikeRide($user, $bikeRide);
    }

    private function testAdminClusterShow(Session $session): void
    {
        $this->client->request('GET', sprintf('/admin/groupe/show/%s', $session->getCluster()->getId()));
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('codeError', $response);
        $this->client->getCrawler()->addXmlContent($response['html']);
        $this->assertSelectorExists(sprintf('li#session-%s', $session->getId()));
        $this->assertSelectorExists(sprintf('a[href="/admin/adherent/calendrier/%s"]', $session->getUser()->getId()));
    }

    private function testBikeRideUser(User $user): void
    {
        $this->client->request('GET', sprintf('/admin/adherent/calendrier/%s', $user->getId()));
        $userDtoTransformer = static::getContainer()->get(UserDtoTransformer::class);
        $userDto = $userDtoTransformer->getHeaderFromEntity($user);
        $this->assertAnySelectorTextSame('h1', $userDto->member->fullName);
        $btnBack = $this->client->getCrawler()->filter('a[title="Retour à la liste des adhérents"]');
        $this->client->click($btnBack->link());
    }
}