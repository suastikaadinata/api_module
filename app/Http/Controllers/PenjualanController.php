<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'nama'          => 'required|string',
            'harga'         => 'required|numeric',
            'kode'          => 'required|string'
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            $status = DB::transaction(function () use ($request) {
                $penjualanId = Penjualan::insertGetId(array(
                    'nama'          => $request->nama,
                    'harga'         => $request->harga,
                    'kode'          => $request->kode
                ));

                return Penjualan::findOrFail($penjualanId);
            });

            if ($status) {
                return response()->json(['data' => $status], 200);
            } else {
                return response()->json(['message' => 'failed'], 400);
            }
        }
    }

    public function list()
    {
        return response()->json(['data' => Penjualan::all()], 200);
    }

    public function update($id, Request $request)
    {
        $needValidate = array(
            'id'            => $id,
            'nama'          => $request->nama,
            'harga'         => $request->harga,
            'kode'          => $request->kode
        );

        $validation = Validator::make($needValidate, [
            'id'            => 'required|numeric|exists:penjualan',
            'nama'          => 'nullable|string',
            'harga'         => 'nullable|numeric',
            'kode'          => 'nullable|string'
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            $status = DB::transaction(function () use ($id, $request) {
                $penjualan = Penjualan::findOrFail($id);

                $penjualan->nama = $this->checkParam($penjualan->nama, $request->nama);
                $penjualan->harga = $this->checkParam($penjualan->harga, $request->harga);
                $penjualan->kode = $this->checkParam($penjualan->kode, $request->kode);
                $penjualan->update();

                return $penjualan;
            });

            if ($status) {
                return response()->json(['data' => $status], 200);
            } else {
                return response()->json(['message' => 'failed'], 400);
            }
        }
    }

    public function delete($id)
    {
        $validation = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:penjualan',
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            Penjualan::find($id)->delete();
            return response()->json(['message' => 'delete success'], 200);
        }
    }

    private function checkParam($dbData, $param)
    {
        $data = $dbData;
        if ($param != null) {
            $data = $param;
        }

        return $data;
    }
}
