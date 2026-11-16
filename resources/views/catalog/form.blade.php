<form class="panel" method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="mb-3"><label class="form-label">{{ $kind }} name</label><input class="form-control" name="name" required maxlength="255" value="{{ old('name', $record->name) }}">@error('name')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4">{{ old('description', $record->description) }}</textarea></div>
    <div class="d-flex gap-2"><button class="btn btn-primary">Save {{ $kind }}</button><a class="btn btn-outline-secondary" href="{{ route($routePrefix.'.index') }}">Cancel</a></div>
</form>
