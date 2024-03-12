<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

class EventController extends Controller
{

    public function index()
{
    $events = Event::all();

    // Format timestamps to 12-hour format
    $formattedEvents = $events->map(function ($event) {
        return [
            'id' => $event->id,
            'record_id' => $event->record_id,
            'type' => $event->type,
            'summary' => $event->summary,
            'user' => $event->user,
            'created_at' => Carbon::parse($event->created_at)->format('Y-m-d h:i:s A'),
            'updated_at' => Carbon::parse($event->updated_at)->format('Y-m-d h:i:s A'),
            // Add other attributes as needed
        ];
    });

    \Log::info($formattedEvents);

    return response()->json(['data' => $formattedEvents], 200);
}

    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json(['data' => $event], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'record_id' => 'required|exists:main,id',
            'type' => 'nullable|string|max:20',
            'summary' => 'nullable|string|max:500',
        ]);

        $data['user'] = auth()->user()->name;

        $event = Event::create($data);

        broadcast(new EventCreated($event));

        return response()->json($event, 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $data = $request->validate([
            'record_id' => 'sometimes|exists:main,id',
            'type' => 'nullable|string|max:20',
            'summary' => 'nullable|string|max:500',
        ]);

        $event->update($data);

        return response()->json($event, 200);
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }
}
