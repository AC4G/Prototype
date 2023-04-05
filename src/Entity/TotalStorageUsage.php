<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TotalStorageUsage
 *
 * @ORM\Table(name="total_storage_usage", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="project_UNIQUE", columns={"project_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\TotalStorageUsageRepository")
 */
class TotalStorageUsage
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
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * })
     */
    private Project $project;

    /**
     * @var int
     *
     * @ORM\Column(name="total_usage", type="integer", nullable=false)
     */
    private int $totalUsage;

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

    public function getTotalUsage(): int
    {
        return $this->totalUsage;
    }

    public function setTotalUsage(int $totalUsage): self
    {
        $this->totalUsage = $totalUsage;

        return $this;
    }


}
