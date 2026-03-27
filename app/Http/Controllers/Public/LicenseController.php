<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Registrant;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'machine_id' => 'required|string', // ID unik dari komputer user
        ]);

        $license = Registrant::where('serial_number', $request->serial_number)->first();

        // Cek apakah key ada
        if (!$license) {
            return response()->json(['success' => false, 'message' => 'License Key tidak ditemukan.'], 404);
        }

        // Cek apakah sudah dibayar
        if ($license->status !== 'paid') {
            return response()->json(['success' => false, 'message' => 'License Key belum lunas/pending.'], 403);
        }

        // Hardware Binding
        // Jika machine_id di database masih kosong, simpan machine_id komputer ini
        // Jika sudah ada, cocokkan. Jika beda, tolak (berarti key dipakai di komputer lain)
        if (empty($license->machine_id)) {
            $license->update(['machine_id' => $request->machine_id]);
        } elseif ($license->machine_id !== $request->machine_id) {
            return response()->json(['success' => false, 'message' => 'License Key ini sudah digunakan di perangkat lain.'], 403);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Aktivasi Berhasil',
            'data' => [
                'name' => $license->name,
                'email' => $license->email,
            ]
        ]);
    }
}