<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Facture
 * 
 * @property int $id
 * @property int $cycles_id
 * @property string $statut
 * @property string $mode_paiement
 * @property string $code_promo
 * @property float $reduction
 * @property int $date_paye
 * @property int $delais_paiement
 * @property float $montant
 * @property int $create_at
 * @property int $update_at
 * 
 * @property \App\Models\Cycle $cycle
 *
 * @package App\Models
 */
class Facture extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'cycles_id' => 'int',
		'reduction' => 'float',
		'date_paye' => 'int',
		'delais_paiement' => 'int',
		'montant' => 'float',
		'periode' => 'int',
		'nb_comptes' => 'int',
		'prix_unitaire' => 'int',
		'create_at' => 'int',
		'update_at' => 'int'
	];

	protected $fillable = [
		'cycles_id',
		'statut',
		'mode_paiement',
		'code_promo',
		'reduction',
		'date_paye',
		'delais_paiement',
		'montant',
		'libelle',
		'nb_comptes',
		'periode',
		'prix_unitaire',
		'create_at',
		'update_at'
	];

	public function cycle()
	{
		return $this->belongsTo(\App\Models\Cycle::class, 'cycles_id');
	}
}
