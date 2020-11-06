<?php

declare(strict_types=1);

namespace Aeon\Symfony\AeonBundle\Tests\Functional;

use Aeon\Symfony\AeonBundle\Tests\Functional\App\TestAppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FormTest extends WebTestCase
{
    public function test_not_holiday_validator_for_holiday() : void
    {
        $client = self::createClient();

        $client->request('POST', '/not-holiday', ['not_holidays' => ['day' => '2020-01-01']]);

        $this->assertSame(422, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertStringContainsString('ERROR: Day "2020-01-01" is a holiday.', $client->getResponse()->getContent());
    }

    public function test_not_holiday_validator_for_not_holiday() : void
    {
        $client = self::createClient();

        $client->request('POST', '/not-holiday', ['not_holidays' => ['day' => '2020-01-02']]);

        $this->assertSame(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertSame('not-holiday', $client->getResponse()->getContent());
    }

    protected static function getKernelClass()
    {
        return TestAppKernel::class;
    }
}
