<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="project_name_UNIQUE", columns={"project_name"})}, indexes={@ORM\Index(name="project_name_fulltext", columns={"project_name"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
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
     * @ORM\Column(name="project_name", type="string", length=255, nullable=false)
     */
    private string $projectName;

    /**
     * @var string
     *
     * @ORM\Column(name="project_logo", type="string", length=255, nullable=false)
     */
    private string $projectLogo;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private Organisation $organisation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="with_invitation", type="boolean", nullable=false)
     */
    private bool $withInvitation;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private DateTime $creationDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): self
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getProjectLogo(): string
    {
        return $this->projectLogo;
    }

    public function setProjectLogo(string $projectLogo): self
    {
        $this->projectLogo = $projectLogo;

        return $this;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function setWithInvitation(bool $withInvitation): self
    {
        $this->withInvitation = $withInvitation;

        return $this;
    }

    public function isWithInvitation(): bool
    {
        return $this->withInvitation;
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


}
