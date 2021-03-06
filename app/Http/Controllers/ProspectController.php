<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Prospect;
use App\Prospect_produit;
use App\Prospect_score;
use App\score;
use App\champActivite;
use App\Groupe;
use App\Produit;
use App\Contact;
use App\User;
use App\cntct_email;
use App\cntct_appel;
use App\cntct_terain;
use App\Priorite;
use App\Etat;
use App\Client_produit;
use App\prochaineAction;

use App\Classes\hello;
use App\Classes\PHPExcel\PHPExcel_IOFactory;

class ProspectController extends Controller
{
    public function get($bloque = 0,$type=1)
    {
         //gestion de demandeur
         if ($this->UserType() == 0 && $bloque == 1) {
            return redirect('home')->with('status',  '<div class="alert alert-danger alert-dismissible show" >
                                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                              </button>
                                                               Acces interdit !
                                                             </div>');
         }else{
           switch ($type) {
             case 0:
               $prospects = Prospect::where('bloquer',$bloque)->orderByRaw('id DESC')->get(); //je ne recuppere que les prospects non bloque , inclus les client
               break;
               case 1:
                 $prospects = Prospect::where('bloquer',$bloque)->where('client',0)->orderByRaw('id DESC')->get(); //je ne recuppere que les prospects non bloque , inclus les client
                 break;
                 case 2:
                   $prospects = Prospect::where('bloquer',$bloque)->where('client',1)->orderByRaw('id DESC')->get(); //je ne recuppere que les prospects non bloque , inclus les client
                   break;
           }


           $tousLesScores = Score::get();//pour un nouveau contact/prospect
           $tousLesChampActiv = ChampActivite::get();
           $tousLesGroupes = Groupe::get();
           $tousLesProduits = Produit::get();
           $tousLesUsers = User::where('type',0)->get();
           //pour form ajout tache et recuperation des produit supposes au moment de creation de se prospect
           $produitsPropose = Prospect_produit::get();
           $tousLesPriorites = Priorite::get();

           //pour ne pas generer des erreur lors de l'include of create contact
           $etats = Etat::get();

           $infosProspect = array();//pour chaque prospect , on recupere toute autre infos

           //dernier score marqué
           foreach ($prospects as $prospect) {
              $lastScore = Prospect_score::where('idPros',$prospect->id)->latest()->first();
              $scoreById = Score::where('id',$lastScore->idScore)->first();
              $champActById = champActivite::where('id',$prospect->idChampAct)->first();
              $derniersContacts = Contact::where('idProsp',$prospect->id)->orderByRaw('id DESC')->first();
              $cntct="";
              if (Count($derniersContacts) != 0) { //si un contact exist deja , dans le cas de creatino ce code ne s'execute pas .
                switch ($derniersContacts->type) {

                  case 'A':
                    $cntct = cntct_appel::where('idCntct',$derniersContacts->id)->first();//c sur que y'a q'un seul appel pour un contact
                    break;
                  case 'E':
                    $cntct = cntct_email::where('idCntct',$derniersContacts->id)->first();//c sur que y'a q'un seul mail pour un contact
                    break;
                  case 'T':
                    $cntct = cntct_terain::where('idCntct',$derniersContacts->id)->first();//c sur que y'a q'un seul appel pour un contact
                    break;
                    default:return $derniersContacts->type; break;
                }
                $userCntct = user::where('id',$derniersContacts->idUser)->first();

                //pour l'affichage des prochaine action
                $pa = prochaineAction::where('idCntct',$derniersContacts->id)->latest()->first();
                $infosProspect[] = array( "score" => $scoreById->num,
                                          "scoreLib" => $scoreById->LibScore,
                                          "date" => $lastScore->date,
                                          "remarque" => $lastScore->remarque,
                                          "couleur" => $scoreById->couleur,
                                          "champActiv"=> $champActById->LibChampAct,
                                          "idDernierCntct" => $derniersContacts->id,
                                          "typeDernierCntct" => $derniersContacts->type,
                                          "pa" => $pa,
                                          "remarqueDernierCntct" => $derniersContacts->remarque,
                                          "cntct_info" => json_decode($cntct, true),
                                          "cntct_user" => $userCntct->name." ".$userCntct->prenom
                                        );
              }else {

                $infosProspect[] = array( "score" => $scoreById->num,
                                          "scoreLib" => $scoreById->LibScore,
                                          "date" => $lastScore->date,
                                          "remarque" => $lastScore->remarque,
                                          "couleur" => $scoreById->couleur,
                                          "champActiv"=> $champActById->LibChampAct,
                                          "idDernierCntct" => "",
                                          "typeDernierCntct" => "",
                                          "pa"=>null,
                                          "remarqueDernierCntct" => "",
                                          "cntct_info" => "",
                                          "cntct_user" => ""
                                        );
              }

           }


           return view('prospects')->with('prospects',$prospects)
                                   ->with('tousLeScores',$tousLesScores)
                                   ->with('tousLesChampActiv',$tousLesChampActiv)
                                   ->with('tousLesGroupes',$tousLesGroupes)
                                   ->with('tousLesProduits',$tousLesProduits)
                                   ->with('infosProsp',$infosProspect)
                                   ->with('tousLesUsers',$tousLesUsers)
                                   ->with('produitsPropose',$produitsPropose)
                                   ->with('tousLesPriorites',$tousLesPriorites)
                                   ->with('etats', $etats);
         }

    }

    public function create(Request $rq){
     //dd($rq->email); return 0;
    // try {
       // traitement pour obtenir le num sequentiel d'un nouveau prospect


       //traitement des champs de code
       $NchAct = strval($rq->idChampAct); if(strlen($NchAct) != 2) $NchAct = "0".$NchAct;
       $Nwilaya = strval($rq->wilaya);    if(strlen($Nwilaya) != 2) $Nwilaya = "0".$Nwilaya;
       $year   = substr(date("Y"),-2); //to get only the 2 last number ex: 2016 -> 16
       $sytax = $NchAct.$Nwilaya.$year;

        $oldPrspects = Prospect::select('codeProsp')->get();
        $threeOne = 0 ; // pour savoir si on a prospect avec le meme champ d'activite et la meme wilaya et la meme annee , dans ce cas on doit incrementer , other ways une simple initialisation.
        $list[] = 0 ; //une liste qui contient les num sequenciel qui existe deja
        foreach ($oldPrspects as $prospect) {
          $OPchAct  = substr($prospect->codeProsp,0,2);
          $OPwilaya = substr($prospect->codeProsp,3,2);
          $OPyear   = substr($prospect->codeProsp,-2);
          $OPsytax = $OPchAct.$OPwilaya.$OPyear;
          if ( $sytax == $OPsytax) {
            $threeOne = 1;
            $list[] = intval(substr($prospect->codeProsp,6,4));
          }


        }

        if ($threeOne == 1) {
          $mySeq = max($list)+1;
          if(strlen($mySeq) == 1){$mySeq = "000".$mySeq;}else{if(strlen($mySeq) == 2){$mySeq = "00".$mySeq;}else{if(strlen($mySeq) == 3){$mySeq = "0".$mySeq;}}}
        }else {
          $mySeq = "0001";
        }



        $newCode =$NchAct.".".$Nwilaya.".".$mySeq."/".substr(date("Y"),-2);//le code est pret

        //Societe
        $prospect = new Prospect ;
        $prospect->codeProsp = $newCode;
        $prospect->societe = ucfirst(strtolower($rq->societe));
        $prospect->adresse = $rq->adresse;
        $prospect->codePostal = $rq->codePostal;
        $prospect->wilaya = $rq->wilaya;
        $prospect->nbreEmplyes = $rq->nbreEmplyes;
        //Contact
        $prospect->genre = $rq->genre;
        $prospect->nom = strtoupper($rq->nom);
        $prospect->prenom = ucfirst(strtolower($rq->prenom));
        $prospect->email = $rq->email;
        $prospect->tele1 = $rq->tele1;
        $prospect->tele2 = $rq->tele2;
        $prospect->tele3 = $rq->tele3;
        $prospect->fax = $rq->fax;
        $prospect->skype = $rq->skype;
        $prospect->siteWeb = $rq->siteWeb;

        //Autre
        $prospect->description = $rq->description;
        $prospect->idGrp = $rq->idGrp;
        $prospect->idChampAct = $rq->idChampAct;
        $prospect->bloquer = 0;
        $prospect->client = 0;
        //$prospect->created_at = \Carbon\Carbon::now()->toDateTimeString() ;

        //Done
        $prospect->save();

        //produits supposés pour ce prospect
        foreach ($rq->produits as $produit) {
          $prospect_produit = new Prospect_produit ;
          $prospect_produit->idPrd = $produit;
          $prospect_produit->idProsp = $prospect->id ;
          $prospect_produit->save();
        }
        //preScorring
        $prospect_score = new Prospect_score;
        $prospect_score->idPros = $prospect->id;
        $prospect_score->idScore = $rq->score ;
        $prospect_score->date = date("d/m/Y H:i:s");//la date est en format string
        $prospect_score->remarque = 'Creation.';

        $prospect_score->save();

        return redirect('/prospects')->with('status', '<div class="alert alert-success alert-dismissible show" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><spanaria-hidden="true">&times;</span></button>Ajouté avec succée !</div>');
     // } catch (\Exception $e) {
     //    return redirect('/prospects')->with('status', '<div class="alert alert-danger alert-dismissible show" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><spanaria-hidden="true">&times;</span></button>Erreur ! NB: verifier les informations necessaires pour l\'ajout d\'un nouveau prospect.</div>');
     // }


    }

    //feature added : Juin 15th , 2018
    public function import(Request $rq){

      define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

      $objReader = PHPExcel_IOFactory::createReader('Excel2007');
      $objPHPExcel = $objReader->load($rq->file);

      foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
      //  	echo '--> Feuille : ' , $worksheet->getTitle() . EOL;

        	foreach ($worksheet->getRowIterator() as $row) {
        		//echo '<br/>    -->  Ligne - ' . $row->getRowIndex() . EOL;
            if ($row->getRowIndex() > 1) {
        		$cellIterator = $row->getCellIterator();
        		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set

            $imp = new Request;
            $imp->idChampAct = $rq->idChampAct;
            $imp->wilaya = $worksheet->getTitle() ;

            $i = 0;
            foreach ($cellIterator as $cell) {

                  switch ($i) {
                      case 1:
                        $imp->societe = $cell->getCalculatedValue() ;
                        break;
                      case 4:
                        $imp->adresse = $cell->getCalculatedValue() ;
                        break;
                      case 5:
                        $imp->adresse .= ' Commune : '.$cell->getCalculatedValue() ;
                        break;
                      case 6:
                        $imp->tele1 = $cell->getCalculatedValue() ;
                        break;
                      case 7:
                        $imp->fax = $cell->getCalculatedValue() ;
                        break;
                      case 8:
                        $imp->genre = 'M' ;
                        $imp->nom = $cell->getCalculatedValue() ;
                        break;
                      case 10:
                        $imp->email = $cell->getCalculatedValue() ;
                        break;
                      case 11:
                        $imp->siteWeb = $cell->getCalculatedValue() ;
                        break;
                  }
                  $i++;//les colomns
              $imp->description = "Ajouté de Excel";
              $imp->score = 2;
              $imp->produits = [];
        		}
            $ProspectsImporte[] = $imp;
        	}
        }
        }

        foreach ($ProspectsImporte as $PI) {
          $this->create($PI);
        }
      //  return "<br>END";
      return redirect('prospects');;
    }


