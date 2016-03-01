<?php namespace App\Http\Controllers;

use Auth;
use Input;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Puzzle;
use App\Models\PuzzleHelper;
use App\Models\PuzzleHelperSquare;
use App\Models\PuzzleSquare;
use App\Models\PuzzleTemplate;
use App\Models\User;

class PuzzleController extends Controller{
    public static function calculatePuzzleHelperSquares(){
    }
    
    public static function truncatePuzzleHelperSquares(){
        DB::raw('truncate table puzzle_helper_square_options');
        DB::raw('truncate table puzzle_helper_squares');
    }
}