<?php

namespace App\Jobs;

use App\Imports\ProductImport;
use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $category;
    protected $fileName;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($category, $fileName)
    {
        $this->category = $category;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $file = (new ProductImport)->toArray(storage_path('app/public/uploads/' . $this->fileName));

        foreach ($file[0] as $key => $row) {
            $explodedUrl = explode('/', $row[4]);
            $explodedExtension = explode('.', end($explodedUrl));
            $fileName = time() . Str::random(6) . '.' . end($explodedExtension);

            file_put_contents(storage_path('app/public/products') . '/' . $fileName, file_get_contents($row[4]));

            Product::create([
                'name' => $row[0],
                'slug' => $row[0],
                'category_id' => $this->category,
                'description' => $row[1],
                'price' => $row[2],
                'weight' => $row[3],
                'image' => $fileName,
                'status' => true,
            ]);
        }

        File::delete(storage_path('app/public/products/' . $this->fileName));
    }
}
