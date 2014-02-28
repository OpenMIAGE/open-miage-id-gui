{include file='include/docType.tpl'}
<html>
    <head>
        <title>Inscription</title>
        {include file='include/head.tpl'}
    </head>
    <body class="body">
        <div class="container-fluid">
            <div class="col-md-4 openm-id-form">
                <form class="form-signin form-group" method="POST" action="{$action}">
                    {config_load file="create."|cat:$lang|cat:".properties"}
                    <legend><h2>{#inscription_title#}</h2>(v{$version})</legend>
                    <div class="form-group {if $isResponse}has-feedback {if $error.mail!=null}has-error{else}{if $error.head!=null}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="mail">{#login_label#}{if $isResponse && $error.mail!=null} {#$error.mail#}{/if}</label>
                        <div class="controls">
                            <input type="email" {if $isResponse && $error.password==null && $error.mail!=null}autofocus{/if} required name="mail" class="form-control" id="email" value="{$mail}" placeholder="{#login_text_box#}">{if $isResponse}
                            <span class="glyphicon {if $error.mail!=null}glyphicon-remove{else}{if $error.head!=null}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div>
                    <div class="form-group {if $isResponse && $error.mail==null}has-feedback {if $error.password!=null}has-error{else}{if $error.head!=null}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="password">{#password_label#}{if $isResponse && $error.mail!=null} {#$error.password#}{/if}</label>
                        <div class="controls">
                            <input type="password" required {if $isResponse && $error.password!=null && $error.mail==null}autofocus{/if} class="form-control" name="password" id="password" placeholder="{#password_text_box#}"> {if $isResponse && $error.mail==null}
                            <span class="glyphicon {if $error.password!=null}glyphicon-remove{else}{if $error.head!=null}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div>
                    <div class="form-group {if $isResponse && $error.mail==null}has-feedback {if $error.password2!=null}has-error{else}{if $error.head!=null}has-warning{else}has-success{/if}{/if}{/if}">
                        <label class="control-label" for="password2">{#password_2_label#}{if $isResponse && $error.mail!=null} {#$error.password2#}{/if}</label>
                        <div class="controls">
                            <input type="password" required class="form-control" name="password2" id="password2" placeholder="{#password_text_box#}"> {if $isResponse && $error.mail==null}
                            <span class="glyphicon {if $error.password2!=null}glyphicon-remove{else}{if $error.head!=null}glyphicon-warning-sign{else}glyphicon-ok{/if}{/if} form-control-feedback"></span>{/if}
                        </div>
                    </div> {if $isResponse && $error.head!=null}
                    <div class="has-feedback has-error">
                        <label class="control-label">{#$error.head#}</label>
                    </div>{/if}    
                    <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-user"></i>  {#create_label#}</button>
                    <input type="hidden" name="return_to" value="{$return_to}"/>
                    <legend><br></legend>
                        {include file='include/links.tpl'}
                </form>
            </div>
            {include file='include/info.tpl'}
        </div>
    </body>
</html>