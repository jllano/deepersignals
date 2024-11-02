<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * This class provides functionality to import a CSV file and return a team hierarchy.
 */
class TeamHierarchyService
{   
    /**
     * @var SerializerInterface $serializer
     */
    private SerializerInterface $serializer;
    
    /**
     * TeamHierarchyService constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    
    /**
     * This method imports a CSV file and returns a JSON response with the team hierarchy.
     * @param UploadedFile $file
     * @param string|null $query
     * @return JsonResponse
     */
    public function import(UploadedFile $file, ?string $query = null): JsonResponse
    {
        if (!$this->isValidCsv($file)) {
            return new JsonResponse(['error' => 'Invalid file format'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Decode the CSV data
        $csvData = file_get_contents($file->getPathname());
        $data = $this->serializer->decode($csvData, 'csv', [CsvEncoder::DELIMITER_KEY => ',']);
        
        // Build the team hierarchy
        $hierarchy = $this->buildHierarchy($data);
        
        // If there is a query pass, filter the hierarchy based on the query
        if ($query) {
            $hierarchy = $this->filterHierarchy($hierarchy, $query);
        }

        return new JsonResponse($hierarchy);
    }
    
    /**
     * This method checks if the uploaded file is a CSV file.
     * @param UploadedFile $file
     * @return bool
     */
    private function isValidCsv(UploadedFile $file): bool
    {
        return $file->getClientOriginalExtension() === 'csv';
    }
    
    /**
     * This method builds a team hierarchy from the CSV data.
     * @param array $data
     * @return array
     */
    private function buildHierarchy(array $data): array
    {
        $teams = [];
        foreach ($data as $row) {
            $teamName = $row['team'];
            $parentTeam = $row['parent_team'];
            $managerName = $row['manager_name'];
            $businessUnit = $row['business_unit'] ?? '';

            $teams[$teamName] = [
                'teamName' => $teamName,
                'parentTeam' => $parentTeam,
                'managerName' => $managerName,
                'businessUnit' => $businessUnit,
                'teams' => []
            ];
        }

        $hierarchy = [];
        foreach ($teams as $teamName => &$team) {
            if ($team['parentTeam'] === '') {
                $hierarchy[$teamName] = &$team;
            } else {
                $teams[$team['parentTeam']]['teams'][$teamName] = &$team;
            }
        }

        return $hierarchy;
    }
    
    /**
     * This method filters the team hierarchy based on the query.
     * @param array $hierarchy
     * @param string $query
     * @return array
     */
    private function filterHierarchy(array $hierarchy, string $query): array
    {
        foreach ($hierarchy as $teamName => $team) {
            if ($teamName === $query) {
                return [$teamName => $team];
            }

            if (!empty($team['teams'])) {
                $filtered = $this->filterHierarchy($team['teams'], $query);
                if (!empty($filtered)) {
                    return [$teamName => array_merge($team, ['teams' => $filtered])];
                }
            }
        }

        return [];
    }
}