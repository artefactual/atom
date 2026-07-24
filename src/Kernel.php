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
use Atom\EventSubscriber\PropelRequestSubscriber;
use Atom\EventSubscriber\ResourceRouteSubscriber;
use Atom\EventSubscriber\UserRequestSubscriber;
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
use Atom\Framework\Bridge\ViewRuntimeFactory;
use Atom\Framework\Configuration\ApplicationConfiguration;
use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ConfigurationPathResolver;
use Atom\Framework\Configuration\ConstantReplacer;
use Atom\Framework\Configuration\DirectoryParameters;
use Atom\Framework\Configuration\HybridYamlFileLoader;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Configuration\ParameterCompiler;
use Atom\Framework\Configuration\ViewConfiguration;
use Atom\Framework\Database\PropelBootstrap;
use Atom\Framework\Filter\CspFilter;
use Atom\Framework\Filter\FilterConfiguration;
use Atom\Framework\Filter\FilterPipeline;
use Atom\Framework\Filter\HistoryFilter;
use Atom\Framework\Filter\IpLimitFilter;
use Atom\Framework\Filter\IpRangeMatcher;
use Atom\Framework\Filter\MetaFilter;
use Atom\Framework\Filter\PropelSettingsRepository;
use Atom\Framework\Filter\RestApiFilter;
use Atom\Framework\Filter\ResultLimitFilter;
use Atom\Framework\Filter\RuntimeSettingsFilter;
use Atom\Framework\Filter\SettingsRepository;
use Atom\Framework\Filter\SslRequirementFilter;
use Atom\Framework\Filter\SwordHttpAuthFilter;
use Atom\Framework\Filter\TransactionFilter;
use Atom\Framework\Module\ActionLocator;
use Atom\Framework\Module\ComponentLocator;
use Atom\Framework\Module\LayoutLocator;
use Atom\Framework\Module\ModuleDirectories;
use Atom\Framework\Module\TemplateLocator;
use Atom\Framework\Plugin\PdoPluginSettingsReader;
use Atom\Framework\Plugin\PluginRegistry;
use Atom\Framework\Plugin\PluginRuntimeParameters;
use Atom\Framework\Routing\PropelResourceRepository;
use Atom\Framework\Routing\QubitResourceClassifier;
use Atom\Framework\Routing\ResourceClassifier;
use Atom\Framework\Routing\ResourceRepository;
use Atom\Framework\Routing\ResourceRouteResolver;
use Atom\Framework\Routing\RouteCompiler;
use Atom\Framework\Routing\RouteConfigurationLoader;
use Atom\Framework\Security\SecurityConfiguration;
use Atom\Framework\Security\SecurityEnforcer;
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
    private ?array $enabledPlugins = null;

    public function boot(): void
    {
        (new BridgeRegistrar())->register();
        $directories = new LegacyClassDirectories($this->getProjectDir());
        $this->legacyClassLoader ??= new LegacyClassLoader(
            $directories,
            includePaths: $directories->includePaths(),
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
        $plugins = $this->enabledPlugins();
        $configuration = $this->applicationConfiguration();
        $parameters = array_replace(
            $configuration->parameters('config/app.yml', 'app_'),
            $configuration->parameters('config/settings.yml', 'sf_'),
            $this->directoryParameters(),
        );
        $parameters = (new PluginRuntimeParameters())->apply(
            $parameters,
            $plugins,
            $this->getProjectDir(),
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
            'session' => [
                'enabled' => true,
                'storage_factory_id' => 'test' === $this->environment
                    ? 'session.storage.factory.mock_file'
                    : 'session.storage.factory.native',
                'name' => 'symfony',
                'cookie_secure' => true,
                'cookie_httponly' => true,
                'cookie_samesite' => 'strict',
                'save_path' => '%kernel.cache_dir%/sessions',
            ],
            'test' => 'test' === $this->environment,
        ]);

        $services = $container->services();
        $services->defaults()->autowire()->autoconfigure();
        $services->set(User::class)->public();
        $services->set(UserRequestSubscriber::class);
        $services->set(EventDispatcher::class);
        $services->set(RuntimeConfiguration::class)->args([
            self::APPLICATION,
            $this->environment,
            $plugins,
            $this->debug,
            $this->getProjectDir(),
        ]);
        $services->set(ModuleDirectories::class)->args([
            $this->getProjectDir(),
            self::APPLICATION,
            $plugins,
        ]);
        $services->set(ActionLocator::class);
        $services->set(ComponentLocator::class);
        $services->set(TemplateLocator::class);
        $services->set(LayoutLocator::class)->args([
            $this->getProjectDir(),
            self::APPLICATION,
            $parameters['sf_decorator_dirs'],
        ]);
        $services->set(TemplateRenderer::class);
        $services->set(ModuleConfigurationLoader::class)->args([
            $this->getProjectDir(),
            self::APPLICATION,
            $plugins,
        ]);
        $services->set(ConfigurationMerger::class);
        $services->set(ViewConfiguration::class);
        $services->set(ViewRuntimeFactory::class);
        $services->set(SecurityConfiguration::class);
        $services->set(SecurityEnforcer::class);
        $services->set(FilterConfiguration::class);
        $services->set(PropelSettingsRepository::class);
        $services->alias(
            SettingsRepository::class,
            PropelSettingsRepository::class,
        );
        $services->set(RuntimeSettingsFilter::class)->arg(
            '$environment',
            $this->environment,
        );
        $services->set(HistoryFilter::class);
        $services->set(IpRangeMatcher::class);
        $services->set(IpLimitFilter::class);
        $services->set(SslRequirementFilter::class);
        $services->set(MetaFilter::class);
        $services->set(ResultLimitFilter::class);
        $services->set(TransactionFilter::class);
        $services->set(CspFilter::class);
        $services->set(RestApiFilter::class);
        $services->set(SwordHttpAuthFilter::class);
        $services->set(FilterPipeline::class);
        $services->set(ActionRunner::class);
        $services->set(PropelBootstrap::class)->args([
            $configuration->load('config/config.php'),
        ]);
        $services->set(PropelRequestSubscriber::class);
        $services->set(PropelResourceRepository::class);
        $services->alias(
            ResourceRepository::class,
            PropelResourceRepository::class,
        );
        $services->set(QubitResourceClassifier::class);
        $services->alias(
            ResourceClassifier::class,
            QubitResourceClassifier::class,
        );
        $services->set(ResourceRouteResolver::class);
        $services->set(ResourceRouteSubscriber::class);
        $services
            ->set(LegacyController::class)
            ->public()
            ->tag('controller.service_arguments');
    }

    private function applicationConfiguration(
        ?array $plugins = null,
    ): ApplicationConfiguration {
        $parameters = $this->directoryParameters();

        return new ApplicationConfiguration(
            new ConfigurationPathResolver(
                $this->getProjectDir(),
                self::APPLICATION,
                $plugins ?? $this->enabledPlugins(),
            ),
            new HybridYamlFileLoader(),
            new ConfigurationMerger(),
            new ConstantReplacer(),
            new ParameterCompiler(),
            $this->environment,
            $parameters,
        );
    }

    private function enabledPlugins(): array
    {
        if (null !== $this->enabledPlugins) {
            return $this->enabledPlugins;
        }

        $settings = null;

        if ('test' !== $this->environment) {
            $databases = $this->applicationConfiguration([])
                ->load('config/config.php');
            $settings = new PdoPluginSettingsReader($databases);
        }

        return $this->enabledPlugins = (new PluginRegistry(
            $this->getProjectDir(),
            settings: $settings,
        ))->enabled();
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
