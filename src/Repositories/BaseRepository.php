<?php

namespace Megaads\DealsPage\Repositories;

abstract class BaseRepository
{
    protected $pageId;
    protected $pageSize;
    public function create($item) {}
    public function read($filters) {}
    public function update($id, $params) {}
    public function delete($id) {}
}