<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoleIdent
 *
 * @ORM\Table(name="role_ident", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity(repositoryClass="App\Repository\RoleIdentRepository")
 */
class RoleIdent
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
     * @ORM\Column(name="roles", type="text", length=0, nullable=false)
     */
    private string $roles;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleName(): ?string
    {
        return $this->roles;
    }

    public function setRoleName(string $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


}
