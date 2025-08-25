<?php

namespace App\Http\Controllers;

use App\Models\Dandory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Set the default timezone for Indonesia (WIB)
        date_default_timezone_set('Asia/Jakarta');
        
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Query to get ticket counts by status
        $ticketStatusData = Dandory::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        // Get all users with the 'Teknisi' role
        $teknisiUsers = User::role('Teknisi')->get();

        // Query to get tickets assigned to each Dandoriman (Teknisi)
        $dandorimanData = Dandory::select('assigned_to', DB::raw('count(*) as count'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->get();
        
        // Combine the list of all technicians with the ticket count data
        $dandoriManLabels = [];
        $dandoriManCounts = [];
        foreach ($teknisiUsers as $teknisi) {
            $dandoriManLabels[] = $teknisi->name;
            $ticketCount = $dandorimanData->firstWhere('assigned_to', $teknisi->id);
            $dandoriManCounts[] = $ticketCount ? $ticketCount->count : 0;
        }

        // Prepare data for the first chart (Ticket Status)
        $ticketStatusChartData = [
            'labels' => ['TO DO', 'IN PROGRESS', 'PENDING', 'FINISH'],
            'data' => [
                $ticketStatusData['TO DO'] ?? 0,
                $ticketStatusData['IN PROGRESS'] ?? 0,
                $ticketStatusData['PENDING'] ?? 0,
                $ticketStatusData['FINISH'] ?? 0,
            ],
            'colors' => ['#ff0015ff', '#ffc400ff', '#a5a5a5ff', '#00b463ff']
        ];
        
        // Dynamically generate colors for each dandoriman
        $dandoriManColors = [];
        for ($i = 0; $i < count($dandoriManLabels); $i++) {
            $r = mt_rand(0, 255);
            $g = mt_rand(0, 255);
            $b = mt_rand(0, 255);
            $dandoriManColors[] = "rgba($r, $g, $b, 1)";
        }

        $dandoriManChartData = [
            'labels' => $dandoriManLabels,
            'data' => $dandoriManCounts,
            'colors' => $dandoriManColors,
        ];
        
        // --- Get raw daily ticket counts for the last 30 days and pass to the view ---
        $dailyTicketCounts = Dandory::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');
            
        // --- Get monthly ticket counts for the last 12 months ---
        $monthlyTicketCounts = Dandory::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        return view('home', compact('ticketStatusChartData', 'dandoriManChartData', 'dailyTicketCounts', 'monthlyTicketCounts'));
    }
}
