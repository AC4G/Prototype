<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scope
 *
 * @ORM\Table(name="scope", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="scope_UNIQUE", columns={"scope"}), @ORM\UniqueConstraint(name="description_UNIQUE", columns={"description"})})
 * @ORM\Entity(repositoryClass="App\Repository\ScopeRepository")
 */
class Scope
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
     * @ORM\Column(name="scope", type="string", length=255, nullable=false)
     */
    private string $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=false)
     */
    private string $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


}
