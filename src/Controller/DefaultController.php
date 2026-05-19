<?php

namespace App\Controller;

use App\Repository\GitHubPhpProjectRepository;
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
    public function index(GitHubPhpProjectRepository $repository): Response
    {
        $repositories = $repository->findAllOrderedByStars();

        return $this->render('index.html.twig', [
            'repositories' => $repositories,
        ]);
    }

    #[Route('/refresh', name: 'refresh_github_projects')]
    public function refresh(GitHubApiClient $apiClient, GitHubPhpProjectRepository $repository): Response
    {
        try {
            $items = $apiClient->searchMostStarredPhpProjects(25);
            $updated = $repository->upsertFromGitHubSearchResults($items);
            $this->addFlash('success', sprintf('Refreshed %d GitHub projects in the database.', $updated));
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $exception) {
            $this->addFlash('error', 'Unable to refresh GitHub projects: ' . $exception->getMessage());
        }

        return $this->redirectToRoute('index');
    }

    #[Route('/project/{id}', name: 'project_show')]
    public function show(int $id, GitHubPhpProjectRepository $repository): Response
    {
        $repo = $repository->find($id);
        if (!$repo) {
            throw $this->createNotFoundException('Project not found.');
        }

        return $this->render('project/show.html.twig', [
            'repo' => $repo,
        ]);
    }
}
