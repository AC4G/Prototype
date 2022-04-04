<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Token
 *
 * @ORM\Table(name="token", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="user_id_UNIQUE", columns={"user_id"}), @ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"})}, indexes={@ORM\Index(name="fk_User_Id_Token_Type", columns={"user_id", "token", "token_type"})})
 * @ORM\Entity(repositoryClass="App\Repository\TokenRepository")
 */
class Token
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private User $user;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    private string $token;

    /**
     * @var string
     *
     * @ORM\Column(name="token_type", type="text", length=255, nullable=false)
     */
    private string $type;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expire_date", type="datetime", nullable=false)
     */
    private DateTime $expireDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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
}
