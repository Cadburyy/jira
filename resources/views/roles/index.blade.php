@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Role Management</h2>
        @can('role-create')
            <a class="btn btn-primary" href="{{ route('roles.create') }}">
                <i class="fa fa-plus me-2"></i> Create New Role
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive shadow-sm rounded-3">
        <table class="table table-bordered table-hover">
            <thead class="bg-light">
                <tr>
                    <th width="100px">No</th>
                    <th>Name</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $key => $role)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $role->name }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a class="btn btn-info btn-sm text-white" href="{{ route('roles.show', $role->id) }}">
                                <i class="fa-solid fa-list me-1"></i> Show
                            </a>
                            @can('role-edit')
                                <a class="btn btn-primary btn-sm" href="{{ route('roles.edit', $role->id) }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </a>
                            @endcan
                            @can('role-delete')
                                <form method="POST" action="{{ route('roles.destroy', $role->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                        <i class="fa-solid fa-trash me-1"></i> Delete
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {!! $roles->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
