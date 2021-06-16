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
}
