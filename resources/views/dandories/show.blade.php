@extends('layouts.app')

@section('content')
<style>
    /* CSS from index.blade.php for consistency */
    .badge-status {
        font-size: 0.8em;
        padding: 0.5em 0.8em;
        border-radius: 1rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-todo { background-color: #f8d7da; color: #721c24; }
    .badge-inprogress { background-color: #fff3cd; color: #856404; }
    .badge-finish { background-color: #d1e7dd; color: #155724; }
    .badge-pending { background-color: #e2e3e5; color: #495057; }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>#{{ $dandory->ddcnk_id }}</h2>
        <a class="btn btn-secondary" href="{{ route('dandories.index') }}">
            <i class="fa fa-arrow-left"></i> Back to list
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mt-3 rounded-3 shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif
    
    <div id="message-container" class="my-3"></div>

    <div class="row g-4 mt-3">
        {{-- Ticket Details Card --}}
        <div class="{{ $dandory->status == 'FINISH' ? 'col-md-6' : 'col-md-8 offset-md-2' }}">
            <div class="card shadow-sm p-4 h-100">
                <h5 class="card-title mb-3">Ticket Details</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Key:</strong><br>
                        {{ $dandory->ddcnk_id }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Requestor:</strong><br>
                        {{ App\Models\User::find($dandory->added_by)->name }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Customer:</strong><br>
                        {{ $dandory->customer }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Line Produksi:</strong><br>
                        {{ $dandory->line_production ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Part Name:</strong><br>
                        {{ $dandory->nama_part }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Part Number:</strong><br>
                        {{ $dandory->nomor_part }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Process:</strong><br>
                        {{ $dandory->proses }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Machine:</strong><br>
                        {{ $dandory->mesin }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Quantity:</strong><br>
                        {{ $dandory->qty_pcs }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Planning Shift:</strong><br>
                        {{ $dandory->planning_shift ?? 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Check-in Time:</strong><br>
                        {{ $dandory->check_in ? \Carbon\Carbon::parse($dandory->check_in)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') : 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Check-out Time:</strong><br>
                        {{ $dandory->check_out ? \Carbon\Carbon::parse($dandory->check_out)->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') : 'N/A' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-status badge-{{ strtolower(str_replace(' ', '', $dandory->status)) }}">{{ $dandory->status }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Assigned To:</strong><br>
                        @if ($dandory->assigned_to)
                            {{ App\Models\User::find($dandory->assigned_to)->name }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Date Created:</strong><br>
                        {{ $dandory->created_at }}
                    </div>
                    <div class="col-12 mb-3">
                        <strong>Notes:</strong><br>
                        @if (Auth::user()->hasRole('Admin') || (Auth::user()->hasRole('Teknisi') && $dandory->status == 'PENDING' && $dandory->assigned_to == Auth::id()))
                            <form action="{{ route('dandories.updateNotes', $dandory->id) }}" method="POST" id="notes-form">
                                @csrf
                                @method('PUT')
                                <textarea name="notes" class="form-control" rows="3">{{ $dandory->notes }}</textarea>
                                <button type="submit" class="btn btn-success btn-sm mt-2">Save Notes</button>
                            </form>
                        @else
                            {{ $dandory->notes }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Card (Only if FINISH) --}}
        @if ($dandory->status == 'FINISH')
        <div class="col-md-6">
            <div class="card shadow-sm p-4 h-100 d-flex flex-column">
                <div class="card-header text-center pb-0">
                    <h5 class="mb-1">Productivity Metric</h5>
                    <p class="text-muted mb-0">Quantity per Minute</p>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center h-100">
                    <canvas id="productivityChart" class="w-100 h-100"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if ($dandory->status == 'FINISH')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dandory = @json($dandory);
        
        const checkIn = new Date(dandory.check_in);
        const checkOut = new Date(dandory.check_out);
        const durationMinutes = (checkOut.getTime() - checkIn.getTime()) / (1000 * 60);
        const qtyPerMinute = durationMinutes > 0 ? dandory.qty_pcs / durationMinutes : 0;
        
        const productivityData = [qtyPerMinute.toFixed(2)];
        const labels = ['Ticket #' + dandory.ddcnk_id];

        const ctx = document.getElementById('productivityChart').getContext('2d');
        const productivityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Qty per Minute',
                    data: productivityData,
                    backgroundColor: '#1f2937',
                    borderColor: '#1f2937',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.2,
                    categoryPercentage: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity per Minute'
                        },
                        ticks: { precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(31, 41, 55, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 1,
                        cornerRadius: 4,
                        padding: 8,
                        displayColors: false,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 }
                    }
                }
            }
        });
    });
</script>
@endif
@endsection