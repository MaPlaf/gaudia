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
            <img src="../assets/img/titre_litterature.svg" alt="Page Litterature" id="litterature_titre">
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

                $a = $db->prepare("SELECT id FROM livres WHERE resume = :resume");
                $a->execute([ 'resume' => $resume]);
                $resultat_a = $a->rowCount();
                $resulta = $a->fetch();

                if($resultat_a == 0){

                    $q = $db->prepare("INSERT INTO livres(titre, auteur, annee, genre, resume, poster, nbpage) VALUES(:titre , :auteur, :annee, :genre, :resume, :poster, :nbpage)");
                    $q->execute([
                        'titre' => $titre,
                        'auteur' => $auteur,
                        'annee' => $annee,
                        'genre' => $genre,
                        'resume' => $resume,
                        'poster' => $couverture,
                        'nbpage' => $page
                        ]);
                        
                    $b = $db->prepare("SELECT id FROM livres WHERE resume = :resume");
                    $b->execute([ 'resume' => $resume]);
                    $resultb = $b->fetch();

                    $r = $db->prepare("INSERT INTO livres_users(id_user, id_livres) VALUES(:id_user, :id_livres)");
                    $r->execute([
                        'id_user' => $id_user_active,
                        'id_livres' => $resultb['id']
                        ]);

                    $c = $db->prepare("SELECT id FROM livres_users WHERE id_user = :id_user AND id_livres = :id_livres");
                    $c->execute([ 'id_user' => $id_user_active, 'id_livres' => $resultb['id']]);
                    $resultc = $c->fetch();

                    $s = $db->prepare("INSERT INTO livres_elements_listes(id_liste, id_livres_user) VALUES(:id_liste, :id_livres_user)");
                    $s->execute([
                        'id_liste' => $id_liste,
                        'id_livres_user' => $resultc['id']
                        ]);

                    header("Location: litterature_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                    die();
                                    
                }else{

                    $d = $db->prepare("SELECT id FROM livres_users WHERE id_user = :id_user AND id_livres = :id_livres");
                    $d->execute(['id_user' => $id_user_active, 'id_livres' => $resulta['id']]);
                    $resultd = $d->fetch();
                    $resultat_d = $d->rowCount();

                    if($resultat_d == 0){

                        $i = $db->prepare("INSERT INTO livres_users(id_user, id_livres) VALUES(:id_user, :id_livres)");
                        $i->execute([
                            'id_user' => $id_user_active,
                            'id_livres' => $resulta['id']
                            ]);

                        $j = $db->prepare("SELECT id FROM livres_users WHERE id_user = :id_user AND id_livres = :id_livres");
                        $j->execute([ 'id_user' => $id_user_active, 'id_livres' => $resulta['id']]);
                        $resultj = $j->fetch();

                        $k = $db->prepare("INSERT INTO livres_elements_listes(id_liste, id_livres_user) VALUES(:id_liste, :id_livres_user)");
                        $k->execute([
                            'id_liste' => $id_liste,
                            'id_livres_user' => $resultj['id']
                            ]);

                        header("Location: litterature_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                        die();

                    }else{

                        $dd = $db->prepare("SELECT date_realise FROM livres_users WHERE id_user = :id_user AND id_livres = :id_livres");
                        $dd->execute(['id_user' => $id_user_active, 'id_livres' => $resulta['id']]);
                        $resultdd = $dd->fetch();

                        if($resultdd ='null'){

                            $e = $db->prepare("SELECT * FROM livres_elements_listes WHERE id_liste = :id_liste AND id_livres_user = :id_livres_user");
                            $e->execute(['id_liste' => $id_liste, 'id_livres_user' =>$resultd['id']]);
                            $resultat_e = $e->rowCount();
                            $resulte = $e->fetch();

                            if($resultat_e == 0){
                                $t = $db->prepare("INSERT INTO livres_elements_listes(id_liste, id_livres_user) VALUES(:id_liste, :id_livres_user)");
                                $t->execute([
                                    'id_liste' => $id_liste,
                                    'id_livres_user' => $resultd['id']
                                    ]);

                                    header("Location: litterature_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                                    die();

                            }else{
                                $deja_existe = "<p style='color:red; text-align:center;'>Ce livre est déjà dans la liste!</p>";
                                echo '<script type="text/javascript">function displayFunction(){document.getElementById("myModal").style.display = "block";};</script>';
                                echo '<BODY onLoad="displayFunction()">';
                            }

                        }else{

                            $deja_existe = "<p style='color:red; text-align:center;'>Ce livre a déjà été lu</p>";
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

                foreach($db->query("SELECT id_livres_user FROM livres_elements_listes WHERE id_liste = $id_liste ORDER BY $ordre") as $row){
                    foreach($db->query("SELECT id_livres FROM livres_users WHERE id = $row[0]") as $row){
                        foreach($db->query("SELECT titre, poster FROM livres WHERE id = $row[0]") as $row){
                            $listes_listes = $listes_listes . 
                                '<div class="liste_element">
                                    <img src="'.$row[1].'" alt="'.$row[0] .'">
                                    <h5>'.$row[0] .'</h5>
                                    <div class="survol">
                                        <button name="ajout_realise" onclick="ouvrir_modal(`myModalc`); envoie_donnee(`nom_livre_pese`,`'.$row[0].'`,`poster_livre_pese`,`'.$row[1].'`);" class="bouton_a btn_survol">MARQUER COMME RÉALISÉ</button>
                                        <form method="POST">
                                            <input type="hidden" name="nom" value="'.$row[0] .'" />
                                            <input type="hidden" name="poster" value="'.$row[1].'" />
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

                foreach($db->query("SELECT id_livres_user FROM livres_elements_listes WHERE id_liste = $id_liste") as $row){
                    foreach($db->query("SELECT id_livres FROM livres_users WHERE id = $row[0]") as $row){
                        foreach($db->query("SELECT titre, poster FROM livres WHERE id = $row[0]") as $row ){
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
                                        <button name="ajout_realise'.$val.'" id="'.$key .'" onclick="ouvrir_modal(`myModalc`); envoie_donnee(`nom_livre_pese`,`'.$val.'`,`poster_livre_pese`,`'.$key .'`);" class="bouton_a btn_survol">MARQUER COMME RÉALISÉ</button>
                                    <form method="POST">
                                        <input type="hidden" name="nom" value="'.$val.'" />
                                        <input type="hidden" name="poster" value="'.$key .'" />
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
            

            $f = $db->prepare("SELECT * FROM livres_elements_listes WHERE id_liste = $id_liste");
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
                        genere_listealpha("titre asc");
                        break;

                    case 'alpha_desc': 
                        genere_listealpha("titre desc");
                        break;

                    default: 
                        genere_listedate("date desc");
                        break;
                    }

            }else{
                $vide = "<h3 class='vide_h3' style='margin-top:5rem;'>Vous n'avez pas encore ajouté de livres à cette liste!</h3>";
            }

            if(isset($_POST["modifnom"])){
                extract($_POST);

                $m = $db->prepare("UPDATE livres_listes SET nom = ? WHERE id = ?");
                $m->execute([$nom, $id_liste]);

                header("Location: litterature_liste.php?id=".$id_liste."&liste=".$nom."");
                die();
            }

            if(isset($_POST["supp_liste"])){

                $n = $db->prepare("DELETE FROM livres_elements_listes WHERE id_liste = :id_liste");
                $n->execute(['id_liste' => $id_liste]);

                $o = $db->prepare("DELETE FROM livres_listes WHERE id = ?");
                $o->execute([$id_liste]);
                
                header("Location: litterature.php");
                die();

            }

            if(isset($_POST["ajout_realise"])){
                extract($_POST);

                $ee = $db->prepare("SELECT id FROM livres WHERE titre = :titre AND poster = :poster");
                $ee->execute(['titre' => $nom, 'poster' =>$poster]);
                $resultee = $ee->fetch();

                $hh = $db->prepare("SELECT id FROM livres_users WHERE id_user = :id_user AND id_livres = :id_livres");
                $hh->execute(['id_user' => $id_user_active, 'id_livres' =>$resultee['id']]);
                $resulthh = $hh->fetch();
                
                $ff = $db->prepare("UPDATE livres_users SET note = ? , commentaire = ?, date_realise = ? WHERE id = ?");
                $ff->execute([$note, $commentaire, date("Y-m-d h:i:s",time()), $resulthh['id']]);

                $gg = $db->prepare("DELETE FROM livres_elements_listes WHERE id_livres_user = :id_livres_user");
                $gg->execute(['id_livres_user' => $resulthh['id']]);

                header("Location: litterature_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                die();
            }

            if(isset($_POST["supprime_element"])){
                extract($_POST);

                $aa = $db->prepare("SELECT id FROM livres WHERE titre = :titre AND poster = :poster");
                $aa->execute(['titre' => $nom, 'poster' =>$poster]);
                $resultaa = $aa->fetch();

                $bb = $db->prepare("SELECT id FROM livres_users WHERE id_user = :id_user AND id_livres = :id_livres");
                $bb->execute(['id_user' => $id_user_active, 'id_livres' =>$resultaa['id']]);
                $resultbb = $bb->fetch();

                $o = $db->prepare("DELETE FROM livres_elements_listes WHERE id_liste = ? AND id_livres_user = ?");
                $o->execute([$id_liste, $resultbb['id']]);

                header("Location: litterature_liste.php?id=".$id_liste."&liste=".$nom_liste."");
                die();
            }

        ?>

        <main class="page_main">
            <div id="fait_nonfait">
                <a id="afaire" class="nav_b" href="litterature.php">À FAIRE</a><span> | </span><a id="realise" class="nav_b" href="litterature_realise.php">RÉALISÉS</a>
            </div>

            <div id="retour_reglage">
                <a href="litterature.php" class="retour"><img src="../assets/img/retour.svg" alt="Retour" width="25px"> Retour</a>

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

                <button type="button" id="btn_ouvrir" onclick="ouvrir_modal('myModal');"><span title="Ajouter un livre">+</span></button>

            </div>

            <div id="myModal" class="modal">
                <div class="modal-content modalc">
                    <span class="close" onclick="ferme_modal('myModal', 1);">&times;</span>

                    <form id="ajout_element_livres" method="post">
                        <div>
                            <div>
                                <label for="titre">TITRE</label>
                                <input type="text" name="titre" id="titre" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="auteur">AUTEUR(E)</label>
                                <input type="text" name="auteur" id="auteur" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="annee">ANNÉE DE PUBLICATION</label>
                                <input type="number" name="annee" id="annee" min="1700" max="2030" value="2015" step="1" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="genre">GENRE</label>
                                <input type="text" name="genre" id="genre" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="resume">RÉSUMÉ</label>
                                <textarea rows="2" name="resume" id="resume" class="cInput_text" required></textarea></br>
                            </div>
                            <div>
                                <label for="page">NOMBRE DE PAGES</label>
                                <input type="number" name="page" id="page" min="2" max="5000" value="400" step="1" class="cInput" required></br>
                            </div>
                            <div>
                                <label for="couverture">LIEN DE LA COUVERTURE</label>
                                <input type="text" name="couverture" id="couverture" class="cInput" required></br>
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
                            <input type="hidden" id="nom_livre_pese" name="nom" value="" />
                            <input type="hidden" id="poster_livre_pese" name="poster" value="" />
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