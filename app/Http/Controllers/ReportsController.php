<?php

// app/Http/Controllers/ReportsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Events\ReportCreated;
use App\Events\ReportUpdated;
use App\Events\ReportDeleted;

class ReportsController extends Controller
{
    public function index()
    {
        $reports = Report::all();
        return response()->json($reports);
    }

    public function show($id)
    {
        $report = Report::findOrFail($id);
        return response()->json($report);
    }

    public function store(Request $request)
    {
        $report = Report::create($request->all());
        broadcast(new ReportCreated($report));
        return response()->json($report, 201);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $report->update($request->all());
        broadcast(new ReportUpdated($report));
        return response()->json($report, 200);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();
        broadcast(new ReportDeleted($id));
        return response()->json(null, 204);
    }
}
