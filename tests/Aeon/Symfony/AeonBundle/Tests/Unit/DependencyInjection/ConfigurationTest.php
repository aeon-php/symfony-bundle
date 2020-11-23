<?php

declare(strict_types=1);

namespace Aeon\Symfony\AeonBundle\Tests\Unit\DependencyInjection;

use Aeon\Calendar\Gregorian\Holidays\GoogleRegionalHolidaysFactory;
use Aeon\Symfony\AeonBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function test_default_configuration() : void
    {
        $config = $this->process([]);

        $this->assertEquals('UTC', $config['calendar_timezone']);
        $this->assertEquals('UTC', $config['ui_timezone']);
        $this->assertEquals('Y-m-d H:i:s', $config['ui_datetime_format']);
        $this->assertEquals('Y-m-d', $config['ui_date_format']);
        $this->assertEquals('H:i:s', $config['ui_time_format']);
        $this->assertSame([], $config['rate_limiter']);
        $this->assertEquals(GoogleRegionalHolidaysFactory::class, $config['calendar_holidays_factory_service']);
    }

    public function test_changed_configuration() : void
    {
        $config = $this->process([
            'aeon' => [
                'calendar_timezone' => 'UTC',
                'ui_timezone' => 'America/Los_Angeles',
                'ui_datetime_format' => 'Y-m-d H i s',
                'ui_date_format' => 'Y m d',
                'ui_time_format' => 'H i s',
            ],
        ]);

        $this->assertEquals('UTC', $config['calendar_timezone']);
        $this->assertEquals('America/Los_Angeles', $config['ui_timezone']);
        $this->assertEquals('Y-m-d H i s', $config['ui_datetime_format']);
        $this->assertEquals('Y m d', $config['ui_date_format']);
        $this->assertEquals('H i s', $config['ui_time_format']);
    }

    public function test_rate_limiter_leaky_bucket() : void
    {
        $config = $this->process([
            'aeon' => [
                'rate_limiter' => [
                    [
                        'id' => 'test',
                        'algorithm' => 'leaky_bucket',
                        'configuration' => [
                            'bucket_size' => 5,
                            'leak_size' => 2,
                            'leak_time' => '1 minute',
                            'storage_service_id' => 'symfony.storage.array',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            [
                'id' => 'test',
                'algorithm' => 'leaky_bucket',
                'configuration' => [
                    'bucket_size' => 5,
                    'leak_size' => 2,
                    'leak_time' => '1 minute',
                    'storage_service_id' => 'symfony.storage.array',
                ],
            ],
        ], $config['rate_limiter']);
    }

    public function test_rate_limiter_with_invalid_leaky_bucket_configuration() : void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "aeon.rate_limiter.0": leaky_bucket algorithm requires "bucket_size", "leak_size", "storage_service_id" and "leak_time" options to be configured');

        $this->process([
            'aeon' => [
                'rate_limiter' => [
                    [
                        'id' => 'test',
                        'algorithm' => 'leaky_bucket',
                        'configuration' => [],
                    ],
                ],
            ],
        ]);
    }

    public function test_rate_limiter_with_invalid_sliding_window_configuration() : void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "aeon.rate_limiter.0": sliding_window algorithm requires "limit", "storage_service_id" and "leak_time" options to be configured');

        $this->process([
            'aeon' => [
                'rate_limiter' => [
                    [
                        'id' => 'test',
                        'algorithm' => 'sliding_window',
                        'configuration' => [],
                    ],
                ],
            ],
        ]);
    }

    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