 public function update(Request $rq,$prospect ){

      Prospect::where('id',$prospect)
                ->update(["societe"=>$rq->societe,
                          "adresse"=>$rq->adresse,
                          "codePostal"=>$rq->codePostal,
                          "wilaya"=>$rq->wilaya,
                          "nbreEmplyes"=>$rq->nbreEmplyes,
                          //contact
                          "genre"=>$rq->genre,
                          "nom"=>$rq->nom,
                          "prenom"=>$rq->prenom,
                          "email"=>$rq->email,
                          "tele1"=>$rq->tele1,
                          "tele2"=>$rq->tele2,
                          "tele3"=>$rq->tele3,
                          "fax"=>$rq->fax,
                          "skype"=>$rq->skype,
                          "siteWeb"=>$rq->siteWeb,
                          //autre
                          "description"=>$rq->description,
                          "idGrp"=>$rq->idGrp,
                          "idChampAct"=>$rq->idChampAct
                         ]);

        //mise a jour des produits
        $prospect_produits = Prospect_produit::where('idProsp',$prospect)->delete();
        //Nouveau produits affectés pour ce prospect
        foreach ($rq->produits as $produit) {
          $prospect_produit = new Prospect_produit ;
          $prospect_produit->idPrd = $produit;
          $prospect_produit->idProsp = $prospect ;
          $prospect_produit->save();
        }
        //updateScorring -> if it's defrent from the last one
        $lastScore = Prospect_score::where('idPros',$prospect)->latest()->first();
        if ($lastScore->idScore != $rq->score ) {
          $prospect_score = new Prospect_score;
          $prospect_score->idPros = $prospect;
          $prospect_score->idScore = $rq->score ;
          $prospect_score->date = date("d/m/Y H:i:s");//la date est en format string
          $prospect_score->remarque = 'Modification de prospect.';
          $prospect_score->save();

        }

        //si le score donne est le max des score c'est a dire que ce prospect devient un client
        //ainsi je doit mis a jour l'attribut client et ajouter un tuple dans la table client_produits

        $clientScore = Score::whereRaw('num = (select max(`num`) from Scores)')->first();
        if($rq->score == $clientScore->id ){
          //dans ce cas je save le client avec les produits qu'on a derja lui relier . car on a pas choisi des produit lors de update de prospect
          Prospect::where('id',$prospect)
                    ->update(["client"=>1]);
           $prspt_prd = Prospect_produit::where('idProsp',$prospect)->get();//je recupere ces produits
           foreach ($prspt_prd as $pp) {
             $client_produit = new Client_produit;
             $client_produit->idPros = $prospect;
             $client_produit->idPrd = $pp->idPrd;
             $client_produit->save();
           }

        }else{
          Prospect::where('id',$prospect)
                    ->update(["client"=>0]);
        }


      return redirect('detailsProspect/'.$prospect)->with('status', '<div class="alert alert-success alert-dismissible show" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><spanaria-hidden="true">&times;</span></button>Modifier avec succée !</div>');
    }

