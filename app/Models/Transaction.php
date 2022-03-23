<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Transaction
 * 
 * @property int $id
 * @property int $comptes_id
 * @property int $operations_id
 * @property float $montant
 * @property string $libelle
 * @property string $etat
 * @property int $date_created
 * @property string $created_by
 * @property string $debit_credit
 * @property float $montant_attendu
 * 
 * @property \App\Models\Compte $compte
 * @property \App\Models\Operation $operation
 *
 * @package App\Models
 */
class Transaction extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'comptes_id' => 'int',
		'operations_id' => 'int',
		'montant' => 'float',
		'date_created' => 'int',
		'montant_attendu' => 'float'
	];

	protected $fillable = [
		'comptes_id',
		'operations_id',
		'montant',
		'libelle',
		'etat',
		'date_created',
		'created_by',
		'debit_credit',
		'montant_attendu'
	];

	public function compte()
	{
		return $this->belongsTo(\App\Models\Compte::class, 'comptes_id');
	}

	public function operation()
	{
		return $this->belongsTo(\App\Models\Operation::class, 'operations_id');
	}
}
