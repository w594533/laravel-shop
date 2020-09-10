<?php
namespace App\Services;

use Auth;
use App\Models\Category;

class CategoryService
{
    public function getCategoriesTree($parentId=null, $allCategories=null) 
    {
        if (is_null($allCategories)) {
            $allCategories = Category::all();
        }

        $result = $allCategories
                    ->where('parent_id', $parentId)
                    ->map(function (Category $category) use ($allCategories) {
                        $data = ['id' => $category->id, 'name' => $category->name];
                        if (!$category->is_directory) {
                            return $data;
                        }

                        $data['children'] = $this->getCategoriesTree($category->id, $allCategories);
                        return $data;
                    });
        return $result;
    }
}
