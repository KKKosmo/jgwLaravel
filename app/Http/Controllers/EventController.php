<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{

    
    public function index()
    {
        $events = Event::all();

        return response()->json(['data' => $events], 200);
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
