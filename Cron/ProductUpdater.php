<?php
namespace Ecommistry\ProductUpdater\Cron;

use Couchbase\Exception;

/**
 * Class ProductUpdater
 * @package Ecommistry\ProductProduct\Cron
 */
class ProductUpdater
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
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;


    /**
     * @var Status
     */
    protected $_productStatus;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * DISABLE
     */
    const DISABLE_ID = 0;

    /**
     * SALE
     */
    const SALE_ID = 1;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productApiFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productApiRepository;

    /**
     * ProductUpdater constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productApiFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productApiRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\State $state,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productApiFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productApiRepository
    ) {
        $this->storeManager = $storeManager;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->state = $state;
        $this->csvProcessor = $csvProcessor;
        $this->productApiFactory = $productApiFactory;
        $this->productApiRepository = $productApiRepository;
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $importProductRawData = $this->csvProcessor->getData('app/code/Ecommistry/ProductUpdater/resources/product_updates.csv');
        $i = 0;
        $skuArray = [];
        $dataArray = [];
        foreach ($importProductRawData as $rowIndex => $dataRow) {
            if($i>0){
                $sku = $dataRow[1];
                $skuArray[$sku] = $sku;
                $dataArray[$sku] = [
                    'sku' => $dataRow[1],
                    'id' => null,
                    'name' => $dataRow[2],
                    'price' => $dataRow[4],
                    'status' => 'approved',
                    'url' => str_replace(' ', '-', strtolower($dataRow[2])),
                    'weight' => $dataRow[6],
                    'length' => $dataRow[7],
                    'width' => $dataRow[8],
                    'height' => $dataRow[9],
                    'packaging' => $dataRow[5],
                ];
            }
            $i++;
        }

        $storeId = 1;
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('sku', ['in' => $skuArray]);

        echo "Total SKUs found to update ".$collection->getSize()."\n";

        foreach ($collection->getItems() as $item) {
            $product = $this->productRepository->getById($item->getId(), true, $storeId);
            if ($product->getId()) {
                $productInfo = $dataArray[$product->getSku()];
                $dataArray[$product->getSku()]['id'] = $product->getId();
                try {
                    $product->setName($productInfo['name']);
                    $product->setPrice($productInfo['price']);
                    $product->setAnimatesStatus($productInfo['status']);
                    $product->setUrlKey($productInfo['url']);
                    $product->setWeight($productInfo['weight']);
                    $product->setTemandoPackage1Weight($productInfo['weight']);
                    $product->setTemandoPackage1Length($productInfo['length']);
                    $product->setTemandoPackage1Width($productInfo['width']);
                    $product->setTemandoPackage1Height($productInfo['height']);
                    $product->setPackagingPreference($productInfo['packaging']);
                    $this->productRepository->save($product);
                    echo "SKU ".$product->getSku()." updated\n";
                }
                catch (\Exception $e) {
                    echo "Error saving SKU ".$product->getSku() .' '. $e->getMessage()."\n";
                }
            }
            else{
                echo "SKU ".$product->getSku()." not found\n";
            }
        }

        foreach ($dataArray as $newProduct){
            //Product to create
            if($newProduct['id']==null){
                try {
                    $productNew = $this->productApiFactory->create();
                    $productNew->setSku($newProduct['sku']);
                    $productNew->setName($newProduct['name']);
                    $productNew->setPrice($newProduct['price']);
                    $productNew->setAnimatesStatus($newProduct['status']);
                    $productNew->setUrlKey($newProduct['url']);
                    $productNew->setWeight($newProduct['weight']);
                    $productNew->setTemandoPackage1Weight($newProduct['weight']);
                    $productNew->setTemandoPackage1Length($newProduct['length']);
                    $productNew->setTemandoPackage1Width($newProduct['width']);
                    $productNew->setTemandoPackage1Height($newProduct['height']);
                    $productNew->setPackagingPreference($newProduct['packaging']);
                    $productNew->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                    $productNew->setVisibility(4);
                    $productNew->setAttributeSetId(4); // Default attribute set for products
                    $productNew->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

                    $this->productApiRepository->save($productNew);
                    echo "SKU ".$newProduct['sku']." created\n";
                }
                catch (\Exception $e) {
                    echo "SKU ".$newProduct['sku']." cannot to create ".$e->getMessage()."\n";
                }
            }
        }
    }
}
