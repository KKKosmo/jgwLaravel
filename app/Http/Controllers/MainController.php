<?php

// app/Http/Controllers/MainController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Main;
use App\Events\MainCreated;
use App\Events\MainUpdated;
use App\Events\MainDeleted;

class MainController extends Controller
{
    public function index(Request $request)
    {
        $query = Main::query();
    
        // Handle sorting
        $sortColumn = $request->query('sort');
        $sortOrder = $request->query('order', 'asc');
    
        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortOrder);
        }
    
        // Add the raw expression for balance
        $query->select('*', \DB::raw('(full_payment - partial_payment) as balance'));
    
        // Retrieve data
        $mains = $query->get();
    
        return response()->json($mains);
    }
    

    public function show($id)
    {
        $main = Main::findOrFail($id);
        return response()->json($main);
    }

    public function store(Request $request)
    {

        try {
            $main = Main::create($request->all());
            broadcast(new MainCreated($main));
            return response()->json(['message' => 'Record created successfully', 'data' => $main], 201);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['response' => $e], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $main = Main::findOrFail($id);
        $main->update($request->all());
        broadcast(new MainUpdated($main));
        return response()->json(['message' => 'Record updated successfully']);
    }

    public function destroy($id)
    {
        $main = Main::findOrFail($id);
        $main->delete();
        broadcast(new MainDeleted($id));
        return response()->json(['message' => 'Record deleted successfully']);
    }

    

    public function getNewSet(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
        ]);

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');


        $firstDayOfMonth = \Carbon\Carbon::parse($startDate)->firstOfMonth();
        $lastDayOfMonth = \Carbon\Carbon::parse($endDate)->lastOfMonth();

        try {
            $mains = \DB::table('main')
                ->select('id', 'checkIn', 'checkOut', 'room')
                ->where('checkIn', '<=', $lastDayOfMonth)
                ->where('checkOut', '>=', $firstDayOfMonth)
                ->get();

            return response()->json($mains);
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function checkForm(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
            'room' => 'required'
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $userRooms = explode(',', $request->input('room'));
    
        try {
            $databaseRooms = \DB::table('main')
                ->select('room')
                ->where('checkIn', '<=', $endDate)
                ->where('checkOut', '>=', $startDate)
                ->get()
                ->pluck('room')
                ->toArray();
    
            $commonRooms = array_intersect($userRooms, $databaseRooms);
    
            if (!empty($commonRooms)) {
                return response()->json(['available' => 'false']);
            }
    
            return response()->json(['available' => 'true']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}
