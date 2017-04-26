<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ConverterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('integrated_export.converter.converter_registry')) {
            return;
        }

        $registry = $container->getDefinition('integrated_export.converter.converter_registry');

        foreach ($container->findTaggedServiceIds('integrated_export.converter') as $service => $tag) {
            $registry->addMethodCall('addConverter', [$container->getDefinition($service)]);
        }
    }
}
