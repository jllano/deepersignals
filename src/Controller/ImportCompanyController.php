<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\TeamHierarchyService;

class ImportCompanyController extends AbstractController
{   
    /**
     * @var TeamHierarchyService $teamHierarchyService
     */
    private TeamHierarchyService $teamHierarchyService;
    
    /**
     * ImportCompanyController constructor.
     *
     * @param TeamHierarchyService $teamHierarchyService
     */
    public function __construct(TeamHierarchyService $teamHierarchyService)
    {
        $this->teamHierarchyService = $teamHierarchyService;
    }
    
    #[Route('/api/import-team', name: 'api_import-team', methods: 'POST')]
    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $query = $request->query->get('_q');

        return $this->teamHierarchyService->import($file, $query);
    }
}