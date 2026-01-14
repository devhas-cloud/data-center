<?php

namespace App\Http\Controllers;

use App\Models\ParameterModel;
use App\Models\SensorModel;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use PhpParser\Builder\Param;

class AdminParameterController extends Controller
{
    //

    public function index(){
        $parameters = ParameterModel::orderBy('id', 'desc')->get();
        return response()->json($parameters);
    }

    public function manageParameters()
    {
        return view('admin.manage_parameters');
    }

    public function show($id){
        $parameter = ParameterModel::find($id);
        if(!$parameter){
            return response()->json(['message' => 'Parameter not found'], 404);
        }
        return response()->json($parameter);
    }

   public function store(Request $request){
        try {
            $data = $request->validate(
                [
                    'parameter_name'  => 'required|string|max:255|unique:tbl_parameter,parameter_name',
                    'parameter_unit' => 'required|string|max:255',
                    'parameter_label' => 'nullable|string|max:100',
                    'parameter_indicator_min' => 'required|integer',
                    'parameter_indicator_max' => 'required|integer',

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
            $parameter = ParameterModel::create($data);
            return response()->json([
                'message' => 'Parameter created',
                'data'    => $parameter
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' =>  $e->getMessage(),
                'error'   => 'server error'
            ], 500);

        }
    }

    public function update(Request $request, $id){
        $parameter = ParameterModel::find($id);
        if(!$parameter){
            return response()->json(['message' => 'Parameter not found'], 404);
        }

        // jika nama parameter tidak diubah, jangan periksa uniknya
        $uniqueRule = $request->input('parameter_name') !== $parameter->parameter_name
            ? 'unique:tbl_parameter,parameter_name'
            : '';
        try {
            $data = $request->validate(
                [
                    'parameter_name'  => ['required','string','max:255',$uniqueRule],
                    'parameter_unit' => 'required|string|max:255',
                    'parameter_label' => 'nullable|string|max:100',
                    'parameter_indicator_min' => 'required|integer',
                    'parameter_indicator_max' => 'required|integer',
                ]
            );

            //jika update berhasil maka update tbl_sensor.parameter_indicator_range, sesuai dengan parameter_indicator
            $cek = $parameter->update($data);
            if($cek){
                
                SensorModel::where('parameter_name', $parameter->parameter_name)
                    ->update([
                        'parameter_indicator_min' => $data['parameter_indicator_min'],
                        'parameter_indicator_max' => $data['parameter_indicator_max'],
                    ]);
                   
            }

            return response()->json([
                'message' => 'Parameter updated',
                'data'    => $parameter
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
                'error'   => $e->getMessage()
            ], 500);

        }

    }

    public function destroy($id){
        $parameter = ParameterModel::find($id);
        if(!$parameter){
            return response()->json(['message' => 'Parameter not found'], 404);
        }

        try {
            $parameter->delete();
            return response()->json(['message' => 'Parameter deleted'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }





}
