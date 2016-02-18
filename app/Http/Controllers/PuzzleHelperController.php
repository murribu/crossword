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
    public function calculatePuzzleHelperSquares($puzzle){
        return $puzzle->calculatePuzzleHelperSquares();
    }
}