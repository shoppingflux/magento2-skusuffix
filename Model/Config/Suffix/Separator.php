<?php

namespace ShoppingFeed\SkuSuffix\Model\Config\Suffix;

class Separator
{
    const DEFAULT_SEPARATOR = '_';

    /**
     * @var string
     */
    private $separator;

    /**
     * @var string[]
     */
    private $oldSeparators;

    /**
     * @param string $separator
     * @param string[] $oldSeparators
     */
    public function __construct(
        $separator = self::DEFAULT_SEPARATOR,
        array $oldSeparators = []
    ) {
        $this->separator = $separator;
        $this->oldSeparators = $oldSeparators;

        foreach ($this->getAllSeparators() as $separator) {
            if (!is_string($separator) || !preg_match('/^[^a-z0-9]$/i', $separator)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid separator provided: "%s" (suffix separators must be single non-alphanumeric characters).',
                        $separator
                    )
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getCurrentSeparator()
    {
        return $this->separator;
    }

    /**
     * @return string[]
     */
    public function getOldSeparators()
    {
        return $this->oldSeparators;
    }

    /**
     * @return string[]
     */
    public function getAllSeparators()
    {
        return array_merge(
            [ $this->getCurrentSeparator() ],
            $this->getOldSeparators()
        );
    }
}
