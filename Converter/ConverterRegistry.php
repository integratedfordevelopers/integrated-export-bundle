<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ExportBundle\Converter;

use Integrated\Common\ContentType\ContentTypeInterface;

class ConverterRegistry
{
    /**
     * @var ConverterInterface[]
     */
    protected $converters = [];

    /**
     * @param ContentTypeInterface|null $contentType
     * @return ConverterInterface[]
     */
    public function getConverters(ContentTypeInterface $contentType = null)
    {
        $converters = [];
        if (null !== $contentType) {
            foreach ($this->converters as $converter) {
                if ($converter->supports($contentType)) {
                    $converters[] = $converter;
                }
            }
        } else {
            $converters = $this->converters;
        }

        return $converters;
    }

    /**
     * @param ConverterInterface[] $converters
     * @return $this
     */
    public function setConverters($converters)
    {
        foreach ($converters as $converter) {
            $this->addConverter($converter);
        }

        return $this;
    }

    /**
     * @param ConverterInterface $converter
     */
    public function addConverter(ConverterInterface $converter)
    {
        $this->converters[] = $converter;
    }
}
