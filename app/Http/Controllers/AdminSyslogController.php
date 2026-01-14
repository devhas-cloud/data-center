<?php

namespace App\Http\Controllers;

use App\Models\DeviceModel;
use App\Models\SensorModel;
use App\Models\ParameterModel;
use App\Models\SyslogHeaderModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AdminSyslogController extends Controller
{
    
    public function index()
    {
      // jika user login lavel master maka tampilkan semua user
        if(Auth::user()->level == 'master'){
            $dataList = SyslogHeaderModel::with(['user', 'device'])->get();
        } else {
            // selain itu tampilkan user yang di buat oleh user yang login
            $dataList = SyslogHeaderModel::with(['user', 'device'])->where('user_assigned', Auth::user()->id)->get();
        }

        return response()->json($dataList);
    }

    public function manageSyslog()
    {
        return view('admin.manage_syslog');
    }

    public function showAddForm()
    {
        // Ambil semua device dengan relasi sensors dan parameters
        if(Auth::user()->level == 'master') {
            $devices = DeviceModel::with(['sensors.parameter'])->get();
        } else {
            $devices = DeviceModel::where('user_assigned', Auth::user()->id)
                        ->with(['sensors.parameter'])
                        ->get();
        }
        
        // Format data sesuai struktur yang diminta
        $syslogData = [];
        
        foreach ($devices as $device) {
            // Kelompokkan sensors berdasarkan sensor_name
            $groupedSensors = [];
            foreach ($device->sensors as $sensor) {
                $key = $device->device_id . '_' . $sensor->sensor_name;
                if (!isset($groupedSensors[$key])) {
                    $groupedSensors[$key] = [
                        'device_id' => $device->device_id,
                        'sensor_name' => $sensor->sensor_name,
                        'parameter' => []
                    ];
                }
                // Tambahkan sensor parameter yang memilik status active ke array
                if ($sensor->status == 'active') {
                    $groupedSensors[$key]['parameter'][] = [
                        'parameter_name' => $sensor->parameter_name,
                        'parameter_label' => $sensor->parameter->parameter_label ?? $sensor->parameter_name
                    ];
                }
            }
            
            // Masukkan ke syslogData
            foreach ($groupedSensors as $group) {
                $syslogData[] = $group;
            }
        }
        
        return view('admin.manage_syslog_add', [
        'devices' => $devices,
        'syslogData' => $syslogData
    ]);

    }
    
    public function show($id)
    {
        $syslogHeader = SyslogHeaderModel::with(['details.parameter', 'device', 'user'])->findOrFail($id);
        
        return view('admin.manage_syslog_view', [
            'syslogHeader' => $syslogHeader
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:tbl_device,device_id',
            'date' => 'required|date_format:Y-m-d',
            'category' => 'required|in:maintenance,calibration,installation',
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'note' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.parameter_name' => 'required|string',
            'details.*.description' => 'nullable|string',
        ]);
        
        // Proses upload PDF jika ada
        $pdfPath = null;
        if ($request->hasFile('pdf_file')) {
            $pdfPath = $request->file('pdf_file')->store('syslog_pdfs', 'public');
        }
        
        try {
            // Gunakan database transaction
            DB::beginTransaction();
            
            // Simpan data ke SyslogHeaderModel
            $syslogHeader = new SyslogHeaderModel();
            $syslogHeader->user_assigned = Auth::user()->id;
            $syslogHeader->device_id = $request->input('device_id');
            $syslogHeader->created_date = $request->input('date');
            $syslogHeader->category = $request->input('category');
            $syslogHeader->linked_file = $pdfPath;
            $syslogHeader->note = $request->input('note');
            $syslogHeader->save();

            // Simpan detail syslog
            $details = $request->input('details', []);
            foreach ($details as $detail) {
                // Cari parameter berdasarkan parameter_name
                $parameter = ParameterModel::where('parameter_name', $detail['parameter_name'])->first();
                
                if ($parameter) {
                    $syslogHeader->details()->create([
                        'parameter_id' => $parameter->id,
                        'description' => $detail['description'] ?? null,
                    ]);
                }
            }
            
            // Commit transaction jika semua berhasil
            DB::commit();
            
            return redirect()->route('admin.syslog_add')
                ->with('success', 'Syslog data saved successfully');
                
        } catch (\Exception $e) {
            // Rollback transaction jika ada error
            DB::rollBack();
            
            // Hapus file PDF yang sudah diupload
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
            }
            
            return redirect()->route('admin.syslog_add')
                ->with('error', 'Failed to save syslog data: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function edit($id)
    {
        $syslogHeader = SyslogHeaderModel::with(['details.parameter', 'device'])->findOrFail($id);
        
        // Ambil semua device dengan relasi sensors dan parameters
        if(Auth::user()->level == 'master') {
            $devices = DeviceModel::with(['sensors.parameter'])->get();
        } else {
            $devices = DeviceModel::where('user_assigned', Auth::user()->id)
                        ->with(['sensors.parameter'])
                        ->get();
        }
        
        // Format data sesuai struktur yang diminta
        $syslogData = [];
        
        foreach ($devices as $device) {
            // Kelompokkan sensors berdasarkan sensor_name
            $groupedSensors = [];
            foreach ($device->sensors as $sensor) {
                $key = $device->device_id . '_' . $sensor->sensor_name;
                if (!isset($groupedSensors[$key])) {
                    $groupedSensors[$key] = [
                        'device_id' => $device->device_id,
                        'sensor_name' => $sensor->sensor_name,
                        'parameter' => []
                    ];
                }
                // Tambahkan parameter ke array
                $groupedSensors[$key]['parameter'][] = [
                    'parameter_name' => $sensor->parameter_name,
                    'unit' => $sensor->sensor_unit
                ];
            }
            
            // Masukkan ke syslogData
            foreach ($groupedSensors as $group) {
                $syslogData[] = $group;
            }
        }
        
        return view('admin.manage_syslog_edit', [
            'syslogHeader' => $syslogHeader,
            'devices' => $devices,
            'syslogData' => $syslogData
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'device_id' => 'required|exists:tbl_device,device_id',
            'date' => 'required|date_format:Y-m-d',
            'category' => 'required|in:maintenance,calibration,installation',
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'note' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.parameter_name' => 'required|string',
            'details.*.description' => 'nullable|string',
        ]);
        
        $syslogHeader = SyslogHeaderModel::findOrFail($id);
        $oldPdfPath = $syslogHeader->linked_file;
        
        // Proses upload PDF jika ada
        $pdfPath = $oldPdfPath;
        if ($request->hasFile('pdf_file')) {
            $pdfPath = $request->file('pdf_file')->store('syslog_pdfs', 'public');
        }
        
        try {
            // Gunakan database transaction
            DB::beginTransaction();
            
            // Update data SyslogHeaderModel
            $syslogHeader->device_id = $request->input('device_id');
            $syslogHeader->created_date = $request->input('date');
            $syslogHeader->category = $request->input('category');
            $syslogHeader->linked_file = $pdfPath;
            $syslogHeader->note = $request->input('note');
            $syslogHeader->save();

            // Hapus detail lama
            $syslogHeader->details()->delete();
            
            // Simpan detail baru
            $details = $request->input('details', []);
            foreach ($details as $detail) {
                // Cari parameter berdasarkan parameter_name
                $parameter = ParameterModel::where('parameter_name', $detail['parameter_name'])->first();
                
                if ($parameter) {
                    $syslogHeader->details()->create([
                        'parameter_id' => $parameter->id,
                        'description' => $detail['description'] ?? null,
                    ]);
                }
            }
            
            // Hapus PDF lama jika ada PDF baru
            if ($request->hasFile('pdf_file') && $oldPdfPath && Storage::disk('public')->exists($oldPdfPath)) {
                Storage::disk('public')->delete($oldPdfPath);
            }
            
            // Commit transaction jika semua berhasil
            DB::commit();
            
            return redirect()->route('admin.manage_syslog')
                ->with('success', 'Syslog data updated successfully');
                
        } catch (\Exception $e) {
            // Rollback transaction jika ada error
            DB::rollBack();
            
            // Hapus file PDF baru yang sudah diupload jika ada error
            if ($request->hasFile('pdf_file') && $pdfPath !== $oldPdfPath && Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
            }
            
            return redirect()->route('admin.syslog_edit', $id)
                ->with('error', 'Failed to update syslog data: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function destroy($id)
    {
        try {
            $syslogHeader = SyslogHeaderModel::findOrFail($id);
            $pdfPath = $syslogHeader->linked_file;
            
            // Hapus details
            $syslogHeader->details()->delete();
            
            // Hapus header
            $syslogHeader->delete();
            
            // Hapus file PDF jika ada
            if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
                Storage::disk('public')->delete($pdfPath);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Syslog data deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete syslog data: ' . $e->getMessage()
            ], 500);
        }
    }
}
