<?php

namespace App\Http\Controllers;

use App\Models\LogsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminLogsController extends Controller
{
    //

    public function manageLogs()
    {
        return view('admin.manage_logs');
    }

    public function index()
    {

        if (Auth::user()->level == 'master') {
            $logs = LogsModel::with('devices')->get();
        } else {
            $logs = LogsModel::with('devices')
                ->whereHas('devices', function ($query) {
                    $query->where('user_assigned', Auth::user()->id);
                })
                ->get();
        }

        return response()->json($logs);
    }

    public function update(Request $request, $id)
    {
        $log = LogsModel::find($id);
        if (!$log) {
            return response()->json(['message' => 'Log not found'], 404);
        }

        $data = $request->validate([
            'action' => 'nullable|string|max:255',
        ]);

        $log->update($data);

        return response()->json([
            'message' => 'Log updated successfully',
            'data' => $log
        ]);
    }


}
