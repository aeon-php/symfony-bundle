<?php

declare(strict_types=1);

namespace Aeon\Symfony\AeonBundle\DependencyInjection;

use Aeon\Calendar\Gregorian\Holidays\GoogleRegionalHolidaysFactory;
use Aeon\Calendar\Gregorian\TimeZone;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('aeon');
        /**
         * @psalm-suppress MixedAssignment
         * @phpstan-ignore-next-line
         */
        $rootNode = \method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('aeon');

        $rootNode
            ->fixXmlConfig('rate_limiters')
            ->children()
                ->scalarNode('calendar_timezone')->defaultValue(TimeZone::UTC)->end()
                ->scalarNode('calendar_holidays_factory_service')->defaultValue(GoogleRegionalHolidaysFactory::class)->end()
                ->scalarNode('ui_timezone')->defaultValue(TimeZone::UTC)->end()
                ->scalarNode('ui_datetime_format')->defaultValue('Y-m-d H:i:s')->end()
                ->scalarNode('ui_date_format')->defaultValue('Y-m-d')->end()
                ->scalarNode('ui_time_format')->defaultValue('H:i:s')->end()
                ->arrayNode('rate_limiter')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('id')->end()
                            ->enumNode('algorithm')
                            ->isRequired()
                                ->values(['sliding_window', 'leaky_bucket'])
                            ->end()
                            ->arrayNode('configuration')
                                ->children()
                                    ->integerNode('bucket_size')->end()
                                    ->integerNode('leak_size')->end()
                                    ->integerNode('limit')->end()
                                    ->scalarNode('leak_time')->end()
                                    ->scalarNode('time_window')->end()
                                    ->scalarNode('storage_service_id')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function (array $v) {
                                return 'sliding_window' === $v['algorithm'] && (!isset($v['configuration']['limit']) || !isset($v['configuration']['time_window']) || !isset($v['configuration']['storage_service_id']));
                            })
                            ->thenInvalid('sliding_window algorithm requires "limit", "storage_service_id" and "leak_time" options to be configured')
                        ->end()
                        ->validate()
                        ->ifTrue(function (array $v) {
                            return 'leaky_bucket' === $v['algorithm'] && (!isset($v['configuration']['bucket_size']) || !isset($v['configuration']['leak_size']) || !isset($v['configuration']['leak_time']) || !isset($v['configuration']['storage_service_id']));
                        })
                        ->thenInvalid('leaky_bucket algorithm requires "bucket_size", "leak_size", "storage_service_id" and "leak_time" options to be configured')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
