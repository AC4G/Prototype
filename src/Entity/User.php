<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRolesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * User
 *
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"}), @ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="nickname_UNIQUE", columns={"nickname"})})
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

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
     * @ORM\Column(name="nickname", type="string", length=255, nullable=false)
     */
    private string $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private string $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="text", length=0, nullable=false)
     */
    private string $password;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_private", type="boolean", nullable=false)
     */
    private bool $isPrivate = false;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="email_verified", type="datetime", nullable=true)
     */
    private ?DateTime $emailVerified;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private DateTime $creationDate;


    /**
     * @ORM\OneToMany(targetEntity="UserRoles", mappedBy="user")
     */
    private Collection $roles;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getEmailVerified(): ?DateTime
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(?DateTime $emailVerified): self
    {
        $this->emailVerified = $emailVerified;

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

    public function getRoles(): array
    {
        $collection = $this->roles->toArray();
        $this->roles = $collection[0]['roleIdent']['roles'];

        return $this->roles;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getNickname();
    }
}
