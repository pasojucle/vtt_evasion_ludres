<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DateInterval;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Session;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Repository\LevelRepository;
use App\Entity\Enum\RegistrationEnum;
use App\Repository\CommuneRepository;
use App\Repository\BikeRideRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\AbstractTestController;
use App\DataFixtures\Common\BikeRideTypeFixtures;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class RegistrationAdultController extends AbstractTestController
{
    public function testFullAdultMemberLifecycle(): void
    {
        $adult = ['name' => 'Roue', 'firstName' => 'Libre', 'password' => 'test01'];
        $this->goToRegistration();
        $this->fillIdentityStep($adult);
        $this->validateTarifStep();
        $this->validateAgreementsStep();
        $this->validateHealtStep();
        $this->validateOverviewStep();
        $this->logOut();

        $this->validateFullTrialMemberLifecycle($adult, BikeRideTypeFixtures::ADULT_HIKING);
    }

    private function goToRegistration(): void
    {
    
        $this->client->request('GET', '/inscription');
        $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');
    }

    private function fillIdentityStep(array $user): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $this->addAutocompleteField($form, 'user[identity][birthPlace]'); 
        $communeRepository = static::getContainer()->get(CommuneRepository::class);
        $communes = $communeRepository->findByPostalCode('54000');
        $form['user[identity][name]'] = $user['name'];
        $form['user[identity][firstName]'] = $user['firstName'];
        $form['user[identity][birthDate]'] = '1971-09-09';
        $form['user[identity][birthPlace]'] = 54395;
        $form['user[identity][mobile]'] = '06 35 41 44 73';
        $form['user[identity][email]'] = 'roue.libre@test.fr';
        $form['user[identity][emergencyPhone]'] = '06 00 00 00 00';
        $form['user[identity][emergencyContact]'] = 'banane';
        $form['user[identity][pictureFile]'] = null;
        $form['user[identity][address][street]'] = 'rue des champs';
        $form['user[identity][address][postalCode]'] = '54550';
        $communes = $communeRepository->findByPostalCode('54550');
        /** @var ChoiceFormField $formaddressCommune */
        $formaddressCommune = $form['user[identity][address][commune]'];
        $formaddressCommune->disableValidation()->setValue('54043');
        $form['user[plainPassword][first]'] = $user['password'];
        $form['user[plainPassword][second]'] = $user['password'];
        $form['user[identity][schoolTestingRegistration]'] = '1';
        $this->addAutocompleteField($form, 'user[lastLicence][familyMember]'); 
        $form['user[lastLicence][familyMember]'] = null;
        $this->client->submit($form);
    
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit registration');
    }

    private function validateTarifStep(): void
    {
        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('a.btn', 'Suivant');
        $this->client->clickLink('Suivant');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK,'View tarif');
    }

    private function validateAgreementsStep(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[lastLicence][licenceAuthorizationAgreements][EMERGENCY_CARE_ADULT][agreed]'] = '1';
        $form['user[lastLicence][licenceAuthorizationAgreements][HEALTH_ADULT][agreed]'] = '1';
        $form['user[lastLicence][licenceAuthorizationAgreements][IMAGE_USE_ADULT][agreed]'] = '1';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit aproval');
    }

    private function validateHealtStep(): void
    {
        $this->client->followRedirect();
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[health][consents][check_up_0]'] = '1';
        $form['user[health][consents][check_up_1]'] = '1';
        $form['user[health][consents][check_up_2]'] = '1';
        $form['user[lastLicence][licenceHealthAgreements][HEALTH_ADULT][agreed]'] = '1';
        $form['user[lastLicence][licenceHealthAgreements][HEALTH_ADULT_2][agreed]'] = '1';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit health');
    }

    private function validateOverviewStep(): void
    {
        $this->client->followRedirect();
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[lastLicence][licenceOverviewAgreements][RULES][agreed]'] = '1';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit overview');
    }

    private function validateFullTrialMemberLifecycle(array $adult, string $bikeRideTypeReference): void
    {
        $startAt = (new DateTimeImmutable())->setTime(0,0,0);
        $bikeRideType = $this->getEntityFromReference($bikeRideTypeReference);

        for($i = 1; $i <= 3; ++$i) {
            $bikeRideStartAt = $startAt->add(new DateInterval(sprintf('P%dD', 7 * $i - $startAt->format('w'))));
            $bikeRide = ['bikeRideType' => $bikeRideType, 'startAt' => $bikeRideStartAt];
            $this->validateAdminAddBikeRide($bikeRideType, $bikeRideStartAt, $i);
            $session = $this->validateRegistrationToBikeRide($adult, $bikeRide);
            $this->validateParticipation($session);
            $this->validateLicenceState($adult, $i);
        }
    }

    private function validateLogToBackOffice(): void
    {
        $admin = $this->userRepository->findOneByLicenceNumber('624758');
        $this->loginUser($admin);
    }

    private function getUserFromIdentity(array $identity): User
    {
        $userIdentity = $this->identityRepository->findOneBy(['name' => $identity['name'], 'firstName' => $identity['firstName']]);
        return $userIdentity->getUser();        
        
    }

    private function validateAdminAddBikeRide(BikeRideType $bikeRideType, DateTimeImmutable $startAt, int $loop): void
    {
        $this->validateLogToBackOffice();
        $this->client->request('GET', '/admin/calendrier');
        $this->assertSelectorTextContains('.wrapper h1', 'Programme des sorties');
        $this->client->clickLink('Ajouter une sortie');
        $this->assertSelectorTextContains('.wrapper h1', 'Ajouter une sortie');
        $closingDuration = $bikeRideType->getClosingDuration() ?? 0;

        $form = $this->client->getCrawler()->selectButton('Enregistrer')->form();

        $this->addAutocompleteField($form, 'bike_ride[content]'); 

        $form['bike_ride[bikeRideType]'] = $bikeRideType->getId();
        $form['bike_ride[title]'] = $bikeRideType->getName();
        $form['bike_ride[content]'] = $bikeRideType->getContent();
        $form['bike_ride[startAt]'] = $startAt->format('d/m/Y');
        $form['bike_ride[displayDuration]'] = (string) 8 * $loop;
        $form['bike_ride[closingDuration]'] = (string) $closingDuration;

        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount($loop,'li.list-dropdown');
        $bikeRideRepository = static::getContainer()->get(BikeRideRepository::class);
        $bikeRide = $bikeRideRepository->findOneBy(['bikeRideType' => $bikeRideType, 'startAt' => $startAt]);
        $this->assertSelectorExists(sprintf('a[href="/admin/sortie/groupe/%s"]', $bikeRide->getId()));
        $this->validateAdminBikeRideClusters($bikeRide);
        $this->logOut();
    }

    private function validateAdminBikeRideClusters(BikeRide $bikeRide): void
    {
        $totalClusters = 0;
        $bikeRideType = $bikeRide->getBikeRideType();
        if (RegistrationEnum::SCHOOL === $bikeRideType->getRegistration()) {
            $totalClusters = count($this->levelRepository->findAllTypeMember());
        }
        if (RegistrationEnum::CLUSTERS === $bikeRideType->getRegistration()) {
            $totalClusters = count($bikeRideType->getClusters());
        }
        if ($bikeRideType->isNeedFramers()) {
            ++$totalClusters;
        }
        $this->assertTrue($totalClusters === $bikeRide->getClusters()->count());
    }

    private function validateRegistrationToBikeRide(array $identity, array $bikeRide): Session
    {
        $user = $this->getUserFromIdentity($identity);
        $this->loginUser($user);
        $this->client->request('GET', '/programme');
        $bikeRideRepository = static::getContainer()->get(BikeRideRepository::class);
        $bikeRide = $bikeRideRepository->findOneBy(['bikeRideType' => $bikeRide['bikeRideType'], 'startAt' => $bikeRide['startAt']]);
        $cluster = $bikeRide->getClusters()->first();
        $selector = sprintf('a[href="%s"]', $this->urlGenerator->generate('session_add', ['bikeRide' => $bikeRide->getId()]));
        $this->assertSelectorExists($selector);
        $btn = $this->client->getCrawler()->filter($selector);
        $this->client->click($btn->link());
        $form = $this->client->getCrawler()->selectButton('S\'inscrire')->form();
        $form['session[session][cluster]'] = $bikeRide->getClusters()->first()->getId();
        $form['session[session][practice]'] = 'vtt';
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $session = $this->sessionRepository->findOneBy(['user' => $user, 'cluster' => $cluster]);
        $selector = sprintf('a[href="%s"]', $this->urlGenerator->generate('session_delete', ['session' => $session->getId()]));
        $this->assertSelectorExists($selector);
        $this->logOut();

        return $session;
    }

    private function validateParticipation(Session $session): void
    {
        $this->validateLogToBackOffice();
        $url = $this->urlGenerator->generate('admin_session_present');
        $this->client->request('POST', $url, ['sessionId' => $session->getId()]);
        $this->getEntityManager()->clear();
        $updatedSession = $this->sessionRepository->find($session->getId());

        $this->assertTrue($updatedSession->isPresent());
        $this->logOut();
    }

    private function validateLicenceState(array $identity, int $totalParticipations): void
    {
        $user = $this->getUserFromIdentity($identity);
        $this->getEntityManager()->clear();
        $licence = $this->licenceRepository->findOneBy(['user' => $user, 'season' => $this->seasonService->getCurrentSeason()]);
        $licenceIsYearly = $licence->getState()->isYearly();
        $this->assertTrue(($totalParticipations <= 3) ? !$licenceIsYearly : $licenceIsYearly);
    }

    private function getEntityFromReference(string $reference): object
    {

        return $this->client->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(BikeRideType::class)
            ->findOneBy(['name' => BikeRideTypeFixtures::getBikeRideTypeNameFromReference($reference)]);
    }


    // private function validateCoverageStep(): void
    // {
    //     $this->client->followRedirect();
    //     dump($this->client->getRequest()->getUri()); 
    //     dump($this->client->getResponse()->getStatusCode());
    //     $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
    //     dump(array_keys($form->all()));
    //     $form['user[lastLicence][2][coverage]'] = '1';
    //     $this->client->submit($form);
    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit coverage');
    // }

    // private function testDeleteUser(string $name): void
    // {
    //     $this->client->restart();
    //     $this->loginAdmin();
    //     $this->client->request('GET', '/admin/tool/delete/user');
    //     $users = $this->userRepository->findByFullName($name);
    //     $user = array_shift($users);
    //     $this->assertSelectorExists('form[name="user_search"]');
    //     $form = $this->client->getCrawler()->filter('form[name="user_search"]')->form();
    //     $this->addAutocompleteField($form, 'user_search[user]'); 
    //     $form['user_search[user]'] = $user;
    //     $this->client->submit($form);
    //     $this->client->request('GET', sprintf('/admin/tool/confirm/delete/user/%s', $user->getId()));
    //     $this->assertSelectorExists('form[name="form"]');
    //     $form = $this->client->getCrawler()->filter('form[name="form"]')->form();
    //     $this->client->submit($form);
    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Delete User');
    //     $this->logOut();
    // }
}