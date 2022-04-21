<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\ChatRoom;
use App\Entity\ChatRoomType;
use Doctrine\ORM\Mapping as ORM;

/**
 * ChatRoomMember
 *
 * @ORM\Table(name="chat_room_member", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="room_id_user_id_UNIQUE", columns={"user_id", "chat_room_id"})}, indexes={@ORM\Index(name="fk_chat_room_user1_idx", columns={"user_id"}), @ORM\Index(name="fk_chat_room_member_chat_room1_idx", columns={"chat_room_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ChatRoomMemberRepository")
 */
class ChatRoomMember
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
     * @var ChatRoom
     *
     * @ORM\ManyToOne(targetEntity="ChatRoom")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="chat_room_id", referencedColumnName="id")
     * })
     */
    private ChatRoom $chatRoom;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatRoom(): ?ChatRoom
    {
        return $this->chatRoom;
    }

    public function setChatRoom(?ChatRoom $chatRoom): self
    {
        $this->chatRoom = $chatRoom;

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
