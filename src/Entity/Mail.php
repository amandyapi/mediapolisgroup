<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MailRepository::class)
 */
class Mail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="senderFullName",type="string", length=100)
     */
    private $senderFullName;

    /**
     * @ORM\Column(name="senderMail",type="string", length=100)
     */
    private $senderMail;

    /**
     * @ORM\Column(name="senderContact",type="string", length=25, nullable=true)
     */
    private $senderContact;

    /**
     * @ORM\Column(name="title",type="string", length=60)
     */
    private $title;

    /**
     * @ORM\Column(name="content",type="text")
     */
    private $content;

    /**
     * @ORM\Column(name="createTime",type="string", length=25)
     */
    private $createTime;

    public function __construct()
    {
        $now = new \DateTime('NOW');
        $this->createTime = $now->format('Y-m-d\TH:i:s.u');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderFullName(): ?string
    {
        return $this->senderFullName;
    }

    public function setSenderFullName(string $senderFullName): self
    {
        $this->senderFullName = $senderFullName;

        return $this;
    }

    public function getSenderMail(): ?string
    {
        return $this->senderMail;
    }

    public function setSenderMail(string $senderMail): self
    {
        $this->senderMail = $senderMail;

        return $this;
    }

    public function getSenderContact(): ?string
    {
        return $this->senderContact;
    }

    public function setSenderContact(?string $senderContact): self
    {
        $this->senderContact = $senderContact;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreateTime(): ?string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }
}
