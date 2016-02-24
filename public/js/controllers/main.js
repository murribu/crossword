materialAdmin
    // =========================================================================
    // Base controller for common functions
    // =========================================================================

    .controller('materialadminCtrl', function($timeout, $scope, $state, growlService, profileService, errorFactory){
        
        // Detact Mobile Browser
        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
           angular.element('html').addClass('ismobile');
        }

        // By default Sidbars are hidden in boxed layout and in wide layout only the right sidebar is hidden.
        this.sidebarToggle = {
            left: false,
            right: false
        }

        // By default template has a boxed layout
        this.layoutType = localStorage.getItem('ma-layout-status');
        
        // For Mainmenu Active Class
        this.$state = $state;    
        
        //Close sidebar on click
        this.sidebarStat = function(event) {
            if (!angular.element(event.target).parent().hasClass('active')) {
                this.sidebarToggle.left = false;
            }
        }
        
        //Listview Search (Check listview pages)
        this.listviewSearchStat = false;
        
        this.lvSearch = function() {
            this.listviewSearchStat = true; 
        }
        
        //Listview menu toggle in small screens
        this.lvMenuStat = false;
        
        //Blog
        this.wallCommenting = [];
        
        this.wallImage = false;
        this.wallVideo = false;
        this.wallLink = false;
        
        $scope.profile = {};
        $scope.profile.logged_in = false;

        profileService.getMe().success(angular.bind(this, function(d){
            $scope.profile = d;
            if (d.logged_in && d.profile_background_image_url){
                this.profileBgImage = {'background-image': 'url(' + d.profile_background_image_url + ')',
                'background-size': 'cover'};
            }
        })).error(function(data, code){
            errorFactory.handleErrors(data, code);
        });
        
        this.logout = function(){
            profileService.logout().then(function(){
                location.href = '/';
            });
        };
    })


    // =========================================================================
    // Header
    // =========================================================================
    .controller('headerCtrl', function($timeout){
    
         // Top Search
        this.openSearch = function(){
            angular.element('#header').addClass('search-toggled');
            //growlService.growl('Welcome back Mallinda Hollaway', 'inverse');
        }

        this.closeSearch = function(){
            angular.element('#header').removeClass('search-toggled');
        }
        

        //Clear Notification
        this.clearNotification = function($event) {
            $event.preventDefault();
            
            var x = angular.element($event.target).closest('.listview');
            var y = x.find('.lv-item');
            var z = y.size();
            
            angular.element($event.target).parent().fadeOut();
            
            x.find('.list-group').prepend('<i class="grid-loading hide-it"></i>');
            x.find('.grid-loading').fadeIn(1500);
            var w = 0;
            
            y.each(function(){
                var z = $(this);
                $timeout(function(){
                    z.addClass('animated fadeOutRightBig').delay(1000).queue(function(){
                        z.remove();
                    });
                }, w+=150);
            })
            
            $timeout(function(){
                angular.element('#notifications').addClass('empty');
            }, (z*150)+200);
        }
        
        // Clear Local Storage
        this.clearLocalStorage = function() {
            
            //Get confirmation, if confirmed clear the localStorage
            swal({   
                title: "Are you sure?",   
                text: "All your saved localStorage values will be removed",   
                type: "warning",   
                showCancelButton: true,   
                confirmButtonColor: "#F44336",   
                confirmButtonText: "Yes, delete it!",   
                closeOnConfirm: false 
            }, function(){
                localStorage.clear();
                swal("Done!", "localStorage is cleared", "success"); 
            });
            
        }
        
        //Fullscreen View
        this.fullScreen = function() {
            //Launch
            function launchIntoFullscreen(element) {
                if(element.requestFullscreen) {
                    element.requestFullscreen();
                } else if(element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if(element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if(element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
            }

            //Exit
            function exitFullscreen() {
                if(document.exitFullscreen) {
                    document.exitFullscreen();
                } else if(document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if(document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }

            if (exitFullscreen()) {
                launchIntoFullscreen(document.documentElement);
            }
            else {
                launchIntoFullscreen(document.documentElement);
            }
        }
    
    })

