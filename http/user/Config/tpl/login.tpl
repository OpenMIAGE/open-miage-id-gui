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
                    <div class="form-group {if $isResponse}has-feedback {if $error.mail!=null}has-error{else}{if $error.head!=null}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="email">{#login_label#}{if $isResponse && $error.mail!=null} {#$error.mail#}{/if}</label>
                        <div class="controls">
                            <input type="email" {if $isResponse && $error.password==null && $error.mail!=null}autofocus{/if} required name="mail" class="form-control" id="email" value="{$mail}" placeholder="{#login_text_box#}">{if $isResponse}
                            <span class="glyphicon {if $error.mail!=null}glyphicon-remove{else}{if $error.head!=null}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div>
                    <div class="form-group {if $isResponse && $error.mail==null}has-feedback {if $error.password!=null}has-error{else}{if $error.head!=null}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="password">{#password_label#}{if $isResponse && $error.password!=null && $error.mail==null} {#$error.password#}{/if}</label>
                        <div class="controls">
                            <input type="password" required {if $isResponse && $error.password!=null && $error.mail==null}autofocus{/if} class="form-control" name="password" id="password" placeholder="{#password_text_box#}"> {if $isResponse && $error.mail==null}
                            <span class="glyphicon {if $error.password!=null}glyphicon-remove{else}{if $error.head!=null}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div>    {if $isResponse && $error.head!=null}
                    <div class="has-feedback has-error">
                        <label class="control-label">{#$error.head#}</label>
                    </div>{/if}    
                    <label class="checkbox button-inline">
                        <input type="checkbox" name="remember_me" {if $remember_me}checked{/if}>{#remember_me_label#}
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