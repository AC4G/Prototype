<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserRegistrationKey
 *
 * @ORM\Table(name="user_registration_key", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="user_id_UNIQUE", columns={"user_id"}), @ORM\UniqueConstraint(name="key_UNIQUE", columns={"verification_key"})}, indexes={@ORM\Index(name="fk_User_Id_Key", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\UserRegistrationKeyRepository")
 */
class UserRegistrationKey
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
     * @ORM\Column(name="verification_key", type="string", length=255, nullable=false)
     */
    private string $key;

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

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

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
