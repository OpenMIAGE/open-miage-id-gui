{config_load file="links."|cat:$lang|cat:".properties"}
<a class="btn btn-info links" href="{$links.login}{if $embeded}&embeded{/if}"><i class="glyphicon glyphicon-user"> </i> {#login_label#}</a>
<a class="btn btn-success links" href="{$links.create}{if $embeded}&embeded{/if}"><i class="glyphicon glyphicon-plus"> </i> {#inscription_label#}</a>
<a class="btn btn-default links" href="{$links.logout}{if $embeded}&embeded{/if}"><i class="glyphicon glyphicon-off"> </i> {#logout_label#}</a>