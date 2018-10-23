<?php
namespace Ecommistry\ProductUpdater\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProductSpecialPricesCommand
 * @package Ecommistry\ProductUpdater\Console\Command
 */
class ProductSpecialPricesCommand extends Command
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
        \Ecommistry\ProductUpdater\Cron\SpecialPriceUpdater $cron
    ) {
        $this->cronProcess = $cron;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('animates:product-special-prices')
            ->setDescription('Animates product-special-prices');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cronProcess->execute();
    }
}