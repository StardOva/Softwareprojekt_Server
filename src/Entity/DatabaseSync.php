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
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $api_key = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $db_filename = null;

    #[ORM\Column(nullable: true)]
    private ?BlobType $client_db = null;

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

    /**
     * @return string|null
     */
    public function getDbFilename(): ?string
    {
        return $this->db_filename;
    }

    /**
     * @param string|null $db_filename
     * @return DatabaseSync
     */
    public function setDbFilename(?string $db_filename): self
    {
        $this->db_filename = $db_filename;
        return $this;
    }
}
