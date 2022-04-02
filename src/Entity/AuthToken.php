<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\Mapping as ORM;

/**
 * AuthToken
 *
 * @ORM\Table(name="auth_token", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="auth_token_UNIQUE", columns={"auth_token"})}, indexes={@ORM\Index(name="fk_Token_Project1_idx", columns={"project_id"}), @ORM\Index(name="fk_Token_User1_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\AuthTokenRepository")
 */
class AuthToken
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
     * @ORM\Column(name="auth_token", type="string", length=255, nullable=false)
     */
    private string $authToken;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setAuthToken(string $authToken): self
    {
        $this->authToken = $authToken;

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


}
