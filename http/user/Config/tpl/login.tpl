{include file='include/docType.tpl'}
<html>
    <head>
        <title>Login</title>
        {include file='include/head.tpl'}
    </head>
    <body class="body">
        <div class="container-fluid">
            <div class="col-md-4 openm-id-form">
                <form class="form-signin form-group" method="POST" action="{$action}">
                    <legend><h2>OpenM-ID connexion</h2>(v{$version})</legend>
                    {config_load file="login."|cat:$lang|cat:".properties"}
                    <div class="form-group {if $isResponse}has-feedback {if $error.mail!=""}has-error{else}{if $error.head!=""}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="email">{#login_label#}{if $isResponse && $error.mail!=""} {#$error.mail#}{/if}</label>
                        <div class="controls">
                            <input type="email" autofocus required name="mail" class="form-control" id="email" value="{$mail}" placeholder="{#login_text_box#}">{if $isResponse}
                            <span class="glyphicon {if $error.mail!=""}glyphicon-remove{else}{if $error.head!=""}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div>
                    <div class="form-group {if $isResponse}has-feedback {if $error.password!=""}has-error{else}{if $error.head!=""}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="password">{#password_label#}{if $isResponse && $error.password!=""} {#$error.password#}{/if}</label>
                        <div class="controls">
                            <input type="password" required class="form-control" name="password" id="password" placeholder="{#password_text_box#}"> {if $isResponse}
                            <span class="glyphicon {if $error.password!=""}glyphicon-remove{else}{if $error.head!=""}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div>    {if $isResponse && $error.head!=""}
                    <div class="has-feedback has-error">
                        <label class="control-label">{#$error.head#}</label>
                    </div>{/if}    
                    <label class="checkbox button-inline">
                        <input type="checkbox" name="remember-me" {if $rememberMe}checked{/if}>{#remember_me_label#}
                    </label>
                    <button class="btn btn-info button-inline" type="submit"><i class="glyphicon glyphicon-user"> </i>  {#connection_label#}</button>
                    <input type="hidden" name="return_to" value="{$return_to}"/>
                    <legend>&nbsp;</legend>
                    {include file='include/links.tpl'}
                </form>
            </div>
            {include file='include/info.tpl'}
        </div>
    </body>
</html>