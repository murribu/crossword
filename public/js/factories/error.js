materialAdmin
    .factory('errorFactory', function($location, $timeout, growlService){
        return {
            handleErrors: function(data, code){
                if (code == 401){
                    for(e in data['errors']){
                        var msg = data['errors'][e];
                        growlService.growl('Unauthorized: ' + msg, 'danger');
                        if (msg == 'Please log in'){
                            $timeout(function(){
                                $location.path('/');
                                window.reload();
                            },2000);
                        }
                    }
                }
            }
        };
    });