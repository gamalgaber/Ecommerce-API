<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PersonalAccessToken;
use Carbon\Carbon;

class DeleteExpiredTokens extends Command
{
    // Command signature
    protected $signature = 'tokens:delete-expired';

    // Command description
    protected $description = 'Delete expired personal access tokens';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $expiredTokens = PersonalAccessToken::where('expires_at', '<', Carbon::now())->get();

        foreach ($expiredTokens as $token) {
            $token->delete();
        }

        $this->info('Expired tokens deleted successfully.');
        return 0;
    }
}
