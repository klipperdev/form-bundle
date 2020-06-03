<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\FormBundle;

use Klipper\Bundle\FormBundle\DependencyInjection\Compiler\FormTypeDoctrineAwarePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperFormBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FormTypeDoctrineAwarePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }
}
