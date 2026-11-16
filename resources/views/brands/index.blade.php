@extends('layouts.app')
@section('title', 'Brands')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Inventory</p>
            <h1 class="fw-bold mb-0">Brands</h1>
        </div>
        <a class="btn btn-primary btn-sm" href="{{ route('brands.create') }}">Add Brand</a>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table inventory-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Brand Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td><strong>{{ $brand->name }}</strong></td>
                            <td>{{ $brand->description ?? '—' }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('brands.edit', $brand) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('brands.destroy', $brand) }}"
                                    onsubmit="return confirm('Delete this brand?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No brands found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $brands->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection