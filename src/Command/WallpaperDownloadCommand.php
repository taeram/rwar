<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class WallpaperDownloadCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * How many posts to download at a time
     *
     * @var int
     */
    protected $postLimit = 50;

    /**
     * The URL to the reddit hot.json
     *
     * @var string
     */
    protected $redditHotJsonUrl = 'https://www.reddit.com/r/%s/hot.json?limit=%d';

    /**
     * The user agent string for requests
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36';

    /**
     * The cache
     *
     * @var FilesystemCache
     */
    protected $cache;

    /**
     * The path to the /public dir.
     *
     * @var string
     */
    protected $publicDir;

    /**
     * The path to the / dir.
     *
     * @var string
     */
    protected $projectDir;

    /**
     * The Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * The filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The doctrine registry.
     *
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    protected function configure()
    {
        $this->setName('wallpaper:download')
            ->setDescription('Download a fresh set of wallpapers')
            ->setHelp('This command downloads a new set of wallpapers');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->cache = new FilesystemCache();
        $this->projectDir = $this->container->get('kernel')->getProjectDir();
        $this->publicDir = $this->projectDir.'/public';
        $this->guzzle = new \GuzzleHttp\Client(
            [
                'connect_timeout' => 5,
                'read_timeout' => 60,
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
            ]
        );
        $this->filesystem = new Filesystem();
        $this->doctrine = $this->container->get('doctrine');

        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Download the latest wallpapers from each subreddit
        $subreddits = $this->doctrine->getRepository(\App\Entity\SubReddit::class)->findAll();

        foreach ($subreddits as $subreddit) {
            /** @var \App\Entity\SubReddit $subreddit */
            // Create the subreddit image folder
            $subredditDir = $this->publicDir.'/subreddits/'.$subreddit->getName();
            if (!file_exists($subredditDir)) {
                try {
                    $this->filesystem->mkdir($subredditDir);
                } catch (IOExceptionInterface $e) {
                    echo 'Could not create directory: '.$e->getPath()."\n";

                    return false;
                }
            }

            // Download the hot.json
            $output->write("\n" . 'Retrieving /r/'.$subreddit->getName().': ');
            $url = sprintf($this->redditHotJsonUrl, $subreddit->getName(), $this->postLimit);

            $urlHash = hash('sha256', $url);
            $urlCacheId = 'hot.'.$urlHash;
            if (!$this->cache->has($urlCacheId)) {
                for ($i = 0; $i < 20; $i++) {
                    try {
                        $response = $this->guzzle->request('GET', $url);
                        break;
                    } catch (\Exception $e) {
                        if ($e->getCode() === 429) {
                            $output->writeln('Rate limit exceeded, waiting to retry...');
                            sleep(30);
                            continue;
                        }

                        $output->writeln($e->getMessage());

                        return;
                    }
                }

                if ($i >= 20) {
                    $output->writeln('Could not retrieve JSON.');
                    continue;
                }

                if ($response->getStatusCode() !== 200) {
                    $output->writeln('Error retrieving JSON: '.$response->getStatusCode());

                    return;
                }
                $json = $response->getBody()->getContents();

                // Cache the json for 1 day
                $this->cache->set($urlCacheId, $json, 86400);
            } else {
                $json = $this->cache->get($urlCacheId);
            }
            $items = json_decode($json, true);

            foreach ($items['data']['children'] as $item) {
                // Skip NSFW posts
                if ($item['data']['over_18'] !== false) {
                    continue;
                }
                $imageUrl = $item['data']['url'];

                // Have we already seen this image?
                $wallpaper = $this->doctrine->getRepository(\App\Entity\SubReddit\Wallpaper::class)->findByUrl($imageUrl);
                if ($wallpaper !== null) {
                    $output->write('s');
                    continue;
                }

                // Download the image
                $imageTempPath = $this->projectDir.'/var/tmp/image';
                if ($this->downloadImage($imageUrl, $imageTempPath) === false) {
                    $output->write('!');
                    continue;
                }
                $output->write('.');

                // Add this wallpaper to the database
                $wallpaper = new \App\Entity\SubReddit\Wallpaper($subreddit, $item['data']['url']);
                $this->doctrine->getManager()->persist($wallpaper);
                $this->doctrine->getManager()->flush();

                // Move the temp file to it's public location
                $imagePath = $this->publicDir.'/'.$wallpaper->getImageUrl();
                $this->filesystem->rename($imageTempPath, $imagePath, true);
            }
        }
    }

    /**
     * Download the image.
     *
     * @param string $imageUrl The image url.
     * @param string $imageTempPath The temp path for the image
     *
     * @return bool
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function downloadImage($imageUrl, $imageTempPath)
    {
        // Tidy the url
        $imageUrl = str_replace('&amp;', '&', $imageUrl);

        // Download the file
        try {
            $response = $this->guzzle->request('GET', $imageUrl);
        } catch (\Exception $e) {
            return false;
        }

        // Write the image to a temp file
        $this->filesystem->dumpFile($imageTempPath, $response->getBody()->getContents());

        // Verify this is a valid image
        try {
            new \Imagick($imageTempPath);
        } catch (\Exception $e) {
            $this->filesystem->remove($imageTempPath);

            return false;
        }

        return true;
    }
}
