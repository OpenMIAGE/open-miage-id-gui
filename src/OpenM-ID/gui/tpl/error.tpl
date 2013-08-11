<html>
    <head>
        
    </head>
    <body>
        <h1>
            OpenM-ID server v{$version} : Error Page
        </h1>
        An Internal ERROR occured {if $message!=""}({$message}){/if}
        <br><br>
        {include file='include/links.tpl'}
    </body>
</html>