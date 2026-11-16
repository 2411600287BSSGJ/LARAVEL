@extends('layouts.app')
@section('title', 'Categories')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="eyebrow mb-1">Catalog</p><h1 class="fw-bold mb-0">Equipment Types / Categories</h1></div><div class="d-flex gap-2"><a class="btn btn-outline-success" href="{{ route('exports.download', 'categories') }}">Export CSV</a>@can('create', \App\Models\Category::class)<a class="btn btn-primary" href="{{ route('categories.create') }}">Add Category</a>@endcan</div></div>
    <div class="panel table-responsive"><table class="table inventory-table align-middle mb-0"><thead><tr><th>Category</th><th>Description</th><th>Products</th>@can('create', \App\Models\Category::class)<th></th>@endcan</tr></thead><tbody>
        @forelse($categories as $category)<tr><td><strong>{{ $category->name }}</strong></td><td>{{ $category->description ?: '—' }}</td><td>{{ $category->products_count }}</td>@can('create', \App\Models\Category::class)<td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('categories.edit', $category) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Archive this category?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Archive</button></form></td>@endcan</tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>@endforelse
    </tbody></table><div class="mt-3">{{ $categories->links() }}</div></div>
@endsection
