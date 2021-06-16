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
}
