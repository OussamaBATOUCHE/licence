@extends('admin')

@section('content')
<section class="content">
  <div style="text-align:right;float: right">
  <a class="btn btn-info" onclick="" ><i class="fa fa-plus-square"></i>&nbsp; Taches en groupe</a>


  </div>
  <div style="float:left">

  <h3>Taches en cours</h3>
  </div>
  @if (session('status')){!! session('status') !!}@endif
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header " style="clear:both">
          <a class="btn btn-success" data-toggle="modal" data-target="#chargeNouvelleTache" ><i class="fa fa-plus-square"></i>&nbsp; Ajouter un tache</a>
        </div><!-- /.box-header -->
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Titre</th>
                <th>Priorite</th>
                <th>Prospect</th>
                <th>Deadline</th>
                <th>Etat</th>
                <th>Commrcial</th>
                <th> -- </th>
              </tr>
            </thead>
            <tbody>
              @php
                $i = 0 ;
              @endphp
              @foreach($taches as $tache)
              <tr>
                <th><span data-toggle="popover" data-trigger="hover"  title="{{$tache->created_at}}" data-content="{{$tache->remarque}}">{{$tache->titre}}</span></th>
                <th style="background-color:{{$lesPrioritesTaches[$i]['couleur']}}" >{{$lesPrioritesTaches[$i]['num']}}-{{$lesPrioritesTaches[$i]['libPrio']}}</th>
                <th> <ul><?php
                  $b=false ;
                  $j=0;
                  $id = $tache->id;
                  // $arr2 = array_map(function($id) {
                  //                         return $lesProspects[$id];
                  //                     }, $lesProspects);
                  $arr2= array_column($lesProspects, $id);
                  while ($j < sizeof($arr2) ) {
                      echo "<li><a href=\"/#\">".$arr2[$j]->societe."</a></li>";

                    $j++;
                  }
                ?>
                    </ul>
                </th>
                <th>{{$tache->dateDebut}} jusqu'a {{$tache->dateFin}}</th>
                {{-- <th>{{$dernierEtats[$i][]}}</th> pff --}}
                <th>
                </th>
                <th>
                  <a class="btn btn-info col" title="Nouveau contact" onclick="" data-toggle="modal" data-target="#nouveauContact"><i class="fa fa-plus-square"></i></a>
                  <a class="btn btn-info col" title="Programmer une Tache" onclick=""  data-toggle="modal" data-target="#nouvelleTache"><i class="fa fa-calendar"></i></a>
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

<!--Nouveau Tache-->
{{-- @include('layouts.modals.createTache')

<!--Nouveau Contact-->
@include('layouts.modals.createContact') --}}

<script>


    $(document).ready(function(){
        var allSelected = false;
        $('[data-toggle="popover"]').popover({ trigger: "hover" , html: true }); // and now my popover accept hmtl text ^^

    });


</script>



@endsection
