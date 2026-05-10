<?php

namespace HiEvents\Services\Domain\Report\OrganizerReports;

use HiEvents\DomainObjects\Status\OrderRefundStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Helper\Currency;
use HiEvents\Repository\Interfaces\OrganizerRepositoryInterface;
use HiEvents\Services\Domain\Report\DTO\PaginatedReportDTO;
use Illuminate\Cache\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;

class PlatformFeesReport
{
    private const CACHE_TTL_SECONDS = 30;

    public function __construct(
        private readonly Repository                   $cache,
        private readonly DatabaseManager              $queryBuilder,
        private readonly OrganizerRepositoryInterface $organizerRepository,
    ) {
    }

    public function generateReport(
        int     $organizerId,
        ?string $currency = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int    $eventId = null,
        int     $page = 1,
        int     $perPage = 1000,
    ): PaginatedReportDTO {
        $organizer = $this->organizerRepository->findById($organizerId);
        $timezone = $organizer->getTimezone();

        $endDate = $endDate
            ? $endDate->copy()->setTimezone($timezone)->endOfDay()
            : now($timezone)->endOfDay();
        $startDate = $startDate
            ? $startDate->copy()->setTimezone($timezone)->startOfDay()
            : $endDate->copy()->subDays(30)->startOfDay();

        $cacheKey = $this->getCacheKeyWithEvent($organizerId, $currency, $startDate, $endDate, $eventId, $page, $perPage);

        $total = $this->cache->remember(
            key: $cacheKey . '.count',
            ttl: Carbon::now()->addSeconds(self::CACHE_TTL_SECONDS),
            callback: fn() => $this->getCount($organizerId, $startDate, $endDate, $currency, $eventId)
        );

        $results = $this->cache->remember(
            key: $cacheKey,
            ttl: Carbon::now()->addSeconds(self::CACHE_TTL_SECONDS),
            callback: fn() => $this->queryBuilder->select(
                $this->buildSqlQuery($startDate, $endDate, $currency, $eventId, $page, $perPage),
                [
                    'organizer_id' => $organizerId,
                ]
            )
        );

        $data = collect($results)->map(function ($row) {
            $currencyCode = strtoupper($row->currency ?? 'USD');

            return (object) [
                'event_name'       => $row->event_name,
                'event_id'         => $row->event_id,
                'payment_date'     => $row->payment_date,
                'order_reference'  => $row->order_reference,
                'order_id'         => $row->order_id,
                'amount_paid'      => Currency::round($row->amount_paid ?? 0),
                'fee_amount'       => Currency::round($row->application_fee_amount ?? 0),
                'vat_rate'         => null,
                'vat_amount'       => 0,
                'total_fee'        => Currency::round($row->application_fee_amount ?? 0),
                'currency'         => $currencyCode,
                'payment_intent_id' => $row->transaction_id,
            ];
        });

        return new PaginatedReportDTO(
            data: $data,
            total: $total,
            page: $page,
            perPage: $perPage,
            lastPage: (int) ceil($total / $perPage),
        );
    }

    private function getCount(int $organizerId, Carbon $startDate, Carbon $endDate, ?string $currency, ?int $eventId): int
    {
        $result = $this->queryBuilder->select(
            $this->buildCountQuery($startDate, $endDate, $currency, $eventId),
            ['organizer_id' => $organizerId]
        );

        return (int) ($result[0]->count ?? 0);
    }

    private function buildCountQuery(Carbon $startDate, Carbon $endDate, ?string $currency, ?int $eventId): string
    {
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $completedStatus = OrderStatus::COMPLETED->name;
        $refundedStatus = OrderRefundStatus::REFUNDED->name;
        $currencyFilter = $this->buildCurrencyFilter('oppf.currency', $currency);
        $eventFilter = $this->buildEventFilter($eventId);

        return <<<SQL
            SELECT COUNT(*) as count
            FROM order_payment_platform_fees oppf
            INNER JOIN orders o ON oppf.order_id = o.id
            INNER JOIN events e ON o.event_id = e.id
            WHERE e.organizer_id = :organizer_id
                AND e.deleted_at IS NULL
                AND o.deleted_at IS NULL
                AND oppf.deleted_at IS NULL
                AND o.status = '$completedStatus'
                AND (o.refund_status IS NULL OR o.refund_status != '$refundedStatus')
                AND oppf.application_fee_amount > 0
                AND oppf.created_at >= '$startDateStr 00:00:00'
                AND oppf.created_at <= '$endDateStr 23:59:59'
                $currencyFilter
                $eventFilter
SQL;
    }

    private function buildSqlQuery(Carbon $startDate, Carbon $endDate, ?string $currency, ?int $eventId, int $page = 1, int $perPage = 1000): string
    {
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $completedStatus = OrderStatus::COMPLETED->name;
        $refundedStatus = OrderRefundStatus::REFUNDED->name;
        $currencyFilter = $this->buildCurrencyFilter('oppf.currency', $currency);
        $eventFilter = $this->buildEventFilter($eventId);
        $offset = ($page - 1) * $perPage;

        return <<<SQL
            SELECT
                e.title AS event_name,
                e.id AS event_id,
                oppf.created_at AS payment_date,
                o.short_id AS order_reference,
                o.id AS order_id,
                o.total_gross AS amount_paid,
                oppf.application_fee_amount,
                oppf.currency,
                oppf.transaction_id
            FROM order_payment_platform_fees oppf
            INNER JOIN orders o ON oppf.order_id = o.id
            INNER JOIN events e ON o.event_id = e.id
            WHERE e.organizer_id = :organizer_id
                AND e.deleted_at IS NULL
                AND o.deleted_at IS NULL
                AND oppf.deleted_at IS NULL
                AND o.status = '$completedStatus'
                AND (o.refund_status IS NULL OR o.refund_status != '$refundedStatus')
                AND oppf.application_fee_amount > 0
                AND oppf.created_at >= '$startDateStr 00:00:00'
                AND oppf.created_at <= '$endDateStr 23:59:59'
                $currencyFilter
                $eventFilter
            ORDER BY oppf.created_at DESC
            LIMIT $perPage OFFSET $offset
SQL;
    }

    private function buildEventFilter(?int $eventId): string
    {
        if ($eventId === null) {
            return '';
        }
        return "AND e.id = $eventId";
    }

    private function getCacheKeyWithEvent(int $organizerId, ?string $currency, ?Carbon $startDate, ?Carbon $endDate, ?int $eventId, int $page, int $perPage): string
    {
        return static::class . "$organizerId.$currency.{$startDate?->toDateString()}.{$endDate?->toDateString()}.$eventId.$page.$perPage";
    }

    private function buildCurrencyFilter(string $column, ?string $currency): string
    {
        if ($currency === null) {
            return '';
        }
        $escapedCurrency = addslashes($currency);
        return "AND $column = '$escapedCurrency'";
    }
}
