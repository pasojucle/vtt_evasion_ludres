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

class SettingController extends AbstractController
{
    /**
     * @Route("/settings", name="settings")
     */
    public function settings(
        ParameterRepository $parameterRepository,
        ParameterService $parameterService,
        EncryptionService $encryptionService,
        Request $request,
        EntityManagerInterface $entityManager
    )
    {
        $parameters = $parameterRepository->findAll();
        $parameterEncryption = $parameterService->getEncryptionValue($parameters);

        $form = $this->createForm(ParametersType::class, ['parameters' => new ArrayCollection($parameters)]);

        $form->handleRequest($request);
    
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $entityManager->flush();

            if (array_key_exists('parameters', $data) && !empty($data['parameters'])) {
                $dataParameterEncryption = $parameterService->getEncryptionValue($data['parameters']);
                if ($dataParameterEncryption !== $parameterEncryption) {
                    $encryptionService->toggleEncryption($dataParameterEncryption);
                }
            }
        }

        return $this->render('setting/set.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
