<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'is_directory', 'level', 'path'];

    protected $casts = [
        'is_directory' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        //监听创建事件，自动录入level和path
        static::creating(function(Category $category){
            if (is_null($category->parent_id)) {
                //没有父级
                $category->level = 0;
                $category->path = '_';
            } else {
                // 将层级设为父类目的层级 + 1
                $category->level = $category->parent->level + 1;
                // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
                $category->path = $category->parent->path.$category->parent->id.'-';
            }
        });
    }

    public function childrens()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class);
    }

    //获取所有父级类目的Id
    public function getPathIdsAttribute()
    {
        // trim($str, '-') 将字符串两端的 - 符号去除
        // explode() 将字符串以 - 为分隔切割为数组
        // 最后 array_filter 将数组中的空值移除
        return array_filter(explode('-', trim($this->path, '-')));
    }

    //获取所有的父类项目
    public function getAncestorsAttribute()
    {
        return Category::query()
                    ->whereIn('id', $this->path_ids)
                    ->orderBy('level')
                    ->get();
    }

    //获取以 - 为分隔的所有祖先类目名称以及当前类目的名称
    public function getFullNameAttribute()
    {
        return $this->ancestors
                ->pluck('name')
                ->push($this->name)
                ->implode('-');
    }
}
