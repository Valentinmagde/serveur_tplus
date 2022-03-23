<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Wallet
 * 
 * @property int $id
 * @property float $solde
 * @property string $devise
 * @property string $nom
 * @property string $description
 * @property string $etat
 * @property string $type
 * @property int $created_at
 * @property int $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $a_wallets
 * @property \Illuminate\Database\Eloquent\Collection $cash_ins_outs
 * @property \Illuminate\Database\Eloquent\Collection $t_wallets
 * @property \Illuminate\Database\Eloquent\Collection $u_wallets
 * @property \Illuminate\Database\Eloquent\Collection $w_transactions
 *
 * @package App\Models
 */
class Wallet extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'solde' => 'float',
		'created_at' => 'int',
		'updated_at' => 'int',
		'transit' => 'int',
	];

	protected $fillable = [
		'solde',
		'transit',
		'devise',
		'nom',
		'description',
		'etat',
		'type',
		'created_at',
		'updated_at'
	];

	public function a_wallets()
	{
		return $this->hasMany(\App\Models\AWallet::class, 'wallets_id');
	}

	public function cash_ins_outs()
	{
		return $this->hasMany(\App\Models\CashInsOut::class, 'wallets_id');
	}

	public function t_wallets()
	{
		return $this->hasMany(\App\Models\TWallet::class, 'wallets_id');
	}

	public function u_wallets()
	{
		return $this->hasMany(\App\Models\UWallet::class, 'wallets_id');
	}

	public function w_transactions()
	{
		return $this->hasMany(\App\Models\WTransaction::class, 'wallets_source_id');
	}
}
