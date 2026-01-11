<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DateTime;
use DateInterval;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Enum\RegistrationEnum;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\AbstractTestController;
use App\DataFixtures\Common\BikeRideTypeFixtures;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class RegistrationAdultControllerTest extends AbstractTestController
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
        $url = $this->urlGenerator->generate('registration_form');
        $this->client->request('GET', $url);
        $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');
    }

    private function fillIdentityStep(array $user): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $this->addAutocompleteField($form, 'user[identity][birthPlace]'); 
        $form['user[identity][name]'] = $user['name'];
        $form['user[identity][firstName]'] = $user['firstName'];
        $form['user[identity][birthDate]'] = (new DateTime())->sub(new DateInterval('P20Y'))->format('Y-m-d');
        $form['user[identity][birthPlace]'] = 54395;
        $form['user[identity][mobile]'] = '06 35 41 44 73';
        $form['user[identity][email]'] = 'roue.libre@test.fr';
        $form['user[identity][emergencyPhone]'] = '06 00 00 00 00';
        $form['user[identity][emergencyContact]'] = 'banane';
        $form['user[identity][pictureFile]'] = null;
        $form['user[identity][address][street]'] = 'rue des champs';
        $form['user[identity][address][postalCode]'] = '54550';
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

        for($i = 1; $i <= 4; ++$i) {
            if (4 === $i) {
                $this->validateAdultYearlyRegistration($adult, $i);
            }
            $bikeRideStartAt = $startAt->add(new DateInterval(sprintf('P%dD', 7 * $i - $startAt->format('w'))));
            $bikeRide = ['bikeRideType' => $bikeRideType, 'startAt' => $bikeRideStartAt];
            $this->validateAdminAddBikeRide($bikeRideType, $bikeRideStartAt, $i);
            $sessionId = $this->validateAdultRegistrationToBikeRide($adult, $bikeRide);
            $this->validateAdultParticipation($sessionId);
            $this->validateAdultLicenceState($adult, $i);
        }
    }

    private function validateLogToBackOffice(): void
    {
        $admin = $this->userRepository->findOneByLicenceNumber('624758');

        $this->loginUser($admin);
    }

    private function validateAdminAddBikeRide(BikeRideType $bikeRideType, DateTimeImmutable $startAt, int $loop): void
    {
        $this->validateLogToBackOffice();
        $url = $this->urlGenerator->generate('admin_bike_rides');
        $this->client->request('GET', $url);
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
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit coverage');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $url = $this->urlGenerator->generate('admin_bike_rides', ['period' => 'tous']);
        $this->client->request('GET', $url);

        $this->assertSelectorCount($loop,'li.list-dropdown');
        
        $bikeRide = $this->bikeRideRepository->findOneBy(['bikeRideType' => $bikeRideType, 'startAt' => $startAt]);
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
        $this->assertResponseIsSuccessful(sprintf('Validate cluster bike ride %s succesful', $bikeRide->getStartAt()->format('d/m/Y')));
    }

    private function validateAdultRegistrationToBikeRide(array $identity, array $bikeRide): int
    {
        $user = $this->getUserFromIdentity($identity);
        $this->loginUser($user);
        $url = $this->urlGenerator->generate('schedule', ['period' => 'tous']);
        $this->client->request('GET', $url);
        $bikeRide = $this->bikeRideRepository->findOneBy(['bikeRideType' => $bikeRide['bikeRideType'], 'startAt' => $bikeRide['startAt']]);
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
        $sessionId = $session->getId();
        $selector = sprintf('a[href="%s"]', $this->urlGenerator->generate('session_delete', ['session' => $sessionId]));
        $this->assertSelectorExists($selector);
        $this->logOut();

        return $sessionId;
    }

    private function validateAdultParticipation(int $sessionId): void
    {
        $this->validateLogToBackOffice();
        $url = $this->urlGenerator->generate('admin_session_present');
        $this->client->request('POST', $url, ['sessionId' => $sessionId]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->logOut();

        $updatedSession = $this->sessionRepository->find($sessionId);
        $this->assertTrue($updatedSession->isPresent());
    }

    private function validateAdultLicenceState(array $identity, int $totalParticipations): void
    {
        $user = $this->getUserFromIdentity($identity);
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

    private function validateAdultYearlyRegistration(array $identity, int $loop): void
    {            
        $user = $this->getUserFromIdentity($identity);       
        $this->loginUser($user);    

        $this->validateIndentityStep();
        $this->validateTarifStep();
        $this->fillCoverageStep();
        $this->validateAgreementsStep();
        $this->validateHealtStep();
        $this->validateOverviewStep();
        
        $this->logOut();
    }

    private function validateIndentityStep(): void
    {
        $url = $this->urlGenerator->generate('user_registration_form', ['step' => 1]);
        $this->client->request('GET', $url);
        $this->assertSelectorExists('form[name="user"]');
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit coverage');
    }

    private function fillCoverageStep(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[lastLicence][coverage]'] = '1';
        /** @var ChoiceFormField $checkbox */
        foreach ($form['user[lastLicence][options]'] as $checkbox) {
            if ($checkbox->getValue() === 'no_additional_option') {
                $checkbox->tick();
            } else {
                $checkbox->untick();
            }
        }
        $form['user[lastLicence][isVae]'] = '0';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit coverage');
        $this->client->followRedirect();
    }

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