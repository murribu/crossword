materialAdmin
    .controller('dashboardCtrl', function($scope, $timeout, errorFactory, profileService, puzzleService) {
        var self = this;
        self.puzzles = [];
        self.incompletePuzzles = [];
        
        puzzleService.getPuzzles(5).success(function(d){
            self.puzzles = d;
        }).error(function(data, code){
            errorFactory.handleErrors(data, code);
        });
        
        profileService.getMe().success(function(profile){
            self.profile = profile;
            if (profile.logged_in){
                puzzleService.getIncompletePuzzles().success(function(d){
                    self.incompletePuzzles = d;
                }).error(function(data, code){
                    errorFactory.handleErrors(data, code);
                });
            }
        }).error(function(data, code){
            errorFactory.handleErrors(data, code);
        });
    });