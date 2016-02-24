<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\Clue;
use App\Models\Letter;
use App\Models\Puzzle;
use App\Models\PuzzleSquare;
use App\Models\PuzzleTemplate;
use App\Models\User;
use App\Models\Word;

class ActivateAPuzzleTest extends TestCase
{
    use DatabaseMigrations;
        
    /**
     * A basic functional test example.
     *
     * @return void
     */
     
    public function testActivatingAPuzzleWithIncompleteSquares(){
        
        $faker = Faker\Factory::create();
        
        $user = factory(User::class)->create(['username' => 'michaelwacha']);
        
        $name = $faker->sentence;
        $width = 5;
        $height = 5;
        
        $blackSquares = array('1-1', '5-5');
        
        $args = array(
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'blackSquares' => $blackSquares,
            'user_id' => $user->id,
        );
        
        $puzzleTemplate = PuzzleTemplate::create($args);
        
        $user->puzzleTemplates()->save($puzzleTemplate);
        
        $name = $faker->sentence;

        $puzzleSquares = array();
        
        $clues = array();
        
        $args = array(
            'name' => $name,
            'puzzle_template_id' => $puzzleTemplate->id,
            'puzzle_squares' => $puzzleSquares,
            'clues' => $clues,
            'user_id' => $user->id,
        );
        
        $puzzle = Puzzle::create($args);
        
        $firstword = "shop";
        $secondword = "shove";
        $thirdword = "wanes";
        $fourthword = "arent";
        $fifthword = "beds";
        
        for($k = 0; $k < strlen($firstword); $k++){
            $args = array(
                'row' => 1,
                'col' => $k + 2,
                'letter' => substr($firstword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($secondword); $k++){
            $args = array(
                'row' => 2,
                'col' => $k + 1,
                'letter' => substr($secondword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($thirdword); $k++){
            $args = array(
                'row' => 3,
                'col' => $k + 1,
                'letter' => substr($thirdword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($fourthword); $k++){
            $args = array(
                'row' => 4,
                'col' => $k + 1,
                'letter' => substr($fourthword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        $clue_str = $faker->sentence;
        
        $across_clues = array(
            array(
                'ordinal'   => 1,
                'clue'      => 'Woodworking locale',
            ),
            array(
                'ordinal'   => 5,
                'clue'      => 'Forcefully move',
            ),
            array(
                'ordinal'   => 6,
                'clue'      => 'Lessens',
            ),
            array(
                'ordinal'   => 7,
                'clue'      => '"I\'m right, _____ I?',
            ),
            array(
                'ordinal'   => 8,
                'clue'      => 'Places for flowers',
            ),
        );
        $down_clues = array(
            array(
                'ordinal'   => 1,
                'clue'      => 'Unit of stock',
            ),
            array(
                'ordinal'   => 2,
                'clue'      => 'Sharpened (as skills)',
            ),
            array(
                'ordinal'   => 3,
                'clue'      => 'Baking appliances',
            ),
            array(
                'ordinal'   => 4,
                'clue'      => 'Insect',
            ),
            array(
                'ordinal'   => 5,
                'clue'      => 'Gently Brush',
            ),
        );
        
        foreach($across_clues as $clue){
            $args = array(
                'puzzle_slug' => $puzzle->slug,
                'clue' => $clue['clue'],
                'ordinal' => $clue['ordinal'],
                'direction' => 'across',
            );
            $clue = Clue::replace($args);
        }
        
        foreach($down_clues as $clue){
            $args = array(
                'puzzle_slug' => $puzzle->slug,
                'clue' => $clue['clue'],
                'ordinal' => $clue['ordinal'],
                'direction' => 'down',
            );
            $clue = Clue::replace($args);
        }
        
        $ret = $puzzle->activate($user);
        
        $this->assertEquals($puzzle->active, 0);
    }
     
    public function testActivatingAPuzzleWithIncompleteClues(){
        
        $faker = Faker\Factory::create();
        
        $user = factory(User::class)->create(['username' => 'carlosmartinez']);
        
        $name = $faker->sentence;
        $width = 5;
        $height = 5;
        
        $blackSquares = array('1-1', '5-5');
        
        $args = array(
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'blackSquares' => $blackSquares,
            'user_id' => $user->id,
        );
        
        $puzzleTemplate = PuzzleTemplate::create($args);
        
        $user->puzzleTemplates()->save($puzzleTemplate);
        
        $name = $faker->sentence;

        $puzzleSquares = array();
        
        $clues = array();
        
        $args = array(
            'name' => $name,
            'puzzle_template_id' => $puzzleTemplate->id,
            'puzzle_squares' => $puzzleSquares,
            'clues' => $clues,
            'user_id' => $user->id,
        );
        
        $puzzle = Puzzle::create($args);
        
        $firstword = "shop";
        $secondword = "shove";
        $thirdword = "wanes";
        $fourthword = "arent";
        $fifthword = "beds";
        
        for($k = 0; $k < strlen($firstword); $k++){
            $args = array(
                'row' => 1,
                'col' => $k + 2,
                'letter' => substr($firstword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($secondword); $k++){
            $args = array(
                'row' => 2,
                'col' => $k + 1,
                'letter' => substr($secondword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($thirdword); $k++){
            $args = array(
                'row' => 3,
                'col' => $k + 1,
                'letter' => substr($thirdword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($fourthword); $k++){
            $args = array(
                'row' => 4,
                'col' => $k + 1,
                'letter' => substr($fourthword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($fifthword); $k++){
            $args = array(
                'row' => 5,
                'col' => $k + 1,
                'letter' => substr($fifthword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        $clue_str = $faker->sentence;
        
        $across_clues = array(
            array(
                'ordinal'   => 1,
                'clue'      => 'Woodworking locale',
            ),
            array(
                'ordinal'   => 5,
                'clue'      => 'Forcefully move',
            ),
            array(
                'ordinal'   => 6,
                'clue'      => 'Lessens',
            ),
            array(
                'ordinal'   => 7,
                'clue'      => '"I\'m right, _____ I?',
            ),
        );
        $down_clues = array(
            array(
                'ordinal'   => 1,
                'clue'      => 'Unit of stock',
            ),
            array(
                'ordinal'   => 2,
                'clue'      => 'Sharpened (as skills)',
            ),
            array(
                'ordinal'   => 3,
                'clue'      => 'Baking appliances',
            ),
            array(
                'ordinal'   => 4,
                'clue'      => 'Insect',
            ),
            array(
                'ordinal'   => 5,
                'clue'      => 'Gently Brush',
            ),
        );
        
        foreach($across_clues as $clue){
            $args = array(
                'puzzle_slug' => $puzzle->slug,
                'clue' => $clue['clue'],
                'ordinal' => $clue['ordinal'],
                'direction' => 'across',
            );
            $clue = Clue::replace($args);
        }
        
        foreach($down_clues as $clue){
            $args = array(
                'puzzle_slug' => $puzzle->slug,
                'clue' => $clue['clue'],
                'ordinal' => $clue['ordinal'],
                'direction' => 'down',
            );
            $clue = Clue::replace($args);
        }
        
        $ret = $puzzle->activate($user);
        
        $this->assertEquals($puzzle->active, 0);
    }
    
    public function testActivatingAPuzzle(){
        
        $faker = Faker\Factory::create();
        
        $user = factory(User::class)->create(['username' => 'anthonyrizzo']);
        
        $name = $faker->sentence;
        $width = 5;
        $height = 5;
        
        $blackSquares = array('1-1', '5-5');
        
        $args = array(
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'blackSquares' => $blackSquares,
            'user_id' => $user->id,
        );
        
        $puzzleTemplate = PuzzleTemplate::create($args);
        
        $user->puzzleTemplates()->save($puzzleTemplate);
        
        $name = $faker->sentence;

        $puzzleSquares = array();
        
        $clues = array();
        
        $args = array(
            'name' => $name,
            'puzzle_template_id' => $puzzleTemplate->id,
            'puzzle_squares' => $puzzleSquares,
            'clues' => $clues,
            'user_id' => $user->id,
        );
        
        $puzzle = Puzzle::create($args);
        
        $firstword = "shop";
        $secondword = "shove";
        $thirdword = "wanes";
        $fourthword = "arent";
        $fifthword = "beds";
        
        for($k = 0; $k < strlen($firstword); $k++){
            $args = array(
                'row' => 1,
                'col' => $k + 2,
                'letter' => substr($firstword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($secondword); $k++){
            $args = array(
                'row' => 2,
                'col' => $k + 1,
                'letter' => substr($secondword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($thirdword); $k++){
            $args = array(
                'row' => 3,
                'col' => $k + 1,
                'letter' => substr($thirdword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($fourthword); $k++){
            $args = array(
                'row' => 4,
                'col' => $k + 1,
                'letter' => substr($fourthword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        for($k = 0; $k < strlen($fifthword); $k++){
            $args = array(
                'row' => 5,
                'col' => $k + 1,
                'letter' => substr($fifthword, $k, 1),
                'puzzle_id' => $puzzle->id,
            );
            PuzzleSquare::replace($args);
        }
        
        $clue_str = $faker->sentence;
        
        $across_clues = array(
            array(
                'ordinal'   => 1,
                'clue'      => 'Woodworking locale',
            ),
            array(
                'ordinal'   => 5,
                'clue'      => 'Forcefully move',
            ),
            array(
                'ordinal'   => 6,
                'clue'      => 'Lessens',
            ),
            array(
                'ordinal'   => 7,
                'clue'      => '"I\'m right, _____ I?',
            ),
            array(
                'ordinal'   => 8,
                'clue'      => 'Places for flowers',
            ),
        );
        $down_clues = array(
            array(
                'ordinal'   => 1,
                'clue'      => 'Unit of stock',
            ),
            array(
                'ordinal'   => 2,
                'clue'      => 'Sharpened (as skills)',
            ),
            array(
                'ordinal'   => 3,
                'clue'      => 'Baking appliances',
            ),
            array(
                'ordinal'   => 4,
                'clue'      => 'Insect',
            ),
            array(
                'ordinal'   => 5,
                'clue'      => 'Gently Brush',
            ),
        );
        
        foreach($across_clues as $clue){
            $args = array(
                'puzzle_slug' => $puzzle->slug,
                'clue' => $clue['clue'],
                'ordinal' => $clue['ordinal'],
                'direction' => 'across',
            );
            $clue = Clue::replace($args);
        }
        
        foreach($down_clues as $clue){
            $args = array(
                'puzzle_slug' => $puzzle->slug,
                'clue' => $clue['clue'],
                'ordinal' => $clue['ordinal'],
                'direction' => 'down',
            );
            $clue = Clue::replace($args);
        }
        
        $ret = $puzzle->activate($user);
        
        $this->assertEquals($puzzle->active, 1);
    }
}
