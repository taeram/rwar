<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class WallpaperController extends AbstractController
{

    /**
     * The path to the downloader lock file
     *
     * @var string
     */
    protected $downloaderLockFile = '/public/downloader.lock';

    /**
     * The download timeout, in seconds.
     *
     * @var int
     */
    protected $downloaderTimeoutSeconds = 600;

    /**
     * Are we currently downloading wallpapers?
     *
     * @return bool
     */
    protected function isDownloading()
    {
        return file_exists($this->getParameter(
            'kernel.project_dir'
          ).$this->downloaderLockFile);
    }

    /**
     * @Route("/", name="root")
     */
    public function root($id = null)
    {
        return $this->redirectToRoute('wallpaper');
    }

    /**
     * @Route("/wallpapers/{id}", name="wallpaper", defaults={"id": null})
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function index($id = null)
    {
        $subreddits = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit::class)
          ->findAll();

        // Select a random subreddit
        if ($id === null) {
            $subreddit = $this->getDoctrine()
              ->getRepository(\App\Entity\SubReddit::class)
              ->findRandomUnrated();
            if (!$subreddit) {
                return $this->render(
                  'wallpaper/index.html.twig',
                  [
                    'subreddits' => $subreddits,
                    'wallpaper' => null,
                    'is_downloading' => $this->isDownloading(),
                  ]
                );
            }

            return $this->redirectToRoute(
              'wallpaper',
              ['id' => $subreddit->getId()]
            );
        }

        // Handle unknown subreddits
        $subreddit = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit::class)
          ->find($id);
        if (!$subreddit) {
            return $this->redirectToRoute('wallpaper');
        }

        /** @var \App\Entity\SubReddit\Wallpaper $wallpaper */
        $wallpaper = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit\Wallpaper::class)
          ->findFirstUnrated($id);
        if (!$wallpaper) {
            return $this->redirectToRoute('wallpaper');
        }

        return $this->render(
          'wallpaper/index.html.twig',
          [
            'subreddits' => $this->getDoctrine()
              ->getRepository(\App\Entity\SubReddit::class)
              ->findAll(),
            'wallpaper' => $wallpaper,
            'is_downloading' => $this->isDownloading(),
          ]
        );
    }

    /**
     * @Route("/favourite/{id}/{subredditId}", name="wallpaper_favourite")
     */
    public function favourite($id, $subredditId)
    {
        $wallpaper = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit\Wallpaper::class)
          ->find($id);
        if ($wallpaper !== null) {
            $wallpaper->setRating(1);
            $this->getDoctrine()
              ->getManager()
              ->persist($wallpaper);
            $this->getDoctrine()
              ->getManager()
              ->flush();
        }

        return $this->redirectToRoute('wallpaper', ['id' => $subredditId]);
    }

    /**
     * @Route("/reject/{id}/{subredditId}", name="wallpaper_reject")
     */
    public function reject($id, $subredditId)
    {
        $wallpaper = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit\Wallpaper::class)
          ->find($id);
        if ($wallpaper !== null) {
            $wallpaper->setRating(-1);
            $this->getDoctrine()
              ->getManager()
              ->persist($wallpaper);
            $this->getDoctrine()
              ->getManager()
              ->flush();

            $filesystem = new Filesystem();
            $imageFilePath = $this->getParameter(
                'kernel.project_dir'
              ).'/public'.$wallpaper->getImageUrl();
            $filesystem->remove($imageFilePath);
        }

        $returnUrl = $_GET['return_url'] ?? 'wallpaper';
        $pageNum = $_GET['page_num'] ?? null;

        return $this->redirectToRoute(
          $returnUrl,
          ['id' => $subredditId, 'pageNum' => $pageNum]
        );
    }


    /**
     * @Route("/set/{id}/{subredditId}", name="wallpaper_set")
     *
     * @throws \Exception
     */
    public function set($id, $subredditId, KernelInterface $kernel)
    {
        $wallpaper = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit\Wallpaper::class)
          ->find($id);
        if ($wallpaper !== null) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $imageFilePath = $this->getParameter(
                'kernel.project_dir'
              ).'/public'.$wallpaper->getImageUrl();
            $input = new ArrayInput(
              [
                'command' => 'wallpaper:set',
                'input-file' => $imageFilePath,
                'watermark-text' => '/r/'.$wallpaper->getSubreddit()
                    ->getName(),
                'output-file' => getenv(
                    'HOME'
                  ).'/Pictures/Wallpaper/wallpaper.jpg',
              ]
            );

            $output = new NullOutput();
            $application->run($input, $output);
        }

        $returnUrl = $_GET['return_url'] ?? 'wallpaper';
        $pageNum = $_GET['page_num'] ?? null;

        return $this->redirectToRoute(
          $returnUrl,
          ['id' => $subredditId, 'pageNum' => $pageNum]
        );
    }

    /**
     * @Route("/favourites/{pageNum}", name="wallpaper_favourites",
     *   defaults={"pageNum": 1})
     *
     * @throws \Exception
     */
    public function favourites($pageNum = 1)
    {
        $wallpapersPerPage = 5;
        $numWallpapers = $this->getDoctrine()
          ->getRepository(
            \App\Entity\SubReddit\Wallpaper::class
          )
          ->findCountFavourites();
        $numPages = ceil($numWallpapers / $wallpapersPerPage);
        $wallpapers = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit\Wallpaper::class)
          ->findAllFavourites(
            $pageNum,
            $wallpapersPerPage
          );
        $subreddits = $this->getDoctrine()
          ->getRepository(\App\Entity\SubReddit::class)
          ->findAll();

        return $this->render(
          'wallpaper/favourites.html.twig',
          [
            'subreddits' => $subreddits,
            'wallpapers' => $wallpapers,
            'page_num' => $pageNum,
            'num_pages' => $numPages,
            'is_downloading' => $this->isDownloading(),
          ]
        );
    }

    /**
     * @Route("/wallpapers/downloader/start", name="wallpaper_downloader_start")
     */
    public function downloaderStart(KernelInterface $kernel)
    {
        $process = Process::fromShellCommandline(
          'nohup php ./bin/console wallpaper:download > /dev/null 2>&1 &',
          $this->getParameter('kernel.project_dir'),
          null,
          null,
          $this->downloaderTimeoutSeconds
        );
        $process->start();

        $response = new Response();
        $response->setContent("ok");

        return $response;
    }

    /**
     * @Route("/wallpapers/downloader/status", name="wallpaper_downloader_status")
     */
    public function downloaderStatus()
    {
        $downloaderLockFile = $this->getParameter(
            'kernel.project_dir'
          ).$this->downloaderLockFile;

        // Remove a stale lock file
        if (file_exists($downloaderLockFile) && filemtime(
            $downloaderLockFile
          ) < strtotime('now -'.$this->downloaderTimeoutSeconds.' seconds')) {
            unlink($downloaderLockFile);
        }

        if (file_exists($downloaderLockFile)) {
            $status = 'Running';
        } else {
            $status = 'Stopped';
        }

        $response = new Response();
        $response->setContent($status);

        return $response;
    }
}
