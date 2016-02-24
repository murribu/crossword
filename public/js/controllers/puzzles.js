materialAdmin
    .controller('puzzlesCtrl', function($scope, $location, puzzleService, growlService, errorFactory) {
        var self = this;
        self.puzzles = [];
        self.pageSize = 20;
        self.currentPage = 1;
        self.currentSort = 'name';
        self.heightMin = 5;
        self.heightMax = 20;
        self.widthMin = 5;
        self.widthMax = 20;
        self.search = '';
        
        if ($location.path().substr(0,24) == '/puzzles/incomplete-list'){
            puzzleService.getIncompletePuzzles().success(function(d){
                self.puzzles = d;
            }).error(function(data, code){
                errorFactory.handleErrors(data, code);
            });
        }else if($location.path().substr(0,13) == '/puzzles/list'){
            puzzleService.getPuzzles().success(function(d){
                self.puzzles = d;
            }).error(function(data, code){
                errorFactory.handleErrors(data, code);
            });
        }
        
        self.changeSort = function(s){
            var desc = s.substr(0,1) == '-';
            var column = desc ? s.substr(1,99) : s; 
            if (self.currentSort.substr(0,1) != '-'){
                if (self.currentSort == column){
                    self.currentSort = '-' + self.currentSort;
                }else{
                    self.currentSort = s;
                }
            }else{
                if (self.currentSort.substr(1,99) == column){
                    self.currentSort = column;
                }else{
                    self.currentSort = s;
                }
            }
        };
        
        self.filterResults = function(p){
            return p.height >= self.heightMin && p.width >= self.widthMin && p.height <= self.widthMax && p.height <= self.heightMax && (self.search == '' || p.name.toLowerCase().indexOf(self.search.toLowerCase()) > -1);
        }
        
        self.changePage = function(p){
            self.currentPage = p;
        };
    });