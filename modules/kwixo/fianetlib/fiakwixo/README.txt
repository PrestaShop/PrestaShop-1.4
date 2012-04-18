Fia-Net : API PHP V3.1
Module : Kwixo

Installation :
Pour installer le module Kwixo, placer le contenu de l'archive  dans le dossier lib/ de manière à obtenir cette hiériarchie :
lib/kwixo/const
lib/kwixo/lib
lib/kwixo/examples
lib/kwixo/includes.inc.php

Décommenter ou ajouter la ligne suivante dans le fichier lib/includes/includes.inc.php :
require_once ROOT_DIR . '/lib/kwixo/includes.inc.php';


Pour faire fonctionner les services sur votre site, pensez à bien reporter les informations privées dans le fichier lib/kwixo/const/site_params.yml