<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TWallet
 * 
 * @property int $id
 * @property int $wallets_id
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property \App\Models\Wallet $wallet
 *
 * @package App\Models
 */
class TWallet extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'wallets_id' => 'int',
		'created_at' => 'int',
		'updated_at' => 'int'
	];

	protected $fillable = [
		'wallets_id'
	];

	public function wallet()
	{
		return $this->belongsTo(\App\Models\Wallet::class, 'wallets_id');
	}
}
