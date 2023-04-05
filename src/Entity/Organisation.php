<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Organisation
 *
 * @ORM\Table(name="organisation", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="organisation_name_UNIQUE", columns={"organisation_name"})}, indexes={@ORM\Index(name="organisation_name_fulltext", columns={"organisation_name"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 */
class Organisation
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
     * @ORM\Column(name="organisation_logo", type="string", length=255, nullable=true)
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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


}
