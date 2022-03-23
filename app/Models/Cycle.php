<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Cycle
 * 
 * @property int $id
 * @property int $associations_id
 * @property int $duree_cycle
 * @property string $mesure_cycle
 * @property string $type_assemblee
 * @property int $date_premiere_assemblee
 * @property string $heure_assemblee
 * @property float $participation_reception
 * @property float $sanction_retard
 * @property float $sanction_abscence
 * @property float $frais_inscription
 * @property int $date_lim_frais_insc
 * @property string $frequence_seance
 * @property int $jour_semaine
 * @property int $jour_mois
 * @property int $ordre_semaine
 * @property \Carbon\Carbon $create_at
 * @property int $create_by
 * @property \Carbon\Carbon $update_at
 * @property int $update_by
 * @property string $etat
 * @property string $lieu_fixe_ag
 * 
 * @property \App\Models\Association $association
 * @property \Illuminate\Database\Eloquent\Collection $activites
 * @property \Illuminate\Database\Eloquent\Collection $ags
 * @property \Illuminate\Database\Eloquent\Collection $factures
 *
 * @package App\Models
 */
class Cycle extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'duree_cycle' => 'int',
		'date_premiere_assemblee' => 'int',
		'participation_reception' => 'float',
		'sanction_retard' => 'float',
		'sanction_abscence' => 'float',
		'frais_inscription' => 'float',
		'date_lim_frais_insc' => 'int',
		'jour_semaine' => 'int',
		'jour_mois' => 'int',
		'ordre_semaine' => 'int',
		'create_by' => 'int',
		'update_by' => 'int'
	];

	protected $dates = [
		'create_at',
		'update_at'
	];

	protected $fillable = [
		'associations_id',
		'duree_cycle',
		'mesure_cycle',
		'type_assemblee',
		'date_premiere_assemblee',
		'heure_assemblee',
		'participation_reception',
		'sanction_retard',
		'sanction_abscence',
		'frais_inscription',
		'date_lim_frais_insc',
		'frequence_seance',
		'jour_semaine',
		'jour_mois',
		'ordre_semaine',
		'create_at',
		'create_by',
		'update_at',
		'update_by',
		'etat',
		'lieu_fixe_ag'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}

	public function activites()
	{
		return $this->hasMany(\App\Models\Activite::class, 'cycles_id');
	}

	public function ags()
	{
		return $this->hasMany(\App\Models\Ag::class, 'cycles_id');
	}

	public function factures()
	{
		return $this->hasMany(\App\Models\Facture::class, 'cycles_id');
	}
}
