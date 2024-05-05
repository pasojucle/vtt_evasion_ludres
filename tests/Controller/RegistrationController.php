<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\CommuneRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\Controller\AbstractTestController;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class RegistrationController extends AbstractTestController
{
    public function testRegistration()
    {
        $this->init();
        $name = 'Roue';
        $firstName = 'Libre';
        $this->testAdultTestingRegistration($name, $firstName);
        $this->testDeleteUser($name);
    }

    private function testDeleteUser(string $name): void
    {
        $this->client->restart();
        $this->loginAdmin();
        $this->client->request('GET', '/admin/tool/delete/user');
        $users = $this->userRepository->findByFullName($name);
        $user = array_shift($users);
        $this->assertSelectorExists('form[name="user_search"]');
        $form = $this->client->getCrawler()->filter('form[name="user_search"]')->form();
        $this->addAutocompleteField($form, 'user_search[user]'); 
        $form['user_search[user]'] = $user;
        $this->client->submit($form);
        $this->client->request('GET', sprintf('/admin/tool/confirm/delete/user/%s', $user->getId()));
        $this->assertSelectorExists('form[name="form"]');
        $form = $this->client->getCrawler()->filter('form[name="form"]')->form();
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Delete User');
        $this->logOut();
    }

    private function testAdultTestingRegistration(string $name, string $firstName): void
    {
        $this->testCreateIdentity($name, $firstName);
        $this->testViewTarif();
        $this->testAproval();
        $this->testLicenceSwornCertifications();
        $this->assertEmailCount(2);
    }

    private function testCreateIdentity(string $name, string $firstName): void
    {
        $this->client->request('GET', '/inscription');
        $this->client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK,'Registration');
        $this->assertSelectorExists('form[name="user"]');
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $this->addAutocompleteField($form, 'user[identities][0][birthPlace]'); 
        $communeRepository = static::getContainer()->get(CommuneRepository::class);
        $communes = $communeRepository->findByPostalCode('54000');
        $form['user[identities][0][name]'] = $name;
        $form['user[identities][0][firstName]'] = $firstName;
        $form['user[identities][0][birthDate]'] = '09/09/1971';
        $form['user[identities][0][birthPlace]'] = $communes[0]->getId();
        $form['user[identities][0][mobile]'] = '06 35 41 44 73';
        $form['user[identities][0][email]'] = 'roue.libre@test.fr';
        $form['user[identities][0][pictureFile]'] = null;
        $form['user[identities][0][address][street]'] = 'rue des champs';
        $form['user[identities][0][address][postalCode]'] = '54550';
        $communes = $communeRepository->findByPostalCode('54550');
        /** @var ChoiceFormField $formaddressCommune */
        $formaddressCommune = $form['user[identities][0][address][commune]'];
        $formaddressCommune->disableValidation()->setValue((string) $communes[0]->getId());
        $form['user[plainPassword][first]'] = 'test01';
        $form['user[plainPassword][second]'] = 'test01';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit registration');
    }

    private function testViewTarif(): void
    {
        $this->client->followRedirect();
        $this->assertAnySelectorTextContains('a.btn', 'Suivant');
        $this->client->clickLink('Suivant');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK,'View tarif');
    }

    private function testAproval(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[approvals][0][value]'] = '1';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit aproval');

    }

    private function testLicenceSwornCertifications(): void
    {
        $this->client->followRedirect();
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[licences][0][licenceSwornCertifications][0][value]'] = '1';
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND,'Submit Sworm Certifications');
    }

    private function testCoverage(): void
    {
        $form = $this->client->getCrawler()->filter('form[name="user"]')->form();
        $form['user[licences][2][coverage]'] = '1';
        $this->client->submit($form);
    }
}