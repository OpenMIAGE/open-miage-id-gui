{config_load file="login."|cat:$lang|cat:".properties"}
<div class="control-group">
    <label class="control-label" for="mail">{#login_label#}</label>
    <div class="controls">
        <input type="text" autofocus required name="mail" class="input-block-level" id="mail" value="{$mail}" placeholder="{#login_text_box#}">
        <p class="text-error">{$error.mail}</p>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">{#password_label#}</label>
    <div class="controls">
        <input type="password" required class="input-block-level" name="password" id="password" placeholder="{#password_text_box#}">
        <p class="text-error">{$error.password}</p>
    </div>
</div>
<p class="text-error">{$error.head}</p>    
<label class="checkbox button-inline">
    <input type="checkbox" name="remember-me" {if $rememberMe}checked{/if}> 
    <button class="btn btn-large btn-primary button-inline" type="button"> {#remember_me_label#}</button>
</label>
<button class="btn btn-success btn-large button-inline" type="submit"><i class="icon-user icon-white"></i>  {#connection_label#}</button>
<input type="hidden" name="return_to" value="{$return_to}"/>