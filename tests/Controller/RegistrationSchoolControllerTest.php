<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DateTime;
use DateInterval;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Enum\KinshipEnum;
use App\Entity\Enum\RegistrationEnum;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\AbstractTestController;
use App\DataFixtures\Common\BikeRideTypeFixtures;
use DateTimeImmutable;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class RegistrationSchoolControllerTest extends AbstractTestController
{
    public function testFullSchoolMemberLifecycle(): void
    {
        $schoolMember = ['name' => 'Frein', 'firstName' => 'Hydraulique', 'password' => 'test01'];
        $this->goToRegistration();
        $this->fillIdentityStep($schoolMember);
        $this->fillGardianIdentitiesStep();
        $this->validateTarifStep();
        $this->validateAgreementsStep();
        $this->validateHealtStep();
        $this->validateOverviewStep();
        $this->logOut();

        $this->validateFullTrialMemberLifecycle($schoolMember, BikeRideTypeFixtures::SUMMER_MOUNTAIN_BIKING_SCHOOL);
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
        $form['user[identity][birthDate]'] = (new DateTime())->sub(new DateInterval('P10Y'))->format('Y-m-d');
        $form['user[identity][birthPlace]'] = 54395;
        $form['user[identity][mobile]'] = '06 35 41 44 73';
        $form['user[identity][email]'] = 'frein.Hydraulique@test.fr';
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
        $this->client->followRedirect();
    }

    private function fillGardianIdentitiesStep(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[userGardians][0][kinship]'] = KinshipEnum::KINSHIP_FATHER->value;
        $form['user[userGardians][0][identity][name]'] = 'Hydraullique';
        $form['user[userGardians][0][identity][firstName]'] = 'Fourche';
        $form['user[userGardians][0][identity][birthDate]'] = (new DateTime())->sub(new DateInterval('P30Y'))->format('Y-m-d');
        $form['user[userGardians][0][identity][mobile]'] = '06 00 00 00 00';
        $form['user[userGardians][0][identity][email]'] = 'fourche.hydraulique@test.fr';

        $form['user[userGardians][1][kinship]'] = KinshipEnum::KINSHIP_MOTHER->value;
        $form['user[userGardians][1][identity][name]'] = 'Indexée';
        $form['user[userGardians][1][identity][firstName]'] = 'Vitesse';
        $form['user[userGardians][1][identity][mobile]'] = '06 00 00 00 00';
        $form['user[userGardians][1][identity][email]'] = 'vitesse.indexee@test.fr';
 
        $this->client->submit($form);
    
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit registration');
        $this->client->followRedirect();
    }

    private function validateTarifStep(): void
    {
        $this->assertAnySelectorTextContains('a.btn', 'Suivant');
        $this->client->clickLink('Suivant');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK,'View tarif');
    }

    private function validateAgreementsStep(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[lastLicence][licenceAuthorizationAgreements][BACK_HOME_ALONE][agreed]'] = '1';
        $form['user[lastLicence][licenceAuthorizationAgreements][EMERGENCY_CARE_SCHOOL][agreed]'] = '1';
        $form['user[lastLicence][licenceAuthorizationAgreements][IMAGE_USE_SCHOOL][agreed]'] = '1';
        $form['user[lastLicence][licenceAuthorizationAgreements][PARENTAL_CONSENT][agreed]'] = '1';
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
        $form['user[lastLicence][licenceHealthAgreements][HEALTH_SCHOOL][agreed]'] = '1';
        $form['user[lastLicence][licenceHealthAgreements][HEALTH_SCHOOL_2][agreed]'] = '1';
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

    private function validateFullTrialMemberLifecycle(array $schoolMember, string $bikeRideTypeReference): void
    {
        $startAt = (new DateTimeImmutable())->setTime(0,0,0);
        $bikeRideType = $this->getEntityFromReference($bikeRideTypeReference);

        for($i = 1; $i <= 4; ++$i) {
            if (4 === $i) {
                $this->validateSchoolMemberYearlyRegistration($schoolMember, $i);
            }
            $bikeRideStartAt = (clone $startAt)
                ->modify('next saturday')
                ->modify(sprintf('+%d weeks', $i - 1));
            $bikeRide = ['bikeRideType' => $bikeRideType, 'startAt' => $bikeRideStartAt];
            $this->validateAdminAddBikeRide($bikeRideType, $bikeRideStartAt, $i);
            $sessionId = $this->validateSchoolMemberRegistrationToBikeRide($schoolMember, $bikeRide);
            $this->validateSchoolMemberParticipation($sessionId);
            $this->validateSchoolMemberLicenceState($schoolMember, $i);
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

    private function validateSchoolMemberRegistrationToBikeRide(array $identity, array $bikeRide): int
    {
        $user = $this->getUserFromIdentity($identity);
        $this->loginUser($user);
        $url = $this->urlGenerator->generate('schedule', ['period' => 'tous']);
        $this->client->request('GET', $url);
        $bikeRide = $this->bikeRideRepository->findOneBy(['bikeRideType' => $bikeRide['bikeRideType'], 'startAt' => $bikeRide['startAt']]);
        dump(sprintf('bike ride %s => %s / %s',$bikeRide->getId(), $bikeRide->getStartAt()->format('Y-m-d'), $bikeRide->getClosingDuration()));
        $cluster = $this->clusterRepository->findOneBy(['bikeRide' => $bikeRide->getId(), 'level' => $user->getLevel()]);
        dump(sprintf('cluster %s => %s',$cluster->getId(), $cluster->getLevel()->getTitle()));
        $selector = sprintf('a[href="%s"]', $this->urlGenerator->generate('session_add', ['bikeRide' => $bikeRide->getId()]));
        $this->assertSelectorExists($selector);
        $btn = $this->client->getCrawler()->filter($selector);
        $this->client->click($btn->link());
        $form = $this->client->getCrawler()->selectButton('S\'inscrire')->form();
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

    private function validateSchoolMemberParticipation(int $sessionId): void
    {
        $this->validateLogToBackOffice();
        $url = $this->urlGenerator->generate('admin_session_present');
        $this->client->request('POST', $url, ['sessionId' => $sessionId]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->logOut();

        $updatedSession = $this->sessionRepository->find($sessionId);
        $this->assertTrue($updatedSession->isPresent());
    }

    private function validateSchoolMemberLicenceState(array $identity, int $totalParticipations): void
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

    private function validateSchoolMemberYearlyRegistration(array $identity, int $loop): void
    {            
        $user = $this->getUserFromIdentity($identity);       
        $this->loginUser($user);    

        $this->validateIndentityStep();
        $this->validateGardianIndentitiesStep();
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
        $this->client->followRedirect();
    }

    private function validateGardianIndentitiesStep(): void
    {
        $this->assertSelectorExists('form[name="user"]');
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit coverage');
        $this->client->followRedirect();
    }

    private function fillCoverageStep(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[lastLicence][coverage]'] = '2';
        foreach ($form['user[lastLicence][options]'] as $checkbox) {
            if ($checkbox->getValue() === 'no_additional_option') {
                $checkbox->tick();
            } else {
                $checkbox->untick();
            }
        }
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