<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model {
	protected $table = 'puzzles';
	public $timestamps = FALSE;
    
    protected $rules = array(
        'name'                  => 'required',
        'user_id'               => 'required|numeric',
        'puzzle_template_id'    => 'required|numeric',
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
    
    public function clues(){
        return $this->hasMany(Clue::class);
    }
    
    public function puzzle_squares($include_answers = false){
        $ret = array();
        
        $db = PuzzleSquare::where('puzzle_id', $this->id)->orderBy('row')->orderBy('col')->get();
        foreach($db as $ps){
            $ret[$ps->row.'-'.$ps->col] = array(
                'square_type' => $ps->square_type,
            );
            if ($include_answers){
                $ret[$ps->row.'-'.$ps->col]['letter'] = $ps->letter;
            }
        }
        
        return $ret;
    }
    
    public function guess_squares($user_id = 0){
        $ps = PuzzleGuess::where('puzzle_id', $this->id)
            ->where('user_id', $user_id)
            ->first();
            
        if (!$ps){
            return array();
        }else{
            $pgs = PuzzleGuessSquare::where('puzzle_guess_id', $ps->id)
                ->orderBy('col')
                ->orderBy('row')
                ->get();
                
            $ret = array();
            foreach($pgs as $pg){
                $ret[$pg->row.'-'.$pg->col] = array(
                    'letter' => $pg->letter,
                );
            }
                
            return $ret;
        }
    }
    
    public function helper_squares(){
        $helper_squares = PuzzleHelperSquare::leftjoin('puzzle_helper_square_options', 'puzzle_helper_square_options.puzzle_helper_square_id', '=', 'puzzle_helper_squares.id')
            ->where('puzzle_id', $this->id)
            ->selectRaw('row', 'col', 'score', 'letter')
            ->orderBy('col')
            ->orderBy('row')
            ->get();
            
        $ret = array();
        for($i = 0; $i < count($helper_squares); $i++){
            $hs = $helper_squares[$i++];
            if (isset($ret[$hs->row.'-'.$hs->col])){
                $ret[$hs->row.'-'.$hs->col][] = array(
                    'score' => $hs->score,
                    'letter' => $hs->letter,
                );
            }else{
                $ret[$hs->row.'-'.$hs->col] = array(
                    array(
                        'score' => $hs->score,
                        'letter' => $hs->letter,
                    ),
                );
            }
        }
    }
    
    public function puzzle_template(){
        return $this->belongsTo(PuzzleTemplate::class);
    }
    
    public function owner(){
        return User::find($this->user_id)->select('name')->first()['name'];
    }
    
    public function findProblemSquares(){
        $pt = $this->puzzle_template;
        $blackSquares = $pt->blackSquares();
        $puzzle_squares = $this->puzzle_squares(true);
        
        $impossibles = array();
        $problems = array();
        
        for($row = 1; $row <= $pt->height; $row++){
            for($col = 1; $col <= $pt->width; $col++){
                if (!in_array($row.'-'.$col, $blackSquares)){
                    if ($puzzle_squares[$row.'-'.$col]['letter'] == ''){
                        $sugg = PuzzleSquare::findSuggestion($this, $row, $col);
                        if (count($sugg['suggestions']) == 0){
                            $impossibles[] = $row.'-'.$col;
                        }else{
                            $total = 0;
                            foreach($sugg['suggestions'] as $s){
                                $total += $s->score;
                            }
                            if($sugg['suggestions'][0]->score/$total > .4){
                                $problems[$row.'-'.$col] = 100*$sugg['suggestions'][0]->score/$total;
                            }
                        }
                    }
                }
            }
        }
        
        return compact('impossibles', 'problems');
    }
    
    public function calculatePuzzleHelperSquares(){
        $pt = $this->puzzle_template;
        $blackSquares = $pt->blackSquares();
        
        $helperSquares = $this->helper_squares();
        
        for($row = 1; $row <= $pt->height; $row++){
            for($col = 1; $col <= $pt->width; $col++){
                if (!in_array($row.'-'.$col, $blackSquares)){
                    if ($puzzle_squares[$row.'-'.$col]['letter'] == ''){
                        
                    }
                }
            }
        }
    }
    
    public function activate($user, $sure = false){
        $missing_clues = array();
        $missing_clues_for_one_letter_words = array();
        $missing_letters = array();
        $words = array();

        $pt = $this->puzzle_template;
        $pt->blackSquares = $pt->blackSquares();
        
        $squares = $this->puzzle_squares(true);
        $clues = $this->clues;
        
        $ordinal = 1;
        for($row = 1; $row <= $pt->height; $row++){
            for($col = 1; $col <= $pt->width; $col++){
                if (!in_array($row.'-'.$col, $pt->blackSquares)
                    && $squares[$row.'-'.$col]['square_type'] != 'black'
                    && (!@$squares[$row.'-'.$col]['letter'] || $squares[$row.'-'.$col]['letter'] == "")){
                    $missing_letters[] = $row.'-'.$col;
                }
                $square_should_have_across_clue = false;
                $square_should_have_down_clue = false;
                if ($col != $pt->width
                    && $squares[$row.'-'.$col]['square_type'] != 'black'
                    && ($col == 1 || $squares[$row.'-'.($col-1)]['square_type'] == 'black')){
                        $square_should_have_across_clue = true;
                }
                if ($row != $pt->height
                    && $squares[$row.'-'.$col]['square_type'] != 'black'
                    && ($row == 1|| $squares[($row - 1).'-'.$col]['square_type'] == 'black')){
                        $square_should_have_down_clue = true;
                }
                if ($square_should_have_down_clue){
                    $found_clue = false;
                    foreach($clues as $clue){
                        if ($clue->ordinal == $ordinal && $clue->direction == 'down'){
                            $found_clue = $clue;
                        }
                    }
                    if (!$found_clue){
                        if ($row == $pt->height || $squares[($row + 1).'-'.$col]['square_type'] == 'black'){
                            $missing_clues_for_one_letter_words[] = $ordinal.' down';
                        }else{
                            $missing_clues[] = $ordinal.' down';
                        }
                    }
                    //find word
                    $word = "";
                    for($word_row = $row; $word_row <= $pt->height && $squares[$word_row.'-'.$col]['square_type'] != 'black'; $word_row++){
                        $word .= $squares[$word_row.'-'.$col]['letter'];
                    }
                    $word_db = Word::where('word', $word)->first();
                    if (!$word_db){
                        $word_db = new Word;
                        $word_db->word = $word;
                        $word_db->length = strlen($word);
                        $word_db->user_id = $user->id;
                        $word_db->timestamp_utc = time();
                        $word_db->save();
                        for($i = 0; $i < strlen($word); $i++){
                            $letter = new Letter;
                            $letter->letter = substr($word, $i, 1);
                            $letter->word_id = $word_db->id;
                            $letter->ordinal = $i+1;
                            $letter->save();
                        }
                    }
                    if ($found_clue){
                        $clue_available = ClueAvailable::where('word_id', $word_db->id)
                            ->where('clue', $found_clue->clue)
                            ->first();
                        if (!$clue_available){
                            $clue_available = new ClueAvailable;
                            $clue_available->word_id = $word_db->id;
                            $clue_available->clue = $found_clue->clue;
                            $clue_available->timestamp_utc = time();
                            $clue_available->save();
                        }
                    }
                }
                if ($square_should_have_across_clue){
                    $found_clue = false;
                    foreach($clues as $clue){
                        if ($clue->ordinal == $ordinal && $clue->direction == 'across'){
                            $found_clue = $clue;
                        }
                    }
                    if (!$found_clue){
                        if ($col == $pt->width || $squares[$row.'-'.($col + 1)]['square_type'] == 'black'){
                            $missing_clues_for_one_letter_words[] = $ordinal.' across';
                        }else{
                            $missing_clues[] = $ordinal.' across';
                        }
                    }
                }
                if ($square_should_have_across_clue || $square_should_have_down_clue){
                    $ordinal++;
                }
            }
        }
        if (count($missing_clues) > 0 || count($missing_letters) > 0 || (count($missing_clues_for_one_letter_words) > 0 && !$sure)){
            return array(
                'errors' => compact('missing_clues', 'missing_letters', 'missing_clues_for_one_letter_words'),
            );
        }else{
            $this->active = 1;
            $this->timestamp_utc = time();
            $this->save();
            
            return array('success' => 1);
        }
    }
    
    public static function create(array $args = array()){
        $v = new Puzzle;
        if ($v->validate($args)){
            $p = new Puzzle;
            $p->name = $args['name'];
            $p->slug = self::findSlug($args['name']);
            $p->user_id = $args['user_id'];
            $p->puzzle_template_id = $args['puzzle_template_id'];
            $p->timestamp_utc = time();
            $p->save();
            
            $ptss = PuzzleTemplateSquare::where('puzzle_template_id', $args['puzzle_template_id'])->get();
            
            foreach($ptss as $pts){
                $ps = new PuzzleSquare;
                $ps->puzzle_id = $p->id;
                $ps->row = $pts->row;
                $ps->col = $pts->col;
                $letter = "";
                if (isset($args['puzzle_squares'])){
                    foreach($args['puzzle_squares'] as $puzzle_square){
                        if ($puzzle_square['row'] == $pts->row && $puzzle_square['col'] == $pts->col){
                            $letter = $puzzle_square['letter'];
                        }
                    }
                }
                $ps->letter = $letter;
                $ps->square_type = $pts->square_type;
                $ps->save();
            }
            
            if (isset($args['clues'])){
                foreach($args['clues'] as $c){
                    $p->clues()->save($c);
                }
            }

            return $p;
        }else{
            return array('errors' => $v->errors);
        }
    }
    
    public static function findBySlug($slug){
        return self::where('slug', $slug)->whereNull('puzzles.deleted_timestamp_utc')->first();
    }
    
    public static function findSlug($name){
        $slug = strtolower(preg_replace("/[^a-zA-Z\d]/", "-", $name));
        $origslug = $slug;
        $exists = self::where('slug', $slug)->first();
        $i = 0;
        while ($exists){
            $slug = $origslug."-".$i++;
            $exists = self::where('slug', $slug)->first();
        }
        return $slug;
    }
    
    public static function getIncompletePuzzlesByUser($user){
        return self::leftjoin('puzzle_templates', 'puzzle_templates.id', '=', 'puzzles.puzzle_template_id')
            ->leftjoin('users', 'users.id', '=', 'puzzles.user_id')
            ->selectRaw('puzzles.name, puzzles.slug, puzzles.timestamp_utc*1000 created, puzzle_templates.width, puzzle_templates.height, users.name as owner, users.username')
            ->where('puzzles.user_id', $user->id)
            ->whereNull('puzzles.deleted_timestamp_utc')
            ->where('puzzles.active', 0)->get();
    }
    
    public static function getPuzzles($limit){
        return self::where('puzzles.active', 1)
            ->whereNull('puzzles.deleted_timestamp_utc')
            ->leftJoin('puzzle_templates', 'puzzle_templates.id', '=', 'puzzles.puzzle_template_id')
            ->leftJoin('users', 'users.id', '=', 'puzzles.user_id')
            ->selectRaw('puzzles.name, puzzles.slug, users.name as owner, users.username, puzzle_templates.width, puzzle_templates.height, puzzles.timestamp_utc*1000 created')
            ->orderBy('puzzles.timestamp_utc', 'desc')
            ->take($limit)
            ->get();
    }
    
    public function delete(){
        $this->deleted_timestamp_utc = time();
        $this->save();
        
        return array('success' => 1);
    }
}