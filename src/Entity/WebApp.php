<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Client;
use Doctrine\ORM\Mapping as ORM;

/**
 * WebApp
 *
 * @ORM\Table(name="web_app", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Web_App_OAuth_Client1_idx", columns={"oauth_client_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\WebAppRepository")
 */
class WebApp
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="redirect_url", type="text", length=0, nullable=true)
     */
    private ?string $redirectUrl;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="Client", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="oauth_client_id", referencedColumnName="id")
     * })
     */
    private Client $client;

    /**
     * @var array
     *
     * @ORM\Column(name="scopes", type="array", nullable=false)
     */
    private array $scopes = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;

        return $this;
    }


}
