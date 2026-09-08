<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HikvisionAttendanceRequest;
use App\Services\HikvisionAttendanceService;

class HikvisionAttendanceController extends Controller
{
    public function __construct(private HikvisionAttendanceService $service) {}

    /**
     * Receive a single Hikvision attendance event from the office sync agent.
     *
     * POST /api/hikvision/attendance
     * Authorization: Bearer <HIKVISION_SYNC_TOKEN>
     */
    public function store(HikvisionAttendanceRequest $request)
    {
        $result = $this->service->process($request->validated());

        $body = [
            'status'  => $result['status'],
            'message' => $result['message'],
        ];

        if (isset($result['data'])) {
            $body['data'] = $result['data'];
        }

        return response()->json($body, $result['http_code']);
    }
}
