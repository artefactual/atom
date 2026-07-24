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

namespace Atom\Framework\Configuration;

final readonly class ApplicationConfiguration
{
    public function __construct(
        private ConfigurationPathResolver $pathResolver,
        private HybridYamlFileLoader $fileLoader,
        private ConfigurationMerger $merger,
        private ConstantReplacer $constantReplacer,
        private ParameterCompiler $parameterCompiler,
        private string $environment,
        private array $constants = [],
    ) {}

    public function paths(string $configPath): array
    {
        return $this->pathResolver->resolve($configPath);
    }

    public function load(string $configPath): array
    {
        $configurations = array_map(
            $this->fileLoader->load(...),
            $this->paths($configPath),
        );

        $configuration = $this->merger->mergeConfigurations($configurations);
        $configuration = $this->merger->forEnvironment(
            $configuration,
            $this->environment,
        );

        return $this->constantReplacer->replace(
            $configuration,
            $this->constants,
        );
    }

    public function parameters(string $configPath, string $prefix = ''): array
    {
        return $this->parameterCompiler->compile(
            $this->load($configPath),
            $prefix,
        );
    }
}
