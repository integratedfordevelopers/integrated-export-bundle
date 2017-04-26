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

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
interface ConverterInterface
{
    /**
     * @param array $data
     * @return ConverterValue[]
     */
    public function convert(array $data);

    /**
     * @param ContentTypeInterface $contentType
     * @return bool
     */
    public function supports(ContentTypeInterface $contentType);
}
