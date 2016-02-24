materialAdmin
    .controller('puzzleCtrl', function($scope, $location, $stateParams, $timeout, growlService, puzzleService, errorFactory) {
        var self = this;
        self.puzzle = {};
        self.puzzle.clues = {};
        self.selectedRow = 0;
        self.selectedCol = 0;
        self.selectedDirection = 'across';
        self.word_count = 0;
        self.suggestions = [];
        self.total_suggestion_score = 0;
        self.callingSuggestion = false;
        self.currentSuggestionRequest = '';
        self.callingProblem = false;
        self.currentProblemRequest = '';
        self.problems = [];
        self.impossibles = [];
        self.possibleClues = {
            across: {},
            down:   {},
        };
        
        puzzleService.getPuzzle($stateParams.puzzle_slug).success(function(d){
            self.puzzle = d;
            for(s in self.puzzle.clue_squares){
                var row = parseInt(self.puzzle.clue_squares[s].split("-")[0]);
                var col = parseInt(self.puzzle.clue_squares[s].split("-")[1]);
                var key = (parseInt(s) + 1) + "";
                var pad = "0000";
                key = pad.substring(0, pad.length - key.length) + key;
                if (col != self.puzzle.puzzle_template.width 
                    && !self.isBlackSquare(row, col + 1)
                    && (col == 1 || self.isBlackSquare(row, col - 1))){
                        
                    self.possibleClues.across[key] = {
                        clue: self.findClue(key, 'across'),
                        start: {
                            row: row,
                            col: col,
                        },
                    };
                }
                if (row != self.puzzle.puzzle_template.height 
                    && !self.isBlackSquare(row + 1, col)
                    && (row == 1 || self.isBlackSquare(row - 1, col))){
                    self.possibleClues.down[key] = {
                        clue: self.findClue(key, 'down'),
                        start: {
                            row: row,
                            col: col,
                        },
                    };
                }
            }
        }).error(function(data, code){
            errorFactory.handleErrors(data, code);
        });
        
        self.findClue = function(ordinal, direction){
            for(c in self.puzzle.clues){
                if (self.puzzle.clues[c].ordinal == ordinal && self.puzzle.clues[c].direction == direction){
                    return self.puzzle.clues[c].clue;
                }
            }
            return '';
        }
    
    
        $scope.$watch('pctrl.selectedRow', function() {
            self.setFocusOnSelectedSquare();
        });
    
        $scope.$watch('pctrl.selectedCol', function() {
            self.setFocusOnSelectedSquare();
        });
        
        self.setSelectedClueText = function(){
            if (self.selectedRow > 0 && self.selectedCol > 0){
                var pad = "0000";
                var key = self.selectedClueOrdinal() + "";
                key = pad.substring(0, pad.length - key.length) + key;
                self.selectedClueText = self.possibleClues[self.selectedDirection][key].clue;
            }else{
                self.selectedClueText = '';
            }
        };
        
        self.saveClue = function(){
            var args = {
                puzzle_slug: $stateParams.puzzle_slug,
                clue: self.selectedClueText,
                ordinal: self.selectedClueOrdinal(),
                direction: self.selectedDirection,
            };
            puzzleService.saveClue(args).success(function(d){
                var pad = "0000";
                var key = self.selectedClueOrdinal() + "";
                key = pad.substring(0, pad.length - key.length) + key;
                self.possibleClues[self.selectedDirection][key].clue = self.selectedClueText;
                for(c in self.puzzle.clues){
                    if (self.puzzle.clues[c].ordinal == self.selectedClueOrdinal() && self.puzzle.clues[c].direction == self.selectedDirection){
                        return self.puzzle.clues[c].clue;
                    }
                }
            }).error(function(data, code){
                errorFactory.handleErrors(data, code);
            });
        }
        
        self.clueClick = function(clue, direction){
            self.selectedRow = clue.start.row;
            self.selectedCol = clue.start.col;
            self.selectedDirection = direction;
        }
        
        self.getPuzzleSquareSuggestion = function(){
            if (self.puzzle.slug && self.selectedRow > 0 && self.selectedCol > 0
                    && self.puzzle.puzzle_squares[self.currentSuggestionRequest]
                    && self.puzzle.puzzle_squares[self.currentSuggestionRequest].letter == ''){
                if (!self.callingSuggestion){
                    self.callingSuggestion = true;
                    puzzleService.getPuzzleSquareSuggestion(self.puzzle.slug, self.selectedRow, self.selectedCol).success(function(d){
                        self.suggestions = d['suggestions'];
                        self.word_count = d['word_count'];
                        if (self.suggestions && self.suggestions.length > 1){
                            self.total_suggestion_score = self.suggestions.reduce(function(a,b) { return (a['score'] ? a['score'] : a) + b['score']; });
                        }else if (self.suggestions && self.suggestions.length == 1){
                            self.total_suggestion_score = self.suggestions[0]['score'];
                        }else{
                            self.total_suggestion_score = 0;
                        }
                    }).error(function(data, code){
                        errorFactory.handleErrors(data, code);
                    }).then(function(){
                        self.callingSuggestion = false;
                    });
                }
            }else{
                self.suggestions = [];
            }
        }
        
        self.getPuzzleProblemSquares = function(){
            if (self.puzzle.slug){
                if (!self.callingProblem){
                    self.callingProblem = true;
                    puzzleService.getPuzzleProblemSquares(self.puzzle.slug).success(function(d){
                        self.problems = d['problems'];
                        self.impossibles = d['impossibles'];
                    }).error(function(data, code){
                        errorFactory.handleErrors(data, code);
                    }).then(function(){
                        self.callingProblem = false;
                    });
                }
            }
        }
    
        self.setFocusOnSelectedSquare = function() {
            $("[data-row="+self.selectedRow+"][data-col="+self.selectedCol+"] div").focus();
            self.currentSuggestionRequest = self.selectedRow + "-" + self.selectedCol;
            self.currentProblemRequest = self.selectedRow + "-" + self.selectedCol;
            self.setSelectedClueText();
            $timeout(function(){
                if (self.currentSuggestionRequest == self.selectedRow + "-" + self.selectedCol){
                    self.getPuzzleSquareSuggestion();
                }
            }, 1000);
            $timeout(function(){
                if (self.currentProblemRequest == self.selectedRow + "-" + self.selectedCol){
                    self.getPuzzleProblemSquares();
                }
            }, 1000);
        };
        
        self.keyDown = function(e){
            var preventDefault = true;
            if ($('#clue:focus').length > 0){
                if (e.keyCode == 13){
                    //save clue text
                }
                return;
            }
            if (e.keyCode > 64 && e.keyCode < 91){
                var oldLetter = self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter;
                self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = String.fromCharCode(e.keyCode);
                var sent = {
                    puzzle_id: self.puzzle.id,
                    row: self.selectedRow,
                    col: self.selectedCol,
                    letter: String.fromCharCode(e.keyCode),
                };
                puzzleService.setPuzzleSquare(sent).success(function(received){
                    //Do nothing?
                }).error(function(data, code){
                    self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = oldLetter;
                    errorFactory.handleErrors(data, code);
                });
                self.moveNext();
            }else{
                switch (e.keyCode){
                    case 37:
                        //left arrow
                        self.moveLeft();
                        break;
                    case 38:
                        //up arrow
                        self.moveUp();
                        break;
                    case 39:
                        //right arrow
                        self.moveRight();
                        break;
                    case 40:
                        //down arrow
                        self.moveDown();
                        break;
                    case 8:
                        //backspace
                        self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = '';
                        var sent = {
                            puzzle_id: self.puzzle.id,
                            row: self.selectedRow,
                            col: self.selectedCol,
                            letter: '',
                        };
                        puzzleService.setPuzzleSquare(sent).success(function(received){
                            //Do nothing?
                        }).error(function(data, code){
                            self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = oldLetter;
                            errorFactory.handleErrors(data, code);
                        });
                        if (self.selectedDirection == 'across'){
                            self.moveLeft();
                        }else if (self.selectedDirection == 'down'){
                            self.moveUp();
                        }
                        break;
                    case 46:
                        //delete
                        self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = '';
                        var sent = {
                            puzzle_id: self.puzzle.id,
                            row: self.selectedRow,
                            col: self.selectedCol,
                            letter: '',
                        };
                        puzzleService.setPuzzleSquare(sent).success(function(received){
                            //Do nothing?
                        }).error(function(data, code){
                            self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = oldLetter;
                            errorFactory.handleErrors(data, code);
                        });
                        self.setFocusOnSelectedSquare();
                        break;
                    case 9:
                        //tab
                    case 13:
                        //enter
                        if1:
                        if (self.selectedDirection == 'across'){
                            for (var col = self.selectedCol + 1; col <= self.puzzle.puzzle_template.width; col++){
                                if (!self.isBlackSquare(self.selectedRow, col)){
                                    self.selectedCol = col;
                                    break if1;
                                }
                            }
                            loop1:
                            for (var row = self.selectedRow + 1; row <= self.puzzle.puzzle_template.height; row++){
                                for (var col = 1; col <= self.puzzle.puzzle_template.width; col++){
                                    if (!self.isBlackSquare(row, col)){
                                        self.selectedRow = row;
                                        self.selectedCol = col;
                                        break loop1;
                                    }
                                }
                            }
                        }else if (self.selectedDirection == 'down'){
                            for (var row = self.selectedRow + 1; col <= self.puzzle.puzzle_template.height; row++){
                                if (!self.isBlackSquare(row, self.selectedCol)){
                                    self.selectedRow = row;
                                    break if1;
                                }
                            }
                            loop2:
                            for (var col = self.selectedCol + 1; col <= self.puzzle.puzzle_template.width; col++){
                                for (var row = 1; row <= self.puzzle.puzzle_template.height; row++){
                                    if (!self.isBlackSquare(row, col)){
                                        self.selectedRow = row;
                                        self.selectedCol = col;
                                        break loop2;
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        preventDefault = false;
                        break;
                }
                if (preventDefault){
                    e.preventDefault();
                }
            }
        }
        
        self.moveNext = function(){
            if (self.selectedDirection == 'across'){
                self.moveRight();
            }else if (self.selectedDirection == 'down'){
                self.moveDown();
            }
        }
        
        self.moveLeft = function(){
            self.selectedDirection = 'across';
            var destinationCol = self.selectedCol - 1;
            while (destinationCol > 0 && self.isBlackSquare(self.selectedRow, destinationCol)){
                destinationCol--;
            }
            if (destinationCol > 0){
                self.selectedCol = destinationCol;
            }
        };
        
        self.moveRight = function(){
            self.selectedDirection = 'across';
            var destinationCol = self.selectedCol + 1;
            while (destinationCol < self.puzzle.puzzle_template.width + 1 && self.isBlackSquare(self.selectedRow, destinationCol)){
                destinationCol++;
            }
            if (destinationCol < self.puzzle.puzzle_template.width + 1){
                self.selectedCol = destinationCol;
            }
        };
        
        self.moveUp = function(){
            self.selectedDirection = 'down';
            var destinationRow = self.selectedRow - 1;
            while (destinationRow > 0 && self.isBlackSquare(destinationRow, self.selectedCol)){
                destinationRow--;
            }
            if (destinationRow > 0){
                self.selectedRow = destinationRow;
            }
        };
        
        self.moveDown = function(){
            self.selectedDirection = 'down';
            var destinationRow = self.selectedRow + 1;
            while (destinationRow < self.puzzle.puzzle_template.height + 1 && self.isBlackSquare(destinationRow, self.selectedCol)){
                destinationRow++;
            }
            if (destinationRow < self.puzzle.puzzle_template.height + 1){
                self.selectedRow = destinationRow;
            }
        };
        
        self.selectedClueOrdinal = function(){
            if (self.selectedRow == 0 || self.selectedCol == 0){
                return false;
            }else{
                if (self.selectedDirection == 'down'){
                    var row = self.selectedRow;
                    while (row > 0 && !self.isBlackSquare(row, self.selectedCol)){
                        row--;
                    }
                    return self.clueNumber(row + 1, self.selectedCol) + 1;
                }
                if (self.selectedDirection == 'across'){
                    var col = self.selectedCol;
                    while (col > 0 && !self.isBlackSquare(self.selectedRow, col)){
                        col--;
                    }
                    return self.clueNumber(self.selectedRow, col + 1) + 1;
                }
            }
        }
        
        self.inImpossible = function(row, col){
            return self.impossibles.indexOf(row + '-' + col) > -1;
        }
        
        self.inProblem = function(row, col){
            if (self.problems[row + '-' + col]){
                return self.problems[row + '-' + col];
            }else{
                return 0;
            }
        }
        
        self.inSelectedWord = function(row, col){
            if (self.isBlackSquare(row, col)){
                return false;
            }
            if (self.selectedDirection == 'down'){
                if (col != self.selectedCol){
                    return false;
                }
                var keepLookingDown = true;
                var r = row;
                while (keepLookingDown){
                    if (r == self.selectedRow){
                        return true;
                    }
                    r++;
                    if (r == self.puzzle.puzzle_template.width + 1 || self.isBlackSquare(r, col)){
                        keepLookingDown = false;
                    }
                }
                var keepLookingUp = true;
                r = row;
                while (keepLookingUp){
                    if (r == self.selectedRow){
                        return true;
                    }
                    r--;
                    if (r == 0 || self.isBlackSquare(r, col)){
                        keepLookingUp = false;
                    }
                }
            }else if(self.selectedDirection == 'across'){
                if (row != self.selectedRow){
                    return false;
                }
                var keepLookingRight = true;
                var c = col;
                while (keepLookingRight){
                    if (c == self.selectedCol){
                        return true;
                    }
                    c++;
                    if (c == self.puzzle.puzzle_template.height + 1 || self.isBlackSquare(row, c)){
                        keepLookingRight = false;
                    }
                }
                var keepLookupLeft = true;
                c = col;
                while (keepLookupLeft){
                    if (c == self.selectedCol){
                        return true;
                    }
                    c--;
                    if (c == 0 || self.isBlackSquare(row, c)){
                        keepLookupLeft = false;
                    }
                }
            }
            return false;
        };
        
        self.selectLetter = function(row, col){
            if (self.selectedRow == row && self.selectedCol == col){
                if (self.selectedDirection == 'across'){
                    self.selectedDirection = 'down';
                }else{
                    self.selectedDirection = 'across';
                }
            }
            self.selectedRow = row;
            self.selectedCol = col;
        };
        
        self.selectedLetter = function(row, col){
            return self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol];
        };
        
        self.isBlackSquare = function(row, col){
            return self.puzzle.puzzle_squares[row + '-' + col].square_type == 'black';
        };
        
        self.clueNumber = function(row, col){
            return self.puzzle.clue_squares.indexOf(row + '-' + col);
        }
        
        self.range = function(min,max,step){
            step = step || 1;
            var input = [];
            
            for (var i = min; i <= max; i += step){
                input.push(i);
            }
            return input;
        };
    })