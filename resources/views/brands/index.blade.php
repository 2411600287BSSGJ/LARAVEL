@extends('layouts.app')
@section('title', 'Brands')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="eyebrow mb-1">Catalog</p><h1 class="fw-bold mb-0">Manufacturers / Brands</h1></div><div class="d-flex gap-2"><a class="btn btn-outline-success" href="{{ route('exports.download', 'brands') }}">Export CSV</a>@can('create', \App\Models\Brand::class)<a class="btn btn-primary" href="{{ route('brands.create') }}">Add Brand</a>@endcan</div></div>
    <div class="panel table-responsive"><table class="table inventory-table align-middle mb-0"><thead><tr><th>Manufacturer</th><th>Description</th><th>Products</th>@can('create', \App\Models\Brand::class)<th></th>@endcan</tr></thead><tbody>
        @forelse($brands as $brand)<tr><td><strong>{{ $brand->name }}</strong></td><td>{{ $brand->description ?: '—' }}</td><td>{{ $brand->products_count }}</td>@can('create', \App\Models\Brand::class)<td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('brands.edit', $brand) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('brands.destroy', $brand) }}" onsubmit="return confirm('Archive this brand?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Archive</button></form></td>@endcan</tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No brands yet.</td></tr>@endforelse
    </tbody></table><div class="mt-3">{{ $brands->links() }}</div></div>
@endsection
