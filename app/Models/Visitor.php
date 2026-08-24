<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = ['kategori', 'nama', 'nip', 'instansi_unit', 'no_hp', 'keperluan'];
}
