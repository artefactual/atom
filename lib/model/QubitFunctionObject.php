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

class QubitFunctionObject extends BaseFunctionObject
{
    public function __toString()
    {
        $string = $this->authorizedFormOfName;
        if (!isset($string)) {
            $string = $this->getAuthorizedFormOfName(['sourceCulture' => true]);
        }

        return (string) $string;
    }

    public function __get($name)
    {
        $args = func_get_args();

        $options = [];
        if (1 < count($args)) {
            $options = $args[1];
        }

        switch ($name) {
            case 'language':
            case 'script':
                if (!isset($this->values[$name])) {
                    $criteria = new Criteria();
                    $this->addPropertysCriteria($criteria);
                    $criteria->add(QubitProperty::NAME, $name);

                    if (1 == count($query = QubitProperty::get($criteria))) {
                        $this->values[$name] = $query[0];
                    }
                }

                if (isset($this->values[$name]) && null !== $value = unserialize($this->values[$name]->__get('value', $options + ['sourceCulture' => true]))) {
                    return $value;
                }

                return [];
        }

        return call_user_func_array([$this, 'BaseFunctionObject::__get'], $args);
    }

    public function __set($name, $value)
    {
        $args = func_get_args();

        $options = [];
        if (2 < count($args)) {
            $options = $args[2];
        }

        switch ($name) {
            case 'language':
            case 'script':
                if (!isset($this->values[$name])) {
                    $criteria = new Criteria();
                    $this->addPropertysCriteria($criteria);
                    $criteria->add(QubitProperty::NAME, $name);

                    if (1 == count($query = QubitProperty::get($criteria))) {
                        $this->values[$name] = $query[0];
                    } else {
                        $this->values[$name] = new QubitProperty();
                        $this->values[$name]->name = $name;
                        $this->propertys[] = $this->values[$name];
                    }
                }

                $this->values[$name]->__set('value', serialize($value), $options + ['sourceCulture' => true]);

                return $this;
        }

        return call_user_func_array([$this, 'BaseFunctionObject::__set'], $args);
    }

    public function save($connection = null)
    {
        parent::save($connection);

        QubitSearch::getInstance()->update($this);
    }

    public function getLabel()
    {
        $label = null;
        if (null !== $this->descriptionIdentifier) {
            $label .= $this->descriptionIdentifier;
        }
        if (null !== $value = $this->getAuthorizedFormOfName(['cultureFallback' => true])) {
            $label = (0 < strlen($label)) ? $label.' - '.$value : $value;
        }

        return $label;
    }

    protected function insert($connection = null)
    {
        if (!isset($this->slug)) {
            $this->slug = QubitSlug::slugify($this->__get('authorizedFormOfName', ['sourceCulture' => true]));
        }

        return parent::insert($connection);
    }
}
