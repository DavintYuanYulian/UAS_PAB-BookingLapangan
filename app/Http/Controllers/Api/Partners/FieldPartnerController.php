<?php

namespace App\Http\Controllers\Api\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\FieldSchedule;
use App\Models\Booking;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;
use Xendit\Invoice\CreateInvoiceRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;



class FieldPartnerController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/partners/fields",
     *     tags={"Partners"},
     *     summary="Melihat ketersediaan lapangan",
     *     description="Endpoint untuk melihat daftar lapangan beserta sisa kuota per tanggal. Digunakan oleh partner (M2M).",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="date", type="string", example="2025-12-16"),
     *                 @OA\Property(property="field_name", type="string", example="Lapangan Voli"),
     *                 @OA\Property(property="remaining", type="integer", example=9)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $result = [];
        $nextWeek = Carbon::today()->addWeek();

        // ambil semua lapangan
        $fields = \App\Models\Field::all();

        foreach ($fields as $field) {
            for ($date = Carbon::today(); $date < $nextWeek; $date->addDay()) {

                // cari jadwal per lapangan per tanggal
                $schedule = FieldSchedule::where('field_id', $field->id)
                    ->whereDate('schedule_date', $date)
                    ->first();

                $booked = $schedule ? $schedule->booked : 0;
                $used   = $schedule ? $schedule->used : 0;

                $result[] = [
                    'field_id'   => $field->id,
                    'field_name' => $field->name,
                    'type'       => $field->type,
                    'price'      => $field->price,
                    'date'       => $date->toDateString(),
                    'booked'     => $booked,
                    'used'       => $used,
                    'remaining' => 10 - $booked,
                ];
            }
        }

        return response()->json($result, 200);
    }



    /**
     * @OA\Post(
     *     path="/api/partners/fields",
     *     tags={"Partners"},
     *     summary="Membuat booking lapangan",
     *     description="Membuat booking lapangan berdasarkan field dan tanggal, sekaligus membuat invoice pembayaran.",
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_name","email","booking_date","field_id"},
     *             @OA\Property(property="customer_name", type="string", example="Maman Kopling"),
     *             @OA\Property(property="email", type="string", example="maman@gmail.com"),
     *             @OA\Property(property="booking_date", type="string", format="date", example="2025-12-16"),
     *             @OA\Property(property="field_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking berhasil dibuat"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Data tidak valid atau kuota penuh"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email',
            'booking_date' => 'required|date',
            'field_id' => 'required|exists:fields,id',
            // 'customer_name' => 'required|string|max:255',
            // 'email' => 'required|email',
            // 'booking_date' => 'required|date',
        ]);

        $field = \App\Models\Field::find($data['field_id']);

        if (!$field) {
            return response()->json(['message' => 'Field not found'], 404);
        }


        $booking_date = new Carbon($data['booking_date']);

        // cari jadwal lapangan
        // $schedule = FieldSchedule::where('schedule_date', $booking_date)->first();
        $schedule = FieldSchedule::where('field_id', $field->id)
            ->whereDate('schedule_date', $booking_date)
            ->first();


        if ($schedule == null) {
            $schedule = FieldSchedule::create([
                'field_id' => $field->id,
                'schedule_date' => $booking_date,
                'booked' => 0,
                'used' => 0,
            ]);
        }

        // simpan booking
        $data['field_schedule_id'] = $schedule->id;
        $data['channel'] = 'PARTNER';
        $data['is_booked'] = 0;
        $data['is_used'] = 0;

        $saved_data = Booking::create($data);

        // ===============================
        // BUAT INVOICE XENDIT
        // ===============================
        $invoice_description =
            "Booking {$field->name} tanggal "
            . $booking_date->format('d M Y')
            . " (Order ID {$saved_data->order_id})";
        // "Booking Lapangan tanggal "
        // . $booking_date->format('d M Y')
        // . " dengan Order ID " . $saved_data->order_id;

        Configuration::setXenditKey(
            config('services.xendit.secret_key')
        );

        $api = new InvoiceApi();

        $invoice_request = new CreateInvoiceRequest([
            'external_id' => $saved_data->unique_code,
            'amount' => $field->price, // harga booking lapangan
            'description' => $invoice_description,
            'currency' => 'IDR',
            'invoice_duration' => 3600, // 1 jam
        ]);

        try {
            $xendit_result = $api->createInvoice($invoice_request);
        } catch (XenditSdkException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => $e->getFullError(),
            ], 500);
        }

        // ===============================
        // KIRIM EMAIL VIA MAILEROO
        // ===============================
        $mail_body =
            "<p>Halo {$saved_data->customer_name},</p>" .
            "<p>Booking <b>{$field->name}</b> berhasil dibuat.</p>" .
            "<p>Tanggal: {$booking_date->format('d M Y')}</p>" .
            "<p>Harga: Rp " . number_format($field->price) . "</p>" .
            "<p><a href='{$xendit_result['invoice_url']}'>Bayar Sekarang</a></p>";
        // "<p>Halo {$saved_data->customer_name},</p>" .
        // "<p>Booking lapangan tanggal "
        // . $booking_date->format('d M Y')
        // . " dengan Order ID {$saved_data->order_id} berhasil dibuat.</p>" .
        // "<p>Silakan lakukan pembayaran melalui link berikut:</p>" .
        // "<p><a href='{$xendit_result['invoice_url']}'>Bayar Sekarang</a></p>";

        $payload = [
            "from" => [
                "address" => "booking.lapangan@3d0affff715cfbc2.maileroo.org",
                "display_name" => "Booking Lapangan"
            ],
            "to" => [[
                "address" => $saved_data->email,
                "display_name" => $saved_data->customer_name,
            ]],
            "subject" => "Invoice Booking Lapangan",
            "html" => $mail_body,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Api-Key' => config('services.maileroo.api_key'),
        ])->post('https://smtp.maileroo.com/api/v2/emails', $payload);

        if (!$response->successful()) {
            return response()->json([
                'status' => 'email_failed',
                'body' => $response->body(),
            ], $response->status());
        }

        return response()->json($saved_data, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * @OA\Post(
     *     path="/api/partners/fields/book",
     *     tags={"Partners"},
     *     summary="Webhook pembayaran Xendit",
     *     description="Endpoint webhook untuk menerima notifikasi pembayaran dari Xendit dan mengonfirmasi booking.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_id","external_id","user_id"},
     *             @OA\Property(property="payment_id", type="string", example="pay_123456"),
     *             @OA\Property(property="external_id", type="string", example="uuid-booking"),
     *             @OA\Property(property="user_id", type="string", example="user_xendit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook berhasil diproses"
     *     )
     * )
     */
    public function book(Request $request)
    {
        Log::info('🔥 WEBHOOK XENDIT MASUK 🔥', [
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);

        $data = $request->validate([
            'payment_id' => 'required',
            'external_id' => 'exists:bookings,unique_code',
            'user_id' => 'required',
        ]);

        // $booking = Booking::where('unique_code', $data['external_id'])->first();
        // $field = FieldSchedule::find($booking->field_id);
        // $field = FieldSchedule::find($booking->field_schedule_id);
        $booking = Booking::where('unique_code', $data['external_id'])->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $field = FieldSchedule::find($booking->field_schedule_id);

        if (!$field) {
            return response()->json(['message' => 'Field schedule not found'], 404);
        }



        // contoh: max 10 booking per hari
        if ($field->booked < 10) {

            // update kuota lapangan
            $field->booked++;
            $field->save();

            // update status booking
            $booking->is_booked = 1;
            $booking->save();

            // email sukses
            $link = "http://localhost:8000/use/" . $booking->unique_code;

            $mail_body =
                "<p>Halo {$booking->customer_name}!</p>" .
                "<p>Pembayaran booking lapangan <b>{$field->field_name}</b> berhasil.</p>" .
                "<p>Waktu: {$booking->start_time} - {$booking->end_time}</p>" .
                "<p>Bukti booking: <a href='$link'>Klik di sini</a></p>";

            $payload = [
                "from" => [
                    "address" => "booking.lapangan@3d0affff715cfbc2.maileroo.org",
                    "display_name" => "Booking Lapangan"
                ],
                "to" => [[
                    "address" => $booking->email,
                    "display_name" => $booking->customer_name,
                ]],
                "subject" => "Booking Lapangan Berhasil",
                "html" => $mail_body,
            ];
        } else {

            // lapangan penuh
            $booking->status = 'canceled';
            $booking->save();

            $mail_body =
                "<p>Halo {$booking->customer_name},</p>" .
                "<p>Maaf, booking lapangan <b>{$field->field_name}</b> dibatalkan karena penuh.</p>";

            $payload = [
                "from" => [
                    "address" => "booking.lapangan@3d0affff715cfbc2.maileroo.org",
                    "display_name" => "Booking Lapangan"
                ],
                "to" => [[
                    "address" => $booking->email,
                    "display_name" => $booking->customer_name,
                ]],
                "subject" => "Booking Lapangan Dibatalkan",
                "html" => $mail_body,
            ];
        }

        Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Api-Key' => config('services.maileroo.api_key'),
        ])->post('https://smtp.maileroo.com/api/v2/emails', $payload);

        return response()->json(['message' => 'Callback processed'], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
