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
                                                    <span style="display:inline-block;width:10px;height:10px;background-color:{{ $ticketStatusChartData['colors'][$key] }};margin-right:5px;border-radius:50%;"></span>
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
                                                    <span style="display:inline-block;width:10px;height:10px;background-color:{{ $dandoriManChartData['colors'][$key] }};margin-right:5px;border-radius:50%;"></span>
                                                    {{ $label }}: {{ $dandoriManChartData['data'][$key] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Chart 3: Daily, Weekly, Monthly --}}
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
        const dailyBtn = document.getElementById('dailyBtn');
        const weeklyBtn = document.getElementById('weeklyBtn');
        const monthlyBtn = document.getElementById('monthlyBtn');
        const allButtons = [dailyBtn, weeklyBtn, monthlyBtn];

        function setActiveButton(activeButton) {
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
            return `${context.label}: ${value} (${percentage}%)`;
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
                        tooltip:{callbacks:{label:tooltipWithPercentage}}
                    }
                }
            });
        }

        function createDandoriManChart() {
            if (dandoriManChart) dandoriManChart.destroy();
            const ctx = document.getElementById('dandoriManChart').getContext('2d');
            dandoriManChart = new Chart(ctx, {
                type: 'doughnut',
                data: dandoriManChartData,
                options: {
                    responsive:true,
                    plugins:{
                        legend:{display:false},
                        tooltip:{callbacks:{label:tooltipWithPercentage}}
                    }
                }
            });
        }

        function createResolutionChart(type, data, labels, colors, chartLabel) {
            if (resolutionChart) resolutionChart.destroy();
            const ctx = document.getElementById('resolutionChart').getContext('2d');
            resolutionChart = new Chart(ctx, {
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
            updateLegend(labels, data, colors);
        }

        function updateLegend(labels, data, colors){
            legendContainer.innerHTML='';
            labels.forEach((label,index)=>{
                legendContainer.innerHTML += `<li><span style="display:inline-block;width:10px;height:10px;background-color:${colors[index]};margin-right:5px;border-radius:50%;"></span>${label}: ${data[index]} tickets</li>`;
            });
        }

        function filterDaily(){
            const labels=[],data=[];
            const today=new Date();today.setHours(0,0,0,0);
            for(let i=6;i>=0;i--){
                const d=new Date();d.setDate(today.getDate()-i);
                const key=d.toISOString().slice(0,10);
                labels.push(d.toLocaleDateString('id-ID',{weekday:'short',day:'2-digit',month:'2-digit'}));
                data.push(dailyTicketCounts[key]??0);
            }
            createResolutionChart('bar',data,labels,chartColors.slice(0,7),'Daily Tickets');
        }

        function filterWeekly(){
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
            createResolutionChart('bar',weeklyData,weeklyLabels,chartColors.slice(0,4),'Weekly Tickets');
        }

        function filterMonthly(){
            const labels=[],data=[];
            const today=new Date();today.setHours(0,0,0,0);
            for(let i=11;i>=0;i--){
                const month=new Date(today);
                month.setMonth(today.getMonth()-i);
                const key=month.toISOString().slice(0,7);
                labels.push(month.toLocaleDateString('id-ID',{month:'long',year:'numeric'}));
                data.push(monthlyTicketCounts[key]??0);
            }
            createResolutionChart('bar',data,labels,monthlyColors,'Monthly Tickets');
        }

        dailyBtn.addEventListener('click', () => {
            setActiveButton(dailyBtn);
            filterDaily();
        });
        weeklyBtn.addEventListener('click', () => {
            setActiveButton(weeklyBtn);
            filterWeekly();
        });
        monthlyBtn.addEventListener('click', () => {
            setActiveButton(monthlyBtn);
            filterMonthly();
        });

        chartCollapse.addEventListener('shown.bs.collapse', () => {
            createTicketStatusChart();
            createDandoriManChart();
            filterDaily();
            setActiveButton(dailyBtn);
        });
    });
</script>
@endsection
