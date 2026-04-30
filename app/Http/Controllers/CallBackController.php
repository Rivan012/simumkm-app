<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallBackController extends Controller
{
    public function callback(Request $request)
    {
        // \Log::info('MIDTRANS CALLBACK', $request->all());
        $serverKey = config('services.midtrans.server_key');

        // 🔐 Validasi signature
        $hashed = hash(
            "sha512",
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($hashed !== $request->signature_key) {
            // Log::warning('Midtrans signature tidak valid');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 🔍 Ambil order
        $order = Order::with('detail')->where('midtrans_order_id', $request->order_id)->first();

        if (!$order) {
            // Log::warning('Order tidak ditemukan: ' . $request->order_id);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $status = $request->transaction_status;
        $fraud = $request->fraud_status;

        // ======================
        // HANDLE STATUS
        // ======================

        if ($status == 'capture') {
            if ($fraud == 'accept') {
                $this->successOrder($order);
            }
        } elseif ($status == 'settlement') {
            $this->successOrder($order);
        } elseif ($status == 'pending') {
            $order->update(['status' => 'pending']);
        } elseif (in_array($status, ['deny', 'cancel', 'expire'])) {
            $order->update(['status' => 'gagal']);
        }

        return response()->json(['message' => 'OK']);
    }

    // ======================
    // SUCCESS HANDLER
    // ======================
    protected function successOrder($order)
    {
        if ($order->status === 'selesai') {
            return;
        }

        $order->update(['status' => 'Pembayaran Berhasil']);

        // ambil semua detail order
        $detailOrder = OrderDetail::where('order_id', $order->id)->get();

        foreach ($detailOrder as $detail) {
            Produk::where('id', $detail->produk_id)
                ->decrement('stok', $detail->qty);
        }

        // hapus keranjang berdasarkan user
        Keranjang::where('user_id', $order->user_id)->delete();
    }
}