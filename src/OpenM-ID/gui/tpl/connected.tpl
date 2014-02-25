{include file='include/docType.tpl'}
<html>
    <head>
        <title>Connected Page</title>
        {include file='include/head.tpl'}
        <style type="text/css">
            @media (max-width: 500px) {
                .hero-unit {
                    margin: 0px;
                    padding: 15px;
                }

                .body {
                    background-color: #EEE;
                }
            }
        </style>
    </head>
    <body class="body">
        <div class="container">
            <div class="well jumbotron">
                <h1 class="hidden-phone">
                    Connexion OpenM-ID confirmée
                </h1>
                <h3 class="visible-phone">Connexion OpenM-ID confirmée</h3>
                (v{$version})
                <p></p>
                <p>
                    Bonjour {$mail}, vous êtes bien connecté.
                </p>
                <p></p>
                <p>Vous pouvez vous déconnecter :</p>
                <p>
                    <button class="btn btn-large btn-primary" onclick="location.href = '{$links.logout}'"><i class="glyphicon glyphicon-plus"></i> Se déconnecter</button>
                </p>
            </div>
        </div>
    </body>
</html>