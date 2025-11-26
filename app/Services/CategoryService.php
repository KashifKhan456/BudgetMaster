<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    public function getCategories(int $userId, array $filters = [])
    {
        $query = Category::where('user_id', $userId);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate(10)->withQueryString();
    }

    public function createCategory(array $data): Category
    {
        return Category::create(array_merge($data, ['user_id' => Auth::id()]));
    }

    public function updateCategory(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }
}
