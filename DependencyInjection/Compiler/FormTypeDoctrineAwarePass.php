<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\FormBundle\DependencyInjection\Compiler;

use Klipper\Component\Form\Doctrine\FormTypeDoctrineAwareInterface;
use Klipper\Component\Form\Doctrine\Type\EntityType as KlipperEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as SymfonyEntityType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register the form type doctrine aware.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormTypeDoctrineAwarePass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $resolverId = 'klipper_form.doctrine.target_object_resolver';

        $this->buildResolverDefinition($container, $resolverId);
        $this->cleanEntityFormTypeExtensions($container);

        foreach (['form.type', 'form.type_extension'] as $type) {
            foreach ($this->findAndSortTaggedServices($type, $container) as $service) {
                $def = $container->getDefinition($service);

                if (!$def->hasMethodCall('setTargetObjectResolver')
                        && is_a($def->getClass(), FormTypeDoctrineAwareInterface::class, true)) {
                    $def->addMethodCall('setTargetObjectResolver', [new Reference($resolverId)]);
                }
            }
        }
    }

    /**
     * Remove the resolve target form extension if doctrine entity types are not installed.
     *
     * @param ContainerBuilder $container The container
     */
    private function cleanEntityFormTypeExtensions(ContainerBuilder $container): void
    {
        $classes = [
            SymfonyEntityType::class,
            KlipperEntityType::class,
        ];

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $id = 'klipper_form.type_extension.'.strtolower(explode('\\', $class)[0]).'_entity_resolve_target';
                $container->removeDefinition($id);
            }
        }
    }

    /**
     * Build the service of the target object resolver.
     *
     * @param ContainerBuilder $container  The container
     * @param string           $resolverId The service id of target object resolver
     */
    private function buildResolverDefinition(ContainerBuilder $container, $resolverId): void
    {
        if ($container->hasDefinition('doctrine.orm.listeners.resolve_target_entity')) {
            $doctrineResolverDef = $container->getDefinition('doctrine.orm.listeners.resolve_target_entity');
            $resolverDef = $container->getDefinition($resolverId);

            foreach ($doctrineResolverDef->getMethodCalls() as $config) {
                if ('addResolveTargetEntity' === $config[0]) {
                    $resolverDef->addMethodCall('addResolveTargetObject', [$config[1][0], $config[1][1]]);
                }
            }
        }
    }
}
