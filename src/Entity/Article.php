<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *     min = 5,
     *     max = 50,
     *     minMessage = "Le nom d'un article doit comporter au moins {{ limit }} caractères",
     *     maxMessage = "Le nom d'un article doit comporter au plus {{ limit }} caractères"
     * )
     */
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotEqualTo(
     *     value = 0,
     *     message = "Le prix d’un article ne doit pas être égal à 0"
     * )
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?float $prix = null; // Changed to float

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float // Changed return type to float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static // Changed parameter type to float
    {
        $this->prix = $prix;

        return $this;
    }
}
