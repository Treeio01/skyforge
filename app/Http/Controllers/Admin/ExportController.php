<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function transactions(): StreamedResponse
    {
        return $this->stream(
            'transactions-'.now()->format('Y-m-d_His').'.csv',
            ['id', 'user_id', 'type', 'amount_rub', 'balance_before_rub', 'balance_after_rub', 'description', 'created_at'],
            Transaction::query()->orderByDesc('id'),
            fn (Transaction $t) => [
                $t->id,
                $t->user_id,
                $t->type?->value ?? $t->type,
                number_format(((int) $t->amount) / 100, 2, '.', ''),
                number_format(((int) $t->balance_before) / 100, 2, '.', ''),
                number_format(((int) $t->balance_after) / 100, 2, '.', ''),
                $t->description,
                $t->created_at?->toIso8601String(),
            ],
        );
    }

    public function deposits(): StreamedResponse
    {
        return $this->stream(
            'deposits-'.now()->format('Y-m-d_His').'.csv',
            ['id', 'user_id', 'method', 'amount_rub', 'status', 'completed_at', 'created_at'],
            Deposit::query()->orderByDesc('id'),
            fn (Deposit $d) => [
                $d->id,
                $d->user_id,
                $d->method?->value ?? $d->method,
                number_format(((int) $d->amount) / 100, 2, '.', ''),
                $d->status?->value ?? $d->status,
                $d->completed_at?->toIso8601String(),
                $d->created_at?->toIso8601String(),
            ],
        );
    }

    public function withdrawals(): StreamedResponse
    {
        return $this->stream(
            'withdrawals-'.now()->format('Y-m-d_His').'.csv',
            ['id', 'user_id', 'amount_rub', 'status', 'trade_offer_id', 'completed_at', 'created_at'],
            Withdrawal::query()->orderByDesc('id'),
            fn (Withdrawal $w) => [
                $w->id,
                $w->user_id,
                number_format(((int) $w->amount) / 100, 2, '.', ''),
                $w->status?->value ?? $w->status,
                $w->trade_offer_id,
                $w->completed_at?->toIso8601String(),
                $w->created_at?->toIso8601String(),
            ],
        );
    }

    /**
     * @param  array<int, string>  $headers
     * @param  callable(object): array<int, mixed>  $rowMapper
     */
    private function stream(string $filename, array $headers, Builder $query, callable $rowMapper): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $query, $rowMapper): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);

            $query->chunkById(1000, function ($rows) use ($out, $rowMapper): void {
                foreach ($rows as $row) {
                    fputcsv($out, $rowMapper($row));
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
