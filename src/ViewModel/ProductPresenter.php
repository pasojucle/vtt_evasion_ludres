<?php

namespace App\ViewModel;

use App\Entity\Product;
use App\Service\LicenceService;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductPresenter 
{
    private ParameterBagInterface $parameterBag;
    private LicenceService $licenceService;
    private Security $security;
    private $viewModel;

    public function __construct(
        ParameterBagInterface $parameterBag, 
        LicenceService $licenceService,
        Security $security
    )
    {
        $this->parameterBag = $parameterBag;
        $this->licenceService = $licenceService;
        $this->security = $security;
    }

    public function present(?Product $product): void
    {
        $productDirectory = $this->parameterBag->get('products_directory');
        $user = $this->security->getUser();

        if (null !== $product) {
            $this->viewModel = ProductViewModel::fromProduct($product, $productDirectory, $user, $this->licenceService);
        } else {
            $this->viewModel = new ProductViewModel();
        }
    }


    public function viewModel(): ProductViewModel
    {
        return $this->viewModel;
    }

}