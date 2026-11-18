<form class="panel" method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Supplier / Company Name <span class="text-danger">*</span></label>
            <input class="form-control @error('name') is-invalid @enderror" name="name" required value="{{ old('name', $supplier->name) }}" placeholder="e.g. Strength Supply Direct">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Contact Person</label>
            <input class="form-control @error('contact_person') is-invalid @enderror" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="e.g. John Doe, Accounts Manager">
            @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Email Address</label>
            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $supplier->email) }}" placeholder="orders@supplier.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Phone Number</label>
            <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $supplier->phone) }}" placeholder="+63 912 345 6789">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Lead Time (Days) <span class="text-danger">*</span></label>
            <input class="form-control @error('lead_time_days') is-invalid @enderror" type="number" min="0" name="lead_time_days" required value="{{ old('lead_time_days', $supplier->lead_time_days ?? 7) }}">
            @error('lead_time_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Payment Terms</label>
            <select class="form-select @error('payment_terms') is-invalid @enderror" name="payment_terms">
                <option value="Net 30" @selected(old('payment_terms', $supplier->payment_terms ?? 'Net 30') === 'Net 30')>Net 30 Days</option>
                <option value="Net 15" @selected(old('payment_terms', $supplier->payment_terms) === 'Net 15')>Net 15 Days</option>
                <option value="Net 60" @selected(old('payment_terms', $supplier->payment_terms) === 'Net 60')>Net 60 Days</option>
                <option value="COD" @selected(old('payment_terms', $supplier->payment_terms) === 'COD')>Cash on Delivery (COD)</option>
                <option value="Prepaid" @selected(old('payment_terms', $supplier->payment_terms) === 'Prepaid')>Prepaid / Advance</option>
            </select>
            @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Minimum Order Amount (₱)</label>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input class="form-control @error('minimum_order_amount') is-invalid @enderror" type="number" step="0.01" min="0" name="minimum_order_amount" value="{{ old('minimum_order_amount', $supplier->minimum_order_amount ?? 0) }}" placeholder="0.00">
            </div>
            @error('minimum_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 d-flex align-items-center pt-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked(old('active', $supplier->active ?? true))>
                <label class="form-check-label fw-semibold" for="active">Active Vendor Status</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Physical Business Address</label>
            <input class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $supplier->address) }}" placeholder="Warehouse, Street, City, Province">
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 d-flex gap-2 pt-2">
            <button class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i>Save Supplier</button>
            <a class="btn btn-outline-secondary px-4" href="{{ route('suppliers.index') }}">Cancel</a>
        </div>
    </div>
</form>
