<?php

namespace App\Service;

use App\Form\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchService
{
    private $form; 
 
    private $router;
 
    private $formFactory;
 
    public function __construct(UrlGeneratorInterface $router, FormFactoryInterface $formFactory, $term = null) {
 
        $this->router = $router;
 
        $this->formFactory = $formFactory;

        $this->form = $this->formFactory->create(SearchType::class, $term, [
            'attr' =>[
                'action' => $this->router->generate('search')
            ]
        ]);
    }
 
    public function getForm() {
        
        return $this->form;
    }
}