<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\CommuneRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\AbstractTestController;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class RegistrationController extends AbstractTestController
{
    public function testRegistration(): void
    {
        $name = 'Roue';
        $firstName = 'Libre';
        $this->goToRegistration();
        $this->fillIdentityStep($name, $firstName);
        $this->validateTarifStep();
        $this->validateAgreementsStep();
        $this->validateHealtStep();
        $this->validateOverviewStep();
    }

    private function goToRegistration(): void
    {
    
        $this->client->request('GET', '/inscription');
        $this->client->followRedirect();

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name="user"]');
    }

    private function fillIdentityStep(string $name, string $firstName): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $this->addAutocompleteField($form, 'user[identity][birthPlace]'); 
        $communeRepository = static::getContainer()->get(CommuneRepository::class);
        $communes = $communeRepository->findByPostalCode('54000');
        $form['user[identity][name]'] = $name;
        $form['user[identity][firstName]'] = $firstName;
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
        $form['user[plainPassword][first]'] = 'test01';
        $form['user[plainPassword][second]'] = 'test01';
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