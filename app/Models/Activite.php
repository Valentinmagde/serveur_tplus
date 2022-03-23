<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Activite
 * 
 * @property int $id
 * @property int $associations_id
 * @property string $type
 * @property string $nom
 * @property string $description
 * @property string $etat
 * @property int $date_created
 * @property string $created_by
 * @property int $taux_penalite
 * @property bool $gestion_automatique_avoir
 * @property float $caisse
 * @property string $type_penalite
 * @property int $cycles_id
 * @property string $methode_decaissement
 * 
 * @property \App\Models\Association $association
 * @property \App\Models\Cycle $cycle
 * @property \Illuminate\Database\Eloquent\Collection $activites_generiques
 * @property \Illuminate\Database\Eloquent\Collection $assistances
 * @property \Illuminate\Database\Eloquent\Collection $caisses
 * @property \Illuminate\Database\Eloquent\Collection $comptes
 * @property \Illuminate\Database\Eloquent\Collection $credits
 * @property \Illuminate\Database\Eloquent\Collection $evenements
 * @property \Illuminate\Database\Eloquent\Collection $mains_levees
 * @property \Illuminate\Database\Eloquent\Collection $mutuelles
 * @property \Illuminate\Database\Eloquent\Collection $pams
 * @property \Illuminate\Database\Eloquent\Collection $personnes_affiliees
 * @property \Illuminate\Database\Eloquent\Collection $projets
 * @property \Illuminate\Database\Eloquent\Collection $solidarites
 * @property \Illuminate\Database\Eloquent\Collection $taches
 * @property \Illuminate\Database\Eloquent\Collection $tontines
 * @property \Illuminate\Database\Eloquent\Collection $virements
 *
 * @package App\Models
 */
class Activite extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'date_created' => 'int',
		'taux_penalite' => 'int',
		'gestion_automatique_avoir' => 'bool',
		'caisse' => 'float',
		'cycles_id' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'type',
		'nom',
		'description',
		'etat',
		'date_created',
		'created_by',
		'taux_penalite',
		'gestion_automatique_avoir',
		'caisse',
		'type_penalite',
		'cycles_id',
		'methode_decaissement'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}

	public function cycle()
	{
		return $this->belongsTo(\App\Models\Cycle::class, 'cycles_id');
	}

	public function activites_generiques()
	{
		return $this->hasMany(\App\Models\ActivitesGenerique::class, 'activites_id');
	}

	public function assistances()
	{
		return $this->hasMany(\App\Models\Assistance::class, 'solidarites_activites_id');
	}

	public function caisses()
	{
		return $this->hasMany(\App\Models\Caiss::class, 'activites_id1');
	}

	public function comptes()
	{
		return $this->hasMany(\App\Models\Compte::class, 'activites_id');
	}

	public function credits()
	{
		return $this->hasMany(\App\Models\Credit::class, 'mutuelles_activites_id');
	}

	public function evenements()
	{
		return $this->hasMany(\App\Models\Evenement::class, 'activites_id');
	}

	public function mains_levees()
	{
		return $this->hasMany(\App\Models\MainsLevee::class, 'activites_id');
	}

	public function mutuelles()
	{
		return $this->hasMany(\App\Models\Mutuelle::class, 'activites_id');
	}

	public function pams()
	{
		return $this->hasMany(\App\Models\Pam::class, 'activites_id');
	}

	public function personnes_affiliees()
	{
		return $this->hasMany(\App\Models\PersonnesAffiliee::class, 'PAMs_activites_id');
	}

	public function projets()
	{
		return $this->hasMany(\App\Models\Projet::class, 'activites_id');
	}

	public function solidarites()
	{
		return $this->hasMany(\App\Models\Solidarite::class, 'activites_id');
	}

	public function taches()
	{
		return $this->hasMany(\App\Models\Tach::class, 'projets_activites_id');
	}

	public function tontines()
	{
		return $this->hasMany(\App\Models\Tontine::class, 'activites_id');
	}

	public function virements()
	{
		return $this->hasMany(\App\Models\Virement::class, 'activites_id');
	}
}
