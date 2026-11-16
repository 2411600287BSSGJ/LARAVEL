@props(['action' => url()->current(), 'method' => 'GET'])

<form {{ $attributes->merge(['action' => $action, 'method' => $method, 'class' => 'row g-2 mb-3']) }}>
    {{ $slot }}
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </div>
    @if(request()->query())
        <div class="col-auto">
            <a href="{{ $action }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>
    @endif
</form>
