@extends('layouts.app')

@section('content')
    <style>
        tr[data-status="TO DO"] {
            background-color: #f8d7da !important;
        }

        tr[data-status="IN PROGRESS"] {
            background-color: #fff3cd !important;
        }

        tr[data-status="FINISH"] {
            background-color: #d1e7dd !important;
        }

        tr[data-status="PENDING"] {
            background-color: #e2e3e5 !important;
        }

        .sort-arrow {
            margin-left: 5px;
            cursor: pointer;
            user-select: none;
        }

        tr[data-status] td {
            background-color: inherit;
        }

        body,
        html {
            overflow-x: hidden;
            width: 100%;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .dropdown-cell {
            position: relative;
            overflow: visible !important;
        }

        .dropdown-menu {
            position: absolute !important;
            z-index: 1050;
        }

        .table-responsive {
            overflow-x: auto !important;
        }

        .table-responsive .dropdown {
            position: static;
        }

        .table-responsive .dropdown-menu {
            position: absolute;
            transform: none;
            background-color: #fff !important;
        }

        .dropdown-menu .dropdown-item {
            transition: background-color 0.3s, color 0.3s;
            border-radius: 6px;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #0d6efd;
            color: #fff;
        }

        .dropdown-menu .dropdown-item:hover i {
            color: #fff !important;
        }

        .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100vh - 1rem);
            margin: 0 auto;
            width: auto;
        }

        .modal-content {
            flex-grow: 1;
        }

        @media (min-width: 992px) {
            .container {
                max-width: 1400px;
            }

            .card-body.p-0 {
                padding: 0 1rem !important;
            }

            .table {
                min-width: 1200px;
            }
        }
    </style>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dandory Tickets</h2>
            @can('dandory-create')
                <a class="btn btn-primary" href="{{ route('dandories.create') }}">
                    <i class="fa fa-plus me-2"></i> Create New Ticket
                </a>
            @endcan
        </div>

        <div id="message-container" class="my-3"></div>

        @if (Auth::user()->hasAnyRole(['Admin', 'AdminTeknisi', 'Requestor']))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daily Dandoriman Counter</h5>
                    <form action="{{ route('dandories.index') }}" method="GET"
                        class="d-flex flex-column flex-md-row align-items-md-center">
                        <label for="date-filter" class="form-label mb-2 me-md-2 mb-md-0">Filter by Date:</label>
                        <input type="date" name="date" id="date-filter" class="form-control"
                            value="{{ $dateFilter }}" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Tickets</th>
                                    <th>Name</th>
                                    <th>Tickets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sortedTeknisiUsers->chunk(2) as $chunk)
                                    <tr>
                                        @foreach ($chunk as $user)
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->daily_count }}</td>
                                        @endforeach
                                        @if ($chunk->count() < 2)
                                            <td colspan="2"></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-end mb-4">
            <div class="btn-group" role="group">
                <button id="showActiveBtn" class="btn btn-primary">WIP Tickets</button>
                <button id="showFinishedBtn" class="btn btn-secondary">Finished Tickets</button>
            </div>
        </div>

        <div id="active-tickets-container" class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">WIP Tickets</h5>
                @if (Auth::user()->hasAnyRole(['Admin', 'AdminTeknisi']))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                        data-bs-target="#downloadModal" data-ticket-type="wip">
                        <i class="fa-solid fa-download me-1"></i> Download
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="sortable-header" data-sort-by="ddcnk_id"> Key <span class="sort-arrow"
                                        id="active-sort-arrow">&#9650;</span> </th>
                                <th>Line Produksi</th>
                                <th>Requestor</th>
                                <th>Customer</th>
                                <th>Part Name</th>
                                <th>Part No</th>
                                <th>Process</th>
                                <th>Machine</th>
                                <th>Qty</th>
                                <th>Planning</th>
                                <th>Status</th>
                                <th>Dandori Man</th>
                                <th width="100px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeDandories as $dandory)
                                <tr data-ticket-id="{{ $dandory->id }}" data-status="{{ $dandory->status }}">
                                    <td>{{ $dandory->ddcnk_id }}</td>
                                    <td>{{ $dandory->line_production }}</td>
                                    <td>{{ App\Models\User::find($dandory->added_by)->name }}</td>
                                    <td>{{ $dandory->customer }}</td>
                                    <td>{{ $dandory->nama_part }}</td>
                                    <td>{{ $dandory->nomor_part }}</td>
                                    <td>{{ $dandory->proses }}</td>
                                    <td>{{ $dandory->mesin }}</td>
                                    <td>{{ $dandory->qty_pcs }}</td>
                                    <td>{{ $dandory->planning_shift }}</td>
                                    <td>
                                        @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('AdminTeknisi') || (Auth::user()->hasRole('Teknisi') && $dandory->assigned_to == Auth::id()))
                                            <form action="{{ route('dandories.updateStatus', $dandory->id) }}"
                                                method="POST" class="update-form status-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="form-control">
                                                    <option value="TO DO" {{ $dandory->status == 'TO DO' ? 'selected' : '' }}>TO DO
                                                    </option>
                                                    <option value="IN PROGRESS"
                                                        {{ $dandory->status == 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS
                                                    </option>
                                                    <option value="PENDING" {{ $dandory->status == 'PENDING' ? 'selected' : '' }}>
                                                        PENDING</option>
                                                    <option value="FINISH" {{ $dandory->status == 'FINISH' ? 'selected' : '' }}>FINISH
                                                    </option>
                                                </select>
                                            </form>
                                        @else
                                            {{ $dandory->status }}
                                        @endif
                                    </td>
                                    <td class="assigned-to-cell">
                                        @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('AdminTeknisi'))
                                            {{-- Admins/AdminTeknisi can assign to any technician --}}
                                            <form action="{{ route('dandories.assign', $dandory->id) }}" method="POST"
                                                class="update-form assigned-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="assigned_to" class="form-control">
                                                    <option value="">-- Assign --</option>
                                                    @foreach ($users->filter(fn($u) => $u->hasRole('Teknisi')) as $user)
                                                        <option value="{{ $user->id }}"
                                                            {{ $dandory->assigned_to == $user->id ? 'selected' : '' }}>
                                                            {{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @elseif(Auth::user()->hasRole('Teknisi') && !$dandory->assigned_to)
                                            {{-- Teknisi can only self-assign unassigned tickets --}}
                                            <form action="{{ route('dandories.assign', $dandory->id) }}" method="POST"
                                                class="update-form assigned-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="assigned_to" class="form-control">
                                                    <option value="">-- Assign --</option>
                                                    <option value="{{ Auth::id() }}"
                                                        {{ $dandory->assigned_to == Auth::id() ? 'selected' : '' }}>
                                                        {{ Auth::user()->name }}
                                                    </option>
                                                </select>
                                            </form>
                                        @elseif($dandory->assigned_to)
                                            {{-- Display assigned user's name if ticket is assigned --}}
                                            {{ App\Models\User::find($dandory->assigned_to)->name }}
                                        @else
                                            {{-- Show N/A for unassigned tickets if not a technician --}}
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('dandories.show', $dandory->id) }}">
                                                        <i class="fa-solid fa-eye me-2 text-info"></i> View
                                                    </a>
                                                </li>
                                                @can('dandory-edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('dandories.edit', $dandory->id) }}">
                                                            <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('dandory-delete')
                                                    <li>
                                                        <button class="dropdown-item text-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-action="{{ route('dandories.destroy', $dandory->id) }}">
                                                            <i class="fa-solid fa-trash me-2"></i> Delete
                                                        </button>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="finished-tickets-container" class="card shadow-sm mb-4" style="display: none;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Finished Tickets</h5>
                @if (Auth::user()->hasAnyRole(['Admin', 'AdminTeknisi']))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                        data-bs-target="#downloadModal" data-ticket-type="finished">
                        <i class="fa-solid fa-download me-1"></i> Download
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="sortable-header" data-sort-by="ddcnk_id"> Key <span class="sort-arrow"
                                        id="finished-sort-arrow">&#9650;</span> </th>
                                <th>Line Produksi</th>
                                <th>Requestor</th>
                                <th>Customer</th>
                                <th>Nama Part</th>
                                <th>No Part</th>
                                <th>Process</th>
                                <th>Machine</th>
                                <th>Qty</th>
                                <th>Planning</th>
                                <th>Status</th>
                                <th>Dandori Man</th>
                                <th width="100px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($finishedDandories as $dandory)
                                <tr data-ticket-id="{{ $dandory->id }}" data-status="{{ $dandory->status }}">
                                    <td>{{ $dandory->ddcnk_id }}</td>
                                    <td>{{ $dandory->line_production }}</td>
                                    <td>{{ App\Models\User::find($dandory->added_by)->name }}</td>
                                    <td>{{ $dandory->customer }}</td>
                                    <td>{{ $dandory->nama_part }}</td>
                                    <td>{{ $dandory->nomor_part }}</td>
                                    <td>{{ $dandory->proses }}</td>
                                    <td>{{ $dandory->mesin }}</td>
                                    <td>{{ $dandory->qty_pcs }}</td>
                                    <td>{{ $dandory->planning_shift }}</td>
                                    <td>
                                        @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('AdminTeknisi') || (Auth::user()->hasRole('Teknisi') && $dandory->assigned_to == Auth::id()))
                                            <form action="{{ route('dandories.updateStatus', $dandory->id) }}"
                                                method="POST" class="update-form status-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="form-control">
                                                    <option value="TO DO" {{ $dandory->status == 'TO DO' ? 'selected' : '' }}>TO DO
                                                    </option>
                                                    <option value="IN PROGRESS"
                                                        {{ $dandory->status == 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS
                                                    </option>
                                                    <option value="PENDING" {{ $dandory->status == 'PENDING' ? 'selected' : '' }}>
                                                        PENDING</option>
                                                    <option value="FINISH" {{ $dandory->status == 'FINISH' ? 'selected' : '' }}>FINISH
                                                    </option>
                                                </select>
                                            </form>
                                        @else
                                            {{ $dandory->status }}
                                        @endif
                                    </td>
                                    <td class="assigned-to-cell">
                                        @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('AdminTeknisi'))
                                            {{-- Admins/AdminTeknisi can assign to any technician --}}
                                            <form action="{{ route('dandories.assign', $dandory->id) }}" method="POST"
                                                class="update-form assigned-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="assigned_to" class="form-control">
                                                    <option value="">-- Assign --</option>
                                                    @foreach ($users->filter(fn($u) => $u->hasRole('Teknisi')) as $user)
                                                        <option value="{{ $user->id }}"
                                                            {{ $dandory->assigned_to == $user->id ? 'selected' : '' }}>
                                                            {{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @elseif(Auth::user()->hasRole('Teknisi') && !$dandory->assigned_to)
                                            {{-- Teknisi can only self-assign unassigned tickets --}}
                                            <form action="{{ route('dandories.assign', $dandory->id) }}" method="POST"
                                                class="update-form assigned-form">
                                                @csrf
                                                @method('PUT')
                                                <select name="assigned_to" class="form-control">
                                                    <option value="">-- Assign --</option>
                                                    <option value="{{ Auth::id() }}"
                                                        {{ $dandory->assigned_to == Auth::id() ? 'selected' : '' }}>
                                                        {{ Auth::user()->name }}
                                                    </option>
                                                </select>
                                            </form>
                                        @elseif($dandory->assigned_to)
                                            {{-- Display assigned user's name if ticket is assigned --}}
                                            {{ App\Models\User::find($dandory->assigned_to)->name }}
                                        @else
                                            {{-- Show N/A for unassigned tickets if not a technician --}}
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('dandories.show', $dandory->id) }}">
                                                        <i class="fa-solid fa-eye me-2 text-info"></i> View
                                                    </a>
                                                </li>
                                                @can('dandory-edit')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('dandories.edit', $dandory->id) }}">
                                                            <i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Edit
                                                        </a>
                                                    </li>
                                                @endcan
                                                @can('dandory-delete')
                                                    <li>
                                                        <button class="dropdown-item text-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-action="{{ route('dandories.destroy', $dandory->id) }}">
                                                            <i class="fa-solid fa-trash me-2"></i> Delete
                                                        </button>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this ticket?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete-form" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadModalLabel">Download Tickets</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="download-form" action="" method="GET">
                        <div id="wip-options" class="download-options">
                            <h6>WIP Ticket Filters</h6>
                            <div class="mb-3">
                                <label for="wip-date-filter" class="form-label">Filter by Creation Date</label>
                                <input type="date" name="creation_date" id="wip-date-filter" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Filter by Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="statuses[]" value="TO DO"
                                        id="status-todo">
                                    <label class="form-check-label" for="status-todo">
                                        TO DO
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="statuses[]" value="IN PROGRESS"
                                        id="status-inprogress">
                                    <label class="form-check-label" for="status-inprogress">
                                        IN PROGRESS
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="statuses[]" value="PENDING"
                                        id="status-pending">
                                    <label class="form-check-label" for="status-pending">
                                        PENDING
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-info me-2" id="downloadAllWipBtn">
                                    <i class="fa-solid fa-download me-1"></i> Download All WIP
                                </button>
                                <button type="submit" class="btn btn-primary" name="format" value="csv">
                                    <i class="fa-solid fa-download me-1"></i> Download Selected
                                </button>
                            </div>
                        </div>
                        <div id="finished-options" class="download-options" style="display: none;">
                            <h6>Finished Ticket Filters</h6>
                            <div class="mb-3">
                                <label for="finished-from-date" class="form-label">From Date</label>
                                <input type="date" name="from_date" id="finished-from-date" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="finished-to-date" class="form-label">To Date</label>
                                <input type="date" name="to_date" id="finished-to-date" class="form-control">
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-info me-2" id="downloadAllFinishedBtn">
                                    <i class="fa-solid fa-download me-1"></i> Download All Finished
                                </button>
                                <button type="submit" class="btn btn-primary" name="format" value="csv">
                                    <i class="fa-solid fa-download me-1"></i> Download Selected
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showActiveBtn = document.getElementById('showActiveBtn');
            const showFinishedBtn = document.getElementById('showFinishedBtn');
            const activeContainer = document.getElementById('active-tickets-container');
            const finishedContainer = document.getElementById('finished-tickets-container');
            const messageContainer = document.getElementById('message-container');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let sortDirection = {
                'ddcnk_id': 'asc'
            };
            const activeSortArrow = document.getElementById('active-sort-arrow');
            const finishedSortArrow = document.getElementById('finished-sort-arrow');

            function updateSortArrow(arrowElement, direction) {
                if (direction === 'asc') {
                    arrowElement.innerHTML = '&#9650;';
                } else {
                    arrowElement.innerHTML = '&#9660;';
                }
            }

            function showMessage(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                messageContainer.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            }

            function showActiveTickets() {
                activeContainer.style.display = 'block';
                finishedContainer.style.display = 'none';
                showActiveBtn.classList.remove('btn-secondary');
                showActiveBtn.classList.add('btn-primary');
                showFinishedBtn.classList.remove('btn-primary');
                showFinishedBtn.classList.add('btn-secondary');
            }

            function showFinishedTickets() {
                activeContainer.style.display = 'none';
                finishedContainer.style.display = 'block';
                showFinishedBtn.classList.remove('btn-secondary');
                showFinishedBtn.classList.add('btn-primary');
                showActiveBtn.classList.remove('btn-primary');
                showActiveBtn.classList.add('btn-secondary');
            }

            function sortTable(tableId, sortColumn, initialSort = false) {
                const table = document.getElementById(tableId).querySelector('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const arrowElement = tableId === 'active-tickets-container' ? activeSortArrow : finishedSortArrow;

                if (!initialSort) {
                    const currentDirection = sortDirection[sortColumn];
                    sortDirection[sortColumn] = currentDirection === 'asc' ? 'desc' : 'asc';
                }

                const direction = sortDirection[sortColumn];
                updateSortArrow(arrowElement, direction);

                rows.sort((a, b) => {
                    const aValue = a.querySelector(`td:nth-child(${getColumnIndex(table, sortColumn) + 1})`).textContent
                        .trim();
                    const bValue = b.querySelector(`td:nth-child(${getColumnIndex(table, sortColumn) + 1})`).textContent
                        .trim();
                    let comparison = aValue.localeCompare(bValue, undefined, {
                        numeric: true
                    });
                    return direction === 'asc' ? comparison : -comparison;
                });

                rows.forEach(row => tbody.appendChild(row));
            }

            function getColumnIndex(table, sortColumn) {
                const headers = table.querySelectorAll('thead th');
                for (let i = 0; i < headers.length; i++) {
                    if (headers[i].getAttribute('data-sort-by') === sortColumn) {
                        return i;
                    }
                }
                return -1;
            }

            showActiveBtn.addEventListener('click', showActiveTickets);
            showFinishedBtn.addEventListener('click', showFinishedTickets);
            showActiveTickets();

            document.querySelectorAll('form.update-form select').forEach(element => {
                element.addEventListener('change', function(e) {
                    submitForm(element.closest('form'));
                });
            });

            document.querySelectorAll('.sort-arrow').forEach(arrow => {
                arrow.addEventListener('click', function() {
                    const sortColumn = this.closest('.sortable-header').getAttribute('data-sort-by');
                    const tableContainer = this.closest('[id$="-tickets-container"]');
                    sortTable(tableContainer.id, sortColumn);
                });
            });

            sortTable('active-tickets-container', 'ddcnk_id', true);
            sortTable('finished-tickets-container', 'ddcnk_id', true);

            async function submitForm(form) {
                const formData = new FormData(form);
                const url = form.action;
                const row = form.closest('tr');
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: formData
                    });
                    const result = await response.json();
                    if (response.ok) {
                        if (result.success) {
                            showMessage('success', result.message);
                            if (form.classList.contains('status-form')) {
                                const newStatus = formData.get('status');
                                row.setAttribute('data-status', newStatus);
                                const currentTableContainer = row.closest('div[id$="-tickets-container"]');
                                if (newStatus === 'FINISH' && currentTableContainer.id === 'active-tickets-container') {
                                    finishedContainer.querySelector('tbody').prepend(row);
                                } else if (newStatus !== 'FINISH' && currentTableContainer.id ===
                                    'finished-tickets-container') {
                                    activeContainer.querySelector('tbody').prepend(row);
                                }
                            } else if (form.classList.contains('assigned-form')) {
                                const assignedToCell = row.querySelector('.assigned-to-cell');
                                const selectedOption = form.querySelector('select[name="assigned_to"] option:checked');
                                assignedToCell.innerHTML = selectedOption.text;
                            }
                        } else {
                            showMessage('error', result.error);
                        }
                    } else {
                        const errorText = await response.text();
                        console.error('Fetch Error:', errorText);
                        showMessage('error', 'An error occurred while updating the ticket.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                }
            }

            var deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var action = button.getAttribute('data-action');
                var form = deleteModal.querySelector('#delete-form');
                form.action = action;
            });

            const downloadModal = document.getElementById('downloadModal');
            const wipOptions = document.getElementById('wip-options');
            const finishedOptions = document.getElementById('finished-options');
            const downloadForm = document.getElementById('download-form');
            const wipDateFilter = document.getElementById('wip-date-filter');
            const statusCheckboxes = document.querySelectorAll('input[name="statuses[]"]');
            const downloadAllWipBtn = document.getElementById('downloadAllWipBtn');
            const downloadAllFinishedBtn = document.getElementById('downloadAllFinishedBtn');

            downloadModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const ticketType = button.getAttribute('data-ticket-type');
                if (ticketType === 'wip') {
                    wipOptions.style.display = 'block';
                    finishedOptions.style.display = 'none';
                    downloadForm.action = "{{ route('dandories.download', ['type' => 'wip']) }}";
                } else {
                    wipOptions.style.display = 'none';
                    finishedOptions.style.display = 'block';
                    downloadForm.action = "{{ route('dandories.download', ['type' => 'finished']) }}";
                }
                downloadForm.reset();
            });

            downloadAllWipBtn.addEventListener('click', function() {
                const url = '{{ route('dandories.download', ['type' => 'wip']) }}' +
                    '?statuses[]=' + encodeURIComponent('TO DO') +
                    '&statuses[]=' + encodeURIComponent('IN PROGRESS') +
                    '&statuses[]=' + encodeURIComponent('PENDING') +
                    '&format=csv';

                window.location.href = url;
            });
            
            downloadAllFinishedBtn.addEventListener('click', function() {
                const url = '{{ route('dandories.download', ['type' => 'finished']) }}' +
                    '?select_all=1&format=csv';
                window.location.href = url;
            });
        });
    </script>
@endsection