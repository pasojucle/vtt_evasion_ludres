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
 
    public function __construct(UrlGeneratorInterface $router, FormFactoryInterface $formFactory) 
    {
        $this->router = $router;
 
        $this->formFactory = $formFactory;

    }
 
    public function getForm($term = null) 
    {
        $this->form = $this->formFactory->create(SearchType::class, $term, [
            'attr' =>[
                'action' => $this->router->generate('search')
            ]
        ]);
        
        return $this->form;
    }
}