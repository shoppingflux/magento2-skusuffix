<?php

namespace ShoppingFeed\SkuSuffix\Plugin;

use ShoppingFeed\Feed\Product\AbstractProduct as AbstractExportedProduct;
use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Adapter\AttributesInterface as AttributesAdapterInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Config\AttributesInterface as AttributesConfigInterface;

class AddSuffixToFeedProductSku
{
    /**
     * @var AttributesConfigInterface
     */
    private $attributesConfig;

    /**
     * @var AddSkuSuffixOptionToAttributesSectionConfig
     */
    private $skuSuffixOptionPlugin;

    /**
     * @param AttributesConfigInterface $attributesConfig
     * @param AddSkuSuffixOptionToAttributesSectionConfig $skuSuffixOptionPlugin
     */
    public function __construct(
        AttributesConfigInterface $attributesConfig,
        AddSkuSuffixOptionToAttributesSectionConfig $skuSuffixOptionPlugin
    ) {
        $this->attributesConfig = $attributesConfig;
        $this->skuSuffixOptionPlugin = $skuSuffixOptionPlugin;
    }

    public function afterExportBaseProductData(
        AttributesAdapterInterface $subject,
        $result,
        StoreInterface $store,
        array $productData,
        AbstractExportedProduct $exportedProduct
    ) {
        $suffix = $this->skuSuffixOptionPlugin->getConfigSkuSuffix($this->attributesConfig, $store);

        if ('' !== $suffix) {
            $exportedProduct->setReference(
                (string) $exportedProduct->getReference()
                . '-'
                . $suffix
            );
        }

        return $result;
    }
}
