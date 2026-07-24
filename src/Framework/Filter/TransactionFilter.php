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

namespace Atom\Framework\Filter;

use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\StopException;
use Symfony\Component\HttpFoundation\Response;

final readonly class TransactionFilter implements FilterHandler
{
    private const RETRY_LIMIT = 3;
    private const DEADLOCK_ERROR = 1213;

    public function process(Context $context, callable $next): Response
    {
        for ($attempt = 0;; ++$attempt) {
            try {
                $connection = \Propel::getConnection();
                $connection->beginTransaction();
            } catch (\PropelException) {
                return $next();
            }

            try {
                $response = $next();
                $connection->commit();

                return $response;
            } catch (StopException $exception) {
                $connection->commit();

                throw $exception;
            } catch (\PDOException $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }

                if (
                    self::DEADLOCK_ERROR
                        === ($exception->errorInfo[1] ?? null)
                    && $attempt < self::RETRY_LIMIT
                ) {
                    $context->getLogger()->warning(sprintf(
                        'SQL deadlock, retry %d of %d.',
                        $attempt + 1,
                        self::RETRY_LIMIT,
                    ));

                    continue;
                }

                throw $exception;
            } catch (\Throwable $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }

                throw $exception;
            }
        }
    }
}
