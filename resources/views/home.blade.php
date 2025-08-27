@extends('layouts.app')

@section('content')
{{-- Use a PHP block to get the authenticated user and their roles at the very top --}}
@php
    $user = Auth::user();
    $isAdmin = $user->hasRole('Admin');
    $isRequestor = $user->hasRole('Requestor');
    $isTeknisi = $user->hasRole('Teknisi');
    $isView = $user->hasRole('Views');
@endphp
<style>
    body, html {
        overflow-x: hidden;
        overflow-y: auto;
        background-color: #f8f9fa;
    }
    .card-link-hover:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    .card-link-hover .card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    .card {
        border-radius: 1rem;
        border: none;
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }
    .text-primary-dark {
        color: #0056b3;
    }
    .status-todo { background-color: #f8d7da !important; }
    .status-in-progress { background-color: #fff3cd !important; }
    .status-pending { background-color: #e2e3e5 !important; }
    .status-finish { background-color: #d1e7dd !important; }
</style>

<div class="container py-5">
    <h2 class="text-center mb-4 text-dark font-weight-bold">Welcome, {{ $user->name }} 👋</h2>
    
    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center mt-4">

        {{-- Card for Manage Users --}}
        @if($isAdmin)
        <div class="col-md-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-4">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-users fa-3x mb-3 text-primary-dark"></i>
                        <h5 class="card-title text-dark">Manage Users</h5>
                        <p class="card-text text-muted">View and manage all user accounts.</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        {{-- Card for Manage Roles --}}
        @if($isAdmin)
        <div class="col-md-4">
            <a href="{{ route('roles.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-4">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-user-tag fa-3x mb-3 text-success"></i>
                        <h5 class="card-title text-dark">Manage Roles</h5>
                        <p class="card-text text-muted">Assign and modify user roles and permissions.</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
        
        {{-- Card for Dandory Tickets --}}
        @if($isAdmin || $isRequestor || $isTeknisi)
        <div class="col-md-4">
            <a href="{{ route('dandories.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-4">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-clipboard-list fa-3x mb-3 text-info"></i>
                        <h5 class="card-title text-dark">Dandory Tickets</h5>
                        <p class="card-text text-muted">View and manage your dandory tickets.</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success text-center mt-5" role="alert">
            {{ session('status') }}
        </div>
    @endif

    {{-- Dashboard Charts --}}
    @if($isAdmin || $isTeknisi || $isView)
    <div class="row mt-5">
        <h3 class="text-center mb-4 text-dark font-weight-bold">Dandoriman Ticket Support</h3>
        <p class="text-center text-muted mb-4">A visual breakdown of dandoriman ticket data.</p>
        <div class="row g-4 justify-content-center">
            
            {{-- Chart 1: Ticket Status --}}
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100 p-3">
                    <div class="card-header text-center">
                        <h5>Ticket Status Breakdown</h5>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center">
                        <div style="width: 75%;">
                            <canvas id="ticketStatusChart"></canvas>
                        </div>
                        <ul class="list-unstyled mt-3 w-75">
                            @foreach($ticketStatusChartData['labels'] as $key => $label)
                                <li>
                                    <span style="display:inline-block;width:10px;height:10px;background-color:{{ $ticketStatusChartData['colors'][$key] }};margin-right:5px;border-radius:50%;"></span>
                                    {{ $label }}: {{ $ticketStatusChartData['data'][$key] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Chart 2: Tickets per Dandoriman --}}
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm h-100 p-3">
                    <div class="card-header text-center">
                        <h5>Tickets per Dandoriman</h5>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center">
                        <div style="width: 75%;">
                            <canvas id="dandoriManChart"></canvas>
                        </div>
                        <ul class="list-unstyled mt-3 w-75">
                            @foreach($dandoriManChartData['labels'] as $key => $label)
                                <li>
                                    <span style="display:inline-block;width:10px;height:10px;background-color:{{ $dandoriManChartData['colors'][$key] }};margin-right:5px;border-radius:50%;"></span>
                                    {{ $label }}: {{ $dandoriManChartData['data'][$key] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- Chart 3: Daily, Weekly, Monthly - Hidden for 'Views' role --}}
            @if(!$isView)
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm h-100 p-3">
                    <div class="card-header text-center">
                        <h5>Daily, Weekly & Monthly Tickets</h5>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn btn-sm btn-primary" id="dailyBtn">Daily</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="weeklyBtn">Weekly</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="monthlyBtn">Monthly</button>
                        </div>
                        {{-- Date picker for daily filter --}}
                        <div id="daily-filter-container" class="mb-3">
                            <div class="row g-2">
                                <div class="col">
                                    <label for="start-date" class="form-label visually-hidden">Start Date</label>
                                    <input type="date" id="start-date" class="form-control form-control-sm" title="Start Date">
                                </div>
                                <div class="col">
                                    <label for="end-date" class="form-label visually-hidden">End Date</label>
                                    <input type="date" id="end-date" class="form-control form-control-sm" title="End Date">
                                </div>
                            </div>
                        </div>
                        <div style="width: 100%;">
                            <canvas id="resolutionChart"></canvas>
                        </div>
                        <ul id="resolution-legend" class="list-unstyled mt-3 w-100"></ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Dandori Tickets Table for 'Views' role --}}
    @if($isView)
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h5>All Dandori Tickets</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped" id="dandori-table">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Line Produksi</th>
                                <th>Requestor</th>
                                <th>Customer</th>
                                <th>Part Name</th>
                                <th>Part Number</th>
                                <th>Process</th>
                                <th>Machine</th>
                                <th>Qty PCS</th>
                                <th>Planning Shift</th>
                                <th>Status</th>
                                <th>Dandori Man</th>
                            </tr>
                        </thead>
                        <tbody id="dandori-table-body">
                            {{-- Table body will be populated by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    let ticketStatusChart, dandoriManChart, resolutionChart;

    const ticketStatusChartData = {
        labels: @json($ticketStatusChartData['labels']),
        datasets: [{
            data: @json($ticketStatusChartData['data']),
            backgroundColor: @json($ticketStatusChartData['colors']),
            hoverOffset: 4
        }]
    };

    const dandoriManChartData = {
        labels: @json($dandoriManChartData['labels']),
        datasets: [{
            data: @json($dandoriManChartData['data']),
            backgroundColor: @json($dandoriManChartData['colors']),
            hoverOffset: 4
        }]
    };

    const dailyTicketCounts = @json($dailyTicketCounts);
    const monthlyTicketCounts = @json($monthlyTicketCounts);
    
    const colorPalette = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#6c757d', '#fd7e14', '#e83e8c', '#6f42c1', '#20c997', '#d63384'];


    document.addEventListener('DOMContentLoaded', function() {
        const legendContainer = document.getElementById('resolution-legend');
        const dailyBtn = document.getElementById('dailyBtn');
        const weeklyBtn = document.getElementById('weeklyBtn');
        const monthlyBtn = document.getElementById('monthlyBtn');
        const allButtons = [dailyBtn, weeklyBtn, monthlyBtn];
        
        // Add date picker elements
        const dailyFilterContainer = document.getElementById('daily-filter-container');
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        
        // Set date picker defaults
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 29);
        const todayStr = today.toISOString().slice(0, 10);
        const sevenDaysAgo = new Date(today);
        sevenDaysAgo.setDate(today.getDate() - 6);
        const sevenDaysAgoStr = sevenDaysAgo.toISOString().slice(0, 10);
        
        if (startDateInput) {
            startDateInput.setAttribute('max', todayStr);
            endDateInput.setAttribute('max', todayStr);
            startDateInput.value = sevenDaysAgoStr;
            endDateInput.value = todayStr;
        }

        function setActiveButton(activeButton) {
            if (!activeButton) return;
            allButtons.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
            });
            activeButton.classList.remove('btn-secondary');
            activeButton.classList.add('btn-primary');
        }
        
        function tooltipWithPercentage(context) {
            const dataset = context.dataset.data;
            const total = dataset.reduce((a, b) => a + b, 0);
            const value = context.raw;
            const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
            
            if (context.chart.config.type === 'doughnut') {
                if (value === 0) {
                    return '';
                }
                return `${context.label}: ${value} (${percentage}%)`;
            }
            return `${context.label}: ${value}`;
        }
        
        function createTicketStatusChart() {
            if (ticketStatusChart) ticketStatusChart.destroy();
            const ctx = document.getElementById('ticketStatusChart').getContext('2d');
            ticketStatusChart = new Chart(ctx, {
                type: 'doughnut',
                data: ticketStatusChartData,
                options: {
                    responsive:true,
                    plugins:{
                        legend:{display:false},
                        tooltip:{callbacks:{label:tooltipWithPercentage}},
                        datalabels: {
                            color: '#fff',
                            formatter: (value, context) => {
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                if (value === 0) {
                                    return '';
                                }
                                const percentage = (value / total * 100).toFixed(1);
                                return percentage > 0 ? `${percentage}%` : '';
                            },
                            font: {
                                weight: 'bold',
                                size: 14,
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
        
        function createDandoriManChart() {
            if (dandoriManChart) dandoriManChart.destroy();
            const ctx = document.getElementById('dandoriManChart').getContext('2d');
            
            const dandoriManColors = dandoriManChartData.labels.map((_, index) => colorPalette[index % colorPalette.length]);
            
            dandoriManChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: dandoriManChartData.labels,
                    datasets: [{
                        data: dandoriManChartData.datasets[0].data,
                        backgroundColor: dandoriManColors,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive:true,
                    plugins:{
                        legend:{display:false},
                        tooltip:{callbacks:{label:tooltipWithPercentage}},
                        datalabels: {
                            color: '#fff',
                            formatter: (value, context) => {
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                if (value === 0) {
                                    return '';
                                }
                                const percentage = (value / total * 100).toFixed(1);
                                return percentage > 0 ? `${percentage}%` : '';
                            },
                            font: {
                                weight: 'bold',
                                size: 14,
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
        
        function createResolutionChart(type, data, labels, chartLabel) {
            if (resolutionChart) resolutionChart.destroy();
            const ctx = document.getElementById('resolutionChart').getContext('2d');
            
            const resolutionColors = labels.map((_, index) => colorPalette[index % colorPalette.length]);
            
            resolutionChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartLabel,
                        data: data,
                        backgroundColor: resolutionColors,
                        borderColor: resolutionColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: tooltipWithPercentage
                            }
                        }
                    }
                }
            });
            updateLegend(labels, data, resolutionColors);
        }

        function updateLegend(labels, data, colors){
            if (!legendContainer) return;
            legendContainer.innerHTML='';
            labels.forEach((label,index)=>{
                const color = colors[index % colors.length];
                legendContainer.innerHTML += `<li><span style="display:inline-block;width:10px;height:10px;background-color:${color};margin-right:5px;border-radius:50%;"></span>${label}: ${data[index]} tickets</li>`;
            });
        }

        function filterDailyByRange(startDate, endDate) {
            const labels = [];
            const data = [];
            const start = new Date(startDate);
            const end = new Date(endDate);
            const currentDate = new Date(start);

            while (currentDate <= end) {
                const key = currentDate.toISOString().slice(0, 10);
                labels.push(currentDate.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: '2-digit' }));
                data.push(dailyTicketCounts[key] ?? 0);
                currentDate.setDate(currentDate.getDate() + 1);
            }
            createResolutionChart('bar', data, labels, 'Daily Tickets');
        }

        function filterDaily() {
            if (dailyFilterContainer) {
                dailyFilterContainer.style.display = 'block';
                filterDailyByRange(startDateInput.value, endDateInput.value);
            }
        }

        function filterWeekly(){
            if (dailyFilterContainer) {
                dailyFilterContainer.style.display = 'none';
            }
            const weeklyData=[],weeklyLabels=[];
            const today=new Date();today.setHours(0,0,0,0);
            for(let i=3;i>=0;i--){
                const endDate=new Date(today);
                endDate.setDate(today.getDate()-(7*(3-i)));
                const startDate=new Date(endDate);
                startDate.setDate(endDate.getDate()-6);

                const label=`Week ${i+1} (${startDate.toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit'})} - ${endDate.toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit'})})`;
                weeklyLabels.unshift(label);

                let total=0;
                for(const [date,count] of Object.entries(dailyTicketCounts)){
                    const [y,m,d]=date.split('-');
                    const ticketDate=new Date(y,m-1,d);
                    if(ticketDate>=startDate && ticketDate<=endDate) total+=count;
                }
                weeklyData.unshift(total);
            }
            createResolutionChart('bar',weeklyData,weeklyLabels,'Weekly Tickets');
        }

        function filterMonthly(){
            if (dailyFilterContainer) {
                dailyFilterContainer.style.display = 'none';
            }
            const labels=[],data=[];
            const today=new Date();today.setHours(0,0,0,0);
            for(let i=11;i>=0;i--){
                const month=new Date(today);
                month.setMonth(today.getMonth()-i);
                const key=month.toISOString().slice(0,7);
                labels.push(month.toLocaleDateString('id-ID',{month:'long',year:'numeric'}));
                data.push(monthlyTicketCounts[key]??0);
            }
            createResolutionChart('bar',data,labels,'Monthly Tickets');
        }
        
        if (dailyBtn) {
            dailyBtn.addEventListener('click', () => {
                setActiveButton(dailyBtn);
                filterDaily();
            });
        }
        if (weeklyBtn) {
            weeklyBtn.addEventListener('click', () => {
                setActiveButton(weeklyBtn);
                filterWeekly();
            });
        }
        if (monthlyBtn) {
            monthlyBtn.addEventListener('click', () => {
                setActiveButton(monthlyBtn);
                filterMonthly();
            });
        }

        if (startDateInput) {
            startDateInput.addEventListener('change', () => {
                filterDailyByRange(startDateInput.value, endDateInput.value);
            });
        }
        if (endDateInput) {
            endDateInput.addEventListener('change', () => {
                filterDailyByRange(startDateInput.value, endDateInput.value);
            });
        }

        const isView = @json($isView);
        const isAdminOrTeknisi = @json($isAdmin || $isTeknisi);
        
        if (isView || isAdminOrTeknisi) {
            createTicketStatusChart();
            createDandoriManChart();
        }
        
        if(dailyBtn) {
            filterDaily();
        } else if (isAdminOrTeknisi) {
            createResolutionChart('bar',[],[],'');
        }

        if (isView) {
            fetch('{{ route('home.dandories.data') }}')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('dandori-table-body');
                    tableBody.innerHTML = '';
                    data.forEach(ticket => {
                        const row = document.createElement('tr');
                        row.classList.add(`status-${ticket.status.toLowerCase().replace(' ', '-')}`);
                        row.innerHTML = `
                            <td>${ticket.ddcnk_id}</td>
                            <td>${ticket.line_production}</td>
                            <td>${ticket.requestor}</td>
                            <td>${ticket.customer}</td>
                            <td>${ticket.nama_part}</td>
                            <td>${ticket.nomor_part}</td>
                            <td>${ticket.proses}</td>
                            <td>${ticket.mesin}</td>
                            <td>${ticket.qty_pcs}</td>
                            <td>${ticket.planning_shift}</td>
                            <td>${ticket.status}</td>
                            <td>${ticket.assigned_to_name}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                })
                .catch(error => console.error('Error fetching dandori tickets:', error));
        }
    });
</script>
@endsection
