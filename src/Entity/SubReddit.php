<?php

namespace App\Entity;

use App\Entity\SubReddit\Wallpaper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubRedditRepository")
 */
class SubReddit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubReddit\Wallpaper", mappedBy="subreddit")
     */
    private $wallpapers;

    public function __construct()
    {
        $this->wallpapers = new ArrayCollection();
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

    /**
     * @return Collection|Wallpaper[]
     */
    public function getWallpapers(): Collection
    {
        return $this->wallpapers;
    }

    public function addWallpaper(Wallpaper $wallpaper): self
    {
        if (!$this->wallpapers->contains($wallpaper)) {
            $this->wallpapers[] = $wallpaper;
            $wallpaper->setSubreddit($this);
        }

        return $this;
    }

    public function removeWallpaper(Wallpaper $wallpaper): self
    {
        if ($this->wallpapers->contains($wallpaper)) {
            $this->wallpapers->removeElement($wallpaper);
            // set the owning side to null (unless already changed)
            if ($wallpaper->getSubreddit() === $this) {
                $wallpaper->setSubreddit(null);
            }
        }

        return $this;
    }

    public function getNumUnrated() {
        $numUnratedWallpapers = count($this->wallpapers);
        foreach ($this->wallpapers as $wallpaper) {
            if ($wallpaper->getRating() !== 0) {
                $numUnratedWallpapers--;
            }
        }

        return $numUnratedWallpapers;
    }
}
