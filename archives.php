<!DOCTYPE html>
<html>
    <head>
        <title>eGGtor</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
        <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>
        <script type="text/javascript" src="assets/script/jquery.min.js"></script>
        <script type="text/javascript" src="assets/script/javascript.js"></script>
        <script></script>
    </head>
    <body style="font-family:Verdana;">
        <div class="container">
            <?php            
                require ('domainservices.php');
                print_r(buildPage('archives'));  
            ?>
        </div>
        <div class="footer"><?php            
                //require ('domainservices.php');
                print_r(generatefooter('archives'));  
            ?>
        </div>  
    </body>
</html>