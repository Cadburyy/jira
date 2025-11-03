<?php

namespace App\Http\Controllers;

use App\Models\Dandory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $isStandardTeknisi = $user->hasRole('Teknisi') && !$user->hasRole('AdminTeknisi') && !$user->hasRole('Admin');

        $ticketStatusData = Dandory::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $teknisiUsers = User::role('Teknisi')->get();

        $dandorimanData = Dandory::select('assigned_to', DB::raw('count(*) as count'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->get();

        $dandoriManLabels = [];
        $dandoriManCounts = [];
        foreach ($teknisiUsers as $teknisi) {
            $dandoriManLabels[] = $teknisi->name;
            $ticketCount = $dandorimanData->firstWhere('assigned_to', $teknisi->id);
            $dandoriManCounts[] = $ticketCount ? $ticketCount->count : 0;
        }

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

        $dailyQuery = Dandory::where('created_at', '>=', Carbon::now()->subDays(30));
        
        if ($isStandardTeknisi) {
            $dailyQuery->where('assigned_to', $user->id);
        }

        $dailyTicketCounts = $dailyQuery
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');
            
        $monthlyQuery = Dandory::where('created_at', '>=', Carbon::now()->subMonths(12));
        
        if ($isStandardTeknisi) {
            $monthlyQuery->where('assigned_to', $user->id);
        }

        $monthlyTicketCounts = $monthlyQuery
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->get()
            ->pluck('count', 'month');

        return view('home', compact('ticketStatusChartData', 'dandoriManChartData', 'dailyTicketCounts', 'monthlyTicketCounts'));
    }

    public function getDandoriTicketsData()
    {
        $dandories = Dandory::all();
        $tickets = $dandories->map(function ($dandory) {
            $requestor = User::find($dandory->added_by);
            $assignedTo = User::find($dandory->assigned_to);

            return [
                'ddcnk_id' => $dandory->ddcnk_id,
                'line_production' => $dandory->line_production,
                'requestor' => $requestor ? $requestor->name : 'N/A',
                'customer' => $dandory->customer,
                'nama_part' => $dandory->nama_part,
                'nomor_part' => $dandory->nomor_part,
                'proses' => $dandory->proses,
                'mesin' => $dandory->mesin,
                'qty_pcs' => $dandory->qty_pcs,
                'planning_shift' => $dandory->planning_shift,
                'status' => $dandory->status,
                'assigned_to_name' => $assignedTo ? $assignedTo->name : 'N/A',
                'updated_at' => $dandory->updated_at,
            ];
        });

        return response()->json($tickets);
    }

    public function getChartData()
    {
        $ticketStatusData = Dandory::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $teknisiUsers = User::role('Teknisi')->get();

        $dandorimanData = Dandory::select('assigned_to', DB::raw('count(*) as count'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->get();

        $dandoriManLabels = [];
        $dandoriManCounts = [];
        foreach ($teknisiUsers as $teknisi) {
            $dandoriManLabels[] = $teknisi->name;
            $ticketCount = $dandorimanData->firstWhere('assigned_to', $teknisi->id);
            $dandoriManCounts[] = $ticketCount ? $ticketCount->count : 0;
        }

        return response()->json([
            'ticketStatusChartData' => [
                'labels' => ['TO DO', 'IN PROGRESS', 'PENDING', 'FINISH'],
                'data' => [
                    $ticketStatusData['TO DO'] ?? 0,
                    $ticketStatusData['IN PROGRESS'] ?? 0,
                    $ticketStatusData['PENDING'] ?? 0,
                    $ticketStatusData['FINISH'] ?? 0,
                ],
            ],
            'dandoriManChartData' => [
                'labels' => $dandoriManLabels,
                'data' => $dandoriManCounts,
            ]
        ]);
    }
}
