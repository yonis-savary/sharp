<?php

namespace YonisSavary\Sharp\Classes\Data;

class ModelQueryIterator
{
    protected int $batchSize;
    protected int $count;
    protected int $index;
    protected ModelQuery $query;

    public static function forEach(ModelQuery $query, callable $function, int $batchSize=500): void
    {
        $iterator = new self($query, $batchSize);

        while ($batch = $iterator->nextBatch())
        {
            foreach ($batch as $data)
                $function($data, $iterator->getLastIndex(), $iterator->getCount());
        }
    }

    public function __construct(ModelQuery $query, int $batchSize=500)
    {
        $this->batchSize = $batchSize;

        $sql = $query->build();
        $this->query = $query;
        $this->count = Database::getInstance()->query("SELECT COUNT(*) as c FROM ($sql) as _ti")[0]['c'] ?? 0;
        $this->index = 0;
    }

    public function getLastIndex(): int
    {
        $index = $this->index;
        return $index == 0 ? 0 : $index-1;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    protected function nextBatch(): array|false
    {
        if ($this->index >= $this->count)
            return false;

        $array = $this->query
            ->limit($this->batchSize, $this->index)
            ->fetch() ?? false;

        $this->index += $this->batchSize;
        return $array;
    }
}