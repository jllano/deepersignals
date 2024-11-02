<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\TeamHierarchyService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TeamHierarchyServiceTest extends TestCase
{
    private $serializer;
    private $service;

    protected function setUp(): void
    {
        $encoders = [new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->service = new TeamHierarchyService($this->serializer);
    }

    public function testImportInvalidCsv()
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('txt');

        $response = $this->service->import($file);

        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(['error' => 'Invalid file format'], json_decode($response->getContent(), true));
    }

    public function testImportValidCsv()
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('csv');
        $file->method('getPathname')->willReturn(__DIR__ . '/file1.csv');
        
        $csvData = "team,parent_team,manager_name,business_unit\nTeam A,,Manager A,Unit A\nTeam B,Team A,Manager B,Unit B";
        file_put_contents(__DIR__ . '/file1.csv', $csvData);

        $response = $this->service->import($file, 'Team B');

        $expectedHierarchy = [
            'Team A' => [
                'teamName' => 'Team A',
                'parentTeam' => '',
                'managerName' => 'Manager A',
                'businessUnit' => 'Unit A',
                'teams' => [
                    'Team B' => [
                        'teamName' => 'Team B',
                        'parentTeam' => 'Team A',
                        'managerName' => 'Manager B',
                        'businessUnit' => 'Unit B',
                        'teams' => []
                    ]
                ]
            ]
        ];
        
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($expectedHierarchy, json_decode($response->getContent(), true));
    }
}