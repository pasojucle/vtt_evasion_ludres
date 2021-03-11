<?php

namespace App\Controller;

use App\Form\ParametersType;
use App\Service\ParameterService;
use App\Service\EncryptionService;
use App\Repository\ParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SettingController extends AbstractController
{
    /**
     * @Route("/admin/settings", name="settings")
     */
    public function settings(
        ParameterRepository $parameterRepository,
        ParameterService $parameterService,
        EncryptionService $encryptionService,
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    )
    {
        
        $parameters = $parameterRepository->findAll();
        $parameters = new ArrayCollection($parameters);
        $encryption = $parameterService->getEncryption($parameters);

        $form = $this->createForm(ParametersType::class, ['parameters' => $parameters]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('encryptionLock', true);
            $data = $form->getData();
            $files = $request->files->get('parameters');
            $parameterService->uploadFiles($data['parameters'], $files['parameters']);
            $entityManager->flush();

            $dataEncryption = $parameterService->getEncryption($data['parameters']);
            if ($dataEncryption !== $encryption) {
                $encryptionService->toggleEncryption($dataEncryption);
            }
            $session->remove('encryptionLock');
            return $this->redirectToRoute('home');
        }

        return $this->render('setting/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
