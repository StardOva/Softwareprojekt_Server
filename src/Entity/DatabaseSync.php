<?php

namespace App\Entity;

use App\Repository\DatabaseSyncRepository;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DatabaseSyncRepository::class)]
#[ORM\Table(name: 'database_sync')]
class DatabaseSync
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?string $api_key = null;

    #[ORM\Column(nullable: true)]
    private ?BlobType $client_db = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    public function setApiKey(string $api_key): self
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function getClientDb(): ?BlobType
    {
        return $this->client_db;
    }

    public function setClientDb(?BlobType $client_db): self
    {
        $this->client_db = $client_db;

        return $this;
    }
}
