<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateYearlyPartition extends Command
{
    protected $signature = 'partition:create-yearly';
    protected $description = 'Create yearly partition for transactions';

    public function handle()
    {
        $year = Carbon::now()->year;

        $from = "{$year}-01-01";
        $to   = ($year + 1) . "-01-01";
        $partition = "transactions_{$year}";

        DB::statement("
            CREATE TABLE IF NOT EXISTS {$partition}
            PARTITION OF transactions
            FOR VALUES FROM ('{$from}') TO ('{$to}');
        ");

        $this->info("Partition {$partition} ready");
    }
}
