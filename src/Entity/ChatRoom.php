<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatRoom
 *
 * @ORM\Table(name="chat_room", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ChatRoomRepository")
 */
class ChatRoom
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
     * @var string|null
     *
     * @ORM\Column(name="image_path", type="text", length=0, nullable=true)
     */
    private ?string $imagePath;

    /**
     * @var string|null
     *
     * @ORM\Column(name=" settings", type="text", length=0, nullable=true)
     */
    private ?string $settings;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function setSettings(?string $settings): self
    {
        $this->settings = $settings;

        return $this;
    }


}
