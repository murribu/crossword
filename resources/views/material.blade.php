<!DOCTYPE html>
    <!--[if IE 9 ]><html class="ie9" data-ng-app="materialAdmin" data-ng-controller="materialadminCtrl as mactrl"><![endif]-->
    <![if IE 9 ]><html data-ng-app="materialAdmin" data-ng-controller="materialadminCtrl as mactrl"><![endif]>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Crosswords</title>

        <!-- Vendor CSS -->
        <link href="vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="vendors/bower_components/angular-loading-bar/src/loading-bar.css" rel="stylesheet">
        <!-- CSS -->
        <!--This app-level div is used in lazyLoading (public/js/config.js) -->
        <div id="app-level"></div>
        
        <link href="css/app.css" rel="stylesheet">
        <link href="css/demo.css" rel="stylesheet">
        <link href="css/cw.css" rel="stylesheet">

    </head>

    <body data-ng-class="{ 'sw-toggled': mactrl.layoutType === '1', 'modal-open': mactrl.sidebarToggle.left === true }">

        <data ui-view></data>

        <!-- Older IE warning message -->
        <!--[if lt IE 9]>
            <div class="ie-warning">
                <h1 class="c-white">Warning!!</h1>
                <p>You are using an outdated version of Internet Explorer, please upgrade <br/>to any of the following web browsers to access this website.</p>
                <div class="iew-container">
                    <ul class="iew-download">
                        <li>
                            <a href="http://www.google.com/chrome/">
                                <img src="img/browsers/chrome.png" alt="">
                                <div>Chrome</div>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.mozilla.org/en-US/firefox/new/">
                                <img src="img/browsers/firefox.png" alt="">
                                <div>Firefox</div>
                            </a>
                        </li>
                        <li>
                            <a href="http://www.opera.com">
                                <img src="img/browsers/opera.png" alt="">
                                <div>Opera</div>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.apple.com/safari/">
                                <img src="img/browsers/safari.png" alt="">
                                <div>Safari</div>
                            </a>
                        </li>
                        <li>
                            <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
                                <img src="img/browsers/ie.png" alt="">
                                <div>IE (New)</div>
                            </a>
                        </li>
                    </ul>
                </div>
                <p>Sorry for the inconvenience!</p>
            </div>
        <![endif]-->


        <!-- Core -->
        <script src="vendors/bower_components/jquery/dist/jquery.min.js"></script>

        <!-- Angular -->
        <script src="vendors/bower_components/angular/angular.min.js"></script>
        <script src="vendors/bower_components/angular-animate/angular-animate.min.js"></script>
        <script src="vendors/bower_components/angular-resource/angular-resource.min.js"></script>
        
        <!-- Angular Modules -->
        <script src="vendors/bower_components/angular-ui-router/release/angular-ui-router.min.js"></script>
        <script src="vendors/bower_components/angular-loading-bar/src/loading-bar.js"></script>
        <script src="vendors/bower_components/oclazyload/dist/ocLazyLoad.min.js"></script>
        <script src="vendors/bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>

        <!-- Common Vendors -->
        <script src="vendors/bower_components/jquery.nicescroll/jquery.nicescroll.min.js"></script>
        <script src="vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="vendors/bootstrap-growl/bootstrap-growl.min.js"></script>
        <script src="vendors/bower_components/ng-table/dist/ng-table.min.js"></script>
       

        <!-- Placeholder for IE9 -->
        <!--[if IE 9 ]>
            <script src="vendors/bower_components/jquery-placeholder/jquery.placeholder.min.js"></script>
        <![endif]-->

        <!-- Using below vendors in order to avoid misloading on resolve -->
        <script src="vendors/bower_components/flot/jquery.flot.js"></script>
        <script src="vendors/bower_components/flot/jquery.flot.time.js"></script>
        <script src="vendors/bower_components/flot.curvedlines/curvedLines.js"></script>
        <script src="vendors/bower_components/flot/jquery.flot.resize.js"></script>
        <script src="vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="vendors/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
        <script src="vendors/bower_components/flot-orderBars/js/jquery.flot.orderBars.js"></script>
        <script src="vendors/bower_components/flot/jquery.flot.pie.js"></script>
        <script src="vendors/bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
        <script src="vendors/bower_components/angular-nouislider/src/nouislider.min.js"></script>
        
        <script src="js/cw.js"></script>
        
        <!-- App level -->
        <script src="js/app.js"></script>
        <script src="js/config.js"></script>
        <script src="js/services.js"></script>
        <script src="js/controllers/main.js"></script>
        <script src="js/controllers/ui-bootstrap.js"></script>
        <script src="js/controllers/dashboard.js"></script>
        <script src="js/controllers/table.js"></script>
        <script src="js/controllers/profile.js"></script>
        <script src="js/controllers/puzzle.js"></script>
        <script src="js/controllers/puzzleEdit.js"></script>
        <script src="js/controllers/puzzleSolve.js"></script>
        <script src="js/controllers/puzzles.js"></script>
        <script src="js/controllers/puzzleTemplate.js"></script>
        <script src="js/controllers/puzzleTemplates.js"></script>

        <!-- Directives -->
        <script src="js/directives/contenteditable.js"></script>
        <script src="js/directives/createPuzzleWarning.js"></script>

        <!-- Services -->
        <script src="js/services/profile.js"></script>
        <script src="js/services/puzzle.js"></script>

        <!-- Factories -->
        <script src="js/factories/error.js"></script>

        <!-- Filters -->
        <script src="js/filters/ordinal.js"></script>
        <script src="js/filters/startFrom.js"></script>
        
        <!-- Template Modules -->
        <script src="js/modules/template.js"></script>
        <script src="js/modules/ui.js"></script>
        <script src="js/modules/charts/flot.js"></script>
        <script src="js/modules/charts/other-charts.js"></script>
        <script src="js/modules/form.js"></script>
        <script src="js/modules/media.js"></script>
        <script>
            window.twttr = (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0],
                t = window.twttr || {};
                if (d.getElementById(id)) return t;
                js = d.createElement(s);
                js.id = id;
                js.src = "https://platform.twitter.com/widgets.js";
                fjs.parentNode.insertBefore(js, fjs);

                t._e = [];
                t.ready = function(f) {
                t._e.push(f);
                };

                return t;
            }(document, "script", "twitter-wjs"));
        </script>
    </body>
</html>

