<?php namespace App\Models;

use DB;

use Illuminate\Database\Eloquent\Model;

class PuzzleTemplate extends Model {
	protected $table = 'puzzle_templates';
	public $timestamps = FALSE;
    
    const SELECT_RAW = 'puzzle_templates.id, puzzle_templates.name, puzzle_templates.slug, puzzle_templates.symmetrical, width, height, users.name owner, users.username, puzzle_templates.timestamp_utc*1000 created';
    
    protected $rules = array(
        'name'      => 'required',
        'width'     => 'required|numeric|min:5|max:20',
        'height'    => 'required|numeric|min:5|max:20',
        'user_id'   => 'required',
    );
    
    private $errors;
    
    public function validate($data){
        $v = \Validator::make($data, $this->rules);
        
        if ($v->fails()){
            $this->errors = $v->errors();
            return false;
        }

        return true;
    }

    public function errors(){
        return $this->errors;
    }
    
    public function puzzleTemplateSquares(){
        return $this->hasMany(PuzzleTemplateSquare::class);
    }
    
    public function blackSquares(){
        $squares = PuzzleTemplateSquare::where('puzzle_template_id', $this->id)
            ->where('square_type', 'black')
            ->get();
        
        $ret = array();
        foreach($squares as $s){
            $ret[] = $s->row.'-'.$s->col;
        }
        
        return $ret;
    }
    
    public function clueSquares(){
        $squares = PuzzleTemplateSquare::where('puzzle_template_id', $this->id)
            ->whereNotNull('clue_number')
            ->orderBy('clue_number')
            ->get();
        
        $ret = array();
        foreach($squares as $s){
            $ret[] = $s->row.'-'.$s->col;
        }
        
        return $ret;
    }
    
    public static function create(array $args = array()){
        $v = new PuzzleTemplate;
        if ($v->validate($args)){
            $pt = new PuzzleTemplate;
            $pt->name = $args['name'];
            $pt->width = $args['width'];
            $pt->height = $args['height'];
            $pt->user_id = $args['user_id'];
            $pt->active = 1;
            $pt->timestamp_utc = time();
            $pt->slug = PuzzleTemplate::findSlug($args['name']);
            $pt->save();
            
            $clue_number = 1;
            for($row = 1; $row <= $pt->height; $row++){
                for($col = 1; $col <= $pt->width; $col++){
                    $sq = new PuzzleTemplateSquare;
                    $sq->puzzle_template_id = $pt->id;
                    $sq->row = $row;
                    $sq->col = $col;
                    if (in_array($row.'-'.$col, $args['blackSquares'])){
                        $sq->square_type = 'black';
                    }else{
                        if ($row == 1 || $col == 1 || in_array(($row - 1).'-'.$col, $args['blackSquares']) || in_array($row.'-'.($col - 1), $args['blackSquares'])){
                            $sq->clue_number = $clue_number++;
                        }
                        $sq->square_type = 'white';
                    }
                    $sq->save();
                }
            }
            
            return $pt;
        }else{
            return array('errors' => $v->errors);
        }
    }
    
    public static function findSlug($name){
        $slug = strtolower(preg_replace("/[^a-zA-Z\d]/", "-", $name));
        $origslug = $slug;
        $exists = PuzzleTemplate::where('slug', $slug)->first();
        $i = 0;
        while ($exists){
            $slug = $origslug."-".$i++;
            $exists = PuzzleTemplate::where('slug', $slug)->first();
        }
        return $slug;
    }
    
    public static function findBySlug($slug){
        $pt = self::leftjoin('users', 'users.id', '=', 'puzzle_templates.user_id')
            ->selectRaw(self::SELECT_RAW)
            ->where('puzzle_templates.active', 1)
            ->where('slug', $slug)
            ->first();
            
        if ($pt){
            $pt->blackSquares = $pt->blackSquares();
            $pt->clueSquares = $pt->clueSquares();
            unset($pt->id);
        }
        
        return $pt;
    }
    
    public static function findActive(){
        $pts = PuzzleTemplate::leftjoin('users', 'users.id', '=', 'puzzle_templates.user_id')
            ->selectRaw(self::SELECT_RAW)
            ->where('puzzle_templates.active', 1)->get();
            
        foreach($pts as $k=>$pt){
            $pts[$k]->blackSquares = $pt->blackSquares();
            $pts[$k]->clueSquares = $pt->clueSquares();
            unset($pts[$k]->id);
        }
        
        return $pts;
    }
}