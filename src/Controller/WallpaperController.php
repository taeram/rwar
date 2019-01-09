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
        return $this->render(
            'wallpaper/index.html.twig',
            [
                'wallpaper' => $this->getDoctrine()->getRepository(\App\Entity\SubReddit\Wallpaper::class)->findFirstUnrated(),
            ]
        );
    }

    public function setWallpaper() {
        // https://symfony.com/doc/current/console/command_in_controller.html
    }
}
