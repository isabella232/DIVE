<?php
// src/AppBundle/Command/GreetCommand.php
namespace Dive\APIBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueryCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dive:cache:query:clear')
            ->setDescription('Clear query cache')
            ->addArgument(
                'dataset',
                InputArgument::REQUIRED,
                'Dataset id'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataSet = $input->getArgument('dataset');
        $qb = $this->getContainer()->get('doctrine')->getManager()->createQueryBuilder();
        $qb->delete('DiveAPIBundle:QueryCache', 'qc')
            ->where('qc.dataSet = :dataSet')
            ->setParameter(':dataSet', $dataSet);
            $qb->getQuery()->getResult();

        $output->writeln("Query cache cleared for dataset ".  $dataSet);
    }
}