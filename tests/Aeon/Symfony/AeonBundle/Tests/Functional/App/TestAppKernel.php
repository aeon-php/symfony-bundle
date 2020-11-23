<?php

declare(strict_types=1);

namespace Aeon\Symfony\AeonBundle\Tests\Functional\App;

use Aeon\Calendar\Gregorian\Calendar;
use Aeon\RateLimiter\Storage\PSRCacheStorage;
use Aeon\Symfony\AeonBundle\AeonBundle;
use Aeon\Symfony\AeonBundle\Tests\Functional\App\Form\NotHolidaysFormType;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class TestAppKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new AeonBundle(),
        ];
    }

    public function getCacheDir()
    {
        return \sys_get_temp_dir() . '/AeonBundle/cache';
    }

    public function getLogDir()
    {
        return \sys_get_temp_dir() . '/AeonBundle/logs';
    }

    public function holiday(Request $request) : Response
    {
        $form = $this->getContainer()->get('form.factory')->create(NotHolidaysFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return new Response();
        }

        return new Response((string) $form->getErrors(true, false), 422);
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader) : void
    {
        $c->register('cache.psr.array.adapter', ArrayAdapter::class);
        $c->register('cache.psr.array', PSRCacheStorage::class)
            ->setArguments([new Reference('cache.psr.array.adapter'), new Reference(Calendar::class)]);

        $c->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET',
            'test' => $this->environment === 'test',
        ]);
        $c->loadFromExtension('aeon', [
            'rate_limiter' => [
                [
                    'id' => 'leaky_bucket',
                    'algorithm' => 'leaky_bucket',
                    'configuration' => [
                        'bucket_size' => 5,
                        'leak_size' => 1,
                        'leak_time' => '1 second',
                        'storage_service_id' => 'cache.psr.array',
                    ],
                ],
            ],
        ]);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes) : void
    {
        $routes->add('/holiday', 'kernel::holiday');
    }
}
