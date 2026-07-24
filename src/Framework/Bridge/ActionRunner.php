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

namespace Atom\Framework\Bridge;

use Atom\Framework\Module\ActionDescriptor;
use Atom\Framework\Module\ActionLocator;
use Atom\Framework\Module\ModuleException;
use Atom\Framework\Module\TemplateLocator;
use Atom\Framework\Security\SecurityEnforcer;
use Symfony\Component\HttpFoundation\Response;

final readonly class ActionRunner
{
    private const MAX_FORWARDS = 10;

    public function __construct(
        private ActionLocator $actions,
        private TemplateLocator $templates,
        private SecurityEnforcer $security,
    ) {}

    public function run(Context $context): Response
    {
        for ($forwards = 0; $forwards <= self::MAX_FORWARDS; ++$forwards) {
            $module = (string) $context->getRequest()->getParameter('module');
            $action = (string) $context->getRequest()->getParameter('action');

            try {
                return $this->runAction($context, $module, $action);
            } catch (ForwardException $exception) {
                $context->getRequest()->setParameter(
                    'module',
                    $exception->module,
                );
                $context->getRequest()->setParameter(
                    'action',
                    $exception->action,
                );
            }
        }

        throw new BridgeException(sprintf(
            'Action forwarding exceeded %d hops.',
            self::MAX_FORWARDS,
        ));
    }

    private function runAction(
        Context $context,
        string $module,
        string $action,
    ): Response {
        try {
            $descriptor = $this->actions->find($module, $action);
        } catch (ModuleException $exception) {
            throw new NotFoundException(
                sprintf('Action "%s/%s" was not found.', $module, $action),
                previous: $exception,
            );
        }

        if (null === $descriptor) {
            throw new NotFoundException(sprintf(
                'Action "%s/%s" was not found.',
                $module,
                $action,
            ));
        }

        require_once $descriptor->path;
        $class = $this->resolveClass($descriptor);
        $component = new $class($context, $module, $action);

        if (!$component instanceof Component) {
            throw new ModuleException(sprintf(
                'Action class "%s" must extend sfComponent.',
                $class,
            ));
        }

        $context->getViewRuntime()->prepare($module, $action);

        if ($component instanceof Action) {
            $configuration = $this->security->enforce(
                $context->getUser(),
                $module,
                $action,
            );
            $component->setSecurityConfiguration([
                strtolower($action) => $configuration,
            ]);
            $component->preExecute();
        }

        $result = $component->execute($context->getRequest());

        if ($component instanceof Action) {
            $component->postExecute();
        }

        if ($result instanceof Response) {
            return $result;
        }

        $response = $context->getResponse()->getSymfonyResponse();

        if (
            View::HEADER_ONLY === $result
            || $context->getResponse()->isHeaderOnly()
        ) {
            return $response;
        }

        $view = null === $result ? View::SUCCESS : (string) $result;

        if (View::NONE === $view) {
            return $response;
        }

        $templateModule = $component instanceof Action
            ? $component->getTemplateModule() ?? $module
            : $module;
        $templateAction = $component instanceof Action
            ? $component->getTemplate() ?? $action
            : $action;
        $format = $context->getRequest()->getRequestFormat();
        $path = $this->templates->find(
            $templateModule,
            $templateAction,
            $view,
            $format,
        );

        if (null === $path) {
            throw new ModuleException(sprintf(
                'Template for view "%s/%s%s" was not found.',
                $templateModule,
                $templateAction,
                $view,
            ));
        }

        $context->getConfiguration()->loadHelpers(
            (array) Configuration::get('sf_standard_helpers', []),
        );
        $runtime = $context->getViewRuntime();
        $layout = null;
        $mimeType = null === $format
            ? null
            : $context->getRequest()->getMimeType($format);

        if (
            $component instanceof Action
            && $component->hasLayoutOverride()
        ) {
            $layout = $component->getLayout() ?? false;
        } elseif (
            null !== $mimeType
            && null !== $format
            && 'html' !== $format
        ) {
            $layout = false;
        }

        if (null !== $mimeType) {
            $context->getResponse()->setContentType($mimeType);
        }

        $runtime->begin(
            $module,
            $action,
            $view,
            $layout,
        );
        $variables = $component->getVarHolder()->getAll();
        $content = $runtime->render(
            $path,
            $variables,
            $templateModule,
        );
        $response->setContent($runtime->decorate($content, $variables));

        return $response;
    }

    /**
     * @return class-string
     */
    private function resolveClass(ActionDescriptor $descriptor): string
    {
        $pluginClass = $descriptor->module.'_'.$descriptor->class;

        if (class_exists($pluginClass, false)) {
            return $pluginClass;
        }

        if (class_exists($descriptor->class, false)) {
            return $descriptor->class;
        }

        throw new ModuleException(sprintf(
            'Action file "%s" did not define class "%s".',
            $descriptor->path,
            $descriptor->class,
        ));
    }
}
