<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Operation
 * 
 * @property int $id
 * @property int $date_realisation
 * @property string $debit_credit
 * @property string $enregistre_par
 * @property float $montant
 * @property string $etat
 * @property string $mode
 * @property string $preuve
 * @property bool $en_ligne
 * @property int $membre_id
 * @property string $num_recu
 * @property string $commentaire
 * 
 * @property \App\Models\Membre $membre
 * @property \Illuminate\Database\Eloquent\Collection $transactions
 *
 * @package App\Models
 */
class Operation extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'date_realisation' => 'int',
		'montant' => 'float',
		'en_ligne' => 'bool',
		'membre_id' => 'int',
		'membres_id_wallet' => 'int'
	];

	protected $fillable = [
		'date_realisation',
		'debit_credit',
		'enregistre_par',
		'montant',
		'etat',
		'mode',
		'preuve',
		'en_ligne',
		'membre_id',
		'num_recu',
		'commentaire',
		'membres_id_wallet'
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class);
	}

	public function transactions()
	{
		return $this->hasMany(\App\Models\Transaction::class, 'operations_id');
	}
}
