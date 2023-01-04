<!DOCTYPE html>
<html>
    <head>
        <title>eGGtor</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
        <link rel="stylesheet" type="text/css" href="assets/css/style.css?ver=17"/>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js"></script>
        <script type="text/javascript" src="assets/script/jquery.min.js"></script>
        <script type="text/javascript" src="assets/script/javascript.js?ver=12"></script>
        <script>
           /* window.onscroll = function() {scrollFunction()};

            function scrollFunction() {
              if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("navbar").style.top = "0";
              } else {
                document.getElementById("navbar").style.top = "-50px";
              }
            }*/
                    </script>
                    <style>
                        #navbar {
              background-color: #333; /* Black background color */
              position: fixed; /* Make it stick/fixed */
              top: -50px; /* Hide the navbar 50 px outside of the top view */
              width: 100%; /* Full width */
              transition: top 0.3s; /* Transition effect when sliding down (and up) */
            }

            /* Style the navbar links */
            #navbar a {
              float: left;
              display: block;
              color: white;
              text-align: center;
              padding: 15px;
              text-decoration: none;
            }

            #navbar a:hover {
              background-color: #ddd;
              color: black;
            }
        </style>
    </head>
    <body>
        <div id="printdata"  class="printdata"></div>
        <div class="popup" id="popup1" style="display:none">
        
        </div>
        <div id="container">
            <?php            
                require ('domainservices.php');
                print_r(buildPage('grievancegenerator')); 
            ?>
        </div>
        <div class="footer"><?php            
                //require ('domainservices.php');
                print_r(generatefooter('grievancegenerator')); 
                ?>
        </div>  
    </body>
</html>