<?php

namespace App\Controller;

use App\Service\GitHubApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(GitHubApiClient $apiClient): Response
    {
        $repositories = [];
        $error = null;

        try {
            $repositories = $apiClient->searchMostStarredPhpProjects(25);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $exception) {
            $error = 'Unable to load GitHub projects: ' . $exception->getMessage();
        }

        return $this->render('index.html.twig', [
            'repositories' => $repositories,
            'error' => $error,
        ]);
    }
}
