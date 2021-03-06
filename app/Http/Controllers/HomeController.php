<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Prospect_score;
use App\Score;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Prospect;
use App\Tache;
use App\cntct_email;
use App\cntct_appel;
use App\Client_produit;

use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $TousLesScores = Score::get();

        // idUser | nbTaches | fini
        $idUserTaches = DB::SELECT('SELECT t1.idUser ,
                                           COUNT(t1.id) AS nbTaches ,
                                           (SELECT count(t2.id)
                                            FROM taches t2
                                            WHERE t2.termine = 1
                                            AND t2.idUser = t1.idUser
                                            GROUP BY t2.idUser) AS fini
                                    FROM taches t1
                                    GROUP BY t1.idUser
                                    ');
        // idUser | emails | appels
        $idUserContacts = DB::SELECT('SELECT  c.idUser ,
                                             COUNT((SELECT COUNT(e.id)
                                              FROM cntct_emails e
                                              WHERE e.idCntct = c.id
                                              GROUP BY e.idCntct) )AS emails ,
                                              COUNT(
                                             (SELECT COUNT(a.id)
                                              FROM cntct__appels a
                                              WHERE a.idCntct = c.id
                                              GROUP BY a.idCntct) )AS appels
                                      FROM contacts c
                                      GROUP BY c.idUser
                                      ');

        //userObject | nbTaches | fini | nbContacts | emails | appels
      $commStat = array();
      $users = User::get(['id','name','prenom']);
      foreach ($users as $user) {
        $nbTache = 0 ; $fini = 0 ; $nbContacts = 0 ; $emails = 0 ; $appels = 0 ;
        foreach ($idUserTaches as $idUserTach) {
          if ($idUserTach->idUser == $user->id) {
             $nbTache = $idUserTach->nbTaches ;
             $fini = $idUserTach->fini;
          }
        }
        foreach ($idUserContacts as $idUserCntct) {
          if ($idUserCntct->idUser == $user->id ) {
            $nbContacts = $idUserCntct->emails+$idUserCntct->appels;
            $emails = $idUserCntct->emails;
            $appels = $idUserCntct->appels;
          }
        }
        $commStat[] = [$user , $nbTache , $fini , $nbContacts , $emails , $appels ];
      }





       return view('home')->with('tousLesScores',$TousLesScores)
                          ->with('commercialsStat', $commStat)
                          ->with('scorePourcentage', $this->pourcentageScore());
    }

    public function home(){
      if (Auth::user()->id) {
        return redirect('home');
      }
      return view("layouts.app");
    }

    public function pourcentageScore(){
      //pour le pourcentage des scores
      $pros = Prospect::get();
      $sco = array();
      // idScore | idPros
      foreach ($pros as $pro) {
         $sco[] = Prospect_score::WHERE('idPros',$pro->id)->latest()->first(['idScore','idPros']);
      }
      //dd($sco);
      $t = array();
      $scoreP = Score::get(['id','LibScore','couleur']);
      foreach ($scoreP as $s) {
      //  dd($s['idScore']);

         $i = 0;//pour le courant
         $j = $s->id;//pour le test

        foreach ($sco as $sp) {
              if ($j == $sp['idScore'] ) {
                $i ++;
              }

        }

        $t[] = [$s,($i*100)/$pros->count()];

      }
      return $t;
    }
    public function pourcentageScoreChart(){
      //pour le pourcentage des scores
      $pros = Prospect::get();
      $sco = array();
      // idScore | idPros
      foreach ($pros as $pro) {
         $sco[] = Prospect_score::WHERE('idPros',$pro->id)->latest()->first(['idScore','idPros']);
      }
      //dd($sco);
      $t = array();
      $scoreP = Score::get(['id','LibScore','couleur']);
      foreach ($scoreP as $s) {
      //  dd($s['idScore']);

         $i = 0;//pour le courant
         $j = $s->id;//pour le test

        foreach ($sco as $sp) {
              if ($j == $sp['idScore'] ) {
                $i ++;
              }

        }

        $t[] = [$s,$i];

      }
      return $t;
    }


    public function paramList()
    {
        if($this->UserType()==1){
            return view('listParam');
        }else {
         return  $this->messageDroitAccee();
        }
    }

    public function profil(){
        return view('profil');
    }

    public function scoresStat(){
          $data = array();
          $stat = $this->pourcentageScoreChart();
          foreach ($stat as $s) {

            $data[] = [
                        "value"=>$s[1],
                        "color"=>$s[0]->couleur,
                        "highlight"=>$s[0]->couleur,
                        "label"=>$s[0]->LibScore
                      ];
          }
          $returned = "<script>
                           var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
                           var pieChart       = new Chart(pieChartCanvas);
                           var PieData = ".json_encode($data).";
                           pieChart.Doughnut(PieData);
                      </script>";
          return $returned;
    }

    public function scoresStat_prosp($id){

      $scorsHistory = Prospect_score::where('idPros',$id)->get(["idScore","created_at"]);
      //dd($scorsHistory->created_at);
      $labels = array();
      $data = array();

      $stat = $this->pourcentageScoreChart();
      foreach ($scorsHistory as $sh) {
        $labels[] = substr($sh->created_at,0,10);
        $scoreInfo = Score::find($sh->idScore);
        $data[] = $scoreInfo->num;
      }
      $returned = "<script>
                   var areaChartData = {
                    labels  : ".json_encode($labels).",
                    datasets: [
                      {
                        label               : 'Score',
                        fillColor           : 'rgba(60,141,188,0.9)',
                        strokeColor         : 'rgba(60,141,188,0.8)',
                        pointColor          : '#3b8bba',
                        pointStrokeColor    : 'rgba(60,141,188,1)',
                        pointHighlightFill  : '#fff',
                        pointHighlightStroke: 'rgba(60,141,188,1)',
                        data                : ".json_encode($data)."
                      }
                    ]
                  }
                    var areaChartOptions = {
                    //Boolean - If we should show the scale at all
                    showScale               : true,
                    //Boolean - Whether grid lines are shown across the chart
                    scaleShowGridLines      : false,
                    //String - Colour of the grid lines
                    scaleGridLineColor      : 'rgba(0,0,0,.05)',
                    //Number - Width of the grid lines
                    scaleGridLineWidth      : 1,
                    //Boolean - Whether to show horizontal lines (except X axis)
                    scaleShowHorizontalLines: true,
                    //Boolean - Whether to show vertical lines (except Y axis)
                    scaleShowVerticalLines  : true,
                    //Boolean - Whether the line is curved between points
                    bezierCurve             : true,
                    //Number - Tension of the bezier curve between points
                    bezierCurveTension      : 0.3,
                    //Boolean - Whether to show a dot for each point
                    pointDot                : false,
                    //Number - Radius of each point dot in pixels
                    pointDotRadius          : 4,
                    //Number - Pixel width of point dot stroke
                    pointDotStrokeWidth     : 1,
                    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                    pointHitDetectionRadius : 20,
                    //Boolean - Whether to show a stroke for datasets
                    datasetStroke           : true,
                    //Number - Pixel width of dataset stroke
                    datasetStrokeWidth      : 2,
                    //Boolean - Whether to fill the dataset with a color
                    datasetFill             : true,
                    //String - A legend template
                    legendTemplate          : '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
                    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                    maintainAspectRatio     : true,
                    //Boolean - whether to make the chart responsive to window resizing
                    responsive              : true
                  }
                   var lineChartCanvas          = $('#lineChart').get(0).getContext('2d')
                   var lineChart                = new Chart(lineChartCanvas)
                   var lineChartOptions         = areaChartOptions
                   lineChartOptions.datasetFill = false
                   lineChart.Line(areaChartData, lineChartOptions)
                   </script>";
      return $returned;
    }

    public function getAllFromProspectTable(){
      $prospects = Prospect::get();
      return $prospects->count();
    }

    //prospects STAT
    public function nbPrspct(){

      $prospects = Prospect::WHERE('client',0)->get();
      return $prospects->count();

    }
    public function nbPrspctM(){
      $prospectsMois = DB::SELECT("SELECT * FROM prospects WHERE client = 0 AND  year(created_at) = ".date('Y')." AND month(created_at) = ".date('m'));
      return count($prospectsMois);
    }
    public function nbPrspctA(){
      $prospectsAnnee = DB::SELECT("SELECT * FROM prospects WHERE client = 0 AND  year(created_at) = ".date('Y'));
      return count($prospectsAnnee);
    }
    public function nbPrspctT(){
      $prospects = Prospect::WHERE('client',0)->get();
      return $prospects->count();
    }
    public function nbPrspctB(){
      $prospectsBloquer = DB::SELECT("SELECT * FROM prospects WHERE client = 0 AND  bloquer = 1");
      return count($prospectsBloquer);
    }
    //end prospects STAT

    //taches STAT
    public function tachEnCour(){
      $taches = Tache::WHERE('termine',0)->get();
      return $taches->count();
    }
    public function tachEnCourT_M(){
      $tachesM = DB::SELECT("SELECT * FROM taches WHERE termine = 1 AND  year(created_at) = ".date('Y')." AND month(created_at) = ".date('m'));
      return count($tachesM);
    }
    public function tachEnCourT_A(){
      $tachesA = DB::SELECT("SELECT * FROM taches WHERE termine = 1 AND  year(created_at) = ".date('Y'));
      return count($tachesA);
    }
    public function tachEnCourT_T(){
      $tachesT = DB::SELECT("SELECT * FROM taches WHERE termine = 1 ");
      return count($tachesT);
    }

    //end taches STAT

    //contacts STAT
    public function nbCntct(){
      $emails = cntct_email::get();
      $appels = cntct_appel::get();
      return $emails->count()+$appels->count();
    }
    public function nbCntctE_M(){
      $emailsM = DB::SELECT("SELECT * FROM contacts WHERE type = 'E' AND  year(date) = ".date('Y')." AND month(date) = ".date('m'));
      return count($emailsM);
    }
    public function nbCntctA_M(){
      $appelsM = DB::SELECT("SELECT * FROM contacts WHERE type = 'A' AND  year(date) = ".date('Y')." AND month(date) = ".date('m'));
      return count($appelsM);
    }
    public function nbCntctE_A(){
      $emailsA = DB::SELECT("SELECT * FROM contacts WHERE type = 'E' AND  year(date) = ".date('Y'));
      return count($emailsA);
    }
    public function nbCntctA_A(){
      $appelsA = DB::SELECT("SELECT * FROM contacts WHERE type = 'A' AND  year(date) = ".date('Y'));
      return count($appelsA);
    }
    public function nbCntctE_T(){
      $emailsT = DB::SELECT("SELECT * FROM contacts WHERE type = 'E' ");
      return count($emailsT);
    }
    public function nbCntctA_T(){
      $appelsT = DB::SELECT("SELECT * FROM contacts WHERE type = 'A' ");
      return count($appelsT);
    }
    //end contacts STAT

    //client STAT
    public function nbClient(){
      $client = Client_produit::get();
      return $client->count();
    }
    public function nbClient_M(){
      $clientsM = DB::SELECT("SELECT * FROM Client_produits WHERE  year(created_at) = ".date('Y')." AND month(created_at) = ".date('m'));
      return count($clientsM);
    }
    public function nbClient_A(){
      $clientsA = DB::SELECT("SELECT * FROM Client_produits WHERE  year(created_at) = ".date('Y'));
      return count($clientsA);
    }
    public function nbClient_T(){
      $clientsT = DB::SELECT("SELECT * FROM Client_produits ");
      return count($clientsT);
    }
    //end clients STAT
}
