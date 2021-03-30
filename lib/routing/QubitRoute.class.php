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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitRoute extends sfRoute
{
    /**
     * Used to correctly represent the characters * ; : @ = , in the slug part
     * of a URI - this is the path segment.
     * These chars are allowed in slugs when 'permissive slugs' setting is on.
     * If 'permissive slugs' is OFF, use urlencode as normal.  This should ONLY
     * affect the slug portion of an AtoM URI.
     *
     * @param mixed $string
     */
    public static function urlencode3986($string)
    {
        if (QubitSlug::SLUG_PERMISSIVE == sfConfig::get('app_permissive_slug_creation', QubitSlug::SLUG_RESTRICTIVE)) {
            $entities = ['%2A', '%3A', '%40', '%3D', '%2C'];
            $replacements = ['*', ':', '@', '=', ','];

            return str_replace($entities, $replacements, urlencode($string));
        }

        return urlencode($string);
    }

    /**
     * @see sfRoute
     *
     * @param mixed $params
     * @param mixed $context
     */
    public function matchesParameters($params, $context = [])
    {
        return parent::matchesParameters($this->filterParams($params), $context);
    }

    /**
     * @see sfRoute
     *
     * Overridden from sfRoute to call customized generateWithTokens() below.
     * (AtoM ticket 11761).
     *
     * @param mixed $params
     * @param mixed $context
     * @param mixed $absolute
     */
    public function generate($params, $context = [], $absolute = false)
    {
        $params = $this->filterParams($params);

        if (!$this->compiled) {
            $this->compile();
        }

        $url = $this->pattern;

        $defaults = $this->mergeArrays($this->getDefaultParameters(), $this->defaults);
        $tparams = $this->mergeArrays($defaults, $params);

        // All params must be given.
        if ($diff = array_diff_key($this->variables, $tparams)) {
            throw new InvalidArgumentException(sprintf('The "%s" route has some missing mandatory parameters (%s).', $this->pattern, implode(', ', $diff)));
        }

        if ($this->options['generate_shortest_url'] || $this->customToken) {
            $url = $this->generateWithTokens($tparams);
        } else {
            $variables = $this->variables;
            uasort($variables, ['sfRoute', 'generateCompareVarsByStrlen']);
            foreach ($variables as $variable => $value) {
                $url = str_replace($value, urlencode($tparams[$variable]), $url);
            }

            if (!in_array($this->suffix, $this->options['segment_separators'])) {
                $url .= $this->suffix;
            }
        }

        $url = $this->generateStarParameter($url, $defaults, $params);

        if ($this->options['extra_parameters_as_query_string'] && !$this->hasStarParameter()) {
            if ($extra = array_diff_assoc(array_diff_key($params, $this->variables), $this->defaults)) {
                $url .= '?'.http_build_query($extra);
            }
        }

        return $url;
    }

    protected function filterParams($params)
    {
        // Fill in missing parameters with attributes of $params[0]
        if (!is_array($params)) {
            $params = [$params];
        }

        if (isset($params[0])) {
            if ($params[0] instanceof sfOutputEscaper) {
                $params[0] = sfOutputEscaper::unescape($params[0]);
            }

            foreach (array_diff_key($this->params + $this->variables, $params) as $key => $ignore) {
                try {
                    $params[$key] = $params[0][$key];
                } catch (sfException $e) {
                }
            }

            unset($params[0]);
        }

        return $params;
    }

    /**
     * Generates a URL for the given parameters by using the route tokens.
     *
     * Overridden from sfRoute to allow customization to call urlencode3986()
     * (AtoM ticket 11761).
     *
     * @param array $parameters An array of parameters
     */
    protected function generateWithTokens($parameters)
    {
        $url = [];
        $optional = $this->options['generate_shortest_url'];
        $first = true;
        $tokens = array_reverse($this->tokens);
        foreach ($tokens as $token) {
            switch ($token[0]) {
                case 'variable':
                    if (!$optional || !isset($this->defaults[$token[3]]) || $parameters[$token[3]] != $this->defaults[$token[3]]) {
                        $url[] = QubitRoute::urlencode3986($parameters[$token[3]]);
                        $optional = false;
                    }

                    break;

                case 'text':
                    $url[] = $token[2];
                    $optional = false;

                    break;

                case 'separator':
                    if (false === $optional || $first) {
                        $url[] = $token[2];
                    }

                    break;

                default:
                    // Handle custom tokens.
                    if ($segment = call_user_func_array([$this, 'generateFor'.ucfirst(array_shift($token))], array_merge([$optional, $parameters], $token))) {
                        $url[] = $segment;
                        $optional = false;
                    }

                    break;
            }

            $first = false;
        }

        $url = implode('', array_reverse($url));
        if (!$url) {
            $url = '/';
        }

        return $url;
    }
}
