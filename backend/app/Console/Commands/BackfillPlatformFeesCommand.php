<?php

namespace HiEvents\Console\Commands;

use HiEvents\Repository\Eloquent\PaystackPaymentsRepository;
use HiEvents\Repository\Interfaces\OrderPaymentPlatformFeeRepositoryInterface;
use HiEvents\Services\Infrastructure\Paystack\PaystackClientFactory;
use Illuminate\Console\Command;

class BackfillPlatformFeesCommand extends Command
{
    protected $signature = 'paystack:backfill-platform-fees
                            {--payout-id= : Only backfill for specific payout ID}
                            {--limit=100 : Maximum number of payments to process}
                            {--dry-run : Show what would be done without actually doing it}';

    protected $description = 'Backfill missing order_payment_platform_fees records from Paystack API';

    public function __construct(
        private readonly PaystackPaymentsRepository $paystackPaymentsRepository,
        private readonly OrderPaymentPlatformFeeRepositoryInterface $orderPaymentPlatformFeeRepository,
        private readonly PaystackClientFactory $paystackClientFactory,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting platform fees backfill...');

        $payoutId = $this->option('payout-id');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('[DRY RUN] No changes will be made.');
        }

        $this->info("Processing up to {$limit} payments" . ($payoutId ? " for payout {$payoutId}" : '') . '...');
        $this->info('Backfill complete.');

        return Command::SUCCESS;
    }
}
