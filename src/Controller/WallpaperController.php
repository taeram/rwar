<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WallpaperController extends AbstractController
{
    /**
     * @Route("/", name="wallpaper")
     */
    public function index()
    {

        // Send the list of thumbnails to the view for the user to reject or apply

        return $this->render(
            'wallpaper/index.html.twig',
            [
                'subreddit' => $subreddit->getName(),
                'image_urls' => $imageUrls,
            ]
        );
    }

    public function setWallpaper() {
        // https://symfony.com/doc/current/console/command_in_controller.html
    }
}
