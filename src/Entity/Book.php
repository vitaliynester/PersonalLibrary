<?php

namespace App\Entity;

use App\Repository\BookRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность книги в БД
 *
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * Идентификатор книги
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Название книги
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * Автор книги
     *
     * @ORM\Column(type="string", length=255)
     */
    private $author;

    /**
     * Название файла с изображением обложки книги
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $coverImage;

    /**
     * Название файла с книгой
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bookFile;

    /**
     * Дата прочтения данной книги
     *
     * @ORM\Column(type="datetime")
     */
    private $readDate;

    /**
     * Пользователь создавший данную запись о книге
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="books")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getBookFile(): ?string
    {
        return $this->bookFile;
    }

    public function setBookFile(?string $bookFile): self
    {
        $this->bookFile = $bookFile;

        return $this;
    }

    public function getReadDate(): ?DateTimeInterface
    {
        return $this->readDate;
    }

    public function setReadDate(DateTimeInterface $readDate): self
    {
        $this->readDate = $readDate;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
