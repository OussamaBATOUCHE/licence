@extends('admin')

@section('content')
<section class="content">
    <h3 class="box-title">Gestion des prospects</h3>
  <div style="text-align:right;float: right">
    @if (Auth::user()->type == 1)
      <a class="btn btn-info" onclick="chargeNouvelleTachePlusieurProspect({{$tousLesProduits}})" ><i class="fa fa-calendar"></i>&nbsp; Taches en groupe</a>
      <a class="btn btn-warning" data-toggle="modal" data-target="#importprospectsModal" ><i class="fa fa-upload"></i>&nbsp; Importer des prospects(.xlsx)</a>
      <a class="btn btn-success" data-toggle="modal" data-target="#addprospectModal" ><i class="fa fa-user-plus"></i>&nbsp; Ajouter un prospect</a>
    @endif

  </div>
  <div style="float:left">
    <form class="" action="ProspectMR" method="post">
      @csrf
      <div class="form-group col" style="float:left;margin:5px 5px;">
        <select class="form-control" id="scoreMR" name="scoreMR">
          <option disabled selected>Score</option>
          @foreach ($tousLeScores as $score)
            <option value="{{$score->id}}" >{{$score->LibScore}}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col" style="float:left;margin:5px 5px;">
        <select class="form-control" id="champActivite" name="chamActMR">
          <option disabled selected>Champ d'Activite</option>
          @foreach ($tousLesChampActiv as $champActiv)
            <option value="{{$champActiv->id}}" >{{$champActiv->LibChampAct}}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col" style="float:left;margin:5px 5px;">
        <select class="form-control" id="group" name="groupMR">
          <option disabled selected>Groupe</option>
          @foreach ($tousLesGroupes as $groupe)
            <option value="{{$groupe->id}}" >{{$groupe->LibGrp}}</option>
          @endforeach
        </select>
      </div>

      <div id="wilayaF" class="form-group col" style="float:left;margin:5px 5px;">

      </div>

      <div class="form-group col" style="float:left;margin:5px 5px;">
        <button type="submit" id="btn-filtrer" class="btn btn-warning" ><i class="fa fa-search"></i>&nbsp; Filtrer</button>
      </div>
    </form>

  </div>
  @if (session('status')){!! session('status') !!}@endif
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <a href="{{url('prospectQue/0/1')}}" class="btn "><i class="fa fa-fire" style="color:blue;font-size:20px;"></i>&nbsp; Prospects</a>
          <a href="{{url('prospectQue/0/2')}}" class="btn "><i class="fa fa-users" style="color:blue;font-size:20px;"></i>&nbsp; Clients</a>
          <a href="{{url('prospectQue/0/0')}}" class="btn "><i class="fa fa-list" style="color:blue;font-size:20px;"></i>&nbsp; Tous</a>

        </div><!-- /.box-header -->
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th><input id="checkAll" type="checkbox"  style="color:red"/></th>
                <th>Code Prospect</th>
                <th>Societé</th>
                <th>Score</th>
                <th>Contact</th>
                <th>Categorie</th>
                <th>Dernier Echange</th>
                <th>Prospection</th>

              </tr>
            </thead>
            <tbody>

              @php
                $i = 0 ;
              @endphp
              @foreach($prospects as $prospect)
              <tr>
                <th><input class="check" type="checkbox" value="{{$prospect->id}}"/></th>
                <th>{{$prospect->codeProsp}}</th>
                <th class="sub-info"><a href="{{url('detailsProspect/'.$prospect->id)}}" data-toggle="popover" data-trigger="hover"  title="{{$prospect->societe}}" data-content="{{substr($prospect->description,0,50)}}">{{$prospect->societe}}</a> <span><br/> {{$prospect->adresse}} <br/> {{$prospect->codePostal}} <span class="r-prospect-wilaya">{{$prospect->wilaya}}</span></span></th>
                <th style="background-color:{{$infosProsp[$i]["couleur"]}};" > <span class="text-white">{{$infosProsp[$i]["score"]}}</span> <i class="fa fa-info score-info" data-toggle="popover" data-trigger="hover"  title="{{$infosProsp[$i]["date"]}}" data-content="{{substr($infosProsp[$i]["remarque"],0,60)}}"></i></th>
                <th class="sub-info">{{$prospect->genre}}.{{$prospect->nom}} {{$prospect->prenom}} <span><br/> {{$prospect->email}} <br/> {{$prospect->tele1}}</span></th>
                <th>{{$infosProsp[$i]["champActiv"]}}</th>
                <th>

                     @if ($infosProsp[$i]["cntct_user"] != "")
                       <a href="" title="Details & Mettre à joure"
                          onclick="chargeUpdateContact( {{$infosProsp[$i]["idDernierCntct"]}},
                                                        '{{$infosProsp[$i]["typeDernierCntct"]}}',
                                                        '{{str_replace("'","\'",$infosProsp[$i]["remarqueDernierCntct"])}}',
                                                        '{{$infosProsp[$i]["date"]}}',
                                                        '{{str_replace("'","\'",$prospect->societe)}}',
                                                        {{$prospect->id}},
                                                        {{ json_encode($infosProsp[$i]["cntct_info"]) }},
                                                        '{{$infosProsp[$i]["cntct_user"]}}',
                                                        '{{$infosProsp[$i]["scoreLib"]}}',
                                                        {{ json_encode($infosProsp[$i]["pa"]) }}
                                                      )"
                          data-toggle="modal" data-target="#updateContact">
                        {{$infosProsp[$i]["date"]}}
                       </a>
                       <br/>
                       P.A : @if($infosProsp[$i]["pa"] != "")<a>{{$infosProsp[$i]["pa"]["action"]." le ". $infosProsp[$i]["pa"]["date"]}}</a> @else / @endif
                     @else
                       Aucun Echange
                     @endif

                </th>
                 <th>
                  <a class="btn btn-info col" title="Nouvelle prospection" onclick="chargeNouveauContact('{{str_replace("'","\'",$prospect->societe)}}',{{$prospect->id}},0,{{str_replace('"',"'",$produitsPropose)}}, {{str_replace('"',"'",$tousLesProduits)}})" data-toggle="modal" data-target="#nouveauContact"><i class="fa fa-plus-square"></i></a>
                  @if (Auth::user()->type == 1)
                  <a class="btn btn-info col" title="Programmer une Tache" onclick="chargeNouvelleTache('{{str_replace("'","\'",$prospect->societe)}}',{{$prospect->id}} , {{$tousLesProduits}} , {{$produitsPropose}})"  data-toggle="modal" data-target="#nouvelleTache"><i class="fa fa-calendar"></i></a>
                  @endif
                 </th>

              @php $i++; @endphp
              @endforeach
            </tbody>
            </tfoot>
          </table>
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </div><!-- /.col -->
  </div><!-- /.row -->
