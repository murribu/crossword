<?php

namespace App\Console\Commands;

use DB;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;

use App\Models\Puzzle;

class CalculatePuzzleHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CalculatePuzzleHelper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates PuzzleHelpers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $puzzles = Puzzle::whereRaw('id in (select distinct puzzle_id from puzzle_squares where timestamp_utc > coalesce((select last_puzzle_helper_run_timestamp from settings where slug = ? limit 1),0))', array('prod'))
        ->get();
        
        return $puzzles;
    }
}