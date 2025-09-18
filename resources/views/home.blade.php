@extends('layouts.app')

@section('content')
@php
$user = Auth::user();
$isAdmin = $user->hasRole('Admin');
$isRequestor = $user->hasRole('Requestor');
$isTeknisi = $user->hasRole('Teknisi');
$isView = $user->hasRole('Views');
$isTeknisiAdmin = $user->hasRole('AdminTeknisi');
@endphp

<style>
    body, html {
        overflow-x: hidden;
        overflow-y: auto;
    }
    .card-link-hover:hover .card {
        transform: scale(1.03);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.2) !important;
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
    tr[data-status="TO DO"] { background-color: #f8d7da !important; }
    tr[data-status="IN PROGRESS"] { background-color: #fff3cd !important; }
    tr[data-status="PENDING"] { background-color: #e2e3e5 !important; }
    tr[data-status] td { background-color: inherit; }
    strong { font-weight: bold; }

    .chart-container {
        width: 100%;
        max-width: 250px;
    }
</style>

<div class="container py-3">
    <h2 class="text-center mb-2 text-dark"><strong>Welcome, {{ $user->name }} 👋</strong></h2>

    <div class="row row-cols-1 row-cols-md-3 g-3 justify-content-center mt-3">        
        @if($isAdmin || $isRequestor || $isTeknisi || $isTeknisiAdmin)
        <div class="col-md-4">
            <a href="{{ route('dandories.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-3">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-clipboard-list fa-3x mb-2 text-info"></i>
                        <h5 class="card-title text-dark"><strong>Dandory Tickets</strong></h5>
                        <p class="card-text text-muted"><strong>View and manage your dandory tickets.</strong></p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
    @if (session('status'))
        <div class="alert alert-success text-center mt-3" role="alert">
            <strong>{{ session('status') }}</strong>
        </div>
    @endif
    <h3 class="text-center mt-4 text-dark"><strong>Dashboard</strong></h3>
    <div class="row mt-4">
        <div class="row g-3 justify-content-center">
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="card h-100 p-3 shadow-sm">
                    <div class="card-header text-center">
                        <h5><strong>Status Ticket</strong></h5>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="row w-100 g-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-6 d-flex justify-content-center align-items-center">
                                <div class="chart-container">
                                    <canvas id="ticketStatusChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-center">
                                <ul id="ticket-status-legend" class="list-unstyled mt-2 w-100">
                                    @foreach($ticketStatusChartData['labels'] as $key => $label)
                                    <li>
                                        <span style="display:inline-block;width:10px;height:10px;background-color:{{ $ticketStatusChartData['colors'][$key] }};margin-right:5px;border-radius:50%;"></span>
                                        <strong>{{ $label }}: {{ $ticketStatusChartData['data'][$key] }}</strong>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(!$isView)
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="card h-100 p-3 shadow-sm">
                    <div class="card-header text-center">
                        <h5><strong>Dandoriman Ticket</strong></h5>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center">
                        <div class="row w-100 g-3 d-flex align-items-center justify-content-center">
                            <div class="col-md-6 d-flex justify-content-center align-items-center">
                                <div class="chart-container">
                                    <canvas id="dandoriManChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-center">
                                <ul id="dandori-man-legend" class="list-unstyled mt-2 w-100">
                                    @foreach($dandoriManChartData['labels'] as $key => $label)
                                    <li>
                                        <span style="display:inline-block;width:10px;height:10px;background-color:{{ $dandoriManChartData['colors'][$key] }};margin-right:5px;border-radius:50%;"></span>
                                        <strong>{{ $label }}: {{ $dandoriManChartData['data'][$key] }}</strong>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(!$isView)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card h-100 p-3 shadow-sm">
                <div class="card-header text-center">
                    <h5><strong>Daily, Weekly & Monthly Tickets</strong></h5>
                </div>
                <div class="card-body">
                    <div class="btn-group mb-2 d-flex justify-content-center" role="group">
                        <button type="button" class="btn btn-sm btn-primary" id="dailyBtn"><strong>Daily</strong></button>
                        <button type="button" class="btn btn-sm btn-secondary" id="weeklyBtn"><strong>Weekly</strong></button>
                        <button type="button" class="btn btn-sm btn-secondary" id="monthlyBtn"><strong>Monthly</strong></button>
                    </div>
                    <div id="daily-filter-container" class="mb-2">
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" id="start-date" class="form-control form-control-sm" title="Start Date">
                            </div>
                            <div class="col">
                                <input type="date" id="end-date" class="form-control form-control-sm" title="End Date">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="resolutionChart"></canvas>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <ul id="resolution-legend" class="list-unstyled w-100"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isView)
    <div class="row mt-4">
        <h3 class="text-center mb-3 text-dark"><strong>WIP Dandory Tickets</strong></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-dark"><strong>ID</strong></th>
                        <th class="text-dark"><strong>Line Production</strong></th>
                        <th class="text-dark"><strong>Requestor</strong></th>
                        <th class="text-dark"><strong>Customer</strong></th>
                        <th class="text-dark"><strong>Nama Part</strong></th>
                        <th class="text-dark"><strong>Nomor Part</strong></th>
                        <th class="text-dark"><strong>Proses</strong></th>
                        <th class="text-dark"><strong>Mesin</strong></th>
                        <th class="text-dark"><strong>Qty (pcs)</strong></th>
                        <th class="text-dark"><strong>Planning Shift</strong></th>
                        <th class="text-dark"><strong>Status</strong></th>
                        <th class="text-dark"><strong>Dandori Man</strong></th>
                    </tr>
                </thead>
                <tbody id="dandori-table-body">
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<audio id="newTicketAudio" src="{{ asset('audio/new-ticket-alert.mp3') }}" preload="auto"></audio>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    let ticketStatusChart, dandoriManChart, resolutionChart;
    const ticketStatusChartColors = ['#ff0015ff', '#ffc400ff', '#a5a5a5ff', '#00b463ff'];
    const dandoriManChartColors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6610f2', '#6c757d', '#fd7e14', '#e83e8c', '#6f42c1', '#20c997', '#d63384'];

    const centerTextPlugin = {
        id: 'centerTextPlugin',
        beforeDraw: function(chart) {
            const activeElements = chart.tooltip.getActiveElements();
            if (activeElements && activeElements.length > 0) {
                const activeElement = activeElements[0];
                const dataIndex = activeElement.index;
                const value = chart.data.datasets[0].data[dataIndex];
                const label = chart.data.labels[dataIndex];
                const total = chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;

                const ctx = chart.ctx;
                ctx.save();
                ctx.font = 'bolder 1.5rem sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;

                ctx.fillStyle = chart.data.datasets[0].backgroundColor[dataIndex];
                ctx.fillText(`${percentage}%`, centerX, centerY - 15);

                ctx.font = '1rem sans-serif';
                ctx.fillStyle = '#6c757d';
                ctx.fillText(label, centerX, centerY + 15);
                ctx.restore();
            }
        }
    };

    Chart.register(centerTextPlugin);

    function createDoughnutChart(chartId, chartData, chartColors) {
        const ctx = document.getElementById(chartId).getContext('2d');
        if (Chart.getChart(chartId)) {
            Chart.getChart(chartId).destroy();
        }
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: chartColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false,
                    },
                },
            }
        });
    }

    // Helper function to update the chart data and legend
    function updateChart(chart, newData, chartColors) {
        chart.data.datasets[0].data = newData.data;
        chart.data.labels = newData.labels;
        chart.data.datasets[0].backgroundColor = chartColors; // Keep colors consistent
        chart.update();

        // Check if there is a corresponding legend element
        if (chart.canvas.id === 'ticketStatusChart') {
            updateLegend(newData.labels, newData.data, chartColors, document.getElementById('ticket-status-legend'));
        } else if (chart.canvas.id === 'dandoriManChart') {
            updateLegend(newData.labels, newData.data, chartColors, document.getElementById('dandori-man-legend'));
        }
    }

    function updateLegend(labels, data, colors, container) {
        if (!container) return;
        container.innerHTML = '';
        labels.forEach((label, index) => {
            const color = colors[index % colors.length];
            container.innerHTML += `<li><span style="display:inline-block;width:10px;height:10px;background-color:${color};margin-right:5px;border-radius:50%;"></span><strong>${label}: ${data[index]} tickets</strong></li>`;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const legendContainer = document.getElementById('resolution-legend');
        const dailyBtn = document.getElementById('dailyBtn');
        const weeklyBtn = document.getElementById('weeklyBtn');
        const monthlyBtn = document.getElementById('monthlyBtn');
        const allButtons = [dailyBtn, weeklyBtn, monthlyBtn];
        const dailyFilterContainer = document.getElementById('daily-filter-container');
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
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
            updateLegend(labels, data, resolutionColors, legendContainer);
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

        function filterWeekly() {
            if (dailyFilterContainer) {
                dailyFilterContainer.style.display = 'none';
            }
            const weeklyData = [], weeklyLabels = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            for (let i = 3; i >= 0; i--) {
                const endDate = new Date(today);
                endDate.setDate(today.getDate() - (7 * (3 - i)));
                const startDate = new Date(endDate);
                startDate.setDate(endDate.getDate() - 6);
                const label = `Week ${i + 1} (${startDate.toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit'})} - ${endDate.toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit'})})`;
                weeklyLabels.unshift(label);
                let total = 0;
                for (const [date, count] of Object.entries(dailyTicketCounts)) {
                    const [y, m, d] = date.split('-');
                    const ticketDate = new Date(y, m - 1, d);
                    if (ticketDate >= startDate && ticketDate <= endDate) total += count;
                }
                weeklyData.unshift(total);
            }
            createResolutionChart('bar', weeklyData, weeklyLabels, 'Weekly Tickets');
        }

        function filterMonthly() {
            if (dailyFilterContainer) {
                dailyFilterContainer.style.display = 'none';
            }
            const labels = [], data = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            for (let i = 11; i >= 0; i--) {
                const month = new Date(today);
                month.setMonth(today.getMonth() - i);
                const key = month.toISOString().slice(0, 7);
                labels.push(month.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }));
                data.push(monthlyTicketCounts[key] ?? 0);
            }
            createResolutionChart('bar', data, labels, 'Monthly Tickets');
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
        const isAdminOrTeknisi = @json($isAdmin || $isTeknisi || $isTeknisiAdmin);
        const isRequestor = @json($isRequestor);
        
        let initialTicketStatusChartData = {
            labels: @json($ticketStatusChartData['labels']),
            data: @json($ticketStatusChartData['data'])
        };
        let initialDandoriManChartData = {
            labels: @json($dandoriManChartData['labels']),
            data: @json($dandoriManChartData['data'])
        };

        if (isView || isAdminOrTeknisi || isRequestor) {
            ticketStatusChart = createDoughnutChart('ticketStatusChart', initialTicketStatusChartData, ticketStatusChartColors);
        }
        if (isAdminOrTeknisi || isRequestor) {
            dandoriManChart = createDoughnutChart('dandoriManChart', initialDandoriManChartData, dandoriManChartColors);
        }

        if (dailyBtn) {
            filterDaily();
        } else if (isAdminOrTeknisi || isRequestor) {
            createResolutionChart('bar', [], [], '');
        }

        if (isView) {
            let lastTableState = "";
            let lastChartState = "";
            const tableBody = document.getElementById('dandori-table-body');
            const newTicketAudio = document.getElementById('newTicketAudio');
            const discoOverlay = document.getElementById('disco-overlay');

            async function fetchAndUpdateData() {
                try {
                    const [ticketsResponse, chartsResponse] = await Promise.all([
                        fetch('{{ route('home.dandories.data') }}'),
                        fetch('{{ route('home.charts.data') }}')
                    ]);
                    
                    const ticketsData = await ticketsResponse.json();
                    const chartsData = await chartsResponse.json();
                    
                    const filteredTicketsData = ticketsData.filter(ticket => ticket.status !== 'FINISH');
                    
                    const currentTableState = JSON.stringify(filteredTicketsData.map(ticket => ({
                        id: ticket.ddcnk_id,
                        status: ticket.status,
                        assigned_to: ticket.assigned_to_name
                    })).sort((a,b) => a.id.localeCompare(b.id)));
                    
                    const currentChartState = JSON.stringify(chartsData);

                    if (lastTableState !== "" && (currentTableState !== lastTableState || currentChartState !== lastChartState)) {
                        newTicketAudio.play();
                        discoOverlay.classList.add('active'); 
                        setTimeout(() => {
                            discoOverlay.classList.remove('active');
                        }, 15000); 
                    }

                    // Update Table
                    tableBody.innerHTML = '';
                    filteredTicketsData.forEach(ticket => {
                        const row = document.createElement('tr');
                        row.setAttribute('data-status', ticket.status);
                        row.innerHTML = `
                            <td><strong>${ticket.ddcnk_id}</strong></td>
                            <td><strong>${ticket.line_production}</strong></td>
                            <td><strong>${ticket.requestor}</strong></td>
                            <td><strong>${ticket.customer}</strong></td>
                            <td><strong>${ticket.nama_part}</strong></td>
                            <td><strong>${ticket.nomor_part}</strong></td>
                            <td><strong>${ticket.proses}</strong></td>
                            <td><strong>${ticket.mesin}</strong></td>
                            <td><strong>${ticket.qty_pcs}</strong></td>
                            <td><strong>${ticket.planning_shift}</strong></td>
                            <td><strong>${ticket.status}</strong></td>
                            <td><strong>${ticket.assigned_to_name}</strong></td>
                        `;
                        tableBody.appendChild(row);
                    });

                    // Update Charts
                    if (ticketStatusChart) {
                        updateChart(ticketStatusChart, chartsData.ticketStatusChartData, ticketStatusChartColors);
                    }
                    if (dandoriManChart) {
                        updateChart(dandoriManChart, chartsData.dandoriManChartData, dandoriManChartColors);
                    }

                    lastTableState = currentTableState;
                    lastChartState = currentChartState;

                } catch (error) {
                    console.error('Error fetching dashboard data:', error);
                }
            }

            // Initial fetch on page load
            fetchAndUpdateData();
            setInterval(fetchAndUpdateData, 30000);
        }
    });
</script>
@endsection