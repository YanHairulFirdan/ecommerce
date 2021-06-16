<?php

namespace App\Http\Controllers;

use App\Category;
use App\Jobs\ProductJob;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
// use File;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $product = Product::with(['category'])->orderBy('created_at', 'DESC');

        if (request()->q != '') {
            $product = $product->where('name', 'LIKE', '%' . request()->q . '%');
        }

        $product = $product->paginate(10);

        return view('products.index', compact('product'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = Category::orderBy('name', 'DESC')->get();

        return view('products.create', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer',
            'weight' => 'required|integer',
            'image' => 'required|image|mimes:png,jpg,jpeg',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName =  time() . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();

            $file->storeAs('public/products', $fileName);

            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'image' => $fileName,
                'price' => $request->price,
                'weight' => $request->weight,
                'status' => $request->status,
            ]);
        }

        return redirect(route('product.index'))->with(['success' => 'Produk telah ditambahkan']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $category = Category::orderBy('name', 'DESC')->get();

        return view('products.edit', compact('product', 'category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|integer',
            'weight' => 'required|integer',
            'image' => 'nullable|image|mimes:png,jpeg,jpg',
        ]);

        $product = Product::findOrFail($id);
        $fileName = $product->image;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs(storage_path('public/products'), $fileName);

            FIle::delete(storage_path('app/public/products/' . $product->image));
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'weight' => $request->weight,
            'image' => $fileName
        ]);

        return redirect(route('product.index'))->with(['success' => 'Data produk diperbaharui']);
    }

    public function massUploadForm()
    {
        $category = Category::orderBy('name', 'DESC')->get();

        return view('products.bulk', compact('category'));
    }

    public function massUpload(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'file' => 'required|mimes:xlsx'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '-product.' . $file->getClientOriginalExtension();
            // dd($fileName);
            $file->storeAs('public/uploads', $fileName);

            ProductJob::dispatch($request->category_id, $fileName);

            return redirect(route('product.index'))->with(['success' => 'Upload produk dijadwalkan']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        File::delete(storage_path('/app/public/products/' . $product->image));
        $product->delete();
    }
}
