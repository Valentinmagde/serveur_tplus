<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AWallet
 * 
 * @property int $id
 * @property int $wallets_id
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property \App\Models\Wallet $wallet
 * @property \Illuminate\Database\Eloquent\Collection $associations
 *
 * @package App\Models
 */
class AWallet extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'wallets_id' => 'int',
		'created_at' => 'int',
		'updated_at' => 'int'
	];

	protected $fillable = [
		'wallets_id',
		'created_at',
		'updated_at'
	];

	public function wallet()
	{
		return $this->belongsTo(\App\Models\Wallet::class, 'wallets_id');
	}

	public function associations()
	{
		return $this->hasMany(\App\Models\Association::class, 'a_wallets_id');
	}
}
