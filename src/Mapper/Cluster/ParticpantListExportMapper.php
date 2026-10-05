<?php

declare(strict_types=1);

namespace App\Mapper\Cluster;

use App\Dto\View\Gardian\GardianExportView;
use App\Dto\View\User\ParticipantExportView;
use App\Entity\Cluster;
use App\Entity\Enum\DisplayModeEnum;
use App\Entity\Member;
use App\Entity\Session;
use App\Mapper\Identity\PassportPhotoMapper;
use App\Service\PdfService;
use App\Service\ProjectDirService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ParticpantListExportMapper
{    
    public function __construct(
        private TranslatorInterface $translator,
        private Environment $twig,
        private PdfService $pdfService,
        private PassportPhotoMapper $passportPhotoMapper,
        private ProjectDirService $projectDir,
        private Filesystem $filesystem,
    ) {}

    public function streamToPdf(Cluster $cluster): void
    {
        $dirName = $this->projectDir->path('tmp', uniqid());

        if (!$this->filesystem->exists($dirName)) {
            $this->filesystem->mkdir($dirName);
        }

        $files = [];
        /** @var Session $session */
        foreach ($cluster->getSessions() as $session) {
            if ($session->isPresent()) {
                $member = $session->getMember();
                $render = $this->twig->render('cluster/export.html.twig', [
                    'view' => $this->mapMemberToView($member),
                    'media' => DisplayModeEnum::FILE,
                ]);
                $tmp = $member->getId() . '_tmp';
                $pdfFilepath = $this->pdfService->makePdf($render, $tmp, $dirName, 'B6');
                $files[] = [
                    'filename' => $pdfFilepath,
                ];
            }
        }

        $mergedFileName = uniqid() . '.pdf';
        $pathName = $this->pdfService->joinPdf($files, null, null, $this->projectDir->path('tmp', $mergedFileName));
        try {
            if ($this->filesystem->exists($pathName)) {
                readfile($pathName);
                flush();
            }
        } finally {
            $this->filesystem->remove($dirName);
        }
    }

    private function mapMemberToView(Member $member): ParticipantExportView
    {
        $identity = $member->getIdentity();
        $birthCommune = $identity->getBirthCommune();
        [$birthPlace, $birthDepartment, $birthCountry] = ($birthCommune)
            ? [$birthCommune->getName(), $birthCommune->getDepartment()->getName(), 'France']
            : [$identity->getBirthPlace(), null, $identity->getBirthCountry()];
        $emergencyContact = $member->getEmergencyContact();
        $health = $member->getHealth();
        $licence = $member->getLastLicence();
        $legalGardian = $member->getLegalGardian();
        $secondContact = $member->getSecondContact();

        return new ParticipantExportView(
            picture: $this->passportPhotoMapper->mapToPath($identity->getFilename()),
            fullName: $identity->getFullName(),
            birthDate: $identity->getBirthDate()?->format('d/m/Y'),
            birthPlace: $birthPlace,
            birthDepartment: $birthDepartment,
            birthCountry: $birthCountry,
            address: $identity->getAddress()->__tostring(),
            email: $identity->getEmail(),
            phones: implode(', ', [$identity->getMobile(), $identity->getPhone()]),
            emergencyPhone: $emergencyContact?->getPhone(),
            emergencyContact: $emergencyContact?->getKinship(),
            legalGardian: $legalGardian ? GardianExportView::fromEntity($legalGardian, $this->translator) : null,
            secondContact: $secondContact ? GardianExportView::fromEntity($secondContact, $this->translator) : null,
            healthContent: $health->getContent(),
            coverage: $licence->getCoverage()->trans($this->translator),
        );
    }
}
