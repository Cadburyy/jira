<?php

namespace App\Http\Controllers;

use App\Models\Dandory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\TicketAssignedMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DandoryController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');

        $this->middleware('permission:dandory-list|dandory-create|dandory-edit', ['only' => ['index', 'show']]);
        $this->middleware('permission:dandory-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:dandory-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Dandory::latest();
        $dateFilter = $request->input('date', Carbon::today()->toDateString());

        $users = User::with('roles')->get();

        $teknisiUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Teknisi');
        })->get();

        $dailyCounts = Dandory::whereIn('assigned_to', $teknisiUsers->pluck('id'))
                             ->whereDate('created_at', $dateFilter)
                             ->select('assigned_to', DB::raw('count(*) as count'))
                             ->groupBy('assigned_to')
                             ->pluck('count', 'assigned_to');

        $sortedTeknisiUsers = $teknisiUsers->map(function ($teknisiUser) use ($dailyCounts) {
            $teknisiUser->daily_count = $dailyCounts[$teknisiUser->id] ?? 0;
            return $teknisiUser;
        });

        $sortedTeknisiUsers = $sortedTeknisiUsers->sort(function ($a, $b) {
            if ($a->daily_count === $b->daily_count) {
                return $a->name <=> $b->name;
            }
            return $b->daily_count <=> $a->daily_count;
        })->values();

        if ($user->hasRole('Admin') || $user->hasRole('AdminTeknisi')) {
            $activeDandories = $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING'])->get();
            $finishedDandories = Dandory::where('status', 'FINISH')->get();
            return view('dandories.index', compact('activeDandories', 'finishedDandories', 'users', 'sortedTeknisiUsers', 'dailyCounts', 'dateFilter'));
        }

        if ($user->hasRole('Requestor')) {
            $activeDandories = $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING'])->where('added_by', $user->id)->get();
            $finishedDandories = Dandory::where('status', 'FINISH')->where('added_by', $user->id)->get();
            return view('dandories.index', compact('activeDandories', 'finishedDandories', 'users', 'sortedTeknisiUsers', 'dailyCounts', 'dateFilter'));
        }

        if ($user->hasRole('Teknisi')) {
            $activeDandories = $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING'])->get();
            $finishedDandories = Dandory::where('status', 'FINISH')->where('assigned_to', $user->id)->get();
            return view('dandories.index', compact('activeDandories', 'finishedDandories', 'users', 'sortedTeknisiUsers', 'dailyCounts', 'dateFilter'));
        }

        return view('dandories.index', [
            'activeDandories' => collect(),
            'finishedDandories' => collect(),
            'users' => $users,
            'sortedTeknisiUsers' => $sortedTeknisiUsers,
            'dailyCounts' => $dailyCounts,
            'dateFilter' => $dateFilter,
        ]);
    }

    public function create()
    {
        return view('dandories.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'requestor' => 'required|string|max:255',
            'customer' => 'required|string|max:255',
            'nama_part' => 'required|string|max:255',
            'nomor_part' => 'required|string|max:255',
            'proses' => 'required|string|max:255',
            'mesin' => 'required|string|max:255',
            'qty_pcs' => 'required|integer|min:1',
            'planning_shift' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'line_production' => 'required|string|max:255',
        ]);
        
        $lastTicket = Dandory::latest()->first();
        $lastId = $lastTicket ? $lastTicket->id : 0;
        $newId = $lastId + 1;
        
        $validatedData['ddcnk_id'] = 'DDCNK-' . $newId;
        $validatedData['added_by'] = Auth::id();
        
        Dandory::create($validatedData);

        return redirect()->route('dandories.index')->with('success', 'Dandory ticket created successfully!');
    }

    public function show(Dandory $dandory)
    {
        $user = Auth::user();
        if ($user->hasRole('Requestor') && $dandory->added_by !== $user->id) {
            abort(403);
        }

        return view('dandories.show', compact('dandory'));
    }

    public function edit(Dandory $dandory)
    {
        if (!Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('AdminTeknisi')) {
            abort(403);
        }

        $allUsers = User::all();
        $teknisiUsers = $allUsers->filter(fn($u) => $u->hasRole('Teknisi'));
        
        return view('dandories.edit', compact('dandory', 'teknisiUsers'));
    }

    public function update(Request $request, Dandory $dandory)
    {
        if (!Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('AdminTeknisi')) {
            abort(403);
        }

        $validatedData = $request->validate([
            'requestor' => 'required|string|max:255',
            'customer' => 'required|string|max:255',
            'nama_part' => 'required|string|max:255',
            'nomor_part' => 'required|string|max:255',
            'proses' => 'required|string|max:255',
            'mesin' => 'required|string|max:255',
            'qty_pcs' => 'required|integer|min:1',
            'planning_shift' => 'required|string|max:255',
            'status' => 'required|in:TO DO,IN PROGRESS,FINISH,PENDING',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|integer',
            'line_production' => 'required|string|max:255',
        ]);
        
        $dandory->update($validatedData);

        return redirect()->route('dandories.show', $dandory)->with('success', 'Dandory ticket updated successfully!');
    }

    public function updateStatus(Request $request, Dandory $dandory)
    {
        $request->validate([
            'status' => 'required|in:TO DO,IN PROGRESS,FINISH,PENDING',
        ]);

        $user = Auth::user();
        $newStatus = $request->input('status');

        // Allow Admins and AdminTeknisi to update any ticket status.
        // Allow a Teknisi to update the status of tickets assigned to them.
        if (!($user->hasRole('Admin') || $user->hasRole('AdminTeknisi')) && $dandory->assigned_to != $user->id) {
            return response()->json(['success' => false, 'error' => 'You can only update the status of tickets assigned to you.'], 403);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus == 'IN PROGRESS') {
            $updateData['check_in'] = Carbon::now();
        } elseif ($newStatus == 'FINISH') {
            $updateData['check_out'] = Carbon::now();
        } elseif ($newStatus == 'TO DO') {
            $updateData['check_in'] = null;
            $updateData['check_out'] = null;
        }

        $dandory->update($updateData);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function updatePlanning(Request $request, Dandory $dandory)
    {
        $request->validate([
            'field' => 'required|in:check_in,check_out,planning_shift',
            'value' => 'nullable|string',
        ]);

        if (Auth::user()->hasRole('Teknisi') && $dandory->assigned_to != Auth::id()) {
            return response()->json(['success' => false, 'error' => 'You can only update planning for tickets assigned to you.'], 403);
        }

        $field = $request->input('field');
        $value = $request->input('value');
        
        $dandory->update([$field => $value]);
        
        return response()->json(['success' => true, 'message' => 'Planning setting updated successfully.']);
    }

    public function updateNotes(Request $request, Dandory $dandory)
    {
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasRole('AdminTeknisi') || ($user->hasRole('Teknisi') && $dandory->status == 'PENDING' && $dandory->assigned_to == $user->id)) {
            $validatedData = $request->validate([
                'notes' => 'nullable|string',
            ]);

            $dandory->update($validatedData);
            
            return redirect()->route('dandories.show', $dandory)->with('success', 'Notes updated successfully!');
        }

        abort(403, 'You do not have permission to update notes on this ticket.');
    }

    public function assign(Request $request, Dandory $dandory)
    {
        $user = Auth::user();
        $assignedToId = $request->input('assigned_to');

        // Allow Admins and AdminTeknisi to assign to anyone.
        // A Teknisi can only assign themselves to a ticket, and only if it's currently unassigned.
        if (($user->hasRole('Teknisi') && ($dandory->assigned_to !== null || $assignedToId != $user->id)) && 
            !($user->hasRole('Admin') || $user->hasRole('AdminTeknisi'))) {
            return response()->json(['success' => false, 'error' => 'You cannot assign a ticket that is already assigned to someone else or to a different user.'], 403);
        }

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        $dandory->update(['assigned_to' => $assignedToId]);

        $assignedToUser = User::find($assignedToId);
        
        if ($assignedToUser) {
            try {
                Mail::to($assignedToUser->email)->send(new TicketAssignedMail($dandory, $assignedToUser));
                return response()->json(['success' => true, 'message' => 'Ticket assigned successfully. Email notification sent.']);
            } catch (\Exception $e) {
                \Log::error('Failed to send email: ' . $e->getMessage());
                return response()->json(['success' => true, 'message' => 'Ticket assigned successfully, but failed to send email notification.']);
            }
        }

        return response()->json(['success' => true, 'message' => 'Ticket assignment updated successfully.']);
    }

    public function destroy(Dandory $dandory)
    {
        $dandory->delete();
        return redirect()->route('dandories.index')->with('success', 'Dandory ticket deleted successfully!');
    }

    public function download(Request $request, $type)
    {
        if (!Auth::user()->hasAnyRole(['Admin', 'AdminTeknisi'])) {
            abort(403);
        }

        $query = Dandory::query();

        if ($type === 'wip') {
            $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING']);

            if ($request->has('select_all')) {
            } else {
                if ($request->filled('creation_date')) {
                    $query->whereDate('created_at', $request->input('creation_date'));
                }
                if ($request->has('statuses')) {
                    $query->whereIn('status', $request->input('statuses'));
                }
            }
        } elseif ($type === 'finished') {
            $query->where('status', 'FINISH');

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('updated_at', [$request->input('from_date'), $request->input('to_date')]);
            }
        }

        $tickets = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dandory_tickets_' . $type . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, [
                'ID', 'Key', 'Line Production', 'Requestor', 'Customer', 'Part Name', 'Part No', 
                'Process', 'Machine', 'Qty', 'Planning Shift', 'Status', 'Check In', 'Check Out', 
                'Total Time Needed (minutes)', 'Notes', 'Assigned To', 'Created At', 'Updated At'
            ]);

            foreach ($tickets as $ticket) {
                $totalTime = 'N/A';
                if ($ticket->check_in && $ticket->check_out) {
                    $checkIn = Carbon::parse($ticket->check_in);
                    $checkOut = Carbon::parse($ticket->check_out);
                    $totalTime = $checkOut->diffInMinutes($checkIn);
                }

                fputcsv($file, [
                    $ticket->id,
                    $ticket->ddcnk_id,
                    $ticket->line_production,
                    $ticket->addedByUser->name ?? 'N/A',
                    $ticket->customer,
                    $ticket->nama_part,
                    $ticket->nomor_part,
                    $ticket->proses,
                    $ticket->mesin,
                    $ticket->qty_pcs,
                    $ticket->planning_shift,
                    $ticket->status,
                    $ticket->check_in,
                    $ticket->check_out,
                    $totalTime,
                    $ticket->notes,
                    $ticket->assignedUser->name ?? 'N/A',
                    $ticket->created_at,
                    $ticket->updated_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}