<?php

namespace ALajusticia\Expirable\Commands;

use ALajusticia\Expirable\Traits\Expirable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class PurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expirable:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the expired records.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $models = Config::get('expirable.purge', []);

        if (count($models)) {

            $this->line('');
            $this->comment('Deleting expired records...');
            $this->line('');

            foreach (Config::get('expirable.purge', []) as $purgeable) {

                $this->line($purgeable . ': ');

                if (in_array(Expirable::class, class_uses_recursive($purgeable))) {

                    $total = call_user_func($purgeable . '::onlyExpired')->forceDelete();

                    if ($total > 0) {
                        $this->info($total . ' ' . Str::plural('record', $total) . ' deleted.');
                    } else {
                        $this->comment('Nothing to delete.');
                    }

                } else {
                    $this->error('This model is not expirable! (Expirable trait not found)');
                }

                $this->line('');
            }

            $this->info('Purge completed!');
            $this->line('');
        } else {
            $this->comment('There is no model in the purge array.');
            $this->comment('Add models you want to purge in the expirable.php configuration file.');
        }
    }
}
