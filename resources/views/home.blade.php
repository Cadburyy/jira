@extends('layouts.app')

@section('content')
{{-- Use a PHP block to get the authenticated user and their roles at the very top --}}
@php
    $user = Auth::user();
    $isAdmin = $user->hasRole('Admin');
    $isRequestor = $user->hasRole('Requestor');
    $isTeknisi = $user->hasRole('Teknisi');
@endphp
<style>
    body, html {
        overflow-x: hidden; /* Hide horizontal scrollbars */
        overflow-y: auto; /* Allow vertical scroll if needed */
        background-color: #f8f9fa; /* A light grey background for a clean look */
    }
    .card-link-hover:hover .card {
        transform: translateY(-5px); /* Lift card slightly */
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; /* Stronger shadow on hover */
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    .card-link-hover .card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    .card {
        border-radius: 1rem; /* Rounded corners for the cards */
        border: none;
    }
    .card-header {
        background-color: #fff; /* White background for card headers */
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }
    .text-primary-dark {
        color: #0056b3; /* A darker shade of blue for icons */
    }
    .accordion-button:not(.collapsed) .fa-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }
    .accordion-button .fa-chevron-down {
        transition: transform 0.3s ease;
    }
    .accordion-button:focus {
        box-shadow: none;
    }
    .accordion-header .btn {
        width: 100%;
        text-align: left;
        padding: 1rem 1.25rem;
        background-color: #fff;
        border: none;
        color: #212529;
        font-weight: bold;
        font-size: 1.25rem;
        position: relative;
    }
</style>

