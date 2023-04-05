<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectTeamMemberRepository
 *
 * @ORM\Table(name="project_team_member", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="project_user_UNIQUE", columns={"project_id", "user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProjectTeamMemberRepository")
 */
class ProjectTeamMember
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

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

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
