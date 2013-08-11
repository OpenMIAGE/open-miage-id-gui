{config_load file="links."|cat:$lang|cat:".properties"}
<a class="btn btn-info links" href="{$links.login}{if $embeded}&embeded{/if}"><i class="icon-user icon-white"></i> {#login_label#}</a>
<a class="btn btn-success links" href="{$links.create}{if $embeded}&embeded{/if}"><i class="icon-plus-sign icon-white"></i> {#inscription_label#}</a>
<a class="btn btn-inverse links" href="{$links.logout}{if $embeded}&embeded{/if}"><i class="icon-off icon-white"></i> {#logout_label#}</a>