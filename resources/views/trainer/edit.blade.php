@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Edit Trainer</h2>
                <form action="{{ route('trainers.update', $trainer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('trainer.form', ['trainer' => $trainer])
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
@endsection
