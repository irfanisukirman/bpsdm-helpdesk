<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subcategories')->orderBy('name')->get();

        return view('admin.superadmin.subcategories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        Subcategory::create($data);

        return back()->with('status', 'Subkategori ditambahkan.');
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $subcategory->update($data);

        return back()->with('status', 'Subkategori diperbarui.');
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return back()->with('status', 'Subkategori dihapus.');
    }
}
