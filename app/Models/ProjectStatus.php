<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatus extends Model
{
    protected $fillable = ['project_id', 'name', 'slug', 'position', 'is_default'];
}

