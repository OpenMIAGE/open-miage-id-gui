<div class="col-md-8 hidden-xs">
    <div class="well jumbotron openm-id-info">
        <h1>Bienvenu dans la galaxie OpenMIAGE !</h1>
        <p></p>
        <p>
            Première visite ? N'hesitez pas à vous inscrire !
        </p>
        <p>
            <button class="btn btn-default" onclick="location.href='{$links.create}'"><i class="icon-plus-sign icon-white"></i> S'inscrire</button>
        </p>
        <p>
            Vous possedez déjà un compte OpenM-ID ? Connectez-vous !
        </p>        
        <p>
            <button class="btn btn-default" onclick="location.href='{$links.login}'"><i class="icon-user icon-white"></i> Se connecter</button> 
        </p>
        <p><br></p>
        <p>
            {config_load file="info."|cat:$lang|cat:".properties"}
            {#description_content#}
        </p>        

    </div>
</div>