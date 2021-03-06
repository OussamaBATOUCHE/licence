<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/*    List route oussama  */

/* --- GET --- */

Route::get('/','HomeController@home');
Route::get('/home', 'HomeController@index')->name('home');

Route::get('/paramList','HomeController@paramList');
Route::get('/scores','ScoreController@get');

Auth::routes();

Route::get('/deleteContact/{id}','ContactController@delete');
Route::get('/detailsProspect/{id}','ProspectController@getById');


Route::get('/groupes','GroupeController@get');

Route::get('/groupe_delete/{champ}','GroupeController@destroy');
Route::get('/bloquerProspect/{id}','ProspectController@bloquer');
Route::get('/debloquerProspect/{id}','ProspectController@debloquer');

Route::get('/prospectsBloques/{bloque}','ProspectController@get');
Route::get('prospectQue/{bloque}/{type}','ProspectController@get');
Route::get('prospectsGetList','ProspectController@GetList');

Route::get('/priorites','PrioriteController@get');
Route::get('/priorite_delete/{priorite}','PrioriteController@destroy');

Route::get('/etats','EtatController@get');
Route::get('/etat_delete/{etat}','EtatController@destroy');


Route::get('/taches','TacheController@get');
Route::get('/tache/{id}','TacheController@getById');
Route::get('/destroyTache/{id}','TacheController@destroy');
Route::get('tachesTermine/{termine}','TacheController@get');
Route::get('/mesNotifications','TacheController@Notifications');


Route::get('profil/{id}','Controller@getUserById');

Route::get('contacts','ContactController@get');
Route::get('cntctQue/{type}','ContactController@get');

Route::get('/users','Controller@getAllUsers');
Route::get('/bloquerUser/{id}','Controller@bloquerUser');
Route::get('/debloquerUser/{id}','Controller@debloquerUser');
Route::get('/deleteUser/{id}','Controller@deleteUser');

Route::get('messagesAll','MessageController@getAll');
Route::get('message_delete/{id}','MessageController@deleteMsg');



// STATISTIQUES

Route::get('scoresStat',"HomeController@scoresStat");
Route::get('detailsProspect/scoresStat_prosp/{id}',"HomeController@scoresStat_prosp");
Route::get('getAllFromProspectTable','HomeController@getAllFromProspectTable');
//prospects
Route::get('nbPrspct',"HomeController@nbPrspct");
Route::get('nbPrspctM',"HomeController@nbPrspctM");
Route::get('nbPrspctA',"HomeController@nbPrspctA");
Route::get('nbPrspctT',"HomeController@nbPrspctT");
Route::get('nbPrspctB',"HomeController@nbPrspctB");

//taches
Route::get('tachEnCour',"HomeController@tachEnCour");
Route::get('tachEnCourT_M',"HomeController@tachEnCourT_M");
Route::get('tachEnCourT_A',"HomeController@tachEnCourT_A");
Route::get('tachEnCourT_T',"HomeController@tachEnCourT_T");

//contacts
Route::get('nbCntct',"HomeController@nbCntct");
Route::get('nbCntctE_M',"HomeController@nbCntctE_M");
Route::get('nbCntctA_M',"HomeController@nbCntctA_M");
Route::get('nbCntctE_A',"HomeController@nbCntctE_A");
Route::get('nbCntctA_A',"HomeController@nbCntctA_A");
Route::get('nbCntctE_T',"HomeController@nbCntctE_T");
Route::get('nbCntctA_T',"HomeController@nbCntctA_T");

//clients
Route::get('nbClient',"HomeController@nbClient");
Route::get('nbClient_M',"HomeController@nbClient_M");
Route::get('nbClient_A',"HomeController@nbClient_A");
Route::get('nbClient_T',"HomeController@nbClient_T");

//by commercials

Route::get('profil/mes_taches_finis/{id}',"Controller@mes_taches_finis");
Route::get('profil/mes_emails/{id}',"Controller@mes_emails");
Route::get('profil/mes_appels/{id}',"Controller@mes_appels");



/* --- POST --- */

Route::post('/createScore','ScoreController@create');

Route::post('/createPriorite','PrioriteController@create');
Route::patch('/updatePriorite/{priorite}', 'PrioriteController@update');

Route::post('/createEtat','EtatController@create');
Route::patch('/updateEtat/{etat}', 'EtatController@update');

Route::post('/updateProfile','Controller@updateProfile');

Route::post('/createContact/{tache}/{type}/{prospect}','ContactController@create');

Route::post('/createGroupe','GroupeController@create');
Route::patch('/updateGroupe/{champ}', 'GroupeController@update');

Route::post('/updateProspect/{prospect}', 'ProspectController@update');

Route::post('createTache/{id}','TacheController@create');

Route::post('deleteMsgs','MessageController@deleteMsgs');

Route::post('GrpEmail','ContactController@GrpEmail');
Route::post('directEmail','Controller@directEmail');

// moteur de filtre sur les prospects
Route::post('ProspectMR','ProspectController@filtrer');
/*  END  List route oussama  */


/*    List route kamel  */

				/*    ----- GET ------ */


Route::get('/score_delete/{score}','ScoreController@destroy');

Route::get('/champActivite','ChampActiviteController@get');

Route::get('/champActivite_delete/{champ}','ChampActiviteController@destroy');

Route::get('/produits','ProduitController@get');

Route::get('/produit_delete/{produit}','ProduitController@destroy');

Route::get('/prospects','ProspectController@get');

/*
Route::get('/messages',function(){
	return App\Message::all();
});
*/

				/*    ----- POST ------ */


/*
Route::post('/messages',function(){
	$user = Auth::user();

	$user->messages()->create([
		'message' => request()->get('message')
	]);

	return ['status' => 'OK'];
})->middleware('auth');
*/


Route::post('/createChamp','ChampActiviteController@create');

Route::post('/createProduit','ProduitController@create');

Route::post('/createProspect','ProspectController@create');
Route::post('/importProspects','ProspectController@import');



				/*    ----- PATCH ------ */

Route::patch('/updateScore/{score}', 'ScoreController@update');

Route::patch('/updateChamp/{champ}', 'ChampActiviteController@update');

Route::patch('/updateProduit/{produit}', 'ProduitController@update');



/* For Chat */

Route::get('messages','MessageController@get');
Route::get('messages/ajax','MessageController@ajax');
Route::post('messages/add/{id}','MessageController@store');
Route::get('messages/{id}','MessageController@message');
Route::get('/mesMessages','MessageController@notification');



/*  END  List route kamel  */
