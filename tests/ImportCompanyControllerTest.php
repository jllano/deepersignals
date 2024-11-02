<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\ImportCompanyController;
use App\Service\TeamHierarchyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImportCompanyControllerTest extends TestCase
{
    private $teamHierarchyService;
    private $controller;

    protected function setUp(): void
    {
        $this->teamHierarchyService = $this->createMock(TeamHierarchyService::class);
        $this->controller = new ImportCompanyController($this->teamHierarchyService);
    }

    public function testImport()
    {
        $file = $this->createMock(UploadedFile::class);
        $request = new Request([], [], [], [], ['file' => $file], ['QUERY_STRING' => '_q=query']);

        $expectedResponse = new JsonResponse(['some' => 'data']);
        $this->teamHierarchyService->method('import')->willReturn($expectedResponse);

        $response = $this->controller->import($request);

        $this->assertSame($expectedResponse, $response);
    }
}