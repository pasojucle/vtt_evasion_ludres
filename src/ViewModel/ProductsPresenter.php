<?php

namespace App\ViewModel;

use App\Entity\Product;
use App\Service\LicenceService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductsPresenter 
{
    private ParameterBagInterface $parameterBag;
    private LicenceService $licenceService;
    private Security $security;
    private $viewModel;

    public function __construct(
        ParameterBagInterface $parameterBag,
        LicenceService $licenceService,
        Security $security)
    {
        $this->parameterBag = $parameterBag;
        $this->licenceService = $licenceService;
        $this->security = $security;
    }

    public function present(Paginator $products): void
    {
        $productDirectory = $this->parameterBag->get('products_directory');

        if (!empty($products)) {
            $this->viewModel = ProductsViewModel::fromProducts($products, $productDirectory, $this->security, $this->licenceService);
        } else {
            $this->viewModel = new ProductsViewModel();
        }
    }


    public function viewModel(): ProductsViewModel
    {
        return $this->viewModel;
    }

}