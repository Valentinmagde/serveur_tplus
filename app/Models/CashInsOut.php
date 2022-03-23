<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CashInsOut
 * 
 * @property int $id
 * @property int $wallets_id
 * @property float $montant
 * @property string $devise
 * @property string $status
 * @property string $methode_paiement
 * @property string $in_out
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property \App\Models\Wallet $wallet
 *
 * @package App\Models
 */
class CashInsOut extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'wallets_id' => 'int',
		'montant' => 'float',
		'created_at' => 'int',
		'updated_at' => 'int'
	];

	protected $fillable = [
		'wallets_id',
		'montant',
		'devise',
		'status',
		'methode_paiement',
		'in_out',
		'created_at',
		'updated_at',
		'payment_id',
		'payer_id',
		'payer_email',
		'momo_transaction_id',
		'receiver'
	];

	public function wallet()
	{
		return $this->belongsTo(\App\Models\Wallet::class, 'wallets_id');
	}
}
