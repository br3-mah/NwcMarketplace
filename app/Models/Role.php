<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name','section'];

    public $timestamps = false;

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')->withTimestamps();
    }

    public function sectionCheck($value)
    {
        $sections = explode(" , ", $this->section);
        if (in_array($value, $sections)){
            return true;
        }else{
            return false;
        }
        
    }

}
