<?php namespace App\Http\Controllers;

use Auth;
use Input;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Puzzle;
use App\Models\PuzzleSquare;
use App\Models\PuzzleTemplate;
use App\Models\User;

class PuzzleController extends Controller
{
    public function showPopularPuzzles(){
        $puzzles = Puzzle::take(5)->get();
        
        return $puzzles;
    }
    
    public function postPuzzle(){
        $user = Auth::user();
        if (!$user){
            return array('errors', array('Please log in'));
        }
        if (!Input::has('template_slug')){
            return array('errors', array('No puzzle template selected'));
        }
        $p = new Puzzle;

        $args = array();
        $args['user_id'] = $user->id;
        $pt = PuzzleTemplate::where('slug', Input::get('template_slug'))->first();
        $args['puzzle_template_id'] = $pt->id;
        if (!isset($args['name'])){
            $args['name'] = $user->name."'s ".$pt->name." Puzzle";
        }
        
        if ($p->validate($args)){
            return Puzzle::create($args);
        }else{
            abort('401', $p->errors());
        }
    }
    
    public function getPuzzle($slug){
        $p = Puzzle::where('slug', $slug)
            ->first();
        $p->puzzle_template = $p->puzzle_template;
        $p->clue_squares = $p->puzzle_template->clueSquares();
        $p->puzzle_squares = $p->puzzle_squares();
        $p->owner = $p->owner();
        
        return $p;
    }
    
    public function postPuzzleTemplate(){
        $user = Auth::user();
        if (!$user){
            return array('errors', array('Please log in'));
        }
        
        $pt = new PuzzleTemplate;
        $args = Input::all();
        $args['user_id'] = $user->id;
        
        if ($pt->validate($args)){
            return PuzzleTemplate::create($args);
        }else{
            abort('401', $pt->errors());
        }
    }
    
    public function getPuzzleTemplates(){
        $pts = PuzzleTemplate::findActive();
        
        return $pts;
    }
    
    public function getPuzzleTemplate($slug){
        return PuzzleTemplate::findBySlug($slug);
    }
    
    public function postSquare(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $args = Input::all();
        $args['user_id'] = $user->id;
        
        $ps = PuzzleSquare::replace($args);
        
        return $ps;
    }
    
    public function getSuggestion($slug, $row, $col){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $p = Puzzle::findBySlug($slug);
        
        if ($p->user_id != $user->id){
            return array('errors', array('This isn\'t your puzzle'));
        }
        
        return PuzzleSquare::findSuggestion($p, $row, $col);
    }
}
