<?php
namespace App\Exports;

use App\Models\Product;
use PDF; // barryvdh/laravel-dompdf facade

class ProductsPdfExport
{
    public function download()
    {
        $products = Product::all();
        $pdf = PDF::loadView('exports.products', compact('products'));
        return $pdf->download('products.pdf');
    }
}
