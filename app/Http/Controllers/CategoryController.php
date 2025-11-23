<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Categories/Index', [
            'categories' => $this->categoryService->getCategories(Auth::id(), $request->only(['search', 'type'])),
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->categoryService->updateCategory($category, $request->validated());

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $this->categoryService->deleteCategory($category);

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
