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
        return response()->json($main, 200);
    }

    public function destroy($id)
    {
        $main = Main::findOrFail($id);
        $main->delete();
        broadcast(new MainDeleted($id));
        return response()->json(null, 204);
    }
}
