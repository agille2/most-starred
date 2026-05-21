<?php

namespace App\Controller;

use App\Repository\GitHubPhpProjectRepository;
use App\Service\GitHubApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class DefaultController extends AbstractController
{
    /**
     * Renders the initial HTML page layout container.
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        // No repository call here! Twig receives an empty container, speeding up page loads.
        return $this->render('index.html.twig');
    }

    /**
     * Provides the paginated JSON data source consumed by DataTables AJAX.
     */
    #[Route('/api/projects', name: 'api_projects', methods: ['GET'])]
    public function apiData(Request $request, GitHubPhpProjectRepository $repository): JsonResponse
    {
        // 1. Extract processing pagination bounds from DataTables parameters
        $draw = $request->query->getInt('draw', 1);
        $start = $request->query->getInt('start', 0);
        $length = $request->query->getInt('length', 25);
        
        // Extract global text search term
        $searchArray = $request->query->all('search');
        $searchValue = $searchArray['value'] ?? '';

        // 2. Query structural metadata counts
        $totalRecords = $repository->count([]);
        $filteredRecords = $repository->countFilteredForDataTables($searchValue);

        // 3. Fetch slice of records matching parameters
        $projects = $repository->findPaginatedForDataTables($start, $length, $searchValue);

        // 4. Transform entities into uniform key/value schemas matching JavaScript rules
        $data = [];
        foreach ($projects as $project) {
            $data[] = [
                'id'    => $project->getRepoId(),
                'name'  => $project->getName(),
                'stars' => $project->getStars(),
            ];
        }

        // 5. Output response wrapper
        return new JsonResponse([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    #[Route('/refresh', name: 'refresh_github_projects')]
    public function refresh(GitHubApiClient $apiClient, GitHubPhpProjectRepository $repository): Response
    {
        try {
            $items = $apiClient->searchMostStarredPhpProjects(100);
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
