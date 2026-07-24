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

abstract class Action extends Component
{
    protected array $security = [];

    private ?string $template = null;
    private ?string $templateModule = null;
    private ?string $layout = null;
    private bool $layoutSet = false;
    private ?string $viewClass = null;

    public function preExecute(): void {}

    public function postExecute(): void {}

    public function forward404(?string $message = null): never
    {
        throw new NotFoundException($this->get404Message($message));
    }

    public function forward404Unless(
        mixed $condition,
        ?string $message = null,
    ): void {
        if (!$condition) {
            $this->forward404($message);
        }
    }

    public function forward404If(
        mixed $condition,
        ?string $message = null,
    ): void {
        if ($condition) {
            $this->forward404($message);
        }
    }

    public function redirect404(): never
    {
        $module = Configuration::get('sf_error_404_module', 'default');
        $action = Configuration::get('sf_error_404_action', 'error404');

        $this->redirect(sprintf('/%s/%s', $module, $action));
    }

    public function forward(string $module, string $action): never
    {
        throw new ForwardException($module, $action);
    }

    public function forwardIf(
        mixed $condition,
        string $module,
        string $action,
    ): void {
        if ($condition) {
            $this->forward($module, $action);
        }
    }

    public function forwardUnless(
        mixed $condition,
        string $module,
        string $action,
    ): void {
        if (!$condition) {
            $this->forward($module, $action);
        }
    }

    public function redirect(
        array|string $url,
        mixed $statusCode = 302,
        mixed ...$arguments,
    ): never {
        if (is_object($statusCode) || is_array($statusCode)) {
            $url = array_merge(
                ['sf_route' => $url],
                is_object($statusCode)
                    ? ['sf_subject' => $statusCode]
                    : $statusCode,
            );
            $statusCode = $arguments[0] ?? 302;
        }

        $this->getController()->redirect($url, 0, (int) $statusCode);
    }

    public function redirectIf(
        mixed $condition,
        array|string $url,
        mixed $statusCode = 302,
        mixed ...$arguments,
    ): void {
        if ($condition) {
            $this->redirect($url, $statusCode, ...$arguments);
        }
    }

    public function redirectUnless(
        mixed $condition,
        array|string $url,
        mixed $statusCode = 302,
        mixed ...$arguments,
    ): void {
        if (!$condition) {
            $this->redirect($url, $statusCode, ...$arguments);
        }
    }

    public function renderText(mixed $text): string
    {
        $content = $this->getResponse()->getContent();
        $this->getResponse()->setContent(
            (false === $content ? '' : $content).(string) $text,
        );

        return View::NONE;
    }

    public function getSecurityConfiguration(): array
    {
        return $this->security;
    }

    public function setSecurityConfiguration(array $security): void
    {
        $this->security = $security;
    }

    public function getSecurityValue(
        string $name,
        mixed $default = null,
    ): mixed {
        $action = strtolower($this->getActionName());

        return $this->security[$action][$name]
            ?? $this->security['all'][$name]
            ?? $this->security['default'][$name]
            ?? $default;
    }

    public function isSecure(): bool
    {
        return (bool) $this->getSecurityValue('is_secure', false);
    }

    public function getCredential(): mixed
    {
        return $this->getSecurityValue('credentials');
    }

    public function setTemplate(
        string $template,
        ?string $module = null,
    ): void {
        $this->template = $template;
        $this->templateModule = $module;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getTemplateModule(): ?string
    {
        return $this->templateModule;
    }

    public function setLayout(false|string $layout): void
    {
        $this->layout = false === $layout ? null : $layout;
        $this->layoutSet = true;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function hasLayoutOverride(): bool
    {
        return $this->layoutSet;
    }

    public function setViewClass(string $viewClass): void
    {
        $this->viewClass = $viewClass;
    }

    public function getViewClass(): ?string
    {
        return $this->viewClass;
    }

    public function getRoute(): mixed
    {
        return $this->request->getAttribute('sf_route');
    }

    protected function get404Message(?string $message): string
    {
        return $message ?? sprintf(
            'The requested action "%s/%s" does not exist.',
            $this->moduleName,
            $this->actionName,
        );
    }
}
