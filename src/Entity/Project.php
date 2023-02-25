<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\Developer;
use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="project_name_UNIQUE", columns={"project_name"})}, indexes={@ORM\Index(name="fk_Project_Developer1_idx", columns={"developer_id"}), @ORM\Index(name="project_name_fulltext", columns={"project_name"}, flags={"fulltext"})})
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
     * @ORM\Column(name="organisation_name", type="string", length=255, nullable=false)
     */
    private string $organisationName;

    /**
     * @var string
     *
     * @ORM\Column(name="organisation_email", type="string", length=255, nullable=false)
     */
    private string $organisationEmail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="organisation_logo", type="text", length=0, nullable=true)
     */
    private ?string $organisationLogo;

    /**
     * @var string
     *
     * @ORM\Column(name="support_email", type="string", length=255, nullable=false)
     */
    private string $supportEmail;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private DateTime $creationDate;

    /**
     * @var Developer
     *
     * @ORM\ManyToOne(targetEntity="Developer", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="developer_id", referencedColumnName="id")
     * })
     */
    private Developer $developer;

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

    public function getOrganisationName(): ?string
    {
        return $this->organisationName;
    }

    public function setOrganisationName(string $organisationName): self
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    public function getOrganisationEmail(): ?string
    {
        return $this->organisationEmail;
    }

    public function setOrganisationEmail(string $organisationEmail): self
    {
        $this->organisationEmail = $organisationEmail;

        return $this;
    }

    public function getOrganisationLogo(): ?string
    {
        return $this->organisationLogo;
    }

    public function setOrganisationLogo(?string $organisationLogo): self
    {
        $this->organisationLogo = $organisationLogo;

        return $this;
    }

    public function getSupportEmail(): ?string
    {
        return $this->supportEmail;
    }

    public function setSupportEmail(string $supportEmail): self
    {
        $this->supportEmail = $supportEmail;

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

    public function getDeveloper(): ?Developer
    {
        return $this->developer;
    }

    public function setDeveloper(?Developer $developer): self
    {
        $this->developer = $developer;

        return $this;
    }


}
