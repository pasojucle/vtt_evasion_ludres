<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\CertificateType;
use App\UseCase\Certificate\GetAccompanyingAdultCertificate;
use App\UseCase\Certificate\GetRegistrationCertificate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
class CertificateController extends AbstractController
{
    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }

    #[Route('/admin/adherent/certificate/{user}', name: 'user_certificate', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationCertificate(
        Request $request,
        GetRegistrationCertificate $getRegistrationCertificate,
        User $user
    ): Response {
        list($content) = $getRegistrationCertificate->execute($request, $user);
        $form = $this->createForm(CertificateType::class, [
            'content' => $content,
        ]);
        $form->handleRequest($request);
        $filename = null;

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            list($content, $filename) = $getRegistrationCertificate->execute($request, $user, mb_convert_encoding($data['content'], 'UTF-8'));
            $filename = base64_encode($this->parameterBag->get('data_directory_path') . $filename);
        }

        return $this->render('certificate/certificate.html.twig', [
            'form' => $form->createView(),
            'title' => 'Attestation d\'inscription pour CE',
            'content' => $content,
            'filename' => $filename,
        ]);
    }

    #[Route('/admin/accompaganteur/certificate/{user}', name: 'user_accompanying_certificate', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminAccompanyingCertificate(
        Request $request,
        GetAccompanyingAdultCertificate $getAccompanyingAdultCertificate,
        User $user
    ): Response {
        list($content) = $getAccompanyingAdultCertificate->execute($request, $user);
        $form = $this->createForm(CertificateType::class, [
            'content' => $content,
        ]);
        $form->handleRequest($request);
        $filename = null;

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            list($content, $filename) = $getAccompanyingAdultCertificate->execute($request, $user, mb_convert_encoding($data['content'], 'UTF-8'));
            $filename = base64_encode($this->parameterBag->get('data_directory_path') . $filename);
        }

        return $this->render('certificate/certificate.html.twig', [
            'form' => $form->createView(),
            'title' => 'Attestation adulte accompagnateur',
            'content' => $content,
            'filename' => $filename,
        ]);
    }
}
