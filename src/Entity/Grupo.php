<?php

namespace App\Entity;

use App\Repository\GrupoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuario;

#[ORM\Entity(repositoryClass: GrupoRepository::class)]
class Grupo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'grupos')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')] // Elimina el grupo si el usuario que lo creó es eliminado
    private ?Usuario $creadoPor = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $clave = null;

    #[ORM\OneToMany(
        targetEntity: Lista::class,
        mappedBy: 'grupo',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $listas;

    #[ORM\ManyToMany(targetEntity: Usuario::class, inversedBy: 'gruposUnidos')]
    private Collection $miembros;

    public function __construct()
    {
        $this->listas = new ArrayCollection();
        $this->miembros = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getCreadoPor(): ?Usuario
    {
        return $this->creadoPor;
    }

    public function setCreadoPor(?Usuario $creadoPor): static
    {
        $this->creadoPor = $creadoPor;
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

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): static
    {
        $this->clave = $clave;
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
            $lista->setGrupo($this);
        }

        return $this;
    }

    public function removeLista(Lista $lista): static
    {
        if ($this->listas->removeElement($lista)) {
            if ($lista->getGrupo() === $this) {
                $lista->setGrupo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Usuario>
     */
    public function getMiembros(): Collection
    {
        return $this->miembros;
    }

    public function addMiembro(Usuario $usuario): static
    {
        if (!$this->miembros->contains($usuario)) {
            $this->miembros->add($usuario);
        }

        return $this;
    }

    public function removeMiembro(Usuario $usuario): static
    {
        $this->miembros->removeElement($usuario);
        return $this;
    }
}
