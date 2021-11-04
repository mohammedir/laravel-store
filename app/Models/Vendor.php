<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Vendor extends Model
{
    protected $table = 'vendors';

    protected $fillable = [
        'name', 'mobile', 'address', 'email', 'logo', 'category_id', 'active', 'created_at', 'updated_at','password'
    ];

    protected $hidden = ['category_id'];

    public function scopeActive($query){
        return $query-> where('active',1);
    }

    public function getLogoAttribute($val){

        return ($val !== null)? asset('public/assets'.$val) : "";
    }

    public function scopeSelection($query){

        return $query->select('id','category_id','name','logo','mobile');
    }

    public function category()
    {

        return $this->belongsTo('App\Models\MainCategory', 'category_id', 'id');
    }

    public function getActive()
    {
        return $this->active == 1 ? 'مفعل' : 'غير مفعل';

    }


    public function setPasswordAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['password'] = bcrypt($password);
        }
    }

}
