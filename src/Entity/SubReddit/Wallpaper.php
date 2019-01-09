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
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $hash;

    /**
     * @ORM\Column(type="integer")
     */
    private $rating;

    public function __construct(SubReddit $subreddit, $url)
    {
        $this->subreddit = $subreddit;
        $this->url = $url;
        $this->hash = hash('sha256', $url);
    }

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

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getImageUrl() {
        return '/subreddits/' . $this->subreddit->getName() . '/' . $this->hash;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

}
