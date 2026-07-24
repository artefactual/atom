<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom;

use Atom\Controller\LegacyController;
use Atom\Framework\Autoload\LegacyClassDirectories;
use Atom\Framework\Autoload\LegacyClassLoader;
use Atom\Framework\Bridge\ActionRunner;
use Atom\Framework\Bridge\BridgeRegistrar;
use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Bridge\TemplateRenderer;
use Atom\Framework\Bridge\User;
use Atom\Framework\Configuration\ApplicationConfiguration;
use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ConfigurationPathResolver;
use Atom\Framework\Configuration\ConstantReplacer;
use Atom\Framework\Configuration\DirectoryParameters;
use Atom\Framework\Configuration\HybridYamlFileLoader;
use Atom\Framework\Configuration\ParameterCompiler;
use Atom\Framework\Module\ActionLocator;
use Atom\Framework\Module\ModuleDirectories;
use Atom\Framework\Module\TemplateLocator;
use Atom\Framework\Plugin\PluginRegistry;
use Atom\Framework\Routing\RouteCompiler;
use Atom\Framework\Routing\RouteConfigurationLoader;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollection;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const APPLICATION = 'qubit';

    private ?LegacyClassLoader $legacyClassLoader = null;

    public function boot(): void
    {
        (new BridgeRegistrar())->register();
        $this->legacyClassLoader ??= new LegacyClassLoader(
            new LegacyClassDirectories($this->getProjectDir()),
        );
        $this->legacyClassLoader->register();

        try {
            parent::boot();
            Configuration::clear();
            Configuration::add(
                $this->getContainer()->getParameterBag()->all(),
            );
        } catch (\Throwable $exception) {
            $this->legacyClassLoader->unregister();

            throw $exception;
        }
    }

    public function shutdown(): void
    {
        try {
            parent::shutdown();
        } finally {
            Context::setInstance(null);
            Configuration::clear();
            $this->legacyClassLoader?->unregister();
        }
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir()
            .'/cache/'.self::APPLICATION.'/'.$this->environment.'/symfony';
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/log';
    }

    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $routes = (new RouteConfigurationLoader(
            $this->applicationConfiguration(),
            new RouteCompiler(),
        ))->load();

        foreach ($routes as $route) {
            $route->setDefault('_controller', LegacyController::class);
        }

        return $routes;
    }

    protected function configureContainer(
        ContainerConfigurator $container,
    ): void {
        $plugins = (new PluginRegistry($this->getProjectDir()))->enabled();
        $configuration = $this->applicationConfiguration();
        $parameters = array_replace(
            $configuration->parameters('config/app.yml', 'app_'),
            $configuration->parameters('config/settings.yml', 'sf_'),
            $this->directoryParameters(),
        );

        if (false !== $readOnly = getenv('ATOM_READ_ONLY')) {
            $parameters['app_read_only'] = filter_var(
                $readOnly,
                \FILTER_VALIDATE_BOOLEAN,
            );
        }

        $parameters['sf_escaping_strategy'] = true;

        foreach ($parameters as $name => $value) {
            $container->parameters()->set($name, $value);
        }

        $container->extension('framework', [
            'secret' => $this->frameworkSecret($parameters),
            'default_locale' => $parameters['sf_default_culture'] ?? 'en',
            'handle_all_throwables' => true,
            'router' => [
                'utf8' => true,
            ],
            'test' => 'test' === $this->environment,
        ]);

        $services = $container->services();
        $services->defaults()->autowire()->autoconfigure();
        $services->set(User::class)->public();
        $services->set(EventDispatcher::class);
        $services->set(RuntimeConfiguration::class)->args([
            self::APPLICATION,
            $this->environment,
            $plugins,
        ]);
        $services->set(ModuleDirectories::class)->args([
            $this->getProjectDir(),
            self::APPLICATION,
            $plugins,
        ]);
        $services->set(ActionLocator::class);
        $services->set(TemplateLocator::class);
        $services->set(TemplateRenderer::class);
        $services->set(ActionRunner::class);
        $services
            ->set(LegacyController::class)
            ->public()
            ->tag('controller.service_arguments');
    }

    private function applicationConfiguration(): ApplicationConfiguration
    {
        $parameters = $this->directoryParameters();

        return new ApplicationConfiguration(
            new ConfigurationPathResolver(
                $this->getProjectDir(),
                self::APPLICATION,
                (new PluginRegistry($this->getProjectDir()))->enabled(),
            ),
            new HybridYamlFileLoader(),
            new ConfigurationMerger(),
            new ConstantReplacer(),
            new ParameterCompiler(),
            $this->environment,
            $parameters,
        );
    }

    private function directoryParameters(): array
    {
        return (new DirectoryParameters(
            $this->getProjectDir(),
            self::APPLICATION,
            $this->environment,
            $this->debug,
        ))->all();
    }

    private function frameworkSecret(array $parameters): string
    {
        $environmentSecret = $_SERVER['APP_SECRET']
            ?? $_ENV['APP_SECRET']
            ?? getenv('APP_SECRET');

        if (is_string($environmentSecret) && '' !== $environmentSecret) {
            return $environmentSecret;
        }

        $configuredSecret = $parameters['sf_csrf_secret'] ?? null;

        if (is_string($configuredSecret) && '' !== $configuredSecret) {
            return $configuredSecret;
        }

        if ('test' === $this->environment) {
            return 'atom-test-secret';
        }

        throw new ConfigurationException(
            'Set APP_SECRET or configure csrf_secret before booting AtoM.',
        );
    }
}
