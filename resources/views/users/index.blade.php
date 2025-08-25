@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Users Management</h2>
        @can('role-create')
            <a class="btn btn-primary" href="{{ route('users.create') }}">
                <i class="fa fa-plus me-2"></i> Create New User
            </a>
        @endcan
    </div>

    @session('success')
        <div class="alert alert-success rounded-3 shadow-sm" role="alert"> 
            {{ $value }}
        </div>
    @endsession

    <div class="table-responsive shadow-sm rounded-3">
        <table class="table table-bordered table-hover">
            <thead class="bg-light">
                <tr>
                    <th width="100px">No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $user)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            @if(!empty($user->getRoleNames()))
                                @foreach($user->getRoleNames() as $v)
                                   <span class="badge bg-primary rounded-pill">{{ $v }}</span>
                                @endforeach
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a class="btn btn-info btn-sm text-white" href="{{ route('users.show',$user->id) }}">
                               <i class="fa-solid fa-list me-1"></i> Show
                            </a>
                            <a class="btn btn-primary btn-sm" href="{{ route('users.edit',$user->id) }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {!! $data->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection