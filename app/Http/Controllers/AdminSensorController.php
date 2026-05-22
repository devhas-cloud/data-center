<?php

namespace App\Http\Controllers;

use App\Models\DeviceModel;
use App\Models\ParameterModel;
use App\Models\SensorModel;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSensorController extends Controller
{


    public function index()
    {

        if(Auth::user()->level == 'master') {
            $sensors = SensorModel::whereHas('device')->with('device')->orderBy('id', 'desc')->get();
        } else {
            $sensors = SensorModel::whereHas('device', function ($query) {
                $query->where('user_assigned', '=', Auth::user()->id);
            })->with('device')->orderBy('id', 'desc')->get();
        }

        return response()->json($sensors);
    }

    public function manageSensors()
    {

        if(Auth::user()->level == 'master') {
            $devices = DeviceModel::all();
        }else{
            $devices = DeviceModel::where('user_assigned', '=', Auth::user()->id)->get();
        }

        $parameters=ParameterModel::all();

        return view('admin.manage_sensors', ['parameters' => $parameters,'devices' => $devices]);
    }

    public function show($id)
    {
        $sensor = SensorModel::find($id);
        if (!$sensor) {
            return response()->json(['message' => 'Sensor not found'], 404);
        }
        return response()->json($sensor);
    }

    public function storeBulk(Request $request)
    {
        try {
            $device_id = $request->input('device_id');
            $sensors   = $request->input('sensors', []);

            if (empty($device_id)) {
                return response()->json(['message' => 'Device ID is required'], 422);
            }
            if (empty($sensors) || !is_array($sensors)) {
                return response()->json(['message' => 'At least one sensor row is required'], 422);
            }

            $created = [];
            $errors  = [];

            foreach ($sensors as $index => $row) {
                $rowNum = $index + 1;

                $sensorName    = $row['sensor_name']    ?? null;
                $sensorNumber  = $row['sensor_number']  ?? null;
                $parameterName = $row['parameter_name'] ?? null;

                if (empty($sensorName) || empty($parameterName)) {
                    $errors[] = "Row {$rowNum}: Sensor Name and Parameter are required.";
                    continue;
                }

                // Duplicate check
                $exists = SensorModel::where('device_id', $device_id)
                    ->where('sensor_name', $sensorName)
                    ->where('parameter_name', $parameterName)
                    ->where('status', 'active')
                    ->exists();

                if ($exists) {
                    $errors[] = "Row {$rowNum}: Sensor '{$sensorName}' with parameter '{$parameterName}' already exists and is active.";
                    continue;
                }

                $parameter = ParameterModel::where('parameter_name', $parameterName)->first();

                $data = [
                    'device_id'                 => $device_id,
                    'sensor_number'             => $sensorNumber,
                    'sensor_name'               => $sensorName,
                    'parameter_name'            => $parameterName,
                    'parameter_number'          => $row['parameter_number']          ?? null,
                    'parameter_indicator_alert' => !empty($row['parameter_indicator_alert']) ? (int) $row['parameter_indicator_alert'] : null,
                    'sensor_unit'               => $row['sensor_unit']               ?? null,
                    'maintenance_date'          => !empty($row['maintenance_date'])   ? $row['maintenance_date']   : null,
                    'calibration_date'          => !empty($row['calibration_date'])   ? $row['calibration_date']   : null,
                    'status'                    => $row['status']                     ?? 'active',
                    'notes'                     => $row['notes']                      ?? null,
                    'parameter_indicator_min'   => $parameter?->parameter_indicator_min,
                    'parameter_indicator_max'   => $parameter?->parameter_indicator_max,
                ];

                $created[] = SensorModel::create($data);
            }

            if (!empty($errors) && empty($created)) {
                return response()->json(['message' => implode(' | ', $errors)], 422);
            }

            $message = count($created) . ' sensor(s) saved successfully.';
            if (!empty($errors)) {
                $message .= ' Skipped: ' . implode(' | ', $errors);
            }

            return response()->json(['message' => $message, 'data' => $created], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => 'server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'device_id'  => 'required|string|max:255',
                    'sensor_name' => 'required|string|max:255',
                    'sensor_number' => 'required|string|max:100',
                    'parameter_name' => 'required|string|max:255',
                    'parameter_number' => 'required|string|max:100',
                    'parameter_indicator_alert' => 'nullable|integer',
                    'sensor_unit' => 'required|string|max:255',
                    'maintenance_date' => 'required|date',
                    'calibration_date' => 'required|date',
                    'status' => 'required|string|in:active,inactive',
                    'notes' => 'nullable|string',
                ],
                [],
                [],
                function ($validator) {
                    throw new HttpResponseException(
                        response()->json([
                            'message' => 'Validasi gagal',
                            'errors'  => $validator->errors()
                        ], 422)
                    );
                }
            );

            //cek jika pada device_id yang sama, ada sensor_name, parameter_name, status = aktif, maka tolak
            $existingSensor = SensorModel::where('device_id', $data['device_id'])
                ->where('sensor_name', $data['sensor_name'])
                ->where('parameter_name', $data['parameter_name'])
                ->where('status', 'active')
                ->first();

            if ($existingSensor) {
                return response()->json([
                    'message' => 'Sensor with the same device id, sensor name, and parameter name already exists and is active. Please edit the existing status inactive first.'
                ], 422);
            }

            //ambil parameter_indicator_min dan parameter_indicator_max dari ParameterModel
            $parameter = ParameterModel::where('parameter_name', $data['parameter_name'])->first();
            if ($parameter) {
                $data['parameter_indicator_min'] = $parameter->parameter_indicator_min;
                $data['parameter_indicator_max'] = $parameter->parameter_indicator_max;
            }

            $sensor = SensorModel::create($data);
            return response()->json([
                'message' => 'Sensor created',
                'data'    => $sensor
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' =>  $e->getMessage(),
                'error'   => 'server error'
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $sensor = SensorModel::find($id);
        if (!$sensor) {
            return response()->json(['message' => 'Sensor not found'], 404);
        }
        try {

            // jika status == inactive, maka maintenance_date dan calibration_date boleh null
            $data = $request->validate(
                [
                    'device_id'  => 'sometimes|required|string|max:255',
                    'sensor_name' => 'sometimes|required|string|max:255',
                    'sensor_number' => 'sometimes|required|string|max:100',
                    'parameter_name' => 'sometimes|required|string|max:255',
                    'parameter_number' => 'sometimes|required|string|max:100',
                    'parameter_indicator_alert' => 'nullable|integer',
                    'sensor_unit' => 'sometimes|required|string|max:255',
                    'maintenance_date' => 'sometimes|nullable|date',
                    'calibration_date' => 'sometimes|nullable|date',
                    'status' => 'sometimes|required|string|max:50',
                    'notes' => 'sometimes|nullable|string',
                ],
                [],
                [],
                function ($validator) {
                    throw new HttpResponseException(
                        response()->json([
                            'message' => 'Validasi gagal',
                            'errors'  => $validator->errors()
                        ], 422)
                    );
                }
            );

            // jika  status == inactive, maka maintenance_date dan calibration_date simpan null
            if (isset($data['status']) && $data['status'] == 'inactive') {
                $data['maintenance_date'] = null;
                $data['calibration_date'] = null;
            }

            //ambil parameter_indicator_min dan parameter_indicator_max dari ParameterModel
            if (isset($data['parameter_name'])) {
                $parameter = ParameterModel::where('parameter_name', $data['parameter_name'])->first();
                if ($parameter) {
                    $data['parameter_indicator_min'] = $parameter->parameter_indicator_min;
                    $data['parameter_indicator_max'] = $parameter->parameter_indicator_max;
                }
            }

            $sensor->update($data);
            return response()->json([
                'message' => 'Sensor updated',
                'data'    => $sensor
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' =>  $e->getMessage(),
                'error'   => 'server error'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $sensor = SensorModel::find($id);
        if (!$sensor) {
            return response()->json(['message' => 'Sensor not found'], 404);
        }
        try {
            $sensor->delete();
            return response()->json(['message' => 'Sensor deleted'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' =>  $e->getMessage(),
                'error'   => 'server error'
            ], 500);
        }
    }
}
