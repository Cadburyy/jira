<?php

namespace App\Http\Controllers;

use App\Models\Dandory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DandoryController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');

        $this->middleware('permission:dandory-list|dandory-create|dandory-edit', ['only' => ['index', 'show']]);
        $this->middleware('permission:dandory-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:dandory-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $user = Auth::user();
        $query = Dandory::latest();

        $users = User::with('roles')->get();

        if ($user->hasRole('Admin')) {
            $activeDandories = $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING'])->get();
            $finishedDandories = Dandory::where('status', 'FINISH')->get();
            return view('dandories.index', compact('activeDandories', 'finishedDandories', 'users'));
        }

        if ($user->hasRole('Requestor')) {
            $activeDandories = $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING'])->where('added_by', $user->id)->get();
            $finishedDandories = Dandory::where('status', 'FINISH')->where('added_by', $user->id)->get();
            return view('dandories.index', compact('activeDandories', 'finishedDandories', 'users'));
        }

        if ($user->hasRole('Teknisi')) {
            $activeDandories = $query->whereIn('status', ['TO DO', 'IN PROGRESS', 'PENDING'])->where('assigned_to', $user->id)->get();
            $finishedDandories = Dandory::where('status', 'FINISH')->where('assigned_to', $user->id)->get();
            return view('dandories.index', compact('activeDandories', 'finishedDandories', 'users'));
        }

        return view('dandories.index', [
            'activeDandories' => collect(),
            'finishedDandories' => collect(),
            'users' => $users
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
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            abort(403);
        }

        $allUsers = User::all();
        $teknisiUsers = $allUsers->filter(fn($u) => $u->hasRole('Teknisi'));
        
        return view('dandories.edit', compact('dandory', 'teknisiUsers'));
    }

    public function update(Request $request, Dandory $dandory)
    {
        if (!Auth::user()->hasRole('Admin')) {
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

        if (!$user->hasRole('Admin') && $dandory->assigned_to != $user->id) {
            abort(403, 'You can only update the status of tickets assigned to you.');
        }

        $newStatus = $request->input('status');
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

    // New method for updating notes
    public function updateNotes(Request $request, Dandory $dandory)
    {
        $user = Auth::user();

        // Check for permission and ticket status
        if ($user->hasRole('Admin') || ($user->hasRole('Teknisi') && $dandory->status == 'PENDING' && $dandory->assigned_to == $user->id)) {
            $validatedData = $request->validate([
                'notes' => 'nullable|string',
            ]);

            $dandory->update($validatedData);
            
            return redirect()->route('dandories.show', $dandory)->with('success', 'Notes updated successfully!');
        }

        // Abort if the user does not have permission
        abort(403, 'You do not have permission to update notes on this ticket.');
    }

    public function assign(Request $request, Dandory $dandory)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403);
        }

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        $dandory->update(['assigned_to' => $request->assigned_to]);

        return response()->json(['success' => true, 'message' => 'Ticket assigned successfully.']);
    }

    public function destroy(Dandory $dandory)
    {
        $dandory->delete();
        return redirect()->route('dandories.index')->with('success', 'Dandory ticket deleted successfully!');
    }
}