<?php

namespace App\Controller;

use App\Entity\Section;
use App\Form\SectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SectionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/section/edit/{section}", name="section_edit")
     */
    public function edit(Request $request, Section $section)
    {
        $form = $this->createForm(SectionType::class, $section,[
            'action' => $this->generateUrl('section_edit', [
                'section' => $section->getId(),
                ]),
        ]);


        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $section = $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRoute('home',[
                '_fragment' => $section->getId(),
                ]);
        }

        return $this->render('section/edit.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/section/delete/{section}", name="section_delete")
     */
    public function delete(Request $request, Section $section)
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('section_delete', [
                'section'=> $section->getId(),
            ]),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($section);
            $this->entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('section/delete.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
        ]);
    }
}
