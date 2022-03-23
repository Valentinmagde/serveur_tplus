<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class MembresHasUser
 * 
 * @property int $id
 * @property int $utilisateurs_id
 * @property int $membres_id
 * 
 * @property \App\Models\Membre $membre
 * @property \App\Models\Utilisateur $utilisateur
 * @property \Illuminate\Database\Eloquent\Collection $privileges
 *
 * @package App\Models
 */
class MembresHasUser extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'utilisateurs_id' => 'int',
		'membres_id' => 'int'
	];

	protected $fillable = [
		'utilisateurs_id',
		'membres_id'
	];

	public function membre()
	{
		return $this->belongsTo(\App\Models\Membre::class, 'membres_id');
	}

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}

	public function privileges()
	{
		return $this->hasMany(\App\Models\Privilege::class, 'membres_has_users_id');
	}
}
