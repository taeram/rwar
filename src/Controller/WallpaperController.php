<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
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

    /**
     * @Route("/favourite/{id}", name="wallpaper_favourite")
     */
    public function favourite($id)
    {
        $wallpaper = $this->getDoctrine()->getRepository(\App\Entity\SubReddit\Wallpaper::class)->find($id);
        if ($wallpaper !== null) {
            $wallpaper->setRating(1);
            $this->getDoctrine()->getManager()->persist($wallpaper);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('wallpaper');
    }

    /**
     * @Route("/reject/{id}", name="wallpaper_reject")
     */
    public function reject($id)
    {
        $wallpaper = $this->getDoctrine()->getRepository(\App\Entity\SubReddit\Wallpaper::class)->find($id);
        if ($wallpaper !== null) {
            $wallpaper->setRating(-1);
            $this->getDoctrine()->getManager()->persist($wallpaper);
            $this->getDoctrine()->getManager()->flush();

            $filesystem = new Filesystem();
            $imageFilePath = $this->getParameter('kernel.project_dir') . '/public' . $wallpaper->getImageUrl();
            $filesystem->remove($imageFilePath);
        }

        return $this->redirectToRoute('wallpaper');
    }


    /**
     * @Route("/set/{id}", name="wallpaper_set")
     */
    public function set($id, KernelInterface $kernel)
    {
        $wallpaper = $this->getDoctrine()->getRepository(\App\Entity\SubReddit\Wallpaper::class)->find($id);
        if ($wallpaper !== null) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $imageFilePath = $this->getParameter('kernel.project_dir') . '/public' . $wallpaper->getImageUrl();
            $input = new ArrayInput(array(
                'command' => 'wallpaper:set',
                'input-file' => $imageFilePath,
                'watermark-text' => '/r/' . $wallpaper->getSubreddit()->getName(),
                'output-file' => getenv('HOME') . '/Pictures/Wallpaper/wallpaper.jpg',
            ));

            $output = new NullOutput();
            $application->run($input, $output);
        }

        return $this->redirectToRoute('wallpaper');
    }
}
