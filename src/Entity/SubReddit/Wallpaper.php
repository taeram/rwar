<?php

namespace App\Entity\SubReddit;

use App\Entity\SubReddit;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubReddit\WallpaperRepository")
 */
class Wallpaper
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubReddit", inversedBy="wallpapers")
     */
    private $subreddit;

    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $url;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubreddit(): ?SubReddit
    {
        return $this->subreddit;
    }

    public function setSubreddit(?SubReddit $subreddit): self
    {
        $this->subreddit = $subreddit;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
