<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * ResetPasswordRequest
 *
 * @ORM\Table(name="reset_password_request", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_ResetPassword_Request_User_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ResetPasswordRequestRepository")
 */
final class ResetPasswordRequest
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
     * @ORM\Column(name="code", type="text", length=10, nullable=false)
     */
    private string $code;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private DateTime $createdOn;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expire_on", type="datetime", nullable=false)
     */
    private DateTime $expiresOn;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ResetPasswordRequest
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return ResetPasswordRequest
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return ResetPasswordRequest
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    /**
     * @param DateTime $createdOn
     * @return ResetPasswordRequest
     */
    public function setCreatedOn(DateTime $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpiresOn(): DateTime
    {
        return $this->expiresOn;
    }

    /**
     * @param DateTime $expiresOn
     */
    public function setExpiresOn(DateTime $expiresOn): self
    {
        $this->expiresOn = $expiresOn;

        return $this;
    }


}