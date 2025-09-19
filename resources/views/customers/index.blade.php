@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Customers</h2>
        <a class="btn btn-primary" href="{{ route('customers.create') }}">
            <i class="fa fa-plus me-2"></i> Add New Customer
        </a>
    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success shadow-sm rounded-3">
        <p>{{ $message }}</p>
    </div>
    @endif
    @if ($message = Session::get('error'))
    <div class="alert alert-danger shadow-sm rounded-3">
        <p>{{ $message }}</p>
    </div>
    @endif

    <div class="card shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="py-3 px-4">No</th>
                            <th scope="col" class="py-3 px-4">Customer Name</th>
                            <th scope="col" class="py-3 px-4" width="280px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $i => $customer)
                        <tr>
                            <td class="align-middle px-4">{{ $i + 1 }}</td>
                            <td class="align-middle px-4">{{ $customer->name }}</td>
                            <td class="align-middle px-4">
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                    <a class="btn btn-sm btn-primary" href="{{ route('customers.edit', $customer->id) }}">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this customer?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
