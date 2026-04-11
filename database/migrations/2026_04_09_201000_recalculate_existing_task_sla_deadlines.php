<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tasks') || !Schema::hasTable('nodos')) {
            return;
        }

        $tasks = DB::table('tasks as t')
            ->join('nodos as n', 'n.id', '=', 't.nodo_id')
            ->whereNotNull('t.nodo_id')
            ->where(function ($query) {
                $query->whereNotNull('t.sla_hours')
                    ->orWhereNotNull('n.sla_horas');
            })
            ->select([
                't.id',
                't.created_at',
                't.sla_started_at',
                't.sla_hours',
                'n.sla_horas as nodo_sla_horas',
            ])
            ->orderBy('t.id')
            ->get();

        foreach ($tasks as $task) {
            $slaHours = (int) ($task->sla_hours ?: $task->nodo_sla_horas ?: 0);
            if ($slaHours <= 0) {
                continue;
            }

            $slaStartedAt = $task->sla_started_at
                ? Carbon::parse($task->sla_started_at)
                : Carbon::parse($task->created_at);

            $slaDueAt = $this->addBusinessHours($slaStartedAt->copy(), $slaHours);

            DB::table('tasks')
                ->where('id', $task->id)
                ->update([
                    'sla_hours' => $slaHours,
                    'sla_started_at' => $slaStartedAt->format('Y-m-d H:i:s'),
                    'sla_due_at' => $slaDueAt->format('Y-m-d H:i:s'),
                    'due_at' => $slaDueAt->format('Y-m-d H:i:s'),
                ]);
        }
    }

    public function down(): void
    {
        // No revertimos este ajuste porque corrige snapshots históricos.
    }

    private function addBusinessHours(Carbon $from, int $hours): Carbon
    {
        $secondsRemaining = max(0, $hours) * 3600;
        $cursor = $this->normalizeBusinessMoment($from->copy());

        if ($secondsRemaining === 0) {
            return $cursor;
        }

        while ($secondsRemaining > 0) {
            $cursor = $this->normalizeBusinessMoment($cursor);

            $blockEnd = $cursor->hour < 12
                ? $cursor->copy()->setTime(12, 0, 0)
                : $cursor->copy()->setTime(17, 0, 0);

            $available = max(0, $cursor->diffInSeconds($blockEnd, false));

            if ($available <= 0) {
                $cursor = $this->normalizeBusinessMoment($blockEnd->copy()->addSecond());
                continue;
            }

            if ($secondsRemaining <= $available) {
                return $cursor->copy()->addSeconds($secondsRemaining);
            }

            $secondsRemaining -= $available;
            $cursor = $this->normalizeBusinessMoment($blockEnd->copy()->addSecond());
        }

        return $cursor;
    }

    private function normalizeBusinessMoment(Carbon $moment): Carbon
    {
        $dt = $moment->copy();

        while ($dt->isWeekend()) {
            $dt->addDay()->setTime(8, 0, 0);
        }

        if ($dt->hour < 8) {
            return $dt->setTime(8, 0, 0);
        }

        if (($dt->hour === 12 && $dt->minute >= 0) || ($dt->hour > 12 && $dt->hour < 13)) {
            return $dt->setTime(13, 0, 0);
        }

        if ($dt->hour >= 17) {
            do {
                $dt->addDay()->setTime(8, 0, 0);
            } while ($dt->isWeekend());

            return $dt;
        }

        return $dt;
    }
};
