<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackfillAttendanceFromLogs extends Migration
{
    /**
     * Run the migrations.
     * This is a one-time backfill: mark legacy special logs and fill missing attendance times from logs.
     */
    public function up()
    {
        DB::transaction(function () {
            // 1) Mark legacy attendance logs where shift_type = 'special' but type is null
            DB::table('attendance_logs')
                ->where('shift_type', 'special')
                ->where(function ($q) {
                    $q->whereNull('type')->orWhere('type', '');
                })
                ->update(['type' => 'special']);

            // 2) Backfill attendances' check_in_time and check_out_time from logs when missing
            $attendances = DB::table('attendances')
                ->whereNull('check_in_time')
                ->orWhereNull('check_out_time')
                ->get();

            foreach ($attendances as $att) {
                $userId = $att->user_id;
                $date = Carbon::parse($att->date)->format('Y-m-d');

                // Prefer to use logs linked by attendance_id, otherwise fallback to user/date
                $logsQuery = DB::table('attendance_logs')
                    ->where(function ($q) use ($att) {
                        $q->where('attendance_id', $att->id)
                          ->orWhere(function ($qq) use ($att) {
                              $qq->where('user_id', $att->user_id)
                                 ->whereDate('timestamp', $att->date);
                          });
                    })
                    ->orderBy('timestamp', 'asc');

                $logs = $logsQuery->get();

                if ($logs->count() === 0) continue;

                $firstCheckIn = $logs->firstWhere('action', 'check_in');
                $lastCheckOut = $logs->where('action', 'check_out')->last();

                $update = [];
                if (($att->check_in_time === null || $att->check_in_time == '') && $firstCheckIn) {
                    $update['check_in_time'] = Carbon::parse($firstCheckIn->timestamp)->format('Y-m-d H:i:s');
                    $update['check_in_latitude'] = $firstCheckIn->latitude ?? null;
                    $update['check_in_longitude'] = $firstCheckIn->longitude ?? null;
                    $update['check_in_address'] = $firstCheckIn->address ?? null;
                }
                if (($att->check_out_time === null || $att->check_out_time == '') && $lastCheckOut) {
                    $update['check_out_time'] = Carbon::parse($lastCheckOut->timestamp)->format('Y-m-d H:i:s');
                    $update['check_out_latitude'] = $lastCheckOut->latitude ?? null;
                    $update['check_out_longitude'] = $lastCheckOut->longitude ?? null;
                    $update['check_out_address'] = $lastCheckOut->address ?? null;
                }

                if (!empty($update)) {
                    DB::table('attendances')->where('id', $att->id)->update($update);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     * We do not revert the backfill automatically.
     */
    public function down()
    {
        // Intentionally left blank. This is a one-time data migration.
    }
}
