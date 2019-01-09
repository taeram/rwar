<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class WallpaperSetCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The path to the / dir.
     *
     * @var string
     */
    protected $projectDir;

    /**
     * The filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    protected function configure()
    {
        $this->setName('wallpaper:set')
            ->addArgument('input-file', InputArgument::REQUIRED, 'The source image file.')
            ->addArgument('watermark-text', InputArgument::REQUIRED, 'The watermark text.')
            ->addArgument('output-file', InputArgument::REQUIRED, 'The output file.')
            ->setDescription('Set a watermarked image as the current wallpaper')
            ->setHelp('This command downloads sets a watermarked image as the current wallpaper');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->projectDir = $this->container->get('kernel')->getProjectDir();
        $this->filesystem = new Filesystem();
        parent::initialize($input, $output);
    }

    protected function cmd($command, $printOutput = false) {
        ob_start();
        passthru($command);
        $output = trim(ob_get_clean());

        if ($printOutput === true) {
            echo $output;
        }

        return $output;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceImage = $input->getArgument('input-file');
        $watermarkText = $input->getArgument('watermark-text');
        $outputImage = $input->getArgument('output-file');

        // Are we running in OSX?
        $isOsx = ($this->cmd('echo $OSTYPE | grep -i darwin | wc -l') === "1");

        // Is Imagemagick installed?
        $isImagemagickInstalled = $this->cmd("which convert") !== "";
        if (!$isImagemagickInstalled) {
            $output->writeln('Imagemagick not installed, exiting...');
            return;
        }

        if ($isOsx) {
            $desktopResolution = $this->cmd("system_profiler SPDisplaysDataType | grep Resolution | head -1 | sed -e 's/^ *//' -e 's/Resolution: //' -e 's/ [A-Za-z]*$//' -e 's/ x /x/'");
        } else {
            $desktopResolution = $this->cmd("xrandr | head -n2 | tail -n1 | awk '{print $4}' | sed -e 's/\+.*$//'");
        }
        $output->writeln("Resizing wallpaper to $desktopResolution");

        // Resize the image
        $imageTempPath = $this->projectDir.'/var/tmp/image.jpg';
        $this->filesystem->remove($imageTempPath);
        $this->cmd("convert '$sourceImage' -geometry $desktopResolution^ -gravity center -crop {$desktopResolution}+0+0 '$imageTempPath'", true);
        if (!file_exists($imageTempPath)) {
            $output->writeln('Could not resize image, exiting...');
            return;
        }

        $output->writeln("Adding watermark: $watermarkText");
        [$desktopWidth, ] = explode('x', $desktopResolution);
        $fontSize = $desktopWidth / 128;
        if ($isOsx) {
            $textOffset = "+3+3";
        } else {
            $textOffset = "+5+30";
        }
        $this->cmd("mogrify -fill \#999 -pointsize $fontSize -gravity southwest -annotate $textOffset \"$watermarkText\" \"$imageTempPath\"");

        $this->filesystem->rename($imageTempPath, $outputImage, true);

        if ($isOsx) {
            // Set the wallpaper on the primary monitor
            $this->cmd("osascript -e 'tell application \"System Events\" to set picture of desktop 1 to \"'$outputImage'\"'");

            // Set the lock screen wallpaper
            $this->cmd("cp \"$outputImage\" /Library/Caches/com.apple.desktop.admin.png");

            // Kill the Dock to refresh the wallpaper
            $this->cmd('killall Dock');
        }
    }
}
