{include file='include/docType.tpl'}
<html>
    <head>
        <title>Login</title>
        {include file='include/head_embeded.tpl'}
    </head>
    <body class="body">
        <div class="container-fluid">
            <form class="form-signin" method="POST" action="{$action}">
                {include file='include/login_commons.tpl'}
                <legend>
                    <input type="hidden" name="embeded" value=""/>
                </legend>
                {assign var=embeded value=true}
                {include file='include/links.tpl'}
            </form>
        </div>
    </body>
</html>