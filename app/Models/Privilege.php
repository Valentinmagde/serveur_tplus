<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Privilege
 * 
 * @property int $roles_id
 * @property int $utilisateurs_id
 * @property int $membres_has_users_id
 * @property int $associations_id
 * 
 * @property \App\Models\Association $association
 * @property \App\Models\MembresHasUser $membres_has_user
 * @property \App\Models\Role $role
 * @property \App\Models\Utilisateur $utilisateur
 *
 * @package App\Models
 */
class Privilege extends Eloquent
{
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'roles_id' => 'int',
		'utilisateurs_id' => 'int',
		'membres_has_users_id' => 'int',
		'associations_id' => 'int'
	];

	protected $fillable = [
		'utilisateurs_id',
		'associations_id',
		'roles_id',
		'membres_has_users_id'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}

	public function membres_has_user()
	{
		return $this->belongsTo(\App\Models\MembresHasUser::class, 'membres_has_users_id');
	}

	public function role()
	{
		return $this->belongsTo(\App\Models\Role::class, 'roles_id');
	}

	public function utilisateur()
	{
		return $this->belongsTo(\App\Models\Utilisateur::class, 'utilisateurs_id');
	}
}
