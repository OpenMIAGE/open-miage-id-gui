{include file='include/docType.tpl'}
<html>
    <head>
        <title>Login</title>
        {include file='include/head.tpl'}
    </head>
    <body class="body">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span4">
                    <form class="form-signin" method="POST" action="{$action}">
                        <legend><h2>OpenM-ID connexion</h2>(v{$version})</legend>
                        {include file='include/login_commons.tpl'}
                        <legend>&nbsp;</legend>
                        {include file='include/links.tpl'}
                    </form>
                </div>
                {include file='include/info.tpl'}
            </div>
        </div>
    </body>
</html>