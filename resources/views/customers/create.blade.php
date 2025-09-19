@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Customer</h2>
        <a class="btn btn-secondary" href="{{ route('customers.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger shadow-sm rounded-3">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm rounded-3 p-4">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-group">
                        <label for="name" class="form-label"><strong>Customer Name:</strong></label>
                        <input type="text" name="name" class="form-control" placeholder="Customer Name" value="{{ old('name') }}" required autocomplete='off'>
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
