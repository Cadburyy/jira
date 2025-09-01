@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Edit Appearance</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm p-4">
        <form method="POST" action="{{ route('settings.cms.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label"><strong>Title</strong></label>
                <input type="text" name="title" class="form-control"
                    value="{{ $settings['title'] ?? config('app.name') }}">
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Logo</strong></label>
                <input type="text" name="logo" class="form-control"
                    value="{{ $settings['logo'] ?? asset('images/cnk.png') }}">
                <small class="text-muted">Upload handling can be added later (for now paste URL/path)</small>
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Theme</strong></label>
                <select name="theme" class="form-select">
                    <option value="light" {{ ($settings['theme'] ?? 'light') === 'light' ? 'selected' : '' }}>Light</option>
                    <option value="dark" {{ ($settings['theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Font</strong></label>
                <input type="text" name="font" class="form-control"
                    value="{{ $settings['font'] ?? 'Nunito' }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
