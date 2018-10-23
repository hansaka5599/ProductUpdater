<?php
namespace Ecommistry\ProductUpdater\Cron;

use Couchbase\Exception;

/**
 * Class SpecialPriceUpdater
 * @package Ecommistry\ProductUpdater\Cron
 */
class SpecialPriceUpdater
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * MessagesUpdater constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\File\Csv $csvProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $importProductRawData = $this->csvProcessor->getData('app/code/Ecommistry/ProductUpdater/resources/special_prices.csv');
        $i = 0;
        foreach ($importProductRawData as $rowIndex => $dataRow) {
            //Skip the first row as it contains header
            if($i>0){
                $sku        =   $dataRow[0];
                $price      =   $dataRow[1];
                $from       =   $dataRow[2];
                $to         =   date('m/d/Y',date(strtotime("+1 day", strtotime($dataRow[3]))));
                $product    =   $this->getProductBySku($sku);
                if($product && $product->getId()){
                    try {
                        $product->setSpecialPrice($price);
                        $product->setSpecialFromDate($from);
                        $product->setSpecialFromDateIsFormated(true);
                        $product->setSpecialToDate($to);
                        $product->setSpecialToDateIsFormated(true);
                        /*$product->setCustomAttributes(array(
                                'special_from_date' => $from,
                                'special_to_date' => $to
                            )
                        );*/

                        $this->productRepository->save($product);
                        echo $i." SKU ".$product->getSku()." updated\n";
                    }
                    catch (\Exception $e) {
                        echo $i." Error saving SKU ".$product->getSku() .' '. $e->getMessage()."\n";
                    }
                }
                else{
                    echo $i." SKU ".$sku." not found\n";
                }
            }
            $i++;
        }
        echo "Process completed\n";
    }

    /**
     * @param $sku
     * @return mixed
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }
}