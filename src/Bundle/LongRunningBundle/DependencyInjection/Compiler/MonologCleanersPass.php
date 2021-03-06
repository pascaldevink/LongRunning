<?php

namespace LongRunning\Bundle\LongRunningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MonologCleanersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $fingersCrossedHandlersId = 'long_running.monolog.clear_fingers_crossed_handlers';
        if ($container->has($fingersCrossedHandlersId)) {
            $fingersCrossedServiceReferences = [];
            foreach ($container->getDefinitions() as $serviceId => $definition) {
                if (strpos($serviceId, 'monolog.handler.') === 0) {
                    $class = $container->getParameterBag()->resolveValue($definition->getClass());
                    if (is_a($class, 'Monolog\Handler\FingersCrossedHandler', true)) {
                        $fingersCrossedServiceReferences[] = new Reference($serviceId);
                    }
                }
            }

            $definition = $container->getDefinition($fingersCrossedHandlersId);
            $definition->replaceArgument(0, $fingersCrossedServiceReferences);
        }

        $bufferHandlersId = 'long_running.monolog.close_buffer_handlers';
        if ($container->has($bufferHandlersId)) {
            $bufferHandlerServiceReferences = [];
            foreach ($container->getDefinitions() as $serviceId => $definition) {
                if (strpos($serviceId, 'monolog.handler.') === 0) {
                    $class = $container->getParameterBag()->resolveValue($definition->getClass());
                    if (is_a($class, 'Monolog\Handler\BufferHandler', true)) {
                        $bufferHandlerServiceReferences[] = new Reference($serviceId);
                    }
                }
            }

            $definition = $container->getDefinition($bufferHandlersId);
            $definition->replaceArgument(0, $bufferHandlerServiceReferences);
        }
    }
}
