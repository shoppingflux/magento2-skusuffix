<?php

namespace ShoppingFeed\SkuSuffix\Plugin;

use Magento\Framework\App\ObjectManager;
use ShoppingFeed\Manager\Api\Data\Account\StoreInterface;
use ShoppingFeed\Manager\Model\Config\Field\TextBox;
use ShoppingFeed\Manager\Model\Config\FieldFactoryInterface;
use ShoppingFeed\Manager\Model\Config\Value\HandlerFactoryInterface as ValueHandlerFactoryInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Attribute\SourceInterface as AttributeSourceInterface;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Config\Attributes as AttributesConfig;
use ShoppingFeed\Manager\Model\Feed\Product\Section\Config\AttributesInterface as AttributesConfigInterface;
use ShoppingFeed\SkuSuffix\Model\Config\Suffix\Separator as SeparatorConfig;
use ShoppingFeed\SkuSuffix\Model\Config\Value\Handler\Alphanumeric as AlphanumericHandler;

class AddSkuSuffixOptionToAttributesSectionConfig extends AttributesConfig
{
    const KEY_SKU_SUFFIX = 'sku_suffix';

    /**
     * @var SeparatorConfig
     */
    private $separatorConfig;

    /**
     * @param FieldFactoryInterface $fieldFactory
     * @param ValueHandlerFactoryInterface $valueHandlerFactory
     * @param AttributeSourceInterface $renderableAttributeSource
     * @param SeparatorConfig|null $separatorConfig
     * @param string[] $mappableAttributes
     */
    public function __construct(
        FieldFactoryInterface $fieldFactory,
        ValueHandlerFactoryInterface $valueHandlerFactory,
        AttributeSourceInterface $renderableAttributeSource,
        SeparatorConfig $separatorConfig = null,
        array $mappableAttributes = []
    ) {
        $this->separatorConfig = $separatorConfig ?: ObjectManager::getInstance()->get(SeparatorConfig::class);
        parent::__construct($fieldFactory, $valueHandlerFactory, $renderableAttributeSource, $mappableAttributes);
    }

    public function afterGetFields(AttributesConfigInterface $subject, $result, StoreInterface $store)
    {
        if (is_array($result)) {
            $result[self::KEY_SKU_SUFFIX] = $this->fieldFactory->create(
                TextBox::TYPE_CODE,
                [
                    'name' => self::KEY_SKU_SUFFIX,
                    'valueHandler' => $this->valueHandlerFactory->create(AlphanumericHandler::TYPE_CODE),
                    'label' => __('SKU Suffix'),
                    'sortOrder' => 15,
                    'notice' => __('The suffix will be added to the product references exported in the feed.')
                        . "\n"
                        . __('Leave empty to not use a suffix.')
                        . "\n"
                        . __(
                            'For example, with "TEST" as the suffix, SKU "1234" will be exported as "1234%1TEST".',
                            $this->separatorConfig->getCurrentSeparator()
                        ),
                ]
            );
        }

        return $result;
    }

    /**
     * @param AttributesConfigInterface $config
     * @param StoreInterface $store
     * @return string
     */
    public function getConfigSkuSuffix(AttributesConfigInterface $config, StoreInterface $store)
    {
        return trim((string) $config->getFieldValue($store, self::KEY_SKU_SUFFIX));
    }
}
