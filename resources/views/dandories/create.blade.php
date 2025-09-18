@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Dandory Ticket</h2>
        <a class="btn btn-secondary" href="{{ route('dandories.index') }}">
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
        <form action="{{ route('dandories.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="requestor" class="form-label"><strong>Requestor:</strong></label>
                        <input type="text" name="requestor" class="form-control" value="{{ Auth::user()->name }}" required autocomplete="off" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customer" class="form-label"><strong>Customer:</strong></label>
                        <input type="text" name="customer" class="form-control" placeholder="Customer" value="{{ old('customer') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="line_production" class="form-label"><strong>Line Produksi:</strong></label>
                        <input type="text" name="line_production" class="form-control" placeholder="Line Produksi" value="{{ old('line_production') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nama_part" class="form-label"><strong>Nama Part:</strong></label>
                        <input type="text" name="nama_part" class="form-control" placeholder="Nama Part" value="{{ old('nama_part') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nomor_part" class="form-label"><strong>Nomor Part:</strong></label>
                        <input type="text" name="nomor_part" class="form-control" placeholder="Nomor Part" value="{{ old('nomor_part') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="proses" class="form-label"><strong>Proses:</strong></label>
                        <input type="text" name="proses" class="form-control" placeholder="Proses" value="{{ old('proses') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mesin" class="form-label"><strong>Mesin:</strong></label>
                        <input type="text" name="mesin" class="form-control" placeholder="Mesin" value="{{ old('mesin') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="qty_pcs" class="form-label"><strong>Qty. PCS:</strong></label>
                        <input type="number" name="qty_pcs" class="form-control" placeholder="Qty. PCS" value="{{ old('qty_pcs') }}" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="planning_shift" class="form-label"><strong>Planning Shift:</strong></label>
                        <select name="planning_shift" class="form-control" required>
                            <option value="">-- Select Shift --</option>
                            @for ($i = 7; $i <= 16; $i++)
                                <option value="1/{{ sprintf('%02d:00', $i) }}" {{ old('planning_shift') == '1/' . sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                    1/{{ sprintf('%02d:00', $i) }}
                                </option>
                            @endfor
                            @for ($i = 16; $i <= 24; $i++)
                                <option value="2/{{ sprintf('%02d:00', $i) }}" {{ old('planning_shift') == '2/' . sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                    2/{{ sprintf('%02d:00', $i) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="notes" class="form-label"><strong>Notes:</strong></label>
                        <textarea class="form-control" style="height:150px" name="notes" placeholder="Notes">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Submit Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection