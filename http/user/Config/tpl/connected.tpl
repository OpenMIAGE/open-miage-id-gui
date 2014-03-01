{include file='include/docType.tpl'}
<html>
    <head>
        <title>Connected Page</title>
        {include file='include/head.tpl'}
        </style>
    </head>
    <body class="body">
        <div class="container">
            <div class="well jumbotron">
                <h1 class="hidden-xs">
                    Connexion OpenM-ID confirmée
                </h1>
                <h3 class="visible-xs">Connexion OpenM-ID confirmée</h3>
                (v{$version})
                <p></p>
                <p>
                    Bonjour {$mail}, vous êtes bien connecté.
                </p>
                <p></p>
                <p>Vous pouvez vous déconnecter :</p>
                <p>
                    <button class="btn btn-default" onclick="location.href = '{$links.logout}'"><span class="glyphicon glyphicon-off"></span> Se déconnecter</button>
                </p>
            </div>
        </div>
    </body>
</html>