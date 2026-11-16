@extends('layouts.app')
@section('title', 'Categories')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Inventory</p>
            <h1 class="fw-bold mb-0">Categories</h1>
        </div>
        <a class="btn btn-primary btn-sm" href="{{ route('categories.create') }}">Add Category</a>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table inventory-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td><span class="badge bg-secondary">{{ $category->products_count }}</span></td>
                            <td>{{ $category->description ?? '—' }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary"
                                    href="{{ route('categories.edit', $category) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('categories.destroy', $category) }}"
                                    onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection