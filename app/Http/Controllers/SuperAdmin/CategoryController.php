<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['subcategories', 'tickets'])->orderBy('name')->get();

        return view('admin.superadmin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.superadmin.categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.superadmin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request));

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->tickets()->exists()) {
            return back()->withErrors(['category' => 'Kategori tidak dapat dihapus karena masih memiliki tiket.']);
        }

        $category->delete();

        return back()->with('status', 'Kategori dihapus.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'routing_role' => ['required', Rule::in(['admin_bidang', 'super_admin'])],
            'routing_bidang' => ['nullable', 'string', 'max:60', 'required_if:routing_role,admin_bidang'],
            'notify_email' => ['required', 'email', 'max:150'],
            'lms_category_ref' => ['nullable', 'string', 'max:100'],
        ]);

        // Bila ke super admin, kosongkan kode bidang.
        if ($data['routing_role'] === 'super_admin') {
            $data['routing_bidang'] = null;
        }
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
