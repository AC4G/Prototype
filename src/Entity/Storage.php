<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Storage
 *
 * @ORM\Table(name="storage", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="project_key_UNIQUE", columns={"project_id", "storage_key"})}, indexes={@ORM\Index(name="key_fulltext", columns={"storage_key"}, flags={"fulltext"}), @ORM\Index(name="fk_project_key_idx", columns={"project_id", "storage_key"})})
 * @ORM\Entity(repositoryClass="App\Repository\StorageRepository")
 */
class Storage
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
     * @var string
     *
     * @ORM\Column(name="storage_key", type="string", length=255, nullable=false)
     */
    private string $key;

    /**
     * @var string
     *
     * @ORM\Column(name="storage_value", type="text", length=0, nullable=false)
     */
    private string $value;

    /**
     * @var int
     *
     * @ORM\Column(name="length", type="integer", nullable=false)
     */
    private int $length;

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

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }


}