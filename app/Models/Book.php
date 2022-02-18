<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Book extends Model {
    protected $connection = 'mongodb';
    protected $collection = 'books';
}