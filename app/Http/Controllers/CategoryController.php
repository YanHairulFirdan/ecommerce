<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::with(['parent'])->orderBy('created_at', 'DESC')->paginate(10);

        $parent = Category::getParent()->orderBy('name')->get();

        return view('categories.index', compact('category', 'parent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories'
        ]);

        $request->request->add(['slug' => $request->name]);

        Category::create($request->except('_token'));

        return redirect(route('category.index'))->with(['success' => 'Kategori baru diambahkan']);
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $parent   = Category::getParent()->orderBy('name', 'ASC')->get();

        return view('categories.edit', compact('category', 'parent'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50'
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect(route('category.index'))->with(['success' => 'Kategori dioerbaharui']);
    }

    public function destroy($id)
    {
        $category = Category::withCount(['child', 'product'])->findOrFail($id);

        if (!$category->child_count && !$category->product_count) {
            $category->delete();

            return redirect(route('category.index'))->with(['success' => 'Kategori dihapus']);
        }

        return redirect(route('category.index'))->with(['error' => 'Kategori ini memiliki anak kategory']);
    }
}
