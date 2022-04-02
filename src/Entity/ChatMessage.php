<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use App\Entity\User;
use App\Entity\ChatRoom;
use Doctrine\ORM\Mapping as ORM;

/**
 * ChatMessage
 *
 * @ORM\Table(name="chat_message", indexes={@ORM\Index(name="fk_chat_message_chat_room1_idx", columns={"room_id"}), @ORM\Index(name="fk_chat_message_user1_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ChatMessageRepository")
 */
class ChatMessage
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
     * @ORM\Column(name="message", type="text", length=0, nullable=true)
     */
    private ?string $message;

    /**
     * @var string|null
     *
     * @ORM\Column(name="data_path", type="text", length=0, nullable=true)
     */
    private ?string $dataPath;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="send_date", type="datetime", nullable=false)
     */
    private DateTime $sendDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private User $user;

    /**
     * @var ChatRoom
     *
     * @ORM\ManyToOne(targetEntity="ChatRoom")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     * })
     */
    private ChatRoom $room;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getDataPath(): ?string
    {
        return $this->dataPath;
    }

    public function setDataPath(?string $dataPath): self
    {
        $this->dataPath = $dataPath;

        return $this;
    }

    public function getSendDate(): ?DateTime
    {
        return $this->sendDate;
    }

    public function setSendDate(DateTime $sendDate): self
    {
        $this->sendDate = $sendDate;

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

    public function getRoom(): ?ChatRoom
    {
        return $this->room;
    }

    public function setRoom(?ChatRoom $room): self
    {
        $this->room = $room;

        return $this;
    }


}
