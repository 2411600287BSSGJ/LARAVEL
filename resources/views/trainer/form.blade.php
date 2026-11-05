@php($trainer = $trainer ?? null)
<div class="mb-3">
    <label for="name">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $trainer?->name) }}" required>
</div>
<div class="mb-3">
    <label for="contact_person">Contact person</label>
    <input id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person', $trainer?->contact_person) }}">
</div>
<div class="mb-3">
    <label for="email">Email</label>
    <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $trainer?->email) }}" required>
</div>
<div class="mb-3">
    <label for="phone">Phone</label>
    <input id="phone" name="phone" class="form-control" value="{{ old('phone', $trainer?->phone) }}">
</div>
<div class="mb-3">
    <label for="specialty">Specialty</label>
    <input id="specialty" name="specialty" class="form-control" value="{{ old('specialty', $trainer?->specialty) }}" required>
</div>
<div class="mb-3">
    <label for="lead_time_days">Lead time (days)</label>
    <input id="lead_time_days" type="number" min="0" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $trainer?->lead_time_days ?? 3) }}" required>
</div>
<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $trainer?->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active</label>
</div>
