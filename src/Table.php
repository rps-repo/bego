<?php

namespace Bego;

class Table
{
    protected $_db;

    protected $_model;

    protected $_consumption = false;

    public function __construct(Database $db, Model $model, $consumption = false)
    {
        $this->_db = $db;
        $this->_model = $model;
        $this->_consumption = $consumption;
    }

    public function fetch($partition, $sort = null)
    {
        $result = $this->_db->client()->getItem([
            'TableName' => $this->_model->name(),
            'Key'       => $this->_getKey($this->_model, $partition, $sort)
        ]);

        return new Item(
            $this->_db->marshaler()->unmarshalItem($result['Item'])
        );
    }

    protected function _getKey($model, $partition, $sort = null)
    {
        $keys[$model->partition()] = $this->_db->marshaler()->marshalValue($partition);

        if ($sort) {
            $keys[$model->sort()] = $this->_db->marshaler()->marshalValue($sort);
        }

        return $keys;
    }

    public function put($attributes)
    {
        $this->_db->client()->putItem([
            'TableName' => $this->_model->name(),
            'Item'      => $this->_db->marshaler()->marshalItem($attributes)
        ]);

        return new Item($attributes);
    }

    public function update($item)
    {
        $expression = new Update\Expression($item->diff());

        /* If nothing changed, do nothing */
        if (!$expression->isDirty()) {
            return false;
        }

        /* Generate a key from the table model */
        $key = $this->_getKey(
            $this->_model, $item->get($this->_model->partition()), $item->get($this->_model->sort())
        );

        /* Marshal the expression values */
        $values = $this->_db->marshaler()->marshalJson(
            json_encode($expression->values())
        );

        $this->_db->client()->updateItem([
            'TableName'                 => $this->_model->name(),
            'Key'                       => $key,
            'ExpressionAttributeNames'  => $expression->names(),
            'ExpressionAttributeValues' => $values,
            'UpdateExpression'          => $expression->statement(),
        ]);

        /* Mark item is clean */
        $item->clean();
    }

    public function query($index = null)
    {
        $query = new Query\Statement($this->_db);

        $query->table($this->_model->name());

        $query->consumption($this->_consumption);
            
        if ($index) {
            $spec = $this->_model->index($index);
            $query->index($index);
        }

        return $query->partition(
            isset($spec['key']) ? $spec['key'] : $this->_model->partition()
        );
    }
}