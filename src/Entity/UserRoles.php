<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\RoleIdent;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserRoles
 *
 * @ORM\Table(name="user_roles", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_user_roles_role_ident1_idx", columns={"role_ident_id"}), @ORM\Index(name="fk_Roles_User1_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\UserRolesRepository")
 */
class UserRoles
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
     * @var RoleIdent
     *
     * @ORM\ManyToOne(targetEntity="RoleIdent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_ident_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private RoleIdent $roleIdent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleIdent(): ?RoleIdent
    {
        return $this->roleIdent;
    }

    public function setRoleIdent(?RoleIdent $roleIdent): self
    {
        $this->roleIdent = $roleIdent;

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


}
