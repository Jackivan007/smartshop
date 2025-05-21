<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con este correo electrónico')]
#[UniqueEntity(fields: ['username'], message: 'Ya existe una cuenta con este nombre de usuario')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 100)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Grupo::class, mappedBy: 'creadoPor', orphanRemoval: true)]
    private Collection $grupos;

    #[ORM\OneToMany(targetEntity: Lista::class, mappedBy: 'usuario', orphanRemoval: true)]
    private Collection $listas;

    #[ORM\OneToMany(targetEntity: Notificacion::class, mappedBy: 'usuario', orphanRemoval: true)]
    private Collection $notificaciones;

    #[ORM\ManyToMany(targetEntity: Grupo::class, mappedBy: 'miembros')]
    private Collection $gruposUnidos;

    public function __construct()
    {
        $this->grupos = new ArrayCollection();
        $this->listas = new ArrayCollection();
        $this->notificaciones = new ArrayCollection();
        $this->gruposUnidos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Grupo>
     */
    public function getGrupos(): Collection
    {
        return $this->grupos;
    }

    public function addGrupo(Grupo $grupo): static
    {
        if (!$this->grupos->contains($grupo)) {
            $this->grupos->add($grupo);
            $grupo->setCreadoPor($this);
        }

        return $this;
    }

    public function removeGrupo(Grupo $grupo): static
    {
        if ($this->grupos->removeElement($grupo)) {
            if ($grupo->getCreadoPor() === $this) {
                $grupo->setCreadoPor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lista>
     */
    public function getListas(): Collection
    {
        return $this->listas;
    }

    public function addLista(Lista $lista): static
    {
        if (!$this->listas->contains($lista)) {
            $this->listas->add($lista);
            $lista->setUsuario($this);
        }

        return $this;
    }

    public function removeLista(Lista $lista): static
    {
        if ($this->listas->removeElement($lista)) {
            if ($lista->getUsuario() === $this) {
                $lista->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notificacion>
     */
    public function getNotificaciones(): Collection
    {
        return $this->notificaciones;
    }

    public function addNotificacione(Notificacion $notificacione): static
    {
        if (!$this->notificaciones->contains($notificacione)) {
            $this->notificaciones->add($notificacione);
            $notificacione->setUsuario($this);
        }

        return $this;
    }

    public function removeNotificacione(Notificacion $notificacione): static
    {
        if ($this->notificaciones->removeElement($notificacione)) {
            if ($notificacione->getUsuario() === $this) {
                $notificacione->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Grupo>
     */
    public function getGruposUnidos(): Collection
    {
        return $this->gruposUnidos;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function setRoles(array $roles): static
    {
        return $this;
    }

    public function eraseCredentials(): void {}
}
