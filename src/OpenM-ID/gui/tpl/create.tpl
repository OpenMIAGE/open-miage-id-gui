{include file='include/docType.tpl'}
<html>
    <head>
        <title>Inscription</title>
        {include file='include/head.tpl'}
    </head>
    <body class="body">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="col-md-4">
                    <form class="form-signin" method="POST" action="{$action}">
                        {config_load file="create."|cat:$lang|cat:".properties"}
                        <legend><h2>{#inscription_title#}</h2>(v{$version})</legend>
                        <div class="control-group">
                            <label class="control-label" for="mail">{#login_label#}</label>
                            <div class="controls">
                                <input type="text" name="mail" class="input-block-level" id="mail" value="{$mail}" placeholder="{#login_text_box#}">
                                <p class="text-error">{$error.mail}</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="password">{#password_label#}</label>
                            <div class="controls">
                                <input type="password" class="input-block-level" name="password" id="password" placeholder="{#password_text_box#}">
                                <p class="text-error">{$error.password}</p>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="password2">{#password_2_label#}</label>
                            <div class="controls">
                                <input type="password" class="input-block-level" name="password2" id="password2" placeholder="{#password_text_box#}">
                                <p class="text-error">{$error.password2}</p>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="icon-user icon-white"></i>  {#create_label#}</button>
                        <input type="hidden" name="return_to" value="{$return_to}"/>
                        <legend><br></legend>
                            {include file='include/links.tpl'}
                    </form>
                </div>
                {include file='include/info.tpl'}
            </div>
        </div>
    </body>
</html>