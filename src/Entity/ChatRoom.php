<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatRoom
 *
 * @ORM\Table(name="chat_room", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_chat_room_member_chat_room_type1_idx", columns={"room_type_id"})})
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
     * @ORM\Column(name="settings", type="text", length=0, nullable=true)
     */
    private ?string $settings;

    /**
     * @var ChatRoomType
     *
     * @ORM\ManyToOne(targetEntity="ChatRoomType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="room_type_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private ChatRoomType $type;

    /**
     * @var null|string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="parameter", type="text", length=0, nullable=true)
     */
    private ?string $parameter;

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

    public function getType(): ChatRoomType
    {
        return $this->type;
    }

    public function setType(ChatRoomType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    public function setParameter(?string $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }


}
