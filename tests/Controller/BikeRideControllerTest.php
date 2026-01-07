<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Common\BikeRideTypeFixtures;
use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Session;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Repository\LevelRepository;
use App\Entity\Enum\RegistrationEnum;
use App\Repository\SessionRepository;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Tests\Controller\AbstractTestController;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;


class BikeRideControllerTest extends AbstractTestController
{
    public function testAdminBikeRide()
    {
        // $this->validateAdminSchedule();

        

        // $bikeRideRepository = static::getContainer()->get(BikeRideRepository::class);
        // $bikeRide = $bikeRideRepository->find(1);
        // $this->testAdminBikeRideClusterShow($bikeRideType);
        // $users = $this->userRepository->findAllByCurrentSeason();
        // $user = $users[rand(0, count($users) - 1)];
        // $session = $this->testAdminAddSession($bikeRide, $user);
        // $this->testAdminClusterShow($session);
        // $this->testBikeRideUser($user);
    }

    // protected function getEntityFromReference(string $reference): object
    // {

    //     return $this->client->getContainer()
    //         ->get('doctrine')
    //         ->getManager()
    //         ->getRepository(BikeRideType::class)
    //         ->findOneBy(['name' => BikeRideTypeFixtures::getBikeRideTypeNameFromReference($reference)]);
    // }

    // private function validateAdminSchedule(): void
    // {
    //     $this->loginAdmin();
    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertResponseIsSuccessful();
    //     $this->client->request('GET', '/admin/calendrier');
    //     $this->assertSelectorTextContains('.wrapper h1', 'Programme des sorties');
    // }

    // private function validateAdminAddBikeRides(string $bikeRideTypeReference): void
    // {
    //     $startAt = (new DateTime());
    //     for($i = 0; $i < 3; ++$i) {
    //         $startAt->add(new DateInterval(sprintf('P%dD', 7 - $startAt->format('w'))));
    //         $bikeRideType = $this->validateAdminAddBikeRide(BikeRideTypeFixtures::ADULT_HIKING, $startAt);
    //     }
    // }

    // private function validateAdminAddBikeRide(string $bikeRideTypeReference, DateTime $startAt): void
    // {
    //     $this->client->clickLink('Ajouter une sortie');
    //     $this->assertSelectorTextContains('.wrapper h1', 'Ajouter une sortie');
    //     $bikeRideType = $this->getEntityFromReference($bikeRideTypeReference);
    //     $closingDuration = $bikeRideType->getClosingDuration() ?? 0;

    //     $form = $this->client->getCrawler()->selectButton('Enregistrer')->form();

    //     $this->addAutocompleteField($form, 'bike_ride[content]'); 

    //     $form['bike_ride[bikeRideType]'] = $bikeRideType->getId();
    //     $form['bike_ride[title]'] = $bikeRideType->getName();
    //     $form['bike_ride[content]'] = $bikeRideType->getContent();
    //     $form['bike_ride[startAt]'] = $startAt->format('d/m/Y');
    //     $form['bike_ride[displayDuration]'] = '8';
    //     $form['bike_ride[closingDuration]'] = (string) $closingDuration;

    //     $this->client->submit($form);
    //     $this->assertResponseRedirects();
    //     $this->client->followRedirect();
    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorCount(1,'li.list-dropdown');
    //     $this->assertSelectorExists('a[href="/admin/sortie/groupe/1"]');

    // }

    

    // private function testAdminAddSession(BikeRide $bikeRide, User $user): Session
    // {
    //     $this->client->request('GET', sprintf('/admin/rando/inscription/%s', $bikeRide->getId()));
    //     $form = $this->client->getCrawler()->selectButton('session_submit')->form();
    //     /** @var ChoiceFormField $formUserSession */
    //     $formUserSession = $form['session[user]'];
    //     $formUserSession->disableValidation()->setValue((string) $user->getId());
    //     $this->client->submit($form);
    //     $sessionRepository = static::getContainer()->get(SessionRepository::class);
    //     return $sessionRepository->findOneByUserAndBikeRide($user, $bikeRide);
    // }

    // private function testAdminClusterShow(Session $session): void
    // {
    //     $this->client->request('GET', sprintf('/admin/groupe/show/%s', $session->getCluster()->getId()));
    //     $response = json_decode($this->client->getResponse()->getContent(), true);
    //     $this->assertArrayHasKey('codeError', $response);
    //     $this->client->getCrawler()->addXmlContent($response['html']);
    //     $this->assertSelectorExists(sprintf('li#session-%s', $session->getId()));
    //     $this->assertSelectorExists(sprintf('a[href="/admin/adherent/calendrier/%s"]', $session->getUser()->getId()));
    // }

    // private function testBikeRideUser(User $user): void
    // {
    //     $this->client->request('GET', sprintf('/admin/adherent/calendrier/%s', $user->getId()));
    //     $userDtoTransformer = static::getContainer()->get(UserDtoTransformer::class);
    //     $userDto = $userDtoTransformer->getHeaderFromEntity($user);
    //     $this->assertAnySelectorTextSame('h1', $userDto->member->fullName);
    //     $btnBack = $this->client->getCrawler()->filter('a[title="Retour à la liste des adhérents"]');
    //     $this->client->click($btnBack->link());
    // }
}