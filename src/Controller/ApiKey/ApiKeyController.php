<?php

namespace App\Controller\ApiKey;

use App\Entity\DatabaseSync;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ApiKeyController extends AbstractController
{

    #[Route('/create/api_key', name: 'create_api_key')]
    public function createApiKey(Request $request, ManagerRegistry $doctrine): Response
    {
        $uuid = Uuid::v4();

        $apiKey = new DatabaseSync();
        $apiKey->setApiKey($uuid);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($apiKey);
        $entityManager->flush();

        return $this->renderForm('base/success.html.twig', [
            'message' => 'API-Key wurde generiert.'
        ]);
    }

    #[Route('/api_key', methods: ['GET'])]
    public function getApiKeyTable(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $allKeys = $entityManager->getRepository(DatabaseSync::class)->findAll();

        if (empty($allKeys)) {
            return new Response('Keine API-Keys gefunden');
        }

        return $this->renderForm('api_key/api_key_table.html.twig', [
            'apiKeys' => $allKeys
        ]);

    }

}