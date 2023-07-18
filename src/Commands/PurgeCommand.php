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
    protected $signature = 'expirable:purge
                            {model?* : Optional list of models to purge. If not provided, will take the models in the purge array of the configuration file.}
                            {--since= : Time since expiration.}
                            {--mode= : Whether the deletion mode is "soft" (hard otherwise). If not provided, will take the mode value of the configuration file or default to "hard".}';

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
        $models = $this->argument('model');

        if (empty($models)) {
            $models = Config::get('expirable.purge', []);
        }

        if (count($models)) {

            $expiredSince = $this->option('since');
            $mode = $this->option('mode') ?: Config::get('expirable.mode', 'hard');

            $this->line('');
            $this->comment('Deleting expired records...');
            $this->line('');

            foreach ($models as $purgeable) {

                $this->line($purgeable . ': ');

                if (in_array(Expirable::class, class_uses_recursive($purgeable))) {

                    if (!$expiredSince) {
                        $query = call_user_func($purgeable . '::onlyExpired');
                    } else {
                        $query = call_user_func($purgeable . '::expiredSince', $expiredSince);
                    }

                    $total = Str::lower($mode) == 'soft' ? $query->delete() : $query->forceDelete();

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
            $this->comment('There is no model to purge.');
            $this->comment('Add models you want to purge in the expirable.php configuration file or pass models in argument of this command.');
        }
    }
}
