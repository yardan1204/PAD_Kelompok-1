<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseCampusEntity extends Model
{
    abstract public function getEntitySummary(): string;
}