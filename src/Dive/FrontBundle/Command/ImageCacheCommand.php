<?php
// src/AppBundle/Command/GreetCommand.php
namespace Dive\FrontBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dive:cache:image:clear')
            ->setDescription('Clear image cache')
            ->addArgument(
                'delete_files',
                InputArgument::REQUIRED,
                'Delete files from disk (0 = false, 1 = true)'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // clear database
        $doctrine = $this->getContainer()->get('doctrine');
        $images = $doctrine->getRepository('DiveFrontBundle:ImageCache')->findAll();
        $manager = $doctrine->getManager();

        $delete = $input->getArgument('delete_files') == '1';
        switch($input->getArgument('delete_files')){
            case '0': $delete = false; break;
            case '1': $delete = true; break;
            default:
                $output->writeln("delete_files not correct, please specify: 0 = false or 1 = true");
            return;
        }

        foreach($images as $image){
            if ($delete){
                if ($image->getUrl()){
                    $url = getcwd() . '/web' . $image->getUrl();
                    if (file_exists($url)){
                        unlink($url);
                    }
                }
            }
            $manager->remove($image);
            $manager->flush();
        }

        $output->writeln("Image cache cleared. ".  ($delete ? 'Files were removed.': 'Files were not removed.') );
    }
}