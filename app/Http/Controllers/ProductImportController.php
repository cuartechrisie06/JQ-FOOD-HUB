<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductImportRequest;
use App\Imports\ProductsImport;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductImportController extends Controller
{
    public function import(ProductImportRequest $request): RedirectResponse
    {
        $import = new ProductsImport;
        Excel::import($import, $request->file('csv_file'));

        return redirect()->route('admin.products.index')
            ->with('import_report', $import->report);
    }

    public function template(): BinaryFileResponse
    {
        $path = public_path('templates/JQ_Initial_Products_Import_Template.csv');

        return response()->download(
            $path,
            'JQ_Initial_Products_Import_Template.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
