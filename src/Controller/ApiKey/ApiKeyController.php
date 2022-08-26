<?php

namespace App\Controller\ApiKey;

use App\Entity\DatabaseSync;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ApiKeyController extends AbstractController
{

    #[Route('/api_key/create', name: 'create_api_key')]
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

    #[Route('/api_key/database', name: 'upload_database', methods: ['POST'])]
    public function uploadDatabase(Request $request, FileUploader $uploader, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repo = $entityManager->getRepository(DatabaseSync::class);

        $apiKey = $request->get("api_key");

        $dbSync = $repo->find($apiKey);

        // api key prüfen
        if ($dbSync === null) {
            return new Response("Operation not allowed", Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }

        $file = $request->files->get('db_file');

        if (empty($file)) {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        // alte Datei löschen -> damit wird nur eine Datei pro API-Key gespeichert
        if (!empty($dbSync->getDbFilename())) {
            $filePath = $this->getParameter('db_directory') . '/' . $dbSync->getDbFilename();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Datei uploaden
        $fileName = $uploader->upload($file);

        // Dateinamen in der Entity updaten
        $dbSync = $repo
            ->find($apiKey)
            ->setDbFilename($fileName);

        $entityManager->persist($dbSync);
        $entityManager->flush();

        $this->addFlash('success', 'Datei wurde erfolgreich hochgeladen.');

        return $this->redirectToRoute('baseView');
    }

}