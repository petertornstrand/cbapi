<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller for home page.
 */
class HomeController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function home(Request $request): Response {
        return $this->render('home.html.twig');
    }
}
