<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\Project;
use Doctrine\ORM\Mapping as ORM;

/**
 * OauthClient
 *
 * @ORM\Table(name="oauth_client", uniqueConstraints={@ORM\UniqueConstraint(name="client_secret_UNIQUE", columns={"client_secret"}), @ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="client_id_UNIQUE", columns={"client_id"})}, indexes={@ORM\Index(name="fk_OAuth_Client_Project1_idx", columns={"project_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\OauthClientRepository")
 */
class OauthClient
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=255, nullable=false)
     */
    private string $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string", length=255, nullable=false)
     */
    private string $clientSecret;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private DateTime $creationDate;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private Project $project;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getCreationDate(): ?DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(DateTime $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }


}
