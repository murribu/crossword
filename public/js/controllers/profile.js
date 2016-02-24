materialAdmin
    .controller('profileCtrl', function($scope, $stateParams, $state, $timeout, growlService, profileService, errorFactory) {
        var self = this;
        self.profile = {};
        
        if ($state.current.name == 'users.profile'){
            $state.go('users.profile.puzzles-worked');
        }
        
        profileService.getProfile($stateParams.username).success(function(d){
            self.profile = d;
        }).error(function(data, code){
            errorFactory.handleErrors(data, code);
        });
        
        self.isBlackSquare = function(template_slug, row, col){
            return self.profile.templates[template_slug].puzzle_template_squares[row + '-' + col].square_type == 'black';
        }
        
        self.clueNumber = function(template_slug, row, col){
            return self.profile.templates[template_slug].clue_squares.indexOf(row + '-' + col);
        }
        
        self.customStyle = function(t){
            if (t.width > t.height){
                return 'padding-bottom: ' + 100*t.height/t.width + '%;';
            }else if (t.width > t.height){
                return 'padding-bottom: ' + 100*t.height/t.width + '%;';
            }else{
                return '';
            }
            
        }
        
        self.filterMyTemplates = function(){
            var ret = [];
            for (var i in self.profile.templates){
                if (self.profile.templates[i].user_id == self.profile.user_id){
                    ret.push(self.profile.templates[i]);
                }
            }
            return ret;
        }
        
        self.range = function(min,max,step){
            step = step || 1;
            var input = [];
            
            for (var i = min; i <= max; i += step){
                input.push(i);
            }
            return input;
        };
    });