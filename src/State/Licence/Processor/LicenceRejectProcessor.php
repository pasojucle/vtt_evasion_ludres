<?php

declare(strict_types=1);

namespace App\State\Licence\Processor;

use App\Dto\Form\LicenceReject;
use App\Dto\ProcessorResult;
use App\Dto\Service\MailerResult;
use App\Entity\Member;
use App\Service\FilterDecoderService;
use App\Service\LicenceService;
use App\Service\MailerService;
use App\State\DialogProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LicenceRejectProcessor implements DialogProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LicenceService $licenceService,
        private FilterDecoderService $filterDecoder,
        private MailerService $mailerService,
    ) {
    }

    public function process(object $licenceReject, ?string $filter): ProcessorResult
    {
        assert($licenceReject instanceof LicenceReject);

        $licence = $licenceReject->licence;
        $member = $licence->getMember();

        $result = $this->sendMail($licenceReject->content, $member);
        dump($result);

        $tansition = ($licence->getState()->isYearly()) ? 'reject_yearly_file' : 'reject_trial_file';

        if ($result->success && $this->licenceService->applyTransition($licence, $tansition)) {
            $this->entityManager->persist($licence);
            $this->entityManager->flush();

            return new ProcessorResult(
                success: true,
                targetRoute: 'admin_registration_list',
                routeParams: $this->filterDecoder->decode($filter),
                messageKey: 'registration.flash.success.reject',
                flashType: 'success', 
            );
        }

        return new ProcessorResult(
            success: false,
            targetRoute: 'admin_registration_list',
            routeParams: $this->filterDecoder->decode($filter),
            messageKey: 'registration.flash.error.reject',
            flashType: 'danger',
        );
    }

    private function sendMail(string $content, Member $member): MailerResult
    {
        $mainIdentity = $member->getMainIdentity();

        return $this->mailerService->sendMailToMember(
            $mainIdentity->getEmail(),
            $mainIdentity->getFullName(),
            'Votre inscription au club de Vtt Évasion Ludres',
            $content,
        );
    }
}
