<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class UWallet
 * 
 * @property int $id
 * @property int $wallets_id
 * @property int $utilisateurs_id
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property \App\Models\Utilisateur $utilisateur
 * @property \App\Models\Wallet $wallet
 * @property \Illuminate\Database\Eloquent\Collection $membres
 *
 * @package App\Models
 */
class UWallet extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'wallets_id' => 'int',
		'utilisateurs_id' => 'int',
		'created_at' => 'int',
		'updated_at' => 'int'
	];

	protected $fillable = [
		'wallets_id',
		'utilisateurs_id',
		'created_at',
		'updated_at'
	];

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}

	public function wallet()
	{
		return $this->belongsTo(\App\Models\Wallet::class, 'wallets_id');
	}

	public function membres()
	{
		return $this->hasMany(\App\Models\Membre::class, 'default_u_wallets_id');
	}
}
