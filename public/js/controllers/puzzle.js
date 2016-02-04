materialAdmin
    .controller('puzzleCtrl', function($scope, $location, $stateParams, $timeout, growlService, puzzleService) {
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
        
        puzzleService.getPuzzle($stateParams.puzzle_slug).success(function(d){
            self.puzzle = d;
        });
    
    
        $scope.$watch('pctrl.selectedRow', function() {
            self.setFocusOnSelectedSquare();
        });
    
        $scope.$watch('pctrl.selectedCol', function() {
            self.setFocusOnSelectedSquare();
        });
        
        self.getPuzzleSquareSuggestion = function(){
            if (self.puzzle.slug && self.selectedRow > 0 && self.selectedCol > 0){
                if (!self.callingSuggestion){
                    self.callingSuggestion = true;
                    puzzleService.getPuzzleSquareSuggestion(self.puzzle.slug, self.selectedRow, self.selectedCol).success(function(d){
                        if (d['errors']){
                            for(e in d['errors']){
                                growlService.growl('There was an error: ' + d['errors'][e], 'danger');
                            }
                        }else{
                            self.suggestions = d['suggestions'];
                            self.word_count = d['word_count'];
                            if (self.suggestions && self.suggestions.length > 0){
                                self.total_suggestion_score = self.suggestions.reduce(function(a,b) { return (a['score'] ? a['score'] : a) + b['score']; });
                            }else{
                                self.total_suggestion_score = 0;
                            }
                        }
                        self.callingSuggestion = false;
                    });
                }
            }
        }
    
        self.setFocusOnSelectedSquare = function() {
            $("[data-row="+self.selectedRow+"][data-col="+self.selectedCol+"] div").focus();
            self.currentSuggestionRequest = self.selectedRow + "-" + self.selectedCol;
            $timeout(function(){
                    if (self.currentSuggestionRequest == self.selectedRow + "-" + self.selectedCol
                        && self.puzzle.puzzle_squares[self.currentSuggestionRequest]
                        && self.puzzle.puzzle_squares[self.currentSuggestionRequest].letter == ''){
                        self.getPuzzleSquareSuggestion();
                    }
            }, 1000);
        };
        
        self.keyDown = function(e){
            var preventDefault = true;
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
                    if (received['errors']){
                        for(e in received['errors']){
                            growlService.growl('There was an error: ' + received['errors'][e], 'danger');
                        }
                        self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = oldLetter;
                    }
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
                            if (received['errors']){
                                for(e in received['errors']){
                                    growlService.growl('There was an error: ' + received['errors'][e], 'danger');
                                }
                                self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = oldLetter;
                            }
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
                            if (received['errors']){
                                for(e in received['errors']){
                                    growlService.growl('There was an error: ' + received['errors'][e], 'danger');
                                }
                                self.puzzle.puzzle_squares[self.selectedRow + '-' + self.selectedCol].letter = oldLetter;
                            }
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