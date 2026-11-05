@extends('layouts.app')
@section('content')
    <div class="row">
        @include('partials.sidebar')
        <main class="col-lg-9 col-xl-10">
            <div class="panel">
                <h2>Add Trainer</h2>
                <form action="{{ route('trainers.store') }}" method="POST">
                    @csrf
                    @include('trainer.form')
                    <button type="submit" class="btn btn-primary">Save Trainer</button>
                </form>
            </div>
        </main>
    </div>
@endsection
