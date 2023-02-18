<?php

namespace Megaads\DealsPage\Repositories;

abstract class BaseRepository
{
    public function create($item) {}
    public function read($filters) {}
    public function update($id, $params) {}
    public function delete($id) {}
}