<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectScope
 *
 * @ORM\Table(name="project_scope", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="project_scope_id_UNIQUE", columns={"project_id", "scope_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProjectScopeRepository")
 */
class ProjectScope
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
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private Project $project;

    /**
     * @var Scope
     *
     * @ORM\ManyToOne(targetEntity="Scope", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="scope_id", referencedColumnName="id")
     * })
     */
    private Scope $scope;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }

    public function setScope(Scope $scope): self
    {
        $this->scope = $scope;

        return $this;
    }


}