<?php namespace App\Http\Controllers;

use Auth;
use Input;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Clue;
use App\Models\Puzzle;
use App\Models\PuzzleGuess;
use App\Models\PuzzleGuessSquare;
use App\Models\PuzzleSquare;
use App\Models\PuzzleTemplate;
use App\Models\User;

class PuzzleController extends Controller
{
    public function getPuzzles($limit = 100){
        return Puzzle::getPuzzles($limit);
    }
    
    public function showIncompletePuzzles(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $puzzles = Puzzle::getIncompletePuzzlesByUser($user);
        
        return $puzzles;
    }
    
    public function postPuzzle(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        if (!Input::has('template_slug')){
            return array('errors' => array('No puzzle template selected'));
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
    
    public function activatePuzzle(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        if (!Input::has('slug')){
            return array('errors' => array('No puzzle selected'));
        }
        $p = Puzzle::findBySlug(Input::get('slug'));
        if ($p->user_id != $user->id){
            return array('errors' => array('This is not your puzzle'));
        }
        return $p->activate();
    }
    
    public function getPuzzleForEdit($slug){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $p = Puzzle::with('clues')
            ->whereNull('puzzles.deleted_timestamp_utc')
            ->with('puzzle_template')
            ->where('slug', $slug)
            ->first();
            
        if (!$p || $p->user_id != $user->id){
            return array('errors' => array('Puzzle not available'));
        }

        $p->clue_squares = $p->puzzle_template->clueSquares();
        $template_owner = User::find($p->puzzle_template->user_id);
        $p->puzzle_template->owner = $template_owner->name;
        $p->puzzle_template->owner_username = $template_owner->username;
        $puzzle_owner = User::find($p->user_id);
        $p->owner = $puzzle_owner->name;
        $p->owner_username = $puzzle_owner->username;
        $p->puzzle_squares = $p->puzzle_squares(true);
        
        return $p;
    }
    
    public function getPuzzle($slug){
        $p = Puzzle::with('clues')
            ->whereNull('puzzles.deleted_timestamp_utc')
            ->with('puzzle_template')
            ->where('slug', $slug)
            ->first();
        
        if (!$p){
            return array('errors' => array('Puzzle not available'));
        }
        
        $p->clue_squares = $p->puzzle_template->clueSquares();
        $p->puzzle_squares = $p->puzzle_squares(false);
        $template_owner = User::find($p->puzzle_template->user_id);
        $p->puzzle_template->owner = $template_owner->name;
        $p->puzzle_template->owner_username = $template_owner->username;
        $puzzle_owner = User::find($p->user_id);
        $p->owner = $puzzle_owner->name;
        $p->owner_username = $puzzle_owner->username;
        $user = Auth::user();
        if ($user){
            $guess = PuzzleGuess::where('puzzle_id', $p->id)
                ->where('user_id', $user->id)
                ->first();
                
            if ($guess){
                $p->solved = $guess->solved();
            }else{
                $p->solved = 0;
            }
            
            $p->guess_squares = $p->guess_squares($user->id);
        }else{
            $p->solved = 0;
            $p->guess_squares = array();
        }
        
        return $p;
    }
    
    public function postPuzzleTemplate(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
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
            return array('errors' => array('This isn\'t your puzzle'));
        }
        
        return PuzzleSquare::findSuggestion($p, $row, $col);
    }
    
    public function getProblemSquares($slug){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $p = Puzzle::findBySlug($slug);
        
        if ($p->user_id != $user->id){
            return array('errors' => array('This isn\'t your puzzle'));
        }
        
        return $p->findProblemSquares();
    }
    
    public function postClue(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        if (!Input::has('puzzle_slug')){
            return array('errors' => array('Invalid input: no puzzle slug'));
        }
        $slug = Input::get('puzzle_slug');
        $p = Puzzle::findBySlug($slug);
        if ($user->id != $p->user_id){
            return array('errors' => array('This is not your puzzle'));
        }
        $args = Input::all();
        
        $c = Clue::replace($args);
        
        return $c;
    }
    
    public function postGuessSquare(){
        $user = Auth::user();
        if (!$user){
            $user = new User;
            $user->name = "temporary user";
            $user->username = User::findUsername($user->name);
            $user->active = 0;
            $user->temporary = 1;
            $user->created_timestamp_utc = time();
            $user->updated_timestamp_utc = time();
            $user->save();
            Auth::login($user);
        }
        $args = Input::all();
        $args['user_id'] = $user->id;
        
        $pgs = PuzzleGuessSquare::replace($args);
        
        return $pgs;
    }
    
    public function deletePuzzle($slug){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $p = Puzzle::findBySlug($slug);
        
        if ($p->user_id != $user->id){
            return array('errors' => array('This isn\'t your puzzle'));
        }
        
        return $p->delete();
    }
    
    public function setName(){
        $user = Auth::user();
        if (!$user){
            return array('errors' => array('Please log in'));
        }
        $p = Puzzle::findBySlug(Input::get('puzzle_slug'));
        
        if ($p->user_id != $user->id){
            return array('errors' => array('This isn\'t your puzzle'));
        }
        
        $p->name = Input::get('name');
        $p->slug = Puzzle::findSlug(Input::get('name'));
        $p->save();
        
        return array('success' => 1, 'slug' => $p->slug);
    }
    
}
