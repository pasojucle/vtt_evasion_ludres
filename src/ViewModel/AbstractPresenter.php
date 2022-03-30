<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Repository\MembershipFeeAmountRepository;
use App\Service\LicenceService;
use App\Twig\AppExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractPresenter
{
    public function __construct(
        public ServicesPresenter $services
    ) {
  
    }

}
