<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class WTransaction
 * 
 * @property int $id
 * @property float $montant
 * @property float $taux_change
 * @property float $frais
 * @property int $date_transaction
 * @property string $devise_source
 * @property string $devise_destination
 * @property string $type
 * @property string $status
 * @property string $details
 * @property int $wallets_source_id
 * @property int $wallets_destination_id
 * 
 * @property \App\Models\Wallet $wallet
 *
 * @package App\Models
 */
class WTransaction extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'montant' => 'float',
		'taux_change' => 'float',
		'frais' => 'float',
		'date_transaction' => 'int',
		'wallets_source_id' => 'int',
		'wallets_destination_id' => 'int'
	];

	protected $fillable = [
		'montant',
		'taux_change',
		'frais',
		'date_transaction',
		'devise_source',
		'devise_destination',
		'type',
		'status',
		'details',
		'wallets_source_id',
		'wallets_destination_id'
	];

	public function wallet()
	{
		return $this->belongsTo(\App\Models\Wallet::class, 'wallets_source_id');
	}
}
