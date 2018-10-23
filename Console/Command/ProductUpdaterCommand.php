<?php
namespace Ecommistry\ProductUpdater\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProductUpdaterCommand
 * @package Ecommistry\ProductUpdater\Console\Command
 */
class ProductUpdaterCommand extends Command
{
    /**
     * App state
     *
     * @var \Magento\Framework\App\State
     */
    protected $appState;


    /**
     * Cron process
     *
     * @var \Ecommistry\ProductUpdater\Cron\ProductUpdater
     */
    protected $cronProcess;


    public function __construct(
        \Ecommistry\ProductUpdater\Cron\ProductUpdater $cron
    ) {
        $this->cronProcess = $cron;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cron:product-updater')
            ->setDescription('Ecommistry product-updater');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronProcess->execute();
    }
}
