<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);
        $categories = Category::withCount('products')->orderBy('name')->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        $this->authorize('view', $category);
        $products = $category->products()->orderBy('name')->paginate(15);
        return view('categories.show', compact('category', 'products'));
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        Category::create($this->validateRecord($request));
        return redirect()->route('categories.index')->with('success', 'Category added.');
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $category->update($this->validateRecord($request, $category));
        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category archived.');
    }

    private function validateRecord(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category)],
            'description' => ['nullable', 'string'],
        ]);
    }
}