<?php

namespace Bego\Query;

/**
 * key condition expression
 */
class Expression
{
    protected $_names = [];

    protected $_statements = [];

    protected $_values = [];

    public function __construct($items = [])
    {
        foreach ($items as $item) {
            $this->add(
                $item['name'], $item['operator'], $item['value']
            );
        }
    }

    public function isDirty()
    {
        return count($this->_values) > 0;
    }

    public function add($name, $operator, $value)
    {
        $key = '#' . $name;
        $placeholder = ':' . $name;

        if (array_key_exists($placeholder, $this->_values)) {
            throw new \Exception("{$name} cannot be used twice");
        }

        $this->_names[$key] = $name;

        if ($this->_isFunction($operator)) {
            $statement = "{$operator}({$key}, {$placeholder})";
        } else {
            $statement = "{$key} {$operator} {$placeholder}";
        }

        array_push($this->_statements, $statement);

        $this->_values[$placeholder] = $value;

        return $this;
    }

    protected function _isFunction($value)
    {
        $methods = ['begins_with'];

        return in_array($value, $methods);
    }

    public function values()
    {
        return $this->_values;
    }

    public function names()
    {
        return $this->_names;
    }

    public function statement()
    {
        return implode(' and ', $this->_statements);
    }
}