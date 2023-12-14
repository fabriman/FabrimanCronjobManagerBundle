<?php

namespace Fm\CronjobManagerBundle\Entity;

use Fm\CronjobManagerBundle\Repository\FmCronjobManagerRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints\Regex;

#[ORM\Entity(repositoryClass: FmCronjobManagerRepository::class)]
#[UniqueEntity('name')]
class FmCronjobManager
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Regex([
        'pattern' =>     '/^[A-Za-z0-9 ]+$/',
        'message' => 'Invalid name {{ value }}. Please, use only letters, numbers and spaces!',
        ])
    ]
    private ?string $name;

    #[ORM\Column(type: 'json')]
    private array $url = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Regex([
            'pattern' => '/^((((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*\/|\*|) ?){5,7})$/',
            'message' => 'Invalid Format for this Job expression!',
        ])
    ]
    private ?string $jobExpression;

    #[ORM\Column(type: 'text')]
    private ?string $fullExpression;

    #[ORM\Column(type: 'integer', length: 10)]
    private ?int $jobTimeout;

    #[ORM\Column(type: 'datetime', options: ["default" => "CURRENT_TIMESTAMP"])]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', options: ["default" => "CURRENT_TIMESTAMP"])]
    private DateTime $lastRun;

    #[ORM\Column(type: 'boolean', options: ["default" => "0"])]
    private ?bool $active;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->lastRun = new DateTime();
    }

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

    public function getUrl(): ?array
    {
        return $this->url;
    }

    public function setUrl(array $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFullExpression(): ?string
    {
        return $this->fullExpression;
    }

    public function setFullExpression(string $fullExpression): self
    {
        $this->fullExpression = $fullExpression;

        return $this;
    }

    public function getJobExpression(): ?string
    {
        return $this->jobExpression;
    }

    public function setJobExpression(string $jobExpression): self
    {
        $this->jobExpression = $jobExpression;

        return $this;
    }

    public function getJobTimeout(): ?int
    {
        return $this->jobTimeout;
    }

    public function setJobTimeout(int $jobTimeout): self
    {
        $this->jobTimeout = $jobTimeout;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastRun(): ?DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(DateTime $lastRun): self
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
