@extends('layouts.app')
@section('title', 'Add Category')

@section('content')
    <div class="mb-4">
        <p class="eyebrow mb-1">Inventory</p>
        <h1 class="fw-bold mb-0">Add Category</h1>
    </div>

    <div class="panel">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Category Name</label>
                <input type="text" name="name" class="form-control" required>
                @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
@endsection