<form class="panel" method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Product name</label><input class="form-control" name="name" required value="{{ old('name', $product->name ?? '') }}">@error('name')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label">SKU</label><input class="form-control" name="sku" required value="{{ old('sku', $product->sku ?? '') }}">@error('sku')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        <div class="col-md-4"><label class="form-label">Manufacturer / Brand</label><select class="form-select" name="brand_id"><option value="">Unassigned</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Equipment Type / Category</label><select class="form-select" name="category_id"><option value="">Unassigned</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Preferred Supplier</label><select class="form-select" name="supplier_id"><option value="">Unassigned</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></div>
        @if(!isset($product) || !$product->exists)
            <div class="col-md-4"><label class="form-label">Initial quantity</label><input class="form-control" type="number" min="0" name="quantity" required value="{{ old('quantity', 0) }}"></div>
        @endif
        <div class="col-md-4"><label class="form-label">Reorder level</label><input class="form-control" type="number" min="0" name="reorder_level" required value="{{ old('reorder_level', $product->reorder_level ?? 0) }}">@error('reorder_level')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        <div class="col-md-4"><label class="form-label">Unit price</label><input class="form-control" type="number" min="0" step="0.01" name="unit_price" required value="{{ old('unit_price', $product->unit_price ?? '') }}">@error('unit_price')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea></div>
        @if(isset($product) && $product->exists)<div class="col-12 small text-muted">Use Transactions to change stock quantity so inventory movements remain auditable.</div>@endif
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Save Product</button><a class="btn btn-outline-secondary" href="{{ route('products.index') }}">Cancel</a></div>
    </div>
</form>
