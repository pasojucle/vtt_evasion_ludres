<?php

declare(strict_types=1);

namespace App\Dto\View\Session;

use App\Core\Contract\View\ComponentViewInterface;

readonly class SessionAddFormView implements ComponentViewInterface
{
    public function getName(): string
    {
        return 'view';
    }


    public function getTemplate(): string
    {
        return 'session/admin/add/_form.html.twig';
    }
}
