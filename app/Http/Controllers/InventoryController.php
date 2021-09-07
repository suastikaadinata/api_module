<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use Illuminate\Support\Carbon;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'nama'          => 'required|string',
            'tgl_pembelian' => 'required|date_format:Y-m-d',
            'no_bukti'      => 'required|string',
            'harga'         => 'required|numeric',
            'foto'          => 'required|mimes:jpg,jpeg,png|max:2000'
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            $status = DB::transaction(function () use ($request) {
                $path = null;
                if ($file = $request->file('foto')) {
                    $path = $file->store('inventory', 'public');
                }

                $inventoryId = Inventory::insertGetId(array(
                    'nama'          => $request->nama,
                    'tgl_pembelian' => $request->tgl_pembelian,
                    'no_bukti'      => $request->no_bukti,
                    'harga'         => $request->harga,
                    'foto'          => $path
                ));

                return Inventory::findOrFail($inventoryId);
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
        return response()->json(['data' => Inventory::all()], 200);
    }

    public function update($id, Request $request)
    {
        $needValidate = array(
            'id'            => $id,
            'name'          => $request->name,
            'tgl_pembelian' => $request->tgl_pembelian,
            'no_bukti'      => $request->no_bukti,
            'harga'         => $request->harga,
            'foto'          => $request->foto,
        );

        $validation = Validator::make($needValidate, [
            'id'            => 'required|numeric|exists:inventory',
            'nama'          => 'nullable|string',
            'tgl_pembelian' => 'nullable|date_format:Y-m-d',
            'no_bukti'      => 'nullable|string',
            'harga'         => 'nullable|numeric',
            'foto'          => 'nullable|mimes:jpg,jpeg,png|max:2000'
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            $status = DB::transaction(function () use ($id, $request) {
                $inventory = Inventory::findOrFail($id);
                $path = $inventory->foto;
                if ($file = $request->file('foto')) {
                    $path = $file->store('inventory', 'public');
                }

                $inventory->nama = $this->checkParam($inventory->nama, $request->nama);
                $inventory->tgl_pembelian = $this->checkParam($inventory->tgl_pembelian, $request->tgl_pembelian);
                $inventory->no_bukti = $this->checkParam($inventory->no_bukti, $request->no_bukti);
                $inventory->harga = $this->checkParam($inventory->harga, $request->harga);
                $inventory->foto = $path;
                $inventory->timestamps = true;
                $inventory->update();

                return $inventory;
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
            'id' => 'required|numeric|exists:inventory',
        ]);

        if ($validation->fails()) {
            return response()->json(["message" => $validation->errors()->first()], 400);
        } else {
            Inventory::find($id)->delete();
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
