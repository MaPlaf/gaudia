<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>GAUDIA</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="description" content="Stockez, notez et organisez vos loisirs et divertissements, fixez-vous des objectifs en vous créant des listes à réaliser et retrouvez facilement les recettes que vous avez fait, les films que vous avez écouté, les livres que vous avez lu et beaucoup plus encore" />
		<meta name="keywords" content="listes, loisirs, divertissements, organisation, cinéma, littérature, voyage, gastronomie, jeux, spectacles, activités" />
		<meta name="theme-color" content="#654472;"/>
		<link rel="stylesheet" href="../assets/css/style.css" />
        <link rel="icon" type="./image/svg+xml" sizes="32x32" href="../assets/img/icon.svg">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Roboto&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/a3ddde716a.js" crossorigin="anonymous"></script>
	</head>

	<body>
        <?php require 'header_nav.php'; ?>

        <div class="titre">
            <img src="../assets/img/titre_jeux.svg" alt="Page jeux" id="jeux_titre">
        </div>

        <?php 

            include '../db/database.php';
            global $db;
            $id_user_active = $_SESSION['id_user_active'];
            $classe = null;
            $vide ="";
            $listes_listes ="";
            $deja_existe = "";

            if(isset($_POST["add_element"])){

                extract($_POST);

                $scriptjs = '<script type="text/javascript">function displayFunction(){document.getElementById("myModalc").style.display = "block";};</script>';
                $appel_script = '<BODY onLoad="displayFunction(), envoie_donnee(`nom_jeux_pese`,`'.$nom.'`,`photo_jeux_pese`,`'.$photo.'`);">';

                $a = $db->prepare("SELECT id FROM jeux WHERE nom = :nom");
                $a->execute([ 'nom' => $nom]);
                $resultat_a = $a->rowCount();
                $resulta = $a->fetch();

                if($resultat_a == 0){

                    $q = $db->prepare("INSERT INTO jeux(nom, date, description, photo) VALUES(:nom, :date, :description, :photo)");
                    $q->execute([
                        'nom' => $nom,
                        'date' => $date,
                        'description' => $description,
                        'photo' => $photo,
                        ]);
                        
                    $b = $db->prepare("SELECT id FROM jeux WHERE nom = :nom");
                    $b->execute([ 'nom' => $nom]);
                    $resultb = $b->fetch();

                    $r = $db->prepare("INSERT INTO jeux_users(id_user, id_jeux) VALUES(:id_user, :id_jeux)");
                    $r->execute([
                        'id_user' => $id_user_active,
                        'id_jeux' => $resultb['id']
                        ]);

                    echo $scriptjs ;
                    echo $appel_script;
                                    
                }else{

                    $d = $db->prepare("SELECT id FROM jeux_users WHERE id_user = :id_user AND id_jeux = :id_jeux");
                    $d->execute(['id_user' => $id_user_active, 'id_jeux' => $resulta['id']]);
                    $resultd = $d->fetch();
                    $resultat_d = $d->rowCount();

                    if($resultat_d == 0){

                        $i = $db->prepare("INSERT INTO jeux_users(id_user, id_jeux) VALUES(:id_user, :id_jeux)");
                        $i->execute([
                            'id_user' => $id_user_active,
                            'id_jeux' => $resulta['id']
                            ]);

                        echo $scriptjs ;
                        echo $appel_script;

                    }else{

                        $dd = $db->prepare("SELECT date_realise FROM jeux_users WHERE id_user = :id_user AND id_jeux = :id_jeux");
                        $dd->execute(['id_user' => $id_user_active, 'id_jeux' => $resulta['id']]);
                        $resultdd = $dd->fetch();
                    
                        if($resultdd ='null'){

                            echo $scriptjs ;
                            echo $appel_script;

                        }else{

                            $deja_existe = "<p style='color:red; text-align:center;'>Ce jeu a déjà été testé!</p>";
                            echo '<script type="text/javascript">function displayFunction(){document.getElementById("myModal").style.display = "block";}</script>';
                            echo '<BODY onLoad="displayFunction()">';
                        }
                    }   
                }
            }

            if(isset($_POST["ajout_realise"])){
                extract($_POST);

                $ee = $db->prepare("SELECT id FROM jeux WHERE nom = :nom");
                $ee->execute(['nom' => $nom]);
                $resultee = $ee->fetch();

                $hh = $db->prepare("SELECT id FROM jeux_users WHERE id_user = :id_user AND id_jeux = :id_jeux");
                $hh->execute(['id_user' => $id_user_active, 'id_jeux' =>$resultee['id']]);
                $resulthh = $hh->fetch();
                
                $ff = $db->prepare("UPDATE jeux_users SET note = ? , commentaire = ?, date_realise = ? WHERE id = ?");
                $ff->execute([$note, $commentaire, date("Y-m-d h:i:s",time()), $resulthh['id']]);

                $gg = $db->prepare("DELETE FROM jeux_elements_listes WHERE id_jeux_user = :id_jeux_user");
                $gg->execute(['id_jeux_user' => $resulthh['id']]);

                header("Location: jeux_element.php?id=".$resultee['id']."");
                die();
            }


            function genere_liste($ordre, $colone){
                global $db;
                global $listes_listes;
                $id_user_active = $_SESSION['id_user_active'];
                $id ="";
                $note = "";
                $commentaire = "";
                $date_realise = "";
                $nom = "";
                $date = "";
                $description = "";
                $photo= "";
                $tableau = array();
                
                foreach($db->query("SELECT id_jeux, note, commentaire, date_realise FROM jeux_users WHERE id_user = $id_user_active AND date_realise != 'null'") as $row ){
                    $id = $row[0];
                    $note = $row[1];
                    $commentaire = $row[2];
                    $date_realise = $row[3];

                    foreach($db->query("SELECT nom, date, description, photo FROM jeux WHERE id = $id") as $row ){
                        $nom = $row[0];
                        $date = $row[1];
                        $description = $row[2];
                        $photo = $row[3];

                        array_push($tableau, array('id'=>$id, 'note'=>$note, 'commentaire'=>$commentaire, 'date_realise'=>$date_realise, 'nom'=>$nom, 'date'=>$date, 'description'=>$description, 'photo'=>$photo,  ));
                    }
                }

                if(isset($_POST["filtre_ajout"])){
                    extract($_POST);
                    $filtres_appliques = array();
                    $nom_tableau = array();
                    $date_tableau = array();
                    $note_tableau = array();
                    $commentaire_tableau = array();
                    $description_tableau = array();
                    

                    if($nom != ""){

                        for ($row = 0; $row < count($tableau); $row++) {
                            if(stripos($tableau[$row]['nom'], $nom) !== false){
                                array_push($nom_tableau, $tableau[$row]);
                            }
                        }
                        array_push($filtres_appliques, $nom_tableau);
                    }

                    for ($row = 0; $row < count($tableau); $row++) {
                        if( $tableau[$row]['note'] >= $note_min and $tableau[$row]['note'] <= $note_max) {
                            array_push($note_tableau, $tableau[$row]);
                        }
                    }

                    array_push($filtres_appliques, $note_tableau);


                    if($commentaire != ""){
                        for ($row = 0; $row < count($tableau); $row++) {
                            if(stripos($tableau[$row]['commentaire'], $commentaire) !== false){
                                array_push($commentaire_tableau, $tableau[$row]);
                                
                            }
                        }
                        array_push($filtres_appliques, $commentaire_tableau);
                    }

                    for ($row = 0; $row < count($tableau); $row++) {
                        if( $tableau[$row]['date'] >= $date_min and $tableau[$row]['date'] <= $date_max) {
                            array_push($date_tableau, $tableau[$row]);
                        }
                    }

                    array_push($filtres_appliques, $date_tableau);


                    if($description != ""){

                        for ($row = 0; $row < count($tableau); $row++) {
                            if(stripos($tableau[$row]['description'], $description) !== false){
                                array_push($description_tableau, $tableau[$row]);
                            }
                        }
                        array_push($filtres_appliques, $description_tableau);
                    }


                    function resultat($arr){

                        $prev = array();

                        if (count($arr) >1 ){
     
                            for ($a = 0; $a < count($arr[0]); $a++ ) {
                                for ($b = 0; $b < count($arr[1]); $b++ ) {

                                    if($arr[0][$a]['id'] == $arr[1][$b]['id']){

                                        array_push($prev, $arr[1][$b]);
                                    }
                                }
                            }

                            if(count($arr) >2 ){
                            
                                for ($c = 2; $c < count($arr); $c++ ) {

                                    $next = array();

                                    for ($d = 0; $d < count($arr[$c]); $d++ ) {
                                        for($e = 0; $e < count($prev); $e++ ) {

                                            if($prev[$e]['id'] == $arr[$c][$d]['id']){

                                                array_push($next, $arr[$c][$d]);
                                            }
                                        }
                                    }

                                    $prev = $next;
                                }

                                $next;
                                return $next;

                            }else{
                                return $prev;
                            }

                        }else{

                            for ($a = 0; $a < count($arr[0]); $a++ ) {

                                array_push($prev, $arr[0][$a]);
                            }

                            return $prev;
                        }
                    }
                
                    $result = resultat($filtres_appliques);
                    for ($row = 0; $row < count($result); $row++) {

                        $listes_listes = $listes_listes . 
                            '<div class="liste_element" onclick="redirige(`jeux_element.php?id='.$result[$row]['id'].'`);">
                                <img src="'.$result[$row]['photo'].'" alt="'.$result[$row]['nom'] .'">
                                    <h5 class="noteimg">'.$result[$row]['nom'].'</h5>
                                    <h5 class="noteh5">Votre note : <span> '.$result[$row]['note'].'</span></h5>
                                </div>';
                    }

                }else{

                    $columns = array_column($tableau, $colone);
                    array_multisort($columns, $ordre , $tableau);

                    for ($row = 0; $row < count($tableau); $row++) {

                        $listes_listes = $listes_listes . 
                            '<div class="liste_element" onclick="redirige(`jeux_element.php?id='.$tableau[$row]['id'].'`);">
                                <img src="'.$tableau[$row]['photo'].'" alt="'.$tableau[$row]['nom'] .'">
                                    <h5 class="noteimg">'.$tableau[$row]['nom'].'</h5>
                                    <h5 class="noteh5">Votre note : <span> '.$tableau[$row]['note'].'</span></h5>
                                </div>';
                    }
                }
            }

            
            $f = $db->prepare("SELECT id_jeux, date_realise FROM jeux_users WHERE id_user = $id_user_active AND date_realise != 'null'");
            $f->execute();
            $nb_res = $f->rowCount();

            if(($nb_res !== 0)){

                if(isset($_POST['classer_par'])){
                    $classe = $_POST['classer_par'];
                }
                
                switch($classe){
                    case 'date_desc': 
                        genere_liste(SORT_DESC, 'date_realise');
                        break;

                    case 'date_asc': 
                        genere_liste(SORT_ASC, 'date_realise');
                        break;

                    case 'alpha_asc': 
                        genere_liste(SORT_ASC, 'nom');
                        break;

                    case 'alpha_desc': 
                        genere_liste(SORT_DESC, 'nom');
                        break;

                    default: 
                        genere_liste(SORT_DESC, 'date_realise');
                        break;
                    }

            }else{
                $vide = "<h3 style='margin-top:5rem;'>Vous n'avez pas encore marqué de jeu comme réalisé!</h3>";
            }
        ?>

        <main class="page_main">
            <div id="fait_nonfait">
                <a id="afaire" class="nav_b" href="jeux.php">À FAIRE</a><span> | </span><a id="realise" class="nav_b" href="jeux_realise.php">RÉALISÉS</a>
            </div>

            <div id="options">

                <form name="myform" method="post">
                    <label for="classer_par">Classer par</label>
                    <select name="classer_par" onchange="this.form.submit()">
                        <option value="date_desc"<?php if($classe == "date_desc"){ echo " selected"; }?>> Date (récent->ancien)</option>
                        <option value="date_asc"<?php if($classe == "date_asc"){ echo " selected"; }?>> Date (ancien->récent)</option>
                        <option value="alpha_asc"<?php if($classe == "alpha_asc"){ echo " selected"; }?>> Ordre alphabétique</option>
                        <option value="alpha_desc"<?php if($classe == "alpha_desc"){ echo " selected"; }?>> Ordre alphabétique inverse</option>
                    </select>
                </form>

                <button type="button" id="btn_ouvrir" onclick="ouvrir_modal('myModal');"><span title="Ajouter un jeu">+</span></button>

            </div>

            <div id="filtre">
                <div id="m_filtre" style="display:none;">
                    <form method="post" id="modif_filtre" class="form_para">
                        
                        <div>
                            <div>
                                <label for="nom">NOM</label>
                                <input type="text" name="nom" id="nom" class="cInput" require>
                            </div>
                            <div>
                                <label for="date_min">DATE MIN</label>
                                <input type="number" name="date_min" id="date_min" min="1000" max="2030" value="1900" step="1" class="cInput">
                            </div>
                            <div>
                                <label for="date_max">DATE MAX</label>
                                <input type="number" name="date_max" id="date_max" min="1000" max="2030" value="2030" step="1" class="cInput">
                            </div>
                            <div>
                                <label for="description">DESCRIPTION</label>
                                <input type="text" name="description" id="descriptionb" class="cInput">
                            </div>
                            <div>
                                <label for="note_min">NOTE MIN</label>
                                <input type="number" name="note_min" id="note_min" min="0" max="10" value="0" step="0.1" class="cInput">
                            </div>
                            <div>
                                <label for="note_max">NOTE MAX</label>
                                <input type="number" name="note_max" id="note_max" min="0" max="10" value="10" step="0.1" class="cInput">
                            </div>
                            <div>
                                <label for="commentaire">COMMENTAIRES</label>
                                <input type="text" name="commentaire" id="commentaire" class="cInput">
                            </div>
                        </div>

                        <input type="submit" name="filtre_ajout" id="filtre_ajout" class="bouton_a btn_param b" value="APPLIQUER LE(S) FILTRE(S)">
                    </form>
                </div>

                <button id="filtre_selection" onclick="ouvre('m_filtre','filtre_selection', 'APPLIQUER DES FILTRES', 'flex', 'ANNULER');" type="button" class="bouton_a">APPLIQUER DES FILTRES</button>
            </div>

            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="ferme_modal('myModal', 1);">&times;</span>

                    <form id="ajout_element_jeux" method="post">
                        <div>
                            <div>
                                <label for="nom">NOM</label></br>
                                <input type="text" name="nom" id="nom" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="date">ANNÉE DE SORTIE</label></br>
                                <input type="number" name="date" id="date" min="1000" max="2030" value="2010" step="1" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="description">DESCRIPTION</label></br>
                                <textarea type="text" name="description" id="description" class="cInput" required></textarea></br>
                            </div>
                            <div>
                                <label for="photo">URL PHOTO</label></br>
                                <input type="url" name="photo" id="photo" class="cInput" required></br>
                            </div>
                        </div>

                        <input type="submit" name="add_element" class="bouton_a" value="SAUVEGARDER">

                        <?php echo $deja_existe?>
                    </form>
                </div>
            </div>

            <h1>JEUX TESTÉS</h1>

            <?php echo $vide; ?>

            <div id="contenant_liste_element">
                <?php echo $listes_listes;?>
            </div>

            <div id="myModalc" class="modal">
                <div class="modal-content">
                    <span class="closeb" onclick="ferme_modal('myModalc', 0);">&times;</span>

                    <div id="contenu_ajout_realise">
                        <h3 class="hparam">QU'EST CE QUE VOUS<br> EN AVEZ PENSÉ?</h3>
                        <form method="post" id="ajoutrealise">
                            <label for="note">VOTRE NOTE SUR 10</label>
                            <input type="number" name="note" id="note" min="0" max="10" value="5" step="0.1" required class="cInput"></br>
                            <label for="commentaire">VOS COMMENTAIRES</label>
                            <textarea rows="4" name="commentaire" id="commentaire" class="cInput_text" placeholder="Si vous en avez, biensûr."></textarea></br>
                            <input type="hidden" id="nom_jeux_pese" name="nom" value="" />
                            <input type="hidden" id="photo_jeux_pese" name="photo" value="" />
                            <input type="submit" name="ajout_realise" id="ajout_realise" class="bouton_a" value="SAUVEGARDER">
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php require 'footer.php'; ?>

        <script src="../assets/js/main.js"></script>
    </body>
</html>