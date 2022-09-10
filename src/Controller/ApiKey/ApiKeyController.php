<?php

namespace App\Controller\ApiKey;

use App\Entity\DatabaseSync;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

use Symfony\Component\Validator\Constraints\Uuid as UuidConstraint;

class ApiKeyController extends AbstractController
{

    #[Route('/api_key/create', name: 'create_api_key')]
    public function createApiKey(Request $request, ManagerRegistry $doctrine): Response
    {
        $uuid = Uuid::v4();

        $apiKey = new DatabaseSync();
        $apiKey->setApiKey($uuid);
        $apiKey->setFileSize(0);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($apiKey);
        $entityManager->flush();

        return $this->renderForm('base/success.html.twig', [
            'message' => 'API-Key wurde generiert.'
        ]);
    }

    #[Route('/api_key', name: 'get_api_key_table', methods: ['GET'])]
    public function getApiKeyTable(ManagerRegistry $doctrine): Response
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

    #[Route('/database_sync/{apiKey}', name: 'upload_database', methods: ['POST'])]
    public function uploadDatabase(Request         $request, FileUploader $uploader,
                                   ManagerRegistry $doctrine, string $apiKey): Response
    {
        $entityManager = $doctrine->getManager();
        $repo = $entityManager->getRepository(DatabaseSync::class);

        $dbSync = $repo->find($apiKey);

        // api key prüfen
        if ($dbSync === null) {
            return new Response("Operation not allowed", Response::HTTP_FORBIDDEN,
                ['content-type' => 'text/plain']);
        }

        $file = $request->files->get('db_file');

        // Dateigröße setzen
        $dbSync->setFileSize($file->getSize());

        // Prüfen ob Datei existiert und den richtigen Typ hat
        if (empty($file) || !$file instanceof UploadedFile) {
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
        $fileName = $uploader->upload($file, $apiKey);

        // Dateinamen in der Entity updaten
        $dbSync->setDbFilename($fileName);

        $entityManager->persist($dbSync);
        $entityManager->flush();

        $this->addFlash('success', 'Datei wurde erfolgreich hochgeladen.');

        return $this->redirectToRoute('baseView');
    }

    #[Route('/database_sync/{apiKey}', name: 'download_database', methods: ['GET'])]
    public function downloadDatabase(ManagerRegistry $doctrine, string $apiKey): Response
    {
        $entityManager = $doctrine->getManager();
        $repo = $entityManager->getRepository(DatabaseSync::class);

        // api key Format prüfen
        if ($this->isValidUuid($apiKey)) {

            $dbSync = $repo->find($apiKey);

            // api key prüfen
            if ($dbSync === null) {
                return new Response("Operation not allowed", Response::HTTP_FORBIDDEN,
                    ['content-type' => 'text/plain']);
            }

            $filePath = $this->getParameter('db_directory') . '/' . $dbSync->getDbFilename();

            // MimeType raten
            $mimeTypes = new MimeTypes();
            $mimeType = $mimeTypes->guessMimeType($filePath);

            $response = new BinaryFileResponse($filePath);

            $response->headers->set('Content-Type', $mimeType);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                str_replace('_' . $apiKey, '', $dbSync->getDbFilename())
            );

            $response->headers->set('Connection', 'close');

            return $response;
        }

        return new Response("Operation not allowed", Response::HTTP_FORBIDDEN,
            ['content-type' => 'text/plain']);

    }

    private function isValidUuid(string $uuidToValidate): bool
    {
        $validator = Validation::createValidator();

        $uuidConstraint = new UuidConstraint();
        $uuidConstraint->message = Response::HTTP_FORBIDDEN;

        return !empty($validator->validate($uuidToValidate, $uuidConstraint));
    }

}