<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Tontine
 * 
 * @property int $id
 * @property int $activites_id
 * @property string $type
 * @property float $montant_part
 * @property float $montant_cagnote
 * @property int $date_debut
 * @property int $duree
 * @property string $minimum_enchere
 * @property float $maximum_enchere
 * @property float $mise_prix_enchere
 * @property int $taux_emprunt_petits_lots
 * @property int $delais_remboursement_petits_lots
 * @property int $date_fin
 * @property string $attribution_cagnote
 * @property int $taux_maximum
 * 
 * @property \App\Models\Activite $activite
 * @property \Illuminate\Database\Eloquent\Collection $lots_tontines
 *
 * @package App\Models
 */
class Tontine extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'activites_id' => 'int',
		'montant_part' => 'float',
		'montant_cagnote' => 'float',
		'date_debut' => 'int',
		'duree' => 'int',
		'maximum_enchere' => 'float',
		'mise_prix_enchere' => 'float',
		'taux_emprunt_petits_lots' => 'int',
		'delais_remboursement_petits_lots' => 'int',
		'date_fin' => 'int',
		'taux_maximum' => 'int'
	];

	protected $fillable = [
		'activites_id',
		'type',
		'montant_part',
		'montant_cagnote',
		'date_debut',
		'duree',
		'minimum_enchere',
		'maximum_enchere',
		'mise_prix_enchere',
		'taux_emprunt_petits_lots',
		'delais_remboursement_petits_lots',
		'date_fin',
		'attribution_cagnote',
		'taux_maximum',
		'part_cotisation'
	];

	public function activite()
	{
		return $this->belongsTo(\App\Models\Activite::class, 'activites_id');
	}

	public function lots_tontines()
	{
		return $this->hasMany(\App\Models\LotsTontine::class, 'tontines_id');
	}
}