</section><!-- /.content -->

<!--Nouveau Prospect-->
@include('layouts.modals.createProspect')

{{-- Importer des prospects --}}
@include('layouts.modals.importProspect')

<!--Nouveau Contact-->
@include('layouts.modals.createContact')

<!--Lire et Modifier Contact-->
@include('layouts.modals.updateContact')

<!--Nouvelle taches-->
@include('layouts.modals.createTache')

<script>


    $(document).ready(function(){
        var allSelected = false;
        $('[data-toggle="popover"]').popover({ trigger: "hover" , html: true }); // and now my popover accept hmtl text ^^

        $( "#btn-filtrer" ).click(function() {
          $('input[aria-controls="example1"]').val("hotel");
          //alert( "Handler for .click() called." );
        });

        $("#checkAll").click(function() {
          if(allSelected == true ){
            //alert('helo');
            $('.check').each(function(){
              //alert(this.value);
                  this.checked = false;
            });
            allSelected = false;
          }else{
            //alert('kldsjflf');
            $('.check').each(function(){
                this.checked = true;
            });
            allSelected = true;
          }

        });



    });



            nomWilaya = function(num){
              switch (num) {
                case "1": return "Adrar"; break;
                case "2": return "Chlef"; break;
                case "3": return "Laghouat"; break;
                case "4": return "Oum El Bouaghi"; break;
                case "5": return "Batna"; break;
                case "6": return "Béjaïa"; break;
                case "7": return "Biskra"; break;
                case "8": return "Béchar"; break;
                case "9": return "Blida"; break;
                case "10": return "Bouira"; break;
                case "11": return "Tamanrasset"; break;
                case "12": return "Tébessa"; break;
                case "13": return "Tlemcen"; break;
                case "14": return "Tiaret"; break;
                case "15": return "Tizi Ouzou"; break;
                case "16": return "Alger"; break;
                case "17": return "Djelfa"; break;
                case "18": return "Jijel"; break;
                case "19": return "Sétif"; break;
                case "20": return "Saïda"; break;
                case "21": return "Skikda"; break;
                case "22": return "Sidi Bel Abbès"; break;
                case "23": return "Annaba"; break;
                case "24": return "Guelma"; break;
                case "25": return "Constantine"; break;
                case "26": return "Médéa"; break;
                case "27": return "Mostaganem"; break;
                case "28": return "M'Sila"; break;
                case "29": return "Mascara"; break;
                case "30": return "Ouargla"; break;
                case "31": return "Oran"; break;
                case "32": return "El Bayadh"; break;
                case "33": return "Illizi"; break;
                case "34": return "Bordj Bou Arreridj"; break;
                case "35": return "Boumerdès"; break;
                case "36": return "El Tarf"; break;
                case "37": return "Tindouf"; break;
                case "38": return "Tissemsilt"; break;
                case "39": return "El Oued"; break;
                case "40": return "Khenchela"; break;
                case "41": return "Souk Ahras"; break;
                case "42": return "Tipaza"; break;
                case "43": return "Mila"; break;
                case "44": return "Aïn Defla"; break;
                case "45": return "Naâma"; break;
                case "46": return "Aïn Témouchent"; break;
                case "47": return "Ghardaïa"; break;
                case "48": return "Relizane"; break;

              }
            };

            $('.r-prospect-wilaya').html(nomWilaya($('.r-prospect-wilaya').html()));




</script>



@endsection
