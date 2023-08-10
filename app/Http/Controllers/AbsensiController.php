<?php

namespace App\Http\Controllers;

use App\Models\Epresence;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function absenPegawai(Request $request) : JsonResponse {
        $check = Epresence::where('user_id', Auth::user()->id)->where('type', $request->type)->whereDate('time', date('Y-m-d', strtotime($request->time)))->exists();

        if($check) {
            $type = ($request->type == 'IN') ? 'masuk' : 'keluar';

            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah melakukan absensi ' . $type . ' hari ini',
                'data' => null
            ], 400);
        }

        DB::beginTransaction();
        try {
            Epresence::create([
                'user_id' => Auth::user()->id,
                'type' => $request->type,
                'is_approved' => 'FALSE',
                'time' => Carbon::parse($request->time)->format('Y-m-d H:i:s'),
            ]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil dilakukan',
                'data' => null
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // public function absenKeluar() : JsonResponse {
    //     $check = Epresence::where('user_id', Auth::user()->id)->where('type', 'OUT')->whereDate('time', date('Y-m-d'))->exists();

    //     if($check) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Anda sudah melakukan absensi keluar hari ini',
    //             'data' => null
    //         ], 400);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         Epresence::create([
    //             'user_id' => Auth::user()->id,
    //             'type' => 'OUT',
    //             'is_approved' => 'FALSE',
    //             'time' => Carbon::now(),
    //         ]);
    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Absensi berhasil dilakukan',
    //             'data' => null
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         DB::rollback();
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $th->getMessage(),
    //             'data' => null
    //         ], 500);
    //     }
    // }

    public function rekapAbsensiPegawai($pegawai_id) : JsonResponse {
        $absensiData = DB::table('epresences')
            ->select('users.id as id_user', 'users.name as nama', 'epresences.time', 'epresences.type', 'epresences.is_approved')
            ->join('users', 'epresences.user_id', '=', 'users.id')
            ->where('epresences.user_id', $pegawai_id)
            ->orderBy('epresences.time', 'asc')
            ->get();

        $result = [];

        foreach ($absensiData as $absensi) {
            $tanggal = Carbon::parse($absensi->time)->format('Y-m-d');
            $waktu = Carbon::parse($absensi->time)->format('H:i:s');

            if(!isset($result[$tanggal])){
                $result[$tanggal] = [
                    'id_user' => $absensi->id_user,
                    'nama' => $absensi->nama,
                    'tanggal' => $tanggal,
                    'waktu_masuk' => $waktu,
                    'status_masuk' => $absensi->is_approved == 'TRUE' ? 'APPROVE' : 'REJECT',
                ];
            }else if (isset($result[$tanggal]) && $absensi->type == 'OUT') {
                $result[$tanggal]['waktu_keluar'] = $waktu;
                $result[$tanggal]['status_keluar'] = $absensi->is_approved == 'TRUE' ? 'APPROVE' : 'REJECT';
            } else if (isset($result[$tanggal]) && $absensi->type == 'IN') {
                $result[$tanggal]['waktu_masuk'] = $waktu;
                $result[$tanggal]['status_masuk'] = $absensi->is_approved == 'TRUE' ? 'APPROVE' : 'REJECT';
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil didapatkan',
            'data' => $result
        ], 200);
    }

    public function approveAbsensi(Request $request) : JsonResponse {
        $absensi = Epresence::find($request->absensi_id);

        if(!$absensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi tidak ditemukan',
                'data' => null
            ], 404);
        }

        DB::beginTransaction();
        try {
            $absensi->update([
                'is_approved' => 'TRUE',
            ]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Absensi berhasil diapprove',
                'data' => null
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
