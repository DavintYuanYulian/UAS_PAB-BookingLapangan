<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\FieldSchedule;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function index()
    {
        $result = [];
        $nextWeek = Carbon::today()->addWeek();

        for ($date = Carbon::today(); $date < $nextWeek; $date->addDay()) {

            $schedule = FieldSchedule::whereDate('schedule_date', $date)->first();

            $booked = $schedule ? $schedule->booked : 0;
            $used   = $schedule ? $schedule->used : 0;

            $result[] = [
                'date'      => $date->toDateString(),
                'remaining' => 10 - $booked,
                'booked'    => $booked,
                'used'      => $used,
            ];
        }

        return response()->json($result, 200);
    }

    public function show(string $id)
    {
        $customer_name = '';
        $booking_date = Carbon::today();

        $booking = Booking::where('unique_code', $id)->first();

        if ($booking) {
            $customer_name = $booking->customer_name;

            $schedule = FieldSchedule::find($booking->field_schedule_id);
            if ($schedule) {
                $booking_date = $schedule->schedule_date;
            }
        }

        return response()->json([
            'customer_name' => $customer_name,
            'booking_date' => $booking_date,
        ], 200);
    }


    

    /**
     * @OA\Post(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Menandai booking digunakan",
     *     description="Digunakan oleh petugas lapangan untuk menandai booking sudah dipakai.",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Unique code booking",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking berhasil ditandai digunakan"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking tidak ditemukan"
     *     )
     * )
     */
    public function use(Request $request, $id)
    {
        $booking = Booking::where('unique_code', $id)->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $schedule = FieldSchedule::find($booking->field_schedule_id);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        // tandai booking sudah digunakan
        $booking->is_used = 1;
        $booking->save();

        // tambah counter pemakaian lapangan
        $schedule->used++;
        $schedule->save();

        return response()->json($booking, 200);
    }
}
