<?php

namespace HiEvents\Console\Commands;

use Illuminate\Console\Command;

class BackfillPlatformFeesCommand extends Command
{
    protected $signature = 'paystack:backfill-platform-fees
                            {--limit=100 : Maximum number of payments to process}
                            {--dry-run : Show what would be done without actually doing it}';

    protected $description = 'Backfill missing order_payment_platform_fees records from Paystack payments';

    public function handle(): int
    {
        $this->info('This command is a placeholder. Platform fees are recorded automatically during payment processing.');
        return Command::SUCCESS;
    }
}
