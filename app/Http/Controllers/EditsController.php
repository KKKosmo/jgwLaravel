<?php

// app/Http/Controllers/EditsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Edit;
use App\Events\EditCreated;
use App\Events\EditUpdated;
use App\Events\EditDeleted;

class EditsController extends Controller
{
    public function index()
    {
        $edits = Edit::all();
        if ($edits->isEmpty()) {
            return response()->json(['message' => 'No edits found'], 404);
        }

        return response()->json($edits);
    }

    public function show($id)
    {
        $edit = Edit::findOrFail($id);
        return response()->json($edit);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['edit_timestamp'] = $request->input('edit_timestamp', now());
    
        if (!isset($data['record_id'])) {
            $data['record_id'] = 1;
        }
    
        $edit = Edit::create($data);
    
        broadcast(new EditCreated($edit));
        return response()->json($edit, 201);
    }
    
    public function update(Request $request, $id)
    {
        $edit = Edit::findOrFail($id);
        $edit->update($request->all());
        broadcast(new EditUpdated($edit));
        return response()->json($edit, 200);
    }

    public function destroy($id)
    {
        $edit = Edit::findOrFail($id);
        $edit->delete();
        broadcast(new EditDeleted($id));
        return response()->json(null, 204);
    }
}
