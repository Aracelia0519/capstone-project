<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distributor\DistributorWorkingHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkingHourController extends Controller
{
    /**
     * Get the current working hours for the authenticated distributor.
     */
    public function index(Request $request)
    {
        $distributorId = $request->user()->id;

        $schedules = DistributorWorkingHour::where('distributor_id', $distributorId)->get();

        // If no schedule exists in DB, return the default structure
        if ($schedules->isEmpty()) {
            return response()->json([
                'schedule' => $this->getDefaultSchedule()
            ]);
        }

        // Map DB results to frontend format
        $formattedSchedule = $schedules->map(function ($item) {
            return [
                'day' => $item->day_of_week,
                // Cast to boolean to ensure Toggle Switch works correctly
                'isOpen' => (boolean) $item->is_open,
                'start' => $item->start_time,
                'end' => $item->end_time,
            ];
        });

        // Ensure the order is Monday-Sunday
        $orderedSchedule = $this->orderSchedule($formattedSchedule);

        return response()->json(['schedule' => $orderedSchedule]);
    }

    /**
     * Save or update working hours.
     */
    public function update(Request $request)
    {
        $request->validate([
            'schedule' => 'required|array',
            'schedule.*.day' => 'required|string',
            'schedule.*.isOpen' => 'boolean',
            'schedule.*.start' => 'nullable|date_format:H:i',
            'schedule.*.end' => 'nullable|date_format:H:i',
        ]);

        $distributorId = $request->user()->id;
        $scheduleData = $request->input('schedule');

        DB::beginTransaction();

        try {
            foreach ($scheduleData as $dayData) {
                DistributorWorkingHour::updateOrCreate(
                    [
                        'distributor_id' => $distributorId,
                        'day_of_week' => $dayData['day']
                    ],
                    [
                        'is_open' => $dayData['isOpen'],
                        'start_time' => $dayData['start'],
                        'end_time' => $dayData['end'],
                    ]
                );
            }

            DB::commit();

            return response()->json(['message' => 'Working hours updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update working hours', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to return default schedule structure.
     */
    private function getDefaultSchedule()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedule = [];

        foreach ($days as $day) {
            $isWeekend = in_array($day, ['Saturday', 'Sunday']);
            $schedule[] = [
                'day' => $day,
                'isOpen' => !$isWeekend,
                'start' => '09:00',
                'end' => '18:00',
            ];
        }

        return $schedule;
    }

    /**
     * Helper to sort the schedule array starting from Monday.
     */
    private function orderSchedule($scheduleCollection)
    {
        $order = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
        
        return $scheduleCollection->sortBy(function ($item) use ($order) {
            return $order[$item['day']] ?? 99;
        })->values()->all();
    }
}