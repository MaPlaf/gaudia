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
        <?php require 'header_nav.php';?>

        <div class="titre">
            <img src="../assets/img/titre_voyage.svg" alt="Page Voyage" id="voyage_titre">
        </div>

        <?php 

            include '../db/database.php';
            global $db;
            $id_user_active = $_SESSION['id_user_active'];
            $classe = null;
            $deja_existe = "";
            $vide ="";
            $listes_listes ="";
            $id_liste = $_GET["id"];
            $nom_liste = $_GET["liste"];

            if(isset($_POST["add_element"])){

                extract($_POST);

                $a = $db->prepare("SELECT id FROM voyage WHERE ville = :ville");
                $a->execute([ 'ville' => $ville]);
                $resultat_a = $a->rowCount();
                $resulta = $a->fetch();

                if($resultat_a == 0){

                    $q = $db->prepare("INSERT INTO voyage(ville, pays, photo, description) VALUES(:ville, :pays, :photo, :description)");
                    $q->execute([
                        'ville' => $ville,
                        'pays' => $pays,
                        'photo' => $photo,
                        'description' => $description,
                        ]);
                        
                    $b = $db->prepare("SELECT id FROM voyage WHERE ville = :ville");
                    $b->execute([ 'ville' => $ville]);
                    $resultb = $b->fetch();

                    $r = $db->prepare("INSERT INTO voyage_users(id_user, id_voyage) VALUES(:id_user, :id_voyage)");
                    $r->execute([
                        'id_user' => $id_user_active,
                        'id_voyage' => $resultb['id']
                        ]);

                    $c = $db->prepare("SELECT id FROM voyage_users WHERE id_user = :id_user AND id_voyage = :id_voyage");
                    $c->execute([ 'id_user' => $id_user_active, 'id_voyage' => $resultb['id']]);
                    $resultc = $c->fetch();

                    $s = $db->prepare("INSERT INTO voyage_elements_listes(id_liste, id_voyage_user) VALUES(:id_liste, :id_voyage_user)");
                    $s->execute([
                        'id_liste' => $id_liste,
                        'id_voyage_user' => $resultc['id']
                        ]);

                    header("Location: voyage_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                    die();
                                    
                }else{

                    $d = $db->prepare("SELECT id FROM voyage_users WHERE id_user = :id_user AND id_voyage = :id_voyage");
                    $d->execute(['id_user' => $id_user_active, 'id_voyage' => $resulta['id']]);
                    $resultd = $d->fetch();
                    $resultat_d = $d->rowCount();

                    if($resultat_d == 0){

                        $i = $db->prepare("INSERT INTO voyage_users(id_user, id_voyage) VALUES(:id_user, :id_voyage)");
                        $i->execute([
                            'id_user' => $id_user_active,
                            'id_voyage' => $resulta['id']
                            ]);

                        $j = $db->prepare("SELECT id FROM voyage_users WHERE id_user = :id_user AND id_voyage = :id_voyage");
                        $j->execute([ 'id_user' => $id_user_active, 'id_voyage' => $resulta['id']]);
                        $resultj = $j->fetch();

                        $k = $db->prepare("INSERT INTO voyage_elements_listes(id_liste, id_voyage_user) VALUES(:id_liste, :id_voyage_user)");
                        $k->execute([
                            'id_liste' => $id_liste,
                            'id_voyage_user' => $resultj['id']
                            ]);

                        header("Location: voyage_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                        die();

                    }else{

                        $dd = $db->prepare("SELECT date_realise FROM voyage_users WHERE id_user = :id_user AND id_voyage = :id_voyage");
                        $dd->execute(['id_user' => $id_user_active, 'id_voyage' => $resulta['id']]);
                        $resultdd = $dd->fetch();

                        if($resultdd ='null'){

                            $e = $db->prepare("SELECT * FROM voyage_elements_listes WHERE id_liste = :id_liste AND id_voyage_user = :id_voyage_user");
                            $e->execute(['id_liste' => $id_liste, 'id_voyage_user' =>$resultd['id']]);
                            $resultat_e = $e->rowCount();
                            $resulte = $e->fetch();

                            if($resultat_e == 0){
                                $t = $db->prepare("INSERT INTO voyage_elements_listes(id_liste, id_voyage_user) VALUES(:id_liste, :id_voyage_user)");
                                $t->execute([
                                    'id_liste' => $id_liste,
                                    'id_voyage_user' => $resultd['id']
                                    ]);

                                    header("Location: voyage_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                                    die();

                            }else{
                                $deja_existe = "<p style='color:red; text-align:center;'>Cette ville est déjà dans la liste!</p>";
                                echo '<script type="text/javascript">function displayFunction(){document.getElementById("myModal").style.display = "block";};</script>';
                                echo '<BODY onLoad="displayFunction()">';
                            }

                        }else{

                            $deja_existe = "<p style='color:red; text-align:center;'>Cette ville a déjà été visitée</p>";
                            echo '<script type="text/javascript">function displayFunction(){document.getElementById("myModal").style.display = "block";};</script>';
                            echo '<BODY onLoad="displayFunction()">';
                        }
                    }
                }
            }

            function genere_listedate($ordre){
                global $db;
                $id_liste = $_GET['id'];
                global $listes_listes;

                foreach($db->query("SELECT id_voyage_user FROM voyage_elements_listes WHERE id_liste = $id_liste ORDER BY $ordre") as $row){
                    foreach($db->query("SELECT id_voyage FROM voyage_users WHERE id = $row[0]") as $row){
                        foreach($db->query("SELECT ville, photo FROM voyage WHERE id = $row[0]") as $row){
                            $listes_listes = $listes_listes . 
                                '<div class="liste_element">
                                    <img src="'.$row[1].'" alt="'.$row[0] .'">
                                    <h5>'.$row[0] .'</h5>
                                    <div class="survol">
                                        <button name="ajout_realise" onclick="ouvrir_modal(`myModalc`); envoie_donnee(`ville_voyage_pese`,`'.$row[0].'`,`photo_voyage_pese`,`'.$row[1].'`);" class="bouton_a btn_survol">MARQUER COMME RÉALISÉ</button>
                                        <form method="POST">
                                            <input type="hidden" name="ville" value="'.$row[0] .'" />
                                            <input type="hidden" name="photo" value="'.$row[1].'" />
                                            <button type="submit" name="supprime_element" id="supprime_element" class="bouton_a btn_survol">SUPPRIMER</button>
                                        </form>
                                    </div>
                                </div>';
                        }
                    }
                }
            }

            function genere_listealpha($ordre){
                global $db;
                $id_liste = $_GET['id'];
                $tableau = array();

                foreach($db->query("SELECT id_voyage_user FROM voyage_elements_listes WHERE id_liste = $id_liste") as $row){
                    foreach($db->query("SELECT id_voyage FROM voyage_users WHERE id = $row[0]") as $row){
                        foreach($db->query("SELECT ville, photo FROM voyage WHERE id = $row[0]") as $row ){
                            $tableau[$row[0]] = $row[1];
                        }
                    }
                }

                function affiche_ordrealpha($tableau){
                    global $listes_listes;

                    foreach ($tableau as $key => $val) {
                        $listes_listes = $listes_listes . 
                            '<div class="liste_element">
                                <img src="'.$val.'" alt="'.$key .'">
                                <h5>'.$key .'</h5>
                                <div id="class">
                                        <button name="ajout_realise'.$val.'" id="'.$key .'" onclick="ouvrir_modal(`myModalc`); envoie_donnee(`ville_voyage_pese`,`'.$val.'`,`photo_voyage_pese`,`'.$key .'`);" class="bouton_a btn_survol">MARQUER COMME RÉALISÉ</button>
                                    <form method="POST">
                                        <input type="hidden" name="ville" value="'.$val.'" />
                                        <input type="hidden" name="photo" value="'.$key .'" />
                                        <button type="submit" name="supprime_element" id="supprime_element" class="bouton_a btn_survol">SUPPRIMER</button>
                                    </form>
                                </div>
                            </div>';
                    }
                }


                if($ordre == "titre asc" ){
                    ksort($tableau);
                    affiche_ordrealpha($tableau);
                }else{
                    krsort($tableau);
                    affiche_ordrealpha($tableau);  
                }
            }
            

            $f = $db->prepare("SELECT * FROM voyage_elements_listes WHERE id_liste = $id_liste");
            $f->execute();
            $nb_res = $f->rowCount();

            if(($nb_res !== 0)){

                if(isset($_POST['classer_par'])){
                    $classe = $_POST['classer_par'];
                }
                
                switch($classe){
                    case 'date_desc': 
                        genere_listedate("date desc");
                        break;

                    case 'date_asc': 
                        genere_listedate("date asc");
                        break;

                    case 'alpha_asc': 
                        genere_listealpha("ville asc");
                        break;

                    case 'alpha_desc': 
                        genere_listealpha("ville desc");
                        break;

                    default: 
                        genere_listedate("date desc");
                        break;
                    }

            }else{
                $vide = "<h3 class='vide_h3' style='margin-top:5rem;'>Vous n'avez pas encore ajouté de voyage à cette liste!</h3>";
            }

            if(isset($_POST["modifnom"])){
                extract($_POST);

                $m = $db->prepare("UPDATE voyage_listes SET nom = ? WHERE id = ?");
                $m->execute([$nom, $id_liste]);

                header("Location: voyage_liste.php?id=".$id_liste."&liste=".$nom."");
                die();
            }

            if(isset($_POST["supp_liste"])){

                $n = $db->prepare("DELETE FROM voyage_elements_listes WHERE id_liste = :id_liste");
                $n->execute(['id_liste' => $id_liste]);

                $o = $db->prepare("DELETE FROM voyage_listes WHERE id = ?");
                $o->execute([$id_liste]);
                
                header("Location: voyage.php");
                die();

            }

            if(isset($_POST["ajout_realise"])){
                extract($_POST);

                $ee = $db->prepare("SELECT id FROM voyage WHERE ville = :ville");
                $ee->execute(['ville' => $ville]);
                $resultee = $ee->fetch();

                $hh = $db->prepare("SELECT id FROM voyage_users WHERE id_user = :id_user AND id_voyage = :id_voyage");
                $hh->execute(['id_user' => $id_user_active, 'id_voyage' =>$resultee['id']]);
                $resulthh = $hh->fetch();
                
                $ff = $db->prepare("UPDATE voyage_users SET note = ? , commentaire = ?, date_realise = ? WHERE id = ?");
                $ff->execute([$note, $commentaire, date("Y-m-d h:i:s",time()), $resulthh['id']]);

                $gg = $db->prepare("DELETE FROM voyage_elements_listes WHERE id_voyage_user = :id_voyage_user");
                $gg->execute(['id_voyage_user' => $resulthh['id']]);

                header("Location: voyage_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                die();
            }

            if(isset($_POST["supprime_element"])){
                extract($_POST);

                $aa = $db->prepare("SELECT id FROM voyage WHERE ville = :ville");
                $aa->execute(['ville' => $ville]);
                $resultaa = $aa->fetch();

                $bb = $db->prepare("SELECT id FROM voyage_users WHERE id_user = :id_user AND id_voyage = :id_voyage");
                $bb->execute(['id_user' => $id_user_active, 'id_voyage' =>$resultaa['id']]);
                $resultbb = $bb->fetch();

                $o = $db->prepare("DELETE FROM voyage_elements_listes WHERE id_liste = ? AND id_voyage_user = ?");
                $o->execute([$id_liste, $resultbb['id']]);

                header("Location: voyage_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                die();
            }

        ?>

        <main class="page_main">
            <div id="fait_nonfait">
                <a id="afaire" class="nav_b" href="voyage.php">À FAIRE</a><span> | </span><a id="realise" class="nav_b" href="voyage_realise.php">RÉALISÉS</a>
            </div>

            <div id="retour_reglage">
                <a href="voyage.php" class="retour"><img src="../assets/img/retour.svg" alt="Retour" width="25px"> Retour</a>

                <button type="button" id="btn_ouvrirb" class="btn_reglage" onclick="ouvrir_modal('myModalb');">MODIFIER <img src="../assets/img/reglage.svg" alt="Reglage" id="Reglage" width="20px"></button>

                <div id="myModalb" class="modal">
                    <div class="modal-content modalb">
                        <span class="closeb" onclick="ferme_modal('myModalb', 0);">&times;</span>

                        <div id="contenu_reglage">
                            <h3 style="font-size:25px; margin-bottom:3rem;" class="hparam">RÉGLAGE DE LA LISTE</h3>

                            <div id="m_nom" style="display:none;">
                                <form method="post" id="modif_nom">
                                    <label for="nom">NOUVEAU NOM</label>
                                    <input type="text" name="nom" id="nom" required value="<?php echo $nom_liste ?>" class="cInput"></br>
                                    <input type="submit" name="modifnom" id="modifnom" class="bouton_a bouton_c" value="MODIFIER">
                                </form>
                            </div>

                            <button id="change_nom" onclick="ouvre('m_nom','change_nom','MODIFIER LE NOM', 'inline', 'ANNULER');" type="button" class="bouton_a">MODIFIER LE NOM</button>

                            <div id="supprime_liste" style="display:none;">
                                <form method="post" id="sup_liste">
                                    <label style="display:inline-block; height:60px">VOULEZ-VOUS VRAIMENT<br>SUPPRIMER LA LISTE?</label><br>
                                    <input type="submit" name="supp_liste" id="supp_liste" class="bouton_a bouton_c" value="SUPPRIMER">
                                </form>
                            </div>
                            <button id="supression" onclick="ouvre('supprime_liste','supression', 'SUPPRIMER LA LISTE', 'inline', 'ANNULER');" type="button" class="bouton_a">SUPPRIMER LA LISTE</button>
                        </div>
                    
                    </div>
                </div>
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

                <button type="button" id="btn_ouvrir" onclick="ouvrir_modal('myModal');"><span title="Ajouter un voyage">+</span></button>

            </div>

            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="ferme_modal('myModal', 1);">&times;</span>

                    <form id="ajout_element_voyage" method="post">
                        <div>
                            <div>
                                <label for="ville">VILLE</label></br>
                                <input type="text" name="ville" id="ville" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="pays">PAYS</label></br>
                                <input type="text" name="pays" id="pays" class="cInput" required></br>
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

            <h1><?php echo mb_strtoupper($_GET["liste"])?></h1>

            <?php echo $vide;?>


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
                            <input type="number" name="note" id="note" min="0" max="10" value="0" step="0.1" required class="cInput"></br>
                            <label for="commentaire">VOS COMMENTAIRES</label>
                            <textarea rows="4" name="commentaire" id="commentaire" class="cInput_text" placeholder="Si vous en avez, biensûr."></textarea></br>
                            <input type="hidden" id="ville_voyage_pese" name="ville" value="" />
                            <input type="hidden" id="photo_voyage_pese" name="photo" value="" />
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