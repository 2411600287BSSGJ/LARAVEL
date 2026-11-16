<form class="panel" method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Supplier name</label><input class="form-control" name="name" required value="{{ old('name', $supplier->name) }}">@error('name')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label">Contact person</label><input class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $supplier->email) }}">@error('email')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $supplier->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">Lead time (days)</label><input class="form-control" type="number" min="0" name="lead_time_days" required value="{{ old('lead_time_days', $supplier->lead_time_days ?? 0) }}"></div>
        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address', $supplier->address) }}"></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Save Supplier</button><a class="btn btn-outline-secondary" href="{{ route('suppliers.index') }}">Cancel</a></div>
    </div>
</form>
