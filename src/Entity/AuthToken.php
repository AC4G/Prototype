<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var DateTime|null
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     */
    private ?DateTime $creationDate;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="expire_date", type="datetime", nullable=true)
     */
    private ?DateTime $expireDate;

    /**
     * @var User|UserInterface
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private User|UserInterface $user;

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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getCreationDate(): ?DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(?DateTime $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getExpireDate(): ?DateTime
    {
        return $this->expireDate;
    }

    public function setExpireDate(?DateTime $expireDate): self
    {
        $this->expireDate = $expireDate;

        return $this;
    }

    public function getUser(): null|User|UserInterface
    {
        return $this->user;
    }

    public function setUser(null|User|UserInterface $user): self
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