    public function bloquer($id){
      //yak 9oulna prospect jamais la yetsuprimas
      $prospect = Prospect::where('id',$id)->update(["bloquer"=>1]);
      return redirect('detailsProspect/'.$id)->with('status', '<div class="alert alert-danger alert-dismissible show" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><spanaria-hidden="true">&times;</span></button>Le prospect est bloqué (vous pouvez le debloquer dans la <a href="'.url('prospectsBloques/1').'">liste des prospect bloqués</a>)</div>');
    }

    public function debloquer($id){
      $prospect = Prospect::where('id',$id)->update(["bloquer"=>0]);
      return redirect('detailsProspect/'.$id)->with('status', '<div class="alert alert-success alert-dismissible show" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><spanaria-hidden="true">&times;</span></button>Le prospect est Debloquer.</div>');
    }

    public function getById($id)
    {
      $prospect = Prospect::find($id);

      $scores = Prospect_score::where('idPros',$prospect->id)->latest()->first();
      $scoreById = Score::where('id',$scores->idScore)->first();//pour la couleur
      $monGroupe = Groupe::where('id',$prospect->idGrp)->first();
      // if($monGroupe== null){$monGroupe['id'] = 0;}

      $champActById = champActivite::where('id',$prospect->idChampAct)->first();
      $derniersContacts = Contact::where('idProsp',$prospect->id)->orderByRaw('id DESC')->get();

      $cntct=array();
      if ($derniersContacts) { //si un contact exist deja , dans le cas de creation ce code ne s'execute pas .
        foreach ($derniersContacts as $derCntct) {
          switch ($derCntct->type) {

            case 'A':
              $cntct[] = cntct_appel::where('idCntct',$derCntct->id)->first();//c sur que y'a q'un seul appel pour un contact
              break;
            case 'E':
              $cntct[] = cntct_email::where('idCntct',$derCntct->id)->first();//c sur que y'a q'un seul mail pour un contact
              break;
              default://return $derCntct->type; break;
          }
        }

      }

      $produitsPros = Prospect_produit::where('idProsp',$prospect->id)->select('idPrd')->get();
      $pr = array();
      foreach ($produitsPros as $prs) {
           $pr[] = $prs->idPrd ;
       }

      $produits = DB::table('Produits')->whereIn('id',$pr)->get();//et sa marche

      $us = array();
      foreach ($derniersContacts as $uss) {
           $userCntct = User::where('id',$uss->idUser)->first();
           $us[] = array("name"=>$userCntct->name,"prenom"=>$userCntct->prenom);
       }


       //pour le modal de modification
       $tousLesScores = Score::get();
       $tousLesChampActiv = ChampActivite::get();
       $tousLesGroupes = Groupe::get();
       $tousLesProduits = Produit::get();

       //si c'est un client je doit retourner tous les produits ou les services qui la acheter.
       $listProduitClient = array();
       if($prospect->client == 1){
         $produitClient  = Client_produit::where('idPros',$prospect->id)->get();
         foreach ($produitClient as $pc) {
           $listProduitClient[] = [Produit::find($pc->idPrd)];
         }
       }
       //dd($listProduitClient);

      return view('prospectDetails')->with('prospect',$prospect)
                                    ->with('score',$scoreById)
                                    ->with('chamActiv',$champActById)
                                    ->with('monGroupe',$monGroupe)
                                    ->with('contacts',$derniersContacts)
                                    ->with('userContact',$us)
                                    ->with('cntct_infos',$cntct)
                                    ->with('produits',$produits)
                                    ->with('scores',$tousLesScores)
                                    ->with('lesChampActiv',$tousLesChampActiv)
                                    ->with('lesGroupes',$tousLesGroupes)
                                    ->with('lesProduits',$tousLesProduits)
                                    ->with('clientProduit',$listProduitClient);

    }
    public function GetList(){
      $prospects = Prospect::where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
      $list ='';
      foreach ($prospects as $prospect) {
       $list .= '
                   <tr>
                     <td><input class="check " type="checkbox" value="'.$prospect->id.'"/></td>
                     <td>'.$prospect->societe.'</td>
                     <td>'.$prospect->email.'</td>
                     <td>'.$prospect->tele1.'</td>
                   </tr>
                   ';
      }
      return $list;
    }

