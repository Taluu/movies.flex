<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation as Serialization;

/** @ORM\Entity(repositoryClass="App\Repository\MoviesDoctrine") */
class Movie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Serialization\Groups({"public", "all"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Serialization\Groups({"public", "all"})
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Serialization\Groups({"all"})
     */
    private $deleted = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }
}
