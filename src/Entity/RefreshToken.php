<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\Mapping as ORM;

/**
 * RefreshToken
 *
 * @ORM\Table(name="refresh_token", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="refresh_token_UNIQUE", columns={"refresh_token"})}, indexes={@ORM\Index(name="fk_Refresh_Token_Project1_idx", columns={"project_id"}), @ORM\Index(name="fk_Refresh_Token_User1_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\RefreshTokenRepository")
 */
class RefreshToken
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
     * @ORM\Column(name="refresh_token", type="string", length=255, nullable=false)
     */
    private string $refreshToken;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expire_date", type="datetime", nullable=false)
     */
    private DateTime $expireDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private User $user;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private Project $project;

    /**
     * @var array
     *
     * @ORM\Column(name="scopes", type="array", nullable=false)
     */
    private array $scopes = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpireDate(): ?DateTime
    {
        return $this->expireDate;
    }

    public function setExpireDate(DateTime $expireDate): self
    {
        $this->expireDate = $expireDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }


}
