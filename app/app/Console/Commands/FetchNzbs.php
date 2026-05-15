<?php

namespace App\Console\Commands;

use App\Models\ApiResponse;
use App\Services\Api\NzbFetcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

#[Signature('app:fetch-nzbs')]
#[Description('Fetch NZBs from the Newznab API and store the items for later processing')]
class FetchNzbs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(NzbFetcher $NzbService): void
    {
        /** @var array $urls */
        $urls = config('laranab.newznab_apis');

        foreach ($urls as $url) {
            try {
                $items = $NzbService->fetch($url);
            } catch (ConnectionException|\Exception $e) {
                Log::error('Failed to fetch NZBs from ' . $url . ': ' . $e->getMessage());
                continue;
            }

            ApiResponse::create([
                'source' => $url,
                'payload' => $items,
            ]);

            $this->info('API response stored');
        }
    }
}
