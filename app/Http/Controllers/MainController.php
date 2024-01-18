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
    
        $dateOffset = $firstDayOfMonth->startOfMonth()->dayOfWeek;

        $firstDayOfMonth->subDays($dateOffset);
        $lastDayOfMonth->addDays(42 - ($dateOffset + $lastDayOfMonth->day));

        \Log::info($firstDayOfMonth);
        \Log::info($lastDayOfMonth);
    
        try {
            $mains = \DB::table('main')
                ->select('id', 'checkIn', 'checkOut', 'room')
                ->where('checkIn', '<=', $lastDayOfMonth)
                ->where('checkOut', '>=', $firstDayOfMonth)
                ->get();
    
            // Process data
            $availability = [
                'dayNumber' => [],
                'data'      => [],
            ];
    
            // Initialize array of sets with a fixed size of 42
            $setsSize = 42;
            $sets = array_fill(0, $setsSize, ["J", "G", "A", "K1", "K2", "E"]);
    
            foreach ($mains as $main) {
                \Log::info('Main Object: ' . json_encode($main));
                $checkInDate = \Carbon\Carbon::parse($main->checkIn);
                $checkOutDate = \Carbon\Carbon::parse($main->checkOut);
    
                // Iterate over each day in the range of the main booking
                for ($currentDate = $checkInDate; $currentDate->lte($checkOutDate); $currentDate->addDay()) {
                    $dayIndex = ($currentDate->diffInDays($firstDayOfMonth));
                        
                \Log::info($dayIndex);
                
                if($dayIndex < 42){

                    $dataArray = explode(',', $main->room);
                    foreach ($dataArray as $room) {
                        
                        $roomIndex = array_search($room, $sets[$dayIndex]);
                        if ($roomIndex !== false) {
                            unset($sets[$dayIndex][$roomIndex]);
                        }
                    }



                }
                }
            }
    
            // Create the availability arrays
            foreach ($sets as $index => $rooms) {
                $date = $firstDayOfMonth->copy()->addDays(($index + $setsSize) % $setsSize)->format('d');
                $availability['dayNumber'][] = $date;
                $availability['data'][] = implode(", ", $rooms);
            }
    
            return response()->json($availability);
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
