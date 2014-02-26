{include file='include/docType.tpl'}
<html>
    <head>
        <title>Login</title>
        {include file='include/head.tpl'}
    </head>
    <body class="body">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="col-md-4 openm-id-form">
                    <form class="form-signin" method="POST" action="{$action}">
                        <legend><h2>OpenM-ID connexion</h2>(v{$version})</legend>
                        {config_load file="login."|cat:$lang|cat:".properties"}
                        <div class="control-group">
                            <label class="control-label" for="mail">{#login_label#}</label>
                            <div class="controls">
                                <input type="text" autofocus required name="mail" class="form-control" id="mail" value="{$mail}" placeholder="{#login_text_box#}">
                                <p class="text-error">{$error.mail}</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="password">{#password_label#}</label>
                            <div class="controls">
                                <input type="password" required class="form-control" name="password" id="password" placeholder="{#password_text_box#}">
                                <p class="text-error">{$error.password}</p>
                            </div>
                        </div>
                        <p class="text-error">{$error.head}</p>    
                        <label class="checkbox button-inline">
                            <input type="checkbox" name="remember-me" {if $rememberMe}checked{/if}> {#remember_me_label#}
                        </label>
                        <button class="btn btn-info button-inline" type="submit"><i class="glyphicon glyphicon-user"> </i>  {#connection_label#}</button>
                        <input type="hidden" name="return_to" value="{$return_to}"/>
                        <legend>&nbsp;</legend>
                        {include file='include/links.tpl'}
                    </form>
                </div>
                {include file='include/info.tpl'}
            </div>
        </div>
    </body>
</html>