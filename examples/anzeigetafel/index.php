<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Anzeigetafel</title>
        <meta name="description" content="">
              <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    </head>
    <style>
    body{
       
        height:100%;
        font-family:sans-serif;
    }
    #header{
        position:fixed;
        top:0px;
        left:0px;
        right:0px;
        height:35px;
        background-color:#333;
        padding:3px;
        color:white;
    }
    .abfahrttafel{
        position:absolute;
        top:40px;
        left:0px;
        right:0px;
        bottom:0px;
    }
    .change{
    color:red;
    }        
    </style>
    
    <body>       
        <div id="abfahrten">
    
        </div>

    <script>
        $(function(){
            
        
	function Poll(){
		 $('#abfahrten').load("./show_abfahrten.php");
		setTimeout(Poll,15000);    
	}
	Poll();
    
            
        });
        
        
        </script>     
    </body>
</html>
