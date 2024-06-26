<?php

namespace ShoppingFeed\SkuSuffix\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Api\Data\Marketplace\Order\ItemInterface as MarketplaceItemInterface;
use ShoppingFeed\Manager\Model\Sales\Order\ConfigInterface as OrderConfigInterface;
use ShoppingFeed\Manager\Model\Sales\Order\ImporterInterface;
use ShoppingFeed\SkuSuffix\Model\Config\Suffix\Separator as SeparatorConfig;

class MapOrderItemSkusBeforeImport
{
    /**
     * @var SeparatorConfig
     */
    private $separatorConfig;

    /**
     * @var OrderConfigInterface
     */
    private $orderGeneralConfig;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param OrderConfigInterface $orderGeneralConfig
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        OrderConfigInterface $orderGeneralConfig,
        ProductCollectionFactory $productCollectionFactory,
        SeparatorConfig $separatorConfig = null
    ) {
        $this->separatorConfig = $separatorConfig ?: ObjectManager::getInstance()->get(SeparatorConfig::class);
        $this->orderGeneralConfig = $orderGeneralConfig;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param ImporterInterface $subject
     * @param Quote $quote
     * @param MarketplaceItemInterface[] $marketplaceItems
     * @param bool $isUntaxedBusinessOrder
     * @param StoreInterface $store
     * @throws LocalizedException
     */
    public function beforeImportQuoteItems(
        ImporterInterface $subject,
        Quote $quote,
        array $marketplaceItems,
        $isUntaxedBusinessOrder,
        StoreInterface $store
    ) {
        $skuCandidates = [];

        $referenceRegex = '/^(.+)['
            . preg_quote(implode('', $this->separatorConfig->getAllSeparators()), '/')
            . ']([a-z0-9]+)$/iu';

        foreach ($marketplaceItems as $marketplaceItem) {
            $reference = trim((string) $marketplaceItem->getReference());

            if (preg_match($referenceRegex, $reference, $matches)) {
                $skuCandidates[$reference] = [ $matches[1], $reference ];
            }
        }

        if (!empty($skuCandidates)) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('sku');

            $productCollection->addAttributeToFilter(
                'sku',
                [ 'in' => array_merge(...array_values($skuCandidates)) ]
            );

            $existingSkus = [];

            foreach ($productCollection as $product) {
                $existingSkus[] = $product->getSku();
            }

            foreach ($marketplaceItems as $marketplaceItem) {
                $reference = trim((string) $marketplaceItem->getReference());

                if (isset($skuCandidates[$reference])) {
                    $candidates = array_intersect($skuCandidates[$reference], $existingSkus);

                    if (count($candidates) > 1) {
                        throw new LocalizedException(
                            __(
                                'Multiple matches found for suffixed SKU "%1": %2.',
                                $reference,
                                implode(
                                    ', ',
                                    array_map(
                                        function ($sku) {
                                            return '"' . $sku . '"';
                                        },
                                        $candidates
                                    )
                                )
                            )
                        );
                    } elseif (count($candidates) === 1) {
                        $marketplaceItem->setReference(array_shift($candidates));
                    } else {
                        // Let the import process handle the seemingly missing SKU.
                    }
                }
            }
        }
    }
}