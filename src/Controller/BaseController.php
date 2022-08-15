<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{

    #[Route('/', name: 'baseView')]
    public function renderBaseView(): Response
    {
        return $this->render('base.html.twig');
    }

}