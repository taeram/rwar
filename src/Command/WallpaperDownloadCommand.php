<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Cache\Simple\FilesystemCache;

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

    protected function configure()
    {
        $this->setName('wallpaper:download')
            ->setDescription('Download a fresh set of wallpapers')
            ->setHelp('This command downloads a new set of wallpapers');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->cache = new FilesystemCache();
        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Download the latest wallpapers from each subreddit
        $subreddits = $this->container->get('doctrine')->getRepository(\App\Entity\SubReddit::class)->findAll();

        $client = new \GuzzleHttp\Client(
            [
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
            ]
        );
        foreach ($subreddits as $subreddit) {
            /** @var \App\Entity\SubReddit $subreddit */
            // Download the hot.json
            $output->writeln('Retrieving /r/' . $subreddit->getName());
            $url = sprintf($this->redditHotJsonUrl, $subreddit->getName(), $this->postLimit);

            $urlHash = hash('sha256', $url);
            $urlCacheId = 'hot.' . $urlHash;
            if (!$this->cache->has($urlCacheId)) {
                for ($i = 0; $i < 20; $i++) {
                    try {
                        $response = $client->request('GET', $url);
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

                if ($response->getStatusCode() !== 200) {
                    $output->writeln($e->getMessage());
                    return;
                }
                $json = (string) $response->getBody();

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
                $output->writeln($imageUrl);
            }
        }

    }
}
