<?php

error_reporting(E_ERROR); // enlever les messages 'deprecated'...

include("../config.php");
// penser à maj le nom de la base et le mot de passe
include("../maLibSQL.pdo.php");

/*
http://.../data.php?action=updateP&id=2&contenu=test
{"feedback":"ok","paragraphes":[]} 

http://.../data.php?action=getP

{"feedback":"ok","paragraphes":[{"id":"1","contenu":"premier","ordre":"1"},{"id":"2","contenu":"test","ordre":"2"},{"id":"3","contenu":"toto","ordre":"3"}]} 

http://.../data.php?action=addP&ordre=3&contenu=toto
{"feedback":"ok","paragraphes":[],"id":4}

http://.../data.php?action=delP&id=3
{"feedback":"ok","paragraphes":[]} 
*/


$data["feedback"] = "ko"; 
$data["paragraphes"] = array();

if (isset($_GET["action"]))
{
	switch($_GET["action"]) {

		case "delP" : 
		if (isset($_GET["id"])) $id = $_GET["id"];
		if ($id) {
			$SQL = "DELETE FROM paragraphes WHERE id='$id'";
			SQLUpdate($SQL);
			$data["feedback"] = "ok"; 
		}
		break;

		// id, contenu, ordre 
		case "addP" : 
			// Ajoute un P. et renvoie son identifiant
			$contenu = $ordre = false;
			if (isset($_GET["contenu"])) $contenu = $_GET["contenu"]; 
			if (isset($_GET["ordre"])) $ordre = $_GET["ordre"];
            if (isset($_GET["article"])) $article = $_GET["article"];

            if (($ordre !== false) && $contenu)
			{
				$SQL = "INSERT INTO paragraphes(ordre, contenu, article) VALUES ('$ordre','$contenu','$article')";
				$nextId = SQLInsert($SQL);
				$data["feedback"] = "ok"; 
				$data["id"] = $nextId;
			}
		break;

		case "updateP" : 
			// Modifie un P. dont le nom est passé en paramètre
			if (isset($_GET["contenu"])) $contenu = $_GET["contenu"]; 
			if (isset($_GET["id"])) $id = $_GET["id"];
			if ($id && $contenu) {
				$SQL = "UPDATE paragraphes SET contenu='$contenu' WHERE id='$id'";
				SQLUpdate($SQL);
				$data["feedback"] = "ok";
			}
		
		break;

		case "updateOrdre" : 
			// Change l'ordre d'un paragraphe 

			// On indique l'id du paragraphe concerné, 
			// ainsi que le nouveau numéro d'ordre 
			if (isset($_GET["id"])) $id = $_GET["id"];
            if (isset($_GET["ordre"])) $ordre = $_GET["ordre"];
            if (isset($_GET["article"])) $article = $_GET["article"];
			$SQL = "SELECT id FROM paragraphes WHERE id = $id";
            if (SQLGetChamp($SQL)){
                // Il peut s'agir d'un numéro d'ordre qui est déjà utilisé
                // On va décaler les ordres des paragraphes existants après
                $SQL = "UPDATE paragraphes SET ordre = ordre -1 where ordre <= $ordre and article like '$article';
						UPDATE paragraphes SET ordre = $ordre where id = $id";
                parcoursRs(SQLUpdate($SQL));
            }
			break;

		case "updateMetaDatas" :
            if (isset($_GET["article"])) $articles = $_GET["article"];
            $SQL = "SELECT ordre FROM paragraphes where article like '$articles' order by ordre asc ";
            $res = parcoursRs(SQLSelect($SQL));
            $data["feedback"] = "ok";
            $data["ordre"] = $res;
            break;


        case "getPA" :
            if (isset($_GET["article"])) $articles = $_GET["article"];
            $SQL = "SELECT * FROM paragraphes where article like '$articles' ORDER BY ordre ASC";
            $res = parcoursRs(SQLSelect($SQL));
            $data["feedback"] = "ok";
            $data["articles"] = $res;
            break;

        case "getPid" :
            if (isset($_GET["id"])) $id = $_GET["id"];
            $SQL = "SELECT * FROM paragraphes where id= $id ORDER BY ordre ASC";
            $res = parcoursRs(SQLSelect($SQL));
            $data["feedback"] = "ok";
            $data["paragraphes"] = $res;
            break;

        case "getA" :
            // Renvoie tous les articles de la base de données
            $SQL = "SELECT DISTINCT article FROM paragraphes ORDER BY article ASC";
            $res = parcoursRs(SQLSelect($SQL));
            $data["feedback"] = "ok";
            $data["articles"] = $res;
            break;
	}
}

echo json_encode($data);
?>

