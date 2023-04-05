<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * OrganisationMember
 *
 * @ORM\Table(name="organisation_member", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="organisation_user_UNIQUE", columns={"organisation_id", "user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationMemberRepository")
 */
class OrganisationMember
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
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="Organisation", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private Organisation $organisation;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private User $user;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="join_date", type="datetime", nullable=false)
     */
    private DateTime $joinDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getJoinDate(): DateTime
    {
        return $this->joinDate;
    }

    public function setJoinDate(DateTime $joinDate): self
    {
        $this->joinDate = $joinDate;

        return $this;
    }


}
