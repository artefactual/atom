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

class QubitQuery implements ArrayAccess, Countable, Iterator
{
    protected $parent;
    protected $criteria;
    protected $className;
    protected $options;
    protected $statement;
    protected $objects;
    protected $count;
    protected $offset = 0;
    protected $orderByName;
    protected $andSelf;
    protected $indexByName;
    protected $orderByNames;

    public function __isset($name)
    {
        list($objects, $sorted) = $this->getData($this);

        return array_key_exists($name, $this->objects);
    }

    public function __get($name)
    {
        if ('transient' === $name) {
            if (!isset($this->objects)) {
                return [];
            }

            return $this->objects;
        }

        list($objects, $sorted) = $this->getData($this);

        if (isset($this->objects[$name])) {
            return $this->objects[$name];
        }
    }

    public function __set($name, $value)
    {
        if (null === $name) {
            $this->objects[] = $value;
        }

        if (isset($this->indexByName)) {
            $value[$this->indexByName] = $name;
            $this->objects[$name] = $value;

            // HACK
            if (isset($this->parent)) {
                $this->parent[] = $value;
            }
        }

        return $this;
    }

    public static function create(array $options = [])
    {
        $query = new QubitQuery();
        $query->options = $options;

        return $query;
    }

    public static function createFromCriteria(Criteria $criteria, $className, array $options = [])
    {
        $query = new QubitQuery();
        $query->criteria = $criteria;
        $query->className = $className;
        $query->options = $options;

        return $query;
    }

    public function offsetExists($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__isset'], $args);
    }

    public function offsetGet($offset)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__get'], $args);
    }

    public function offsetSet($offset, $value)
    {
        $args = func_get_args();

        return call_user_func_array([$this, '__set'], $args);
    }

    public function offsetUnset($offset) {}

    public function count()
    {
        return $this->getCount($this);
    }

    public function current()
    {
        list($objects, $sorted) = $this->getData($this);

        return current($this->objects);
    }

    public function key()
    {
        list($objects, $sorted) = $this->getData($this);

        return key($this->objects);
    }

    public function next()
    {
        ++$this->offset;

        list($objects, $sorted) = $this->getData($this);

        return next($this->objects);
    }

    public function rewind()
    {
        $this->offset = 0;

        list($objects, $sorted) = $this->getData($this);

        return reset($this->objects);
    }

    public function valid()
    {
        list($objects, $sorted) = $this->getData($this);

        return $this->offset < count($this->objects);
    }

    public function orderBy($name)
    {
        $query = new QubitQuery();
        $query->parent = $this;
        $query->orderByName = $name;

        return $query;
    }

    public function andSelf()
    {
        $query = new QubitQuery();
        $query->parent = $this;

        // Set andSelf and remove 'self' option
        $query->options = $this->getOptions();
        $query->andSelf = $query->options['self'];
        unset($query->options['self']);

        return $query;
    }

    public function indexBy($name)
    {
        $query = new QubitQuery();
        $query->parent = $this;
        $query->indexByName = $name;

        return $query;
    }

    // Not recursive: Only ever called from the root.
    protected function getStatement(QubitQuery $leaf)
    {
        // HACK Tell the caller whether we sorted according to the leaf
        $sorted = false;

        if (!isset($this->statement)) {
            foreach ($leaf->getOrderByNames() as $name) {
                $this->criteria->addAscendingOrderByColumn(constant($this->className.'::'.strtoupper($name)));
            }
            $sorted = true;

            $this->statement = BasePeer::doSelect($this->criteria);
        }

        // TODO Determine whether the sort order matches the previous sort order
        return [$this->statement, $sorted];
    }

    protected function getData(QubitQuery $leaf)
    {
        // HACK Tell the caller whether we sorted according to the leaf
        $sorted = false;

        if (!isset($this->objects)) {
            if (isset($this->parent)) {
                list($this->objects, $sorted) = $this->parent->getData($leaf);

                // Possibly re-index
                if (isset($this->indexByName)) {
                    $objects = [];
                    foreach ($this->objects as $object) {
                        $objects[$object[$this->indexByName]] = $object;
                    }

                    $this->objects = $objects;
                }
            } else {
                $this->objects = [];
                $sorted = true;

                if (isset($this->criteria)) {
                    list($this->statement, $sorted) = $this->getStatement($leaf);

                    while ($row = $this->statement->fetch()) {
                        if (isset($this->options['rows']) && $this->options['rows']) {
                            $object = $row;
                        } else {
                            // $this->parent is unset, so we should have a className?
                            $object = call_user_func([$this->className, 'getFromRow'], $row);
                        }

                        // TODO $this->parent is unset, so we probably do not have
                        // $this->indexByName, but it would be nice to use the indexByName
                        // of the leaf
                        if (isset($this->indexByName)) {
                            $this->objects[call_user_func([$object, 'get'.$this->indexByName])] = $object;
                        } else {
                            $this->objects[] = $object;
                        }
                    }
                }
            }

            // Possibly add self
            if (isset($this->andSelf)) {
                if (count($this->objects) > 0) {
                    $sorted = false;
                }

                if (isset($this->indexByName)) {
                    $this->objects[call_user_func([$this->andSelf, 'get'.$this->indexByName])] = $this->andSelf;
                } else {
                    $this->objects[] = $this->andSelf;
                }
            }

            // If we added to the array of objects, or we should sort and have not
            // yet sorted, then sort according to the leaf.  Since the leaf is a
            // refinement, we will be sorted at least according to our orderByName.
            // Indicate that we sorted according to the leaf, to save further
            // sorting by descendants.
            if (isset($this->orderByName) && !$sorted) {
                if (isset($this->indexByName)) {
                    $sorted = uasort($this->objects, [$leaf, 'sortCallback']);
                } else {
                    $sorted = usort($this->objects, [$leaf, 'sortCallback']);
                }
            }
        }

        return [$this->objects, $sorted];
    }

    protected function getCount(QubitQuery $leaf)
    {
        if (!isset($this->objects)) {
            $count = 0;

            if (isset($this->parent)) {
                $count = $this->parent->getCount($leaf);
            } elseif (isset($this->count)) {
                $count = $this->count;
            } elseif (isset($this->criteria)) {
                $countCriteria = clone $this->criteria;
                $this->count = intval(BasePeer::doCount($countCriteria)->fetchColumn(0));

                $count = $this->count;
            }

            if (isset($this->andSelf)) {
                ++$count;
            }

            return $count;
        }

        return count($this->objects);
    }

    protected function getOrderByNames()
    {
        if (!isset($this->orderByNames)) {
            if (isset($this->parent)) {
                $this->orderByNames = $this->parent->getOrderByNames();
            } else {
                $this->orderByNames = [];
            }

            if (isset($this->orderByName)) {
                $this->orderByNames[] = $this->orderByName;
            }
        }

        return $this->orderByNames;
    }

    protected function sortCallback($a, $b)
    {
        foreach ($this->getOrderByNames() as $name) {
            $aGet = call_user_func([$a, 'get'.$name]);
            $bGet = call_user_func([$b, 'get'.$name]);

            if ($aGet < $bGet) {
                return -1;
            }

            if ($aGet > $bGet) {
                return 1;
            }
        }
    }

    protected function getOptions()
    {
        if (!isset($this->options)) {
            if (isset($this->parent)) {
                $this->options = $this->parent->getOptions();
            } else {
                $this->options = [];
            }
        }

        return $this->options;
    }
}
