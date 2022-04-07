<?php

declare(strict_types=1);

namespace App\UseCase\User;

use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\HeaderUtils;

abstract class ExportUsersFiltered
{
    public string $filterName;

    public function __construct(
        protected UserRepository $userRepository
    ) {
    }

    public function execute(Request $request): Response
    {
        $session = $request->getSession();
        $filters = $session->get($this->filterName);

        $query = $this->getQuery($filters);
        $users = $query->getQuery()->getResult();
        $content = $this->getContent($users);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_email.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function getContent(array $users): string
    {
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach ($users as $user) {
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->isFinal()];
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }

    abstract protected function getQuery(array $filters): QueryBuilder;
}