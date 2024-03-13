<?php

// app/Http/Controllers/MainController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Main;
use App\Models\Event;
use App\Events\MainCreated;
use App\Events\MainUpdated;
use App\Events\MainDeleted;
use Illuminate\Support\Arr;


class MainController extends Controller
{

    public function index(Request $request)
    {
        $query = Main::query();
    
        // Sorting
        $sortColumn = $request->query('sort');
        $sortOrder = $request->query('order', 'asc');
    
        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortOrder);
        }
    
        // Filter by name
        $nameFilter = $request->query('name');
        if ($nameFilter) {
            $query->where('name', 'like', '%' . $nameFilter . '%');
        }
    
        // Filter by start date
        $startDateFilter = $request->query('startDate');
        if ($startDateFilter) {
            $query->where('checkIn', '>=', $startDateFilter);
        }
    
        // Filter by end date
        $endDateFilter = $request->query('endDate');
        if ($endDateFilter) {
            $query->where('checkIn', '<=', $endDateFilter);
        }

        $roomsFilter = $request->query('rooms');
        if ($roomsFilter) {
            $rooms = explode(',', $roomsFilter);
            $query->where(function ($q) use ($rooms) {
                foreach ($rooms as $room) {
                    $q->orWhere('room', 'LIKE', '%' . $room . '%');
                }
            });
        }
        
    
        $query->select(
            '*',
            \DB::raw('(full_payment - partial_payment) as balance'),
            \DB::raw("DATE_FORMAT(dateInserted, '%Y-%m-%d %h:%i:%s %p') as dateInserted"),
            \DB::raw("DATE_FORMAT(checkIn, '%d/%m/%Y') as checkIn"),
            \DB::raw("DATE_FORMAT(checkOut, '%d/%m/%Y') as checkOut")
        );
    

        // Pagination
        $perPage = $request->query('perPage', 10);
        $currentPage = $request->query('page', 1);
    
        // Calculate total before applying filters
        $total = $query->count();
    
        $query->skip(($currentPage - 1) * $perPage)->take($perPage);
    
        $mains = $query->get();
    
        // Calculate total pages after applying filters
        $totalPages = ceil($total / $perPage);
    
        // You can customize the response to include pagination information
        return response()->json([
            'data' => $mains,
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ]);
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
            $event = Event::create([
                'record_id' => $main->id,
                'type' => 'Create',
                
                'summary' => 
                "\nNAME: {$main->name}
                \nPAX: {$main->pax}
                \nVEHICLE: {$main->vehicle}
                \nPETS: {$main->pets}
                \nVIDEOKE: {$main->videoke}
                \nPARTIAL PAYMENT: {$main->partial_payment}
                \nFULL PAYMENT: {$main->full_payment}
                \nPAID: {$main->paid}
                \nCHECK IN: {$main->checkIn}
                \nCHECK OUT: {$main->checkOut}
                \nROOM: {$main->room}",

                'user' => $request->input('user'),
            ]);

    

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
    
        // Store the original values before the update
        $originalValues = $main->getAttributes();
    
    
        // Change $request true to 1, and false to 0
        $requestData = $request->all();
        foreach ($requestData as $key => $value) {
            if (is_bool($value)) {
                $requestData[$key] = $value ? 1 : 0;
            }
        }
    
        // Update the Main model with the modified request data
        $main->update($requestData);
    
        // Get the updated values after the update
        $updatedValues = $main->getAttributes();
    
        // Compare original and updated values to determine the changes
        $ignoredColumns = ['updated_at', 'created_at'];
        $changedFields = array_diff_assoc(Arr::except($updatedValues, $ignoredColumns), Arr::except($originalValues, $ignoredColumns));
    
        // If there are no changes, return the response message
        if (empty($changedFields)) {
            return response()->json(['message' => 'No changes made']);
        }
    
        // Create a summary of the changed fields
        $summary = '';
    
        foreach ($changedFields as $key => $value) {
            $previousValue = $originalValues[$key];
    
            if($key == 'pets' || $key == 'videoke'){
                $previousValue = $previousValue == 0 ? 'No' : 'Yes';
                $value = $value == 0 ? 'No' : 'Yes';
            }
    
            $summary .= "\n" . strtoupper($key) . ": $previousValue → $value";
        }
    
        // Create the Event record
        $event = Event::create([
            'record_id' => $main->id,
            'type' => 'Update',
            'summary' => $summary,
            'user' => Auth::user()->name,
        ]);
    
        broadcast(new MainUpdated($main));
    
        // Include the summary in the JSON response
        return response()->json(['message' => 'Record updated successfully', 'summary' => $summary]);
    }
    
    
    

    public function destroy($id)
    {
        $main = Main::findOrFail($id);
        $main->delete();
        
        $event = Event::create([
            'record_id' => $main->id,
                
            'summary' => 
            "\nNAME: {$main->name}
            \nPAX: {$main->pax}
            \nVEHICLE: {$main->vehicle}
            \nPETS: {$main->pets}
            \nVIDEOKE: {$main->videoke}
            \nPARTIAL PAYMENT: {$main->partial_payment}
            \nFULL PAYMENT: {$main->full_payment}
            \nPAID: {$main->paid}
            \nCHECK IN: {$main->checkIn}
            \nCHECK OUT: {$main->checkOut}
            \nROOM: {$main->room}",
            'summary' => $main,
            'user' => Auth::user()->name
        ]);
        broadcast(new MainDeleted($id));
        return response()->json(['message' => 'Record deleted successfully']);
    }






    public function getNewSet(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date',
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }


        $firstDayOfMonth = \Carbon\Carbon::parse($startDate)->firstOfMonth();
        $lastDayOfMonth = \Carbon\Carbon::parse($endDate)->lastOfMonth();
    
        $dateOffset = $firstDayOfMonth->startOfMonth()->dayOfWeek;

        $firstDayOfMonth->subDays($dateOffset);
        $lastDayOfMonth->addDays(42 - ($dateOffset + $lastDayOfMonth->day));

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
                $checkInDate = \Carbon\Carbon::parse($main->checkIn);
                $checkOutDate = \Carbon\Carbon::parse($main->checkOut);
    
                // Iterate over each day in the range of the main booking
                for ($currentDate = $checkInDate; $currentDate->lte($checkOutDate); $currentDate->addDay()) {
                    $dayIndex = ($currentDate->diffInDays($firstDayOfMonth));
                        
                
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
    public function getNewSetEdit(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date',
            'id'   => 'required',
        ]);
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $id = $request->input('id');
        
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }


        $firstDayOfMonth = \Carbon\Carbon::parse($startDate)->firstOfMonth();
        $lastDayOfMonth = \Carbon\Carbon::parse($endDate)->lastOfMonth();
    
        $dateOffset = $firstDayOfMonth->startOfMonth()->dayOfWeek;

        $firstDayOfMonth->subDays($dateOffset);
        $lastDayOfMonth->addDays(42 - ($dateOffset + $lastDayOfMonth->day));

    
        try {
            $mains = \DB::table('main')
                ->select('id', 'checkIn', 'checkOut', 'room')
                ->where('checkIn', '<=', $lastDayOfMonth)
                ->where('checkOut', '>=', $firstDayOfMonth)
                ->where('id', '!=', $id)
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
                $checkInDate = \Carbon\Carbon::parse($main->checkIn);
                $checkOutDate = \Carbon\Carbon::parse($main->checkOut);
    
                // Iterate over each day in the range of the main booking
                for ($currentDate = $checkInDate; $currentDate->lte($checkOutDate); $currentDate->addDay()) {
                    $dayIndex = ($currentDate->diffInDays($firstDayOfMonth));
                        
                
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
            'endDate'   => 'required|date',
            'room' => 'required'
        ]);
    

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        if ($startDate > $endDate) {
            return response()->json(['error' => 'Check in must be before Check out'], 422);
        }


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

    public function checkEditForm(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date',
            'room' => 'required',
            'id' => 'required'
        ]);
    

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $id = $request->input('id');

        if ($startDate > $endDate) {
            return response()->json(['error' => 'Check in must be before Check out'], 422);
        }


        $userRooms = explode(',', $request->input('room'));
    
        try {
            $databaseRooms = \DB::table('main')
                ->select('room')
                ->where('checkIn', '<=', $endDate)
                ->where('checkOut', '>=', $startDate)
                ->where('id', '!=', $id)
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
