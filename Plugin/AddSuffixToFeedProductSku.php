<?php

namespace ShoppingFeed\SkuSuffix\Plugin;

use Magento\Framework\App\ObjectManager;
use ShoppingFeed\Feed\Product\AbstractProduct as AbstractExportedProduct;
use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Adapter\AttributesInterface as AttributesAdapterInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Config\AttributesInterface as AttributesConfigInterface;
use ShoppingFeed\SkuSuffix\Model\Config\Suffix\Separator as SeparatorConfig;

class AddSuffixToFeedProductSku
{
    /**
     * @var SeparatorConfig
     */
    private $separatorConfig;

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
     * @param SeparatorConfig|null $separatorConfig
     */
    public function __construct(
        AttributesConfigInterface $attributesConfig,
        AddSkuSuffixOptionToAttributesSectionConfig $skuSuffixOptionPlugin,
        SeparatorConfig $separatorConfig = null
    ) {
        $this->separatorConfig = $separatorConfig ?: ObjectManager::getInstance()->get(SeparatorConfig::class);
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
                . $this->separatorConfig->getCurrentSeparator()
                . $suffix
            );
        }

        return $result;
    }
}
