<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\UX\TwigComponent\Tests\Fixture\Component\ComponentA;
use Symfony\UX\TwigComponent\Tests\Fixture\Component\ComponentB;
use Symfony\UX\TwigComponent\Tests\Fixture\Component\ComponentC;
use Symfony\UX\TwigComponent\Tests\Fixture\Service\ServiceA;
use Symfony\UX\TwigComponent\TwigComponentBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new TwigComponentBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'test' => true,
            'router' => ['utf8' => true],
            'secrets' => false,
        ]);
        $c->loadFromExtension('twig', [
            'default_path' => '%kernel.project_dir%/tests/Fixture/templates',
        ]);

        $c->register(ServiceA::class)->setAutoconfigured(true)->setAutowired(true);

        $componentA = $c->register(ComponentA::class)->setAutoconfigured(true)->setAutowired(true);
        $componentB = $c->register('component_b', ComponentB::class)->setAutoconfigured(true)->setAutowired(true);
        $componentC = $c->register(ComponentC::class)->setAutoconfigured(true)->setAutowired(true);

        $c->register('component_d', ComponentB::class)->addTag('twig.component', [
            'key' => 'component_d',
            'template' => 'components/custom2.html.twig',
        ]);

        if (self::VERSION_ID < 50300) {
            // add tag manually
            $componentA->addTag('twig.component', ['key' => 'component_a']);
            $componentB->addTag('twig.component', ['key' => 'component_b', 'template' => 'components/custom1.html.twig']);
            $componentC->addTag('twig.component', ['key' => 'component_c']);
        }

        if ('missing_key' === $this->environment) {
            $c->register('missing_key', ComponentB::class)->setAutowired(true)->addTag('twig.component');
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }
}