{{-- The main container is now a standard container with top padding to position it at the top --}}
<div class="container py-5">
    <h2 class="text-center mb-4 text-dark font-weight-bold">Welcome, {{ $user->name }} 👋</h2>
    
    {{-- The row for the cards remains centered horizontally --}}
    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center mt-4">

        {{-- Card for 'Manage Users' --}}
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

        {{-- Card for 'Manage Roles' --}}
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
        
        {{-- Card for 'Dandory Tickets' (visible to Admin, Requestor, and Teknisi) --}}
        @if($isAdmin || $isRequestor || $isTeknisi)
        <div class="col-md-4">
            <a href="{{ route('dandories.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-4">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-clipboard-list fa-3x mb-3 text-info"></i> {{-- Changed icon for better representation --}}
                        <h5 class="card-title text-dark">Dandory Tickets</h5>
                        <p class="card-text text-muted">View and manage your dandory tickets.</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>

    {{-- Display logged-in status message below the cards --}}
    @if (session('status'))
        <div class="alert alert-success text-center mt-5" role="alert">
            {{ session('status') }}
        </div>
    @endif

    {{-- Dashboard Charts --}}
    @if($isAdmin || $isTeknisi)
    <div class="row mt-5">
        <div class="accordion" id="dashboardAccordion">
            <div class="accordion-item shadow-sm">
                <h2 class="accordion-header" id="chartsHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#chartCollapse" aria-expanded="false" aria-controls="chartCollapse">
                        <i class="fas fa-chart-pie me-3 text-primary-dark"></i> Dandoriman Ticket Support
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </button>
                </h2>
                <div id="chartCollapse" class="accordion-collapse collapse" aria-labelledby="chartsHeading" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <p class="text-center text-muted mb-4">A visual breakdown of dandoriman ticket data.</p>
                            
                            {{-- Chart 1: Ticket Status --}}
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm p-3">
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
                                                    <span style="display: inline-block; width: 10px; height: 10px; background-color: {{ $ticketStatusChartData['colors'][$key] }}; margin-right: 5px; border-radius: 50%;"></span>
                                                    {{ $label }}: {{ $ticketStatusChartData['data'][$key] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Chart 2: Tickets per Dandoriman --}}
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm p-3">
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
                                                    <span style="display: inline-block; width: 10px; height: 10px; background-color: {{ $dandoriManChartData['colors'][$key] }}; margin-right: 5px; border-radius: 50%;"></span>
                                                    {{ $label }}: {{ $dandoriManChartData['data'][$key] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Chart 3: Average Daily Tickets per Week --}}
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm p-3">
                                    <div class="card-header text-center">
                                        <h5>Daily, Weekly & Monthly Tickets</h5>
                                    </div>
                                    <div class="card-body d-flex flex-column align-items-center">
                                        <div class="btn-group mb-3" role="group">
                                            <button type="button" class="btn btn-sm btn-primary" id="dailyBtn">Daily</button>
                                            <button type="button" class="btn btn-sm btn-secondary" id="weeklyBtn">Weekly</button>
                                            <button type="button" class="btn btn-sm btn-secondary" id="monthlyBtn">Monthly</button>
                                        </div>
                                        <div style="width: 100%;">
                                            <canvas id="resolutionChart"></canvas>
                                        </div>
                                        <ul id="resolution-legend" class="list-unstyled mt-3 w-100"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    const chartColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#6c757d'];
    const monthlyColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#6c757d', '#fd7e14', '#e83e8c', '#6f42c1', '#20c997', '#d63384'];

    document.addEventListener('DOMContentLoaded', function() {
        const chartCollapse = document.getElementById('chartCollapse');
        const legendContainer = document.getElementById('resolution-legend');

        function createTicketStatusChart() {
            if (ticketStatusChart) {
                ticketStatusChart.destroy();
            }
            const ticketStatusCtx = document.getElementById('ticketStatusChart').getContext('2d');
            ticketStatusChart = new Chart(ticketStatusCtx, {
                type: 'doughnut',
                data: ticketStatusChartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(2) + '%' : '0%';
                                    return `${label}: ${value} (${percentage})`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function createDandoriManChart() {
            if (dandoriManChart) {
                dandoriManChart.destroy();
            }
            const dandoriManCtx = document.getElementById('dandoriManChart').getContext('2d');
            dandoriManChart = new Chart(dandoriManCtx, {
                type: 'doughnut',
                data: dandoriManChartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(2) + '%' : '0%';
                                    return `${label}: ${value} (${percentage})`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function createResolutionChart(type, data, labels, colors, yAxisText, chartLabel) {
            if (resolutionChart) {
                resolutionChart.destroy();
            }
            const resolutionChartCtx = document.getElementById('resolutionChart').getContext('2d');
            resolutionChart = new Chart(resolutionChartCtx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartLabel,
                        data: data,
                        backgroundColor: colors,
                        borderColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: yAxisText
                            },
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    }
                }
            });
            updateLegend(labels, data, colors, chartLabel);
        }

        function updateLegend(labels, data, colors, chartLabel) {
            legendContainer.innerHTML = '';
            labels.forEach((label, index) => {
                const li = document.createElement('li');
                const legendText = `${label}: ${data[index]} tickets`;
                
                li.innerHTML = `
                    <span style="display: inline-block; width: 10px; height: 10px; background-color: ${colors[index]}; margin-right: 5px; border-radius: 50%;"></span>
                    ${legendText}
                `;
                legendContainer.appendChild(li);
            });
        }

        function filterDaily() {
            const labels = [];
            const data = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);


            for (let i = 6; i >= 0; i--) {
                const date = new Date();
                date.setDate(today.getDate() - i);
                const dateString = date.toISOString().slice(0, 10);
                

                labels.push(date.toLocaleDateString('id-ID', {weekday: 'short', day: '2-digit', month: '2-digit'}));

                data.push(dailyTicketCounts[dateString] ?? 0);
            }
            
            createResolutionChart('bar', data, labels, chartColors.slice(0, 7), 'Total Tickets', 'Daily Tickets');
        }

        function filterWeekly() {
            const weeklyData = [];
            const weeklyLabels = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for (let i = 3; i >= 0; i--) {
                const endDate = new Date(today);
                endDate.setDate(today.getDate() - (7 * (3-i)));
                const startDate = new Date(endDate);
                startDate.setDate(endDate.getDate() - 6);

                const label = `Week ${i + 1} (${startDate.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit'})} - ${endDate.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit'})})`;
                weeklyLabels.unshift(label);

                let totalCount = 0;
                const dailyData = Object.entries(dailyTicketCounts);
                for(const [date, count] of dailyData) {
                    const ticketDate = new Date(date);
                    if (ticketDate >= startDate && ticketDate <= endDate) {
                        totalCount += count;
                    }
                }
                weeklyData.unshift(totalCount);
            }
            const weeklyColors = chartColors.slice(0, 4);
            createResolutionChart('bar', weeklyData, weeklyLabels, weeklyColors, 'Total Tickets', 'Weekly Tickets');
        }

        function filterMonthly() {
            const labels = [];
            const data = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for(let i = 11; i >= 0; i--) {
                const month = new Date(today);
                month.setMonth(today.getMonth() - i);
                const monthString = month.toISOString().slice(0, 7);
                
                labels.push(month.toLocaleDateString('id-ID', {month: 'long', year: 'numeric'}));
                data.push(monthlyTicketCounts[monthString] ?? 0);
            }

            createResolutionChart('bar', data, labels, monthlyColors, 'Total Tickets', 'Monthly Tickets');
        }

        document.getElementById('dailyBtn').addEventListener('click', () => {
            filterDaily();
            document.getElementById('dailyBtn').classList.remove('btn-secondary');
            document.getElementById('dailyBtn').classList.add('btn-primary');
            document.getElementById('weeklyBtn').classList.remove('btn-primary');
            document.getElementById('weeklyBtn').classList.add('btn-secondary');
            document.getElementById('monthlyBtn').classList.remove('btn-primary');
            document.getElementById('monthlyBtn').classList.add('btn-secondary');
        });

        document.getElementById('weeklyBtn').addEventListener('click', () => {
            filterWeekly();
            document.getElementById('dailyBtn').classList.remove('btn-primary');
            document.getElementById('dailyBtn').classList.add('btn-secondary');
            document.getElementById('weeklyBtn').classList.remove('btn-secondary');
            document.getElementById('weeklyBtn').classList.add('btn-primary');
            document.getElementById('monthlyBtn').classList.remove('btn-primary');
            document.getElementById('monthlyBtn').classList.add('btn-secondary');
        });

        document.getElementById('monthlyBtn').addEventListener('click', () => {
            filterMonthly();
            document.getElementById('dailyBtn').classList.remove('btn-primary');
            document.getElementById('dailyBtn').classList.add('btn-secondary');
            document.getElementById('weeklyBtn').classList.remove('btn-primary');
            document.getElementById('weeklyBtn').classList.add('btn-secondary');
            document.getElementById('monthlyBtn').classList.remove('btn-secondary');
            document.getElementById('monthlyBtn').classList.add('btn-primary');
        });

        chartCollapse.addEventListener('shown.bs.collapse', () => {
            createTicketStatusChart();
            createDandoriManChart();
            filterDaily();
        });
    });
</script>
@endsection