    public function filtrer(Request $rq){
      // return $rq->scoreMR;
        $s = $rq->scoreMR;
        $c = $rq->chamActMR;
        $g = $rq->groupMR;
        $w = $rq->wilaya;
        if ($s != "") {
          if ($c != "") {
            if ($g != "") {
              if ($w != "") {// s c g w
                //return 2;
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('idChampAct',$c)
                                       ->where('idGrp',$g)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{ // s c g .
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('idChampAct',$c)
                                       ->where('idGrp',$g)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }else { //g = .
              if ($w != "") {// s c . w
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('idChampAct',$c)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{// s c . .
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('idChampAct',$c)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }
          }else{ //c = .
            if($g != ""){
              if ($w != "") {// s . g w
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('idGrp',$g)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{// s . g .
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('idGrp',$g)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }else{
              if($w != ""){// s . . w
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{// s . . .
                $ps = Prospect_score::where('idScore',$s)->get(['idPros']);
                $prospects = Prospect::whereIn('id',$ps)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }
          }

        }else{ //s= .
          if ($c != "") {
            if ($g != "") {
              if ($w != "") {// . c g w
                $prospects = Prospect::where('idChampAct',$c)
                                       ->where('idGrp',$g)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{ // . c g .
                $prospects = Prospect::where('idChampAct',$c)
                                       ->where('idGrp',$g)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }else { //g = .
              if ($w != "") {// . c . w
                $prospects = Prospect::where('idChampAct',$c)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{// . c . .
                $prospects = Prospect::where('idChampAct',$c)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }
          }else{ //c = .
            if($g != ""){
              if ($w != "") {// . . g w
                $prospects = Prospect::where('idGrp',$g)
                                       ->where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{// . . g .
                $prospects = Prospect::where('idGrp',$g)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }else{
              if($w != ""){// . . . w
                $prospects = Prospect::where('wilaya',$w)
                                       ->where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }else{// . . . .
                $prospects = Prospect::where('bloquer',0)->where('client',0)->orderByRaw('id DESC')->get();
              }
            }
          }
        }




        $tousLesScores = Score::get();//pour un nouveau contact/prospect
        $tousLesChampActiv = ChampActivite::get();
        $tousLesGroupes = Groupe::get();
        $tousLesProduits = Produit::get();
        $tousLesUsers = User::where('type',0)->get();
        //pour form ajout tache et recuperation des produit supposes au moment de creation de se prospect
        $produitsPropose = Prospect_produit::get();
        $tousLesPriorites = Priorite::get();

        //pour ne pas generer des erreur lors de l'include of create contact
        $etats = Etat::get();

        $infosProspect = array();//pour chaque prospect , on recupere toute autre infos

        //dernier score marqué
        foreach ($prospects as $prospect) {
           $lastScore = Prospect_score::where('idPros',$prospect->id)->latest()->first();
           $scoreById = Score::where('id',$lastScore->idScore)->first();
           $champActById = champActivite::where('id',$prospect->idChampAct)->first();
           $derniersContacts = Contact::where('idProsp',$prospect->id)->orderByRaw('id DESC')->first();
           $cntct="";
           if (Count($derniersContacts) != 0) { //si un contact exist deja , dans le cas de creatino ce code ne s'execute pas .
             switch ($derniersContacts->type) {

               case 'A':
                 $cntct = cntct_appel::where('idCntct',$derniersContacts->id)->first();//c sur que y'a q'un seul appel pour un contact
                 break;
               case 'E':
                 $cntct = cntct_email::where('idCntct',$derniersContacts->id)->first();//c sur que y'a q'un seul mail pour un contact
                 break;
               case 'T':
                 $cntct = cntct_terain::where('idCntct',$derniersContacts->id)->first();//c sur que y'a q'un seul appel pour un contact
                 break;
                 default:return $derniersContacts->type; break;
             }
             $userCntct = user::where('id',$derniersContacts->idUser)->first();

             //pour l'affichage des prochaine action
             $pa[] = prochaineAction::where('idCntct',$derniersContacts->id)->latest()->first();
             $infosProspect[] = array( "score" => $scoreById->num,
                                       "scoreLib" => $scoreById->LibScore,
                                       "date" => $lastScore->date,
                                       "remarque" => $lastScore->remarque,
                                       "couleur" => $scoreById->couleur,
                                       "champActiv"=> $champActById->LibChampAct,
                                       "idDernierCntct" => $derniersContacts->id,
                                       "typeDernierCntct" => $derniersContacts->type,
                                       "pa" => $pa,
                                       "remarqueDernierCntct" => $derniersContacts->remarque,
                                       "cntct_info" => json_decode($cntct, true),
                                       "cntct_user" => $userCntct->name." ".$userCntct->prenom
                                     );
           }else {

             $infosProspect[] = array( "score" => $scoreById->num,
                                       "scoreLib" => $scoreById->LibScore,
                                       "date" => $lastScore->date,
                                       "remarque" => $lastScore->remarque,
                                       "couleur" => $scoreById->couleur,
                                       "champActiv"=> $champActById->LibChampAct,
                                       "idDernierCntct" => "",
                                       "typeDernierCntct" => "",
                                       "pa"=>"",
                                       "remarqueDernierCntct" => "",
                                       "cntct_info" => "",
                                       "cntct_user" => ""
                                     );
           }

        }


        return view('prospects')->with('prospects',$prospects)
                                ->with('tousLeScores',$tousLesScores)
                                ->with('tousLesChampActiv',$tousLesChampActiv)
                                ->with('tousLesGroupes',$tousLesGroupes)
                                ->with('tousLesProduits',$tousLesProduits)
                                ->with('infosProsp',$infosProspect)
                                ->with('tousLesUsers',$tousLesUsers)
                                ->with('produitsPropose',$produitsPropose)
                                ->with('tousLesPriorites',$tousLesPriorites)
                                ->with('etats', $etats);

    }
}
