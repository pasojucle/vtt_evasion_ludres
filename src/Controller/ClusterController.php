<?php

namespace App\Controller;

use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\ClusterRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClusterController extends AbstractController
{
    /**
     * @Route("/admin/groupe/", name="admin_cluster")
     */
    public function adminCluster(
        ClusterRepository $clusterRepository,
        Request $request,
        Session $session
    ): Response
    {


        

        return $this->redirectToRoute('admin_event_cluster_show', ['event' => $session->getCluster()->getEvent()->getId()]);
    }
}
