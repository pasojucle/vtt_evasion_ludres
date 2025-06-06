<?php

namespace App\Repository;

use App\Entity\SwornCertification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SwornCertification>
 *
 * @method SwornCertification|null find($id, $lockMode = null, $lockVersion = null)
 * @method SwornCertification|null findOneBy(array $criteria, array $orderBy = null)
 * @method SwornCertification[]    findAll()
 * @method SwornCertification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SwornCertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SwornCertification::class);
    }

    public function findSchoolSwornCertifications(array $existingLicenceSwornCertifications): array
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('sc.school', ':isSchool'));
        $parameters = [new Parameter('isSchool', true)];
        $this->addExistingLicenceSwornCertificationCriteria($andX, $parameters, $existingLicenceSwornCertifications);

        return $this->createQueryBuilder('sc')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findAdultSwornCertifications(array $existingLicenceSwornCertifications): array
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('sc.adult', ':isAdult'));
        $parameters = [new Parameter('isAdult', true)];
        $this->addExistingLicenceSwornCertificationCriteria($andX, $parameters, $existingLicenceSwornCertifications);

        return $this->createQueryBuilder('sc')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findCommonSwornCertifications(array $existingLicenceSwornCertifications): array
    {
        $andX = (new Expr())->andX();
        $andX->add((new Expr())->eq('sc.school', ':isSchool'));
        $andX->add((new Expr())->eq('sc.adult', ':isAdult'));
        $parameters = [new Parameter('isAdult', true), new Parameter('isSchool', true)];
        $this->addExistingLicenceSwornCertificationCriteria($andX, $parameters, $existingLicenceSwornCertifications);


        return $this->createQueryBuilder('sc')
            ->andWhere($andX)
            ->setParameters(new ArrayCollection($parameters))
            ->getQuery()
            ->getResult()
        ;
    }

    private function addExistingLicenceSwornCertificationCriteria(Andx &$andX, array &$parameters, array $existingLicenceSwornCertifications): void
    {
        if (!empty($existingLicenceSwornCertifications)) {
            $andX->add((new Expr())->notIn('sc.id', ':existingLicenceSwornCertifications'));
            $parameters[] = new Parameter('existingLicenceSwornCertifications', $existingLicenceSwornCertifications);
        }
    }
}
