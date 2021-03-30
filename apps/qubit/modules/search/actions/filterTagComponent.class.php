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

class SearchFilterTagComponent extends sfComponent
{
    public function execute($request)
    {
        $this->getParams = $request->getGetParameters();

        // If filter param isn't set in the request, or filter is model-based yet no object
        // has been stored, display nothing
        if (!$this->checkIfParamsSet() || (!empty($this->options['model']) && empty($this->options['object']))) {
            return sfView::NONE;
        }

        // If filter has neither a label nor an object, display nothing
        if (empty($this->options['label']) && empty($this->options['object'])) {
            return sfView::NONE;
        }

        // Remove selected parameter from the current GET parameters
        $this->unsetParams();

        // Expose label and/or object values to filter template
        $this->label = !empty($this->options['label']) ? $this->options['label'] : null;
        $this->object = !empty($this->options['object']) ? $this->options['object'] : null;

        // Default module and action to the current module/action
        $this->module = !empty($this->options['module']) ? $this->options['module'] : $this->context->getModuleName();
        $this->action = !empty($this->options['action']) ? $this->options['action'] : $this->context->getActionName();
    }

    private function checkIfParamsSet()
    {
        if (!empty($this->options['params'])) {
            // Count how many params are set
            $setCount = 0;
            foreach ($this->options['params'] as $param) {
                $setCount += !empty($this->getParams[$param]);
            }

            // Check using params specified in filter tag configuration
            if (empty($this->options['operator']) || 'and' == strtolower($this->options['operator'])) {
                // All specified params must be set for filter tag to show
                return count($this->options['params']) == $setCount;
            }

            // Any specified param can be set for filter tag to show
            return $setCount > 0;
        }

        // Check using param based on filter tag name
        return isset($this->getParams[$this->name]);
    }

    private function unsetParams()
    {
        if (!empty($this->options['params'])) {
            foreach ($this->options['params'] as $param) {
                unset($this->getParams[$param]);
            }
        } else {
            unset($this->getParams[$this->name]);
        }
    }
}
