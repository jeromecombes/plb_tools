<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright -2018 Jérôme Combes

Fichier : importEDT.php
Création : 21 février 2015
Dernière modification : 29 août 2017
@author Jérôme Combes <jerome@planningbilbio.fr>

Description :
Importe les emplois du temps de la table personnel dans la table planning_hebdo

Utilisation :
- placer ce fichier à la racine du dossier planning
- se connecter à http://serveur/planning/importEDT.php?fin=xxxx
      (remplacer xxxx par la date de fin de validité des plannings au format AAAA-MM-JJ)

*/

session_start();

ini_set("display_errors", 1);
error_reporting(999);

$version="importEDT";

include "include/config.php";
$CSRFToken = CSRFToken();
$fin=isset($_GET['fin'])?$_GET['fin']:"2019-12-31";

$insert=new dbh();
$insert->CSRFToken = $CSRFToken;
$insert->prepare("INSERT INTO `{$dbprefix}planning_hebdo` (`perso_id`,`debut`,`fin`,`valide`,`validation`,`actuel`,`temps`) VALUES 
  (:perso_id,SYSDATE(),'$fin',1,SYSDATE(),1,:temps);");

$db=new db();
$db->delete('planning_hebdo');

$db=new db();
$db->select("personnel", "id,temps");
if ($db->result) {
    foreach ($db->result as $elem) {
        $temps = json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        if (!isset($temps[4]) or !$temps[4]) {
            $temps[4]=1;
        }
        $temps = json_encode($temps);
        $insert->execute(array(":perso_id"=>$elem['id'], ":temps"=>$elem['temps']));
    }
}
