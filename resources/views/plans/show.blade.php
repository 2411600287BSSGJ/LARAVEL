@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Plan Details: {{ $plan->reference_number }}</h2>
                <p><strong>Trainer:</strong> {{ $plan->trainer->name }}</p>
                <p><strong>Status:</strong> {{ ucfirst($plan->status) }}</p>

                <h4>Exercises</h4>
                <ul>
                    @foreach($plan->items as $item)
                        <li>{{ $item->quantity }} {{ $item->unit }} of {{ $item->exercise_name }}</li>
                    @endforeach
                </ul>

                @can('approve', $plan)
                    <form action="{{ route('plans.approve', $plan) }}" method="POST">
                        @csrf
                        <button class="btn btn-success">Approve Plan</button>
                    </form>
                @endcan
            </div>
        </main>
    </div>
@endsection