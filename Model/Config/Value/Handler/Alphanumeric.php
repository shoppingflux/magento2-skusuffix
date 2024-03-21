<?php

namespace ShoppingFeed\SkuSuffix\Model\Config\Value\Handler;

use ShoppingFeed\Manager\Model\Config\Value\Handler\Text;

class Alphanumeric extends Text
{
    const TYPE_CODE = 'alphanumeric';

    public function getFieldValidationClasses()
    {
        return [ 'validate-alphanum' ];
    }

    public function isValidValue($value, $isRequired)
    {
        return (
            parent::isValidValue($value, $isRequired)
            && preg_match('/^[a-zA-Z0-9]*$/', (string) $value)
        );
    }
}
