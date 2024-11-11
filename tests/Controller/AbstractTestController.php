<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use DOMDocument;
use App\Repository\UserRepository;
use Symfony\Component\DomCrawler\Form;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\TextareaFormField;

abstract class AbstractTestController extends WebTestCase
{
    public KernelBrowser $client;
    public UserRepository $userRepository;

    public function init()
    {
        $this->client = static::createClient([], ['REMOTE_ADDR' => '11.11.11.11']);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }
    
    public function addAutocompleteField(Form &$form, string $name): void
    {
        $domdocument = new DOMDocument;
        $ff = $domdocument->createElement('textarea');
        $ff->setAttribute('name', $name);
        $formfield = new TextareaFormField($ff);

        $form->set($formfield); 
    }

    public function loginAdmin(): void
    {
        $this->client->request('GET', '/login');
        $testAdmin = $this->userRepository->findOneByLicenceNumber('624758');
        $this->client->loginUser($testAdmin);
    }

    public function logOut():void
    {
        $this->client->request('GET', '/logout');
    }
